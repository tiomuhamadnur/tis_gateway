"""
main.py
========
Entry point gateway TIS MRT Jakarta.
Satu eksekusi = satu sesi download dari satu kereta.

Penggunaan:
    python main.py --rake-id 5
    python main.py --rake-id 5 --host 192.168.1.1 --output-dir ./output
    python main.py --rake-id 5 --host 127.0.0.1   # test dengan mock TIS
    python main.py --rake-id 5 --no-pdf            # skip PDF
    python main.py --rake-id 5 --upload            # enable cloud upload
"""

import argparse
import sys
import os
from datetime import datetime

# Pastikan root project ada di sys.path
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
  python main.py --rake-id 5
  python main.py --rake-id 5 --host 127.0.0.1   # pakai mock TIS
  python main.py --rake-id 5 --upload            # kirim ke cloud
        """,
    )
    parser.add_argument("--rake-id",    type=int, required=True,  help="ID rangkaian kereta (misal: 5)")
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

    # ── Override config dari argumen ──────────────────────────────
    if args.host:
        config.network.tis_host = args.host
    if args.port:
        config.network.tis_port = args.port
    if args.local_port:
        config.network.local_port = args.local_port
    if args.output_dir:
        config.output.output_dir = args.output_dir
    if args.no_csv:
        config.output.export_csv = False
    if args.no_pdf:
        config.output.export_pdf = False
    if args.upload:
        config.cloud.enabled = True
    if args.raw:
        config.output.export_raw = True

    read_time = datetime.now()

    log.info("=" * 60)
    log.info(f"TIS Gateway — RakeID={args.rake_id}")
    log.info(f"Target : {config.network.tis_host}:{config.network.tis_port}")
    log.info(f"Output : {config.output.output_dir}")
    log.info(f"Upload : {'Ya' if config.cloud.enabled else 'Tidak'}")
    log.info("=" * 60)

    # ── FASE 1: Download dari TIS ─────────────────────────────────
    session = TISSession(
        rake_id    = args.rake_id,
        host       = config.network.tis_host,
        port       = config.network.tis_port,
        local_port = config.network.local_port,
    )
    result = session.run()

    if not result.success:
        log.error(f"Download gagal: {result.error_msg}")
        sys.exit(1)

    if not result.records:
        log.warning("Tidak ada records yang berhasil didownload")
        sys.exit(0)

    log.info(f"Download selesai: {result.record_count} records dalam {result.duration_sec:.1f}s")

    exported_files = []

    # ── FASE 2: Export CSV ────────────────────────────────────────
    if config.output.export_csv:
        try:
            csv_path = CSVExporter().export(
                records    = result.records,
                rake_id    = args.rake_id,
                read_time  = read_time,
                output_dir = config.output.output_dir,
            )
            exported_files.append(csv_path)
        except Exception as e:
            log.error(f"Export CSV gagal: {e}")

    # ── FASE 3: Export PDF ────────────────────────────────────────
    if config.output.export_pdf:
        try:
            pdf_path = PDFExporter().export(
                records    = result.records,
                rake_id    = args.rake_id,
                read_time  = read_time,
                output_dir = config.output.output_dir,
            )
            exported_files.append(pdf_path)
        except Exception as e:
            log.error(f"Export PDF gagal: {e}")

    # ── FASE 4: Upload ke cloud ───────────────────────────────────
    if config.cloud.enabled:
        uploader = CloudUploader()

        # Upload records sebagai JSON
        uploader.upload_records(
            records   = result.records,
            rake_id   = args.rake_id,
            read_time = read_time,
        )

        # Upload file CSV dan PDF
        for fpath in exported_files:
            uploader.upload_file(fpath, args.rake_id)

    # ── Summary ───────────────────────────────────────────────────
    log.info("─" * 60)
    log.info(f"✅ Selesai — {result.record_count} records")
    for f in exported_files:
        log.info(f"   📄 {f}")
    log.info("─" * 60)


if __name__ == "__main__":
    main()
