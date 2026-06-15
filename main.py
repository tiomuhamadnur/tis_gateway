"""
main.py
========
Entry point gateway TIS MRT Jakarta — DAEMON MODE.
Berjalan terus-menerus: ping TIS → download → generate → upload → prune.

Penggunaan:
    python main.py                              # daemon mode
    python main.py --once                       # one-shot (legacy)
    python main.py --rake-id 5 --once           # one-shot dengan override
"""

import argparse
import logging
import os
import platform
import signal
import shutil
import subprocess
import sys
import time
from datetime import datetime, timedelta
from typing import List, Optional

sys.path.insert(0, os.path.dirname(__file__))

from config.settings import config
from protocol.session import TISSession, SessionResult
from parsers.record_parser import FailureRecord
from exporter.csv_exporter import CSVExporter
from exporter.pdf_exporter import PDFExporter
from exporter.json_exporter import JSONExporter
from uploader.cloud_uploader import CloudUploader
from utils.logger import get_logger, log_throttled

log = get_logger("main")

_shutdown = False


# ── Signal Handling ─────────────────────────────────────────────────
def _handle_signal(signum, frame):
    global _shutdown
    sig_name = signal.Signals(signum).name
    log.info("[MAIN] Menerima signal %s, shutdown graceful...", sig_name)
    _shutdown = True


def _setup_signal_handlers():
    for sig in (signal.SIGINT, signal.SIGTERM):
        try:
            signal.signal(sig, _handle_signal)
        except (ValueError, OSError):
            pass


def _sleep_or_shutdown(sec: int):
    """Sleep dengan interval kecil agar signal terdeteksi."""
    for _ in range(sec):
        if _shutdown:
            break
        time.sleep(1)


# ── Pre-flight Helpers ──────────────────────────────────────────────
LAST_TS_FILE = ".last_read_timestamp"


def _check_host_reachable(host: str) -> bool:
    """Kirim ICMP ping. Localhost selalu dianggap reachable."""
    if host in ("127.0.0.1", "localhost", "0.0.0.0"):
        return True
    try:
        is_win = platform.system().lower() == "windows"
        cmd = ["ping", "-n", "1", "-w", "2000", host] if is_win else ["ping", "-c", "1", "-W", "2", host]
        return subprocess.run(cmd, capture_output=True, timeout=5).returncode == 0
    except Exception:
        return False


def _read_last_timestamp(output_dir: str) -> Optional[datetime]:
    path = os.path.join(output_dir, LAST_TS_FILE)
    try:
        with open(path, "r") as f:
            return datetime.fromisoformat(f.read().strip())
    except (FileNotFoundError, ValueError):
        return None


def _write_last_timestamp(output_dir: str, dt: Optional[datetime] = None):
    path = os.path.join(output_dir, LAST_TS_FILE)
    try:
        dt = dt or datetime.now()
        with open(path, "w") as f:
            f.write(dt.isoformat())
    except Exception as e:
        log.warning("[MAIN] Gagal menulis last_timestamp: %s", e)


def _is_within_interval(output_dir: str) -> bool:
    last = _read_last_timestamp(output_dir)
    if last is None:
        return False
    elapsed = datetime.now() - last
    interval = timedelta(minutes=config.daemon.tis_interval_read_data_minutes)
    if elapsed < interval:
        log.debug(
            "[MAIN] Skip download — last_read=%s, elapsed=%.1fm < interval=%dm",
            last.strftime("%Y-%m-%d %H:%M"),
            elapsed.total_seconds() / 60,
            config.daemon.tis_interval_read_data_minutes,
        )
        return True
    return False


# ── Session Folder ──────────────────────────────────────────────────
RAW_DIR = "raw"
SENT_DIR = "sent-cloud"


def _session_folder_name(read_time: datetime, rake_id: int) -> str:
    return read_time.strftime("%Y%m%d-%H%M%S") + f"_rake{rake_id:02d}"


def _ensure_dirs(base: str):
    for sub in (RAW_DIR, SENT_DIR):
        os.makedirs(os.path.join(base, sub), exist_ok=True)


def _raw_path(base: str, folder: str) -> str:
    return os.path.join(base, RAW_DIR, folder)


def _sent_path(base: str, folder: str) -> str:
    return os.path.join(base, SENT_DIR, folder)


# ── Generate Files ──────────────────────────────────────────────────
def _generate_session_files(
    result: SessionResult, read_time: datetime, session_dir: str
) -> List[str]:
    files = []
    records = result.records
    rake_id = result.rake_id

    # JSON (source of truth)
    try:
        p = JSONExporter().export(records, rake_id, read_time, session_dir)
        files.append(p)
    except Exception as e:
        log.error("[MAIN] Export JSON gagal: %s", e)

    # CSV
    if config.output.export_csv:
        try:
            p = CSVExporter().export(records, rake_id, read_time, session_dir)
            files.append(p)
        except Exception as e:
            log.error("[MAIN] Export CSV gagal: %s", e)

    # PDF
    if config.output.export_pdf:
        try:
            p = PDFExporter().export(records, rake_id, read_time, session_dir)
            files.append(p)
        except Exception as e:
            log.error("[MAIN] Export PDF gagal: %s", e)

    return files


