"""
main.py
========
Entry point gateway TIS MRT Jakarta.
Satu eksekusi = satu sesi download dari satu kereta.

Penggunaan:
    python main.py                          # auto-detect rake_id dari TIS
    python main.py --rake-id 5             # override rake_id manual
    python main.py --host 127.0.0.1        # test dengan mock TIS
    python main.py --no-pdf                # skip PDF
    python main.py --upload                # enable cloud upload
    python main.py --raw                   # simpan raw bytes untuk debug
"""

import argparse
import sys
import os
from datetime import datetime

sys.path.insert(0, os.path.dirname(__file__))

from config.settings import config
from protocol.session import TISSession
from exporter.csv_exporter import CSVExporter
from exporter.pdf_exporter import PDFExporter
from uploader.cloud_uploader import CloudUploader
from utils.logger import get_logger

log = get_logger("main")


def parse_args():
    parser = argparse.ArgumentParser(
        description="TIS Gateway — MRT Jakarta CP108",
        formatter_class=argparse.RawDescriptionHelpFormatter,
        epilog="""
Contoh:
  python main.py                    # auto-detect rake_id dari TIS
  python main.py --rake-id 5        # override rake_id manual
  python main.py --host 127.0.0.1   # pakai mock TIS
  python main.py --upload            # kirim ke cloud
        """,
    )
    parser.add_argument(
        "--rake-id", type=int, default=None,
        help="Override rake_id (default: auto-detect dari handshake TIS)",
    )
    parser.add_argument("--host",       type=str, default=None,   help=f"IP TIS (default: {config.network.tis_host})")
    parser.add_argument("--port",       type=int, default=None,   help=f"Port TIS (default: {config.network.tis_port})")
    parser.add_argument("--local-port", type=int, default=None,   help=f"Port lokal (default: {config.network.local_port})")
    parser.add_argument("--output-dir", type=str, default=None,   help="Direktori output (default: ./output)")
    parser.add_argument("--no-csv",     action="store_true",      help="Skip export CSV")
    parser.add_argument("--no-pdf",     action="store_true",      help="Skip export PDF")
    parser.add_argument("--upload",     action="store_true",      help="Enable cloud upload")
    parser.add_argument("--raw",        action="store_true",      help="Simpan raw bytes untuk debugging")
    return parser.parse_args()


def main():
    args = parse_args()

    if args.host:        config.network.tis_host  = args.host
    if args.port:        config.network.tis_port  = args.port
    if args.local_port:  config.network.local_port = args.local_port
    if args.output_dir:  config.output.output_dir  = args.output_dir
    if args.no_csv:      config.output.export_csv  = False
    if args.no_pdf:      config.output.export_pdf  = False
    if args.upload:      config.cloud.enabled       = True
    if args.raw:         config.output.export_raw   = True

    read_time = datetime.now()

    # ── FASE 1: Download dari TIS ─────────────────────────────────
    session = TISSession(
        rake_id    = args.rake_id,   # None = auto-detect dari TIS
        host       = config.network.tis_host,
        port       = config.network.tis_port,
        local_port = config.network.local_port,
    )
    result = session.run()

    if not result.success:
        log.error("[MAIN] Download gagal: %s", result.error_msg)
        sys.exit(1)

    # Tentukan rake_id efektif untuk output
    rake_id = result.rake_id
    if rake_id == 0:
        log.error(
            "[MAIN] rake_id tidak diketahui (TIS tidak mengirim, --rake-id tidak diberikan). "
            "Gunakan --rake-id <angka> secara manual."
        )
        sys.exit(1)

    if result.discovered_rake_id and args.rake_id is None:
        log.info("[MAIN] Menggunakan rake_id=%d (auto-detected dari TIS)", rake_id)
    elif args.rake_id is not None:
        log.info("[MAIN] Menggunakan rake_id=%d (dari --rake-id)", rake_id)

    if not result.records:
        log.warning("[MAIN] Tidak ada records yang berhasil didownload")
        sys.exit(0)

    log.info("[MAIN] Download selesai: %d records, %.1fs", result.record_count, result.duration_sec)

    exported_files = []

    # ── FASE 2: Export CSV ────────────────────────────────────────
    if config.output.export_csv:
        try:
            csv_path = CSVExporter().export(
                records    = result.records,
                rake_id    = rake_id,
                read_time  = read_time,
                output_dir = config.output.output_dir,
            )
            exported_files.append(csv_path)
            log.info("[MAIN] CSV: %s", csv_path)
        except Exception as e:
            log.error("[MAIN] Export CSV gagal: %s", e)

    # ── FASE 3: Export PDF ────────────────────────────────────────
    if config.output.export_pdf:
        try:
            pdf_path = PDFExporter().export(
                records    = result.records,
                rake_id    = rake_id,
                read_time  = read_time,
                output_dir = config.output.output_dir,
            )
            exported_files.append(pdf_path)
            log.info("[MAIN] PDF: %s", pdf_path)
        except Exception as e:
            log.error("[MAIN] Export PDF gagal: %s", e)

    # ── FASE 4: Upload ke cloud ───────────────────────────────────
    if config.cloud.enabled:
        uploader = CloudUploader()
        uploader.upload_records(
            records   = result.records,
            rake_id   = rake_id,
            read_time = read_time,
        )
        for fpath in exported_files:
            uploader.upload_file(fpath, rake_id)

    # ── Summary ───────────────────────────────────────────────────
    log.info("-" * 60)
    log.info("[DONE] rake_id=%d records=%d files=%d",
             rake_id, result.record_count, len(exported_files))
    for f in exported_files:
        log.info("  -> %s", f)
    log.info("-" * 60)


if __name__ == "__main__":
    main()