# ── Upload & Move ───────────────────────────────────────────────────
def _upload_session(session_dir: str, result: SessionResult) -> bool:
    """Upload JSON records + CSV/PDF ke cloud. Return True jika semua berhasil."""
    if not config.cloud.enabled:
        log.info("[MAIN] Cloud upload disabled, skip upload")
        return True

    uploader = CloudUploader()
    records = result.records
    rake_id = result.rake_id
    read_time = datetime.now()

    # 1. Upload JSON records
    session_response = uploader.upload_records(records, rake_id, read_time)
    if not session_response:
        log.warning("[MAIN] Upload JSON records gagal — akan di-retry nanti")
        return False

    session_id = session_response.get("session_id")
    is_duplicate = bool(session_response.get("status") == "duplicate")

    if is_duplicate:
        log.info("[MAIN] Session duplikat, upload file dilewati")
        return True

    if not session_id:
        log.warning("[MAIN] session_id tidak tersedia dari response")
        return True

    # 2. Upload CSV / PDF files
    for fname in os.listdir(session_dir):
        if not (fname.endswith(".csv") or fname.endswith(".pdf")):
            continue
        fpath = os.path.join(session_dir, fname)
        ok = uploader.upload_file(fpath, rake_id, session_id)
        if not ok:
            log.warning("[MAIN] Upload file %s gagal", fname)

    return True


def _move_to_sent(base: str, folder: str):
    src = _raw_path(base, folder)
    dst = _sent_path(base, folder)
    try:
        shutil.move(src, dst)
        log.info("[MAIN] Pindah %s → sent-cloud/", folder)
    except Exception as e:
        log.error("[MAIN] Gagal pindah %s ke sent-cloud: %s", folder, e)


def _is_sent(base: str, folder: str) -> bool:
    return os.path.isdir(_sent_path(base, folder))


# ── Retry Pending Sessions ──────────────────────────────────────────
def _retry_pending_sessions(base: str):
    raw_dir = os.path.join(base, RAW_DIR)
    if not os.path.isdir(raw_dir):
        return

    if not config.cloud.enabled:
        return

    folders = sorted(
        [f for f in os.listdir(raw_dir) if os.path.isdir(os.path.join(raw_dir, f))]
    )

    for folder in folders:
        if _shutdown:
            break
        session_dir = _raw_path(base, folder)

        # Cari file JSON
        json_files = [f for f in os.listdir(session_dir) if f.startswith("records_") and f.endswith(".json")]
        if not json_files:
            continue

        data = JSONExporter.load(os.path.join(session_dir, json_files[0]))
        if not data:
            continue

        rake_id = data.get("rake_id", 0)
        records_list = data.get("records", [])
        read_time_str = data.get("read_time", datetime.now().isoformat())

        if not records_list:
            continue

        # Rebuild result object (minimal)
        try:
            read_time = datetime.fromisoformat(read_time_str)
        except Exception:
            read_time = datetime.now()

        records = [FailureRecord.from_dict(r) for r in records_list]
        # Fake session result
        fake_result = SessionResult(
            rake_id=rake_id,
            records=records,
            success=True,
        )

        log.info("[MAIN] Retry upload session %s (%d records)...", folder, len(records))
        ok = _upload_session(session_dir, fake_result)
        if ok:
            _move_to_sent(base, folder)
            _prune_directories(os.path.join(base, RAW_DIR), config.daemon.max_session_raw)
            _prune_directories(os.path.join(base, SENT_DIR), config.daemon.max_session_sent)


# ── Prune ───────────────────────────────────────────────────────────
def _prune_directories(path: str, max_count: int):
    if not os.path.isdir(path):
        return
    entries = sorted(
        [d for d in os.listdir(path) if os.path.isdir(os.path.join(path, d))]
    )
    if len(entries) <= max_count:
        return
    to_delete = entries[: len(entries) - max_count]
    for d in to_delete:
        full = os.path.join(path, d)
        try:
            shutil.rmtree(full)
            log.info("[MAIN] Prune %s — hapus %s", path, d)
        except Exception as e:
            log.warning("[MAIN] Gagal prune %s: %s", full, e)


# ── Argument Parsing ────────────────────────────────────────────────
def parse_args():
    parser = argparse.ArgumentParser(
        description="TIS Gateway — MRT Jakarta CP108 (Daemon Mode)",
    )
    parser.add_argument("--once", action="store_true", help="One-shot mode (legacy — tidak loop)")
    parser.add_argument("--rake-id", type=int, default=None, help="Override rake_id")
    parser.add_argument("--host", type=str, default=None, help="IP TIS")
    parser.add_argument("--port", type=int, default=None, help="Port TIS")
    parser.add_argument("--local-port", type=int, default=None, help="Port lokal")
    parser.add_argument("--output-dir", type=str, default=None, help="Direktori output")
    parser.add_argument("--no-csv", action="store_true", help="Skip CSV")
    parser.add_argument("--no-pdf", action="store_true", help="Skip PDF")
    parser.add_argument("--upload", action="store_true", help="Aktifkan upload")
    parser.add_argument("--raw", action="store_true", help="Simpan raw bytes (debug)")
    return parser.parse_args()


def _apply_args(args):
    if args.host:        config.network.tis_host = args.host
    if args.port:        config.network.tis_port = args.port
    if args.local_port:  config.network.local_port = args.local_port
    if args.output_dir:  config.output.output_dir = args.output_dir
    if args.no_csv:      config.output.export_csv = False
    if args.no_pdf:      config.output.export_pdf = False
    if args.upload:      config.cloud.enabled = True
    if args.raw:         config.output.export_raw = True


# ── One-shot Mode (Legacy) ──────────────────────────────────────────
def _run_one_shot(args):
    log.info("[MAIN] One-shot mode")
    result = _run_session(args)
    if not result or not result.success:
        sys.exit(1)
    rake_id = result.rake_id
    read_time = datetime.now()
    base = config.output.output_dir
    folder = _session_folder_name(read_time, rake_id)
    session_dir = _raw_path(base, folder)
    os.makedirs(session_dir, exist_ok=True)

    _generate_session_files(result, read_time, session_dir)

    if config.cloud.enabled:
        ok = _upload_session(session_dir, result)
        if ok:
            _move_to_sent(base, folder)

    log.info("[DONE] rake_id=%d records=%d", rake_id, result.record_count)


# ── Full Session ────────────────────────────────────────────────────
def _run_session(args) -> Optional[SessionResult]:
    session = TISSession(
        rake_id=args.rake_id,
        host=config.network.tis_host,
        port=config.network.tis_port,
        local_port=config.network.local_port,
    )
    result = session.run()

    if not result.success:
        log.error("[MAIN] Download gagal: %s", result.error_msg)
        return None

    rake_id = result.rake_id
    if rake_id == 0:
        log.error("[MAIN] rake_id=0 — tidak diketahui. Gunakan --rake-id")
        return None

    if not result.records:
        log.warning("[MAIN] Tidak ada records — skip")
        return None

    log.info("[MAIN] Download selesai: %d records dalam %.1fs", result.record_count, result.duration_sec)
    return result


# ── Main ────────────────────────────────────────────────────────────
def main():
    args = parse_args()
    _apply_args(args)

    if args.once:
        _run_one_shot(args)
        return

    # ── Daemon Mode ────────────────────────────────────────────────
    _setup_signal_handlers()
    base = config.output.output_dir
    _ensure_dirs(base)

    log.info("=" * 60)
    log.info("[MAIN] TIS Gateway Daemon — start")
    log.info("[MAIN] Host=%s:%d | Interval=%d menit | Raw=%d | Sent=%d",
             config.network.tis_host, config.network.tis_port,
             config.daemon.tis_interval_read_data_minutes,
             config.daemon.max_session_raw, config.daemon.max_session_sent)
    log.info("=" * 60)

    while not _shutdown:
        # ── Phase 0: Pastikan TIS reachable ────────────────────────
        if not _check_host_reachable(config.network.tis_host):
            log_throttled(log, logging.INFO, "[MAIN] TIS unreachable — retry dalam %ds...", 60, config.daemon.loop_interval_sec)
            _sleep_or_shutdown(config.daemon.loop_interval_sec)
            continue

        # ── Phase 0b: Retry pending sessions ───────────────────────
        _retry_pending_sessions(base)

        # ── Phase 1: Cek interval ──────────────────────────────────
        if _is_within_interval(base):
            _sleep_or_shutdown(config.daemon.loop_interval_sec)
            continue

        # ── Phase 2: Download ──────────────────────────────────────
        result = _run_session(args)
        if not result:
            _sleep_or_shutdown(config.daemon.loop_interval_sec)
            continue

        read_time = datetime.now()
        rake_id = result.rake_id
        folder = _session_folder_name(read_time, rake_id)
        session_dir = _raw_path(base, folder)
        os.makedirs(session_dir, exist_ok=True)

        # ── Phase 3: Generate files ────────────────────────────────
        _generate_session_files(result, read_time, session_dir)

        # ── Phase 4: Upload ───────────────────────────────────────
        upload_ok = _upload_session(session_dir, result)
        if upload_ok:
            _move_to_sent(base, folder)
            _write_last_timestamp(base, read_time)
        else:
            log.info("[MAIN] Upload gagal — session tetap di raw/ untuk retry nanti")

        # ── Phase 5: Prune ─────────────────────────────────────────
        _prune_directories(os.path.join(base, RAW_DIR), config.daemon.max_session_raw)
        _prune_directories(os.path.join(base, SENT_DIR), config.daemon.max_session_sent)

        # ── Phase 6: Sleep ─────────────────────────────────────────
        if not _shutdown:
            log.debug("[MAIN] Loop selesai — tidur %ds...", config.daemon.loop_interval_sec)
            _sleep_or_shutdown(config.daemon.loop_interval_sec)

    log.info("[MAIN] Shutdown selesai.")


if __name__ == "__main__":
    main()
