"""
exporter/csv_exporter.py
=========================
Generate file CSV format identik dengan output PTU Sumitomo.
Sumber referensi: D260507_282.csv

Format file:
  Baris 1-7: Header metadata
  Baris 8+:  Data records (Block.No, Year, Month, Day, ...)
"""

import csv
import os
from datetime import datetime
from typing import List, Optional

from parsers.record_parser import FailureRecord
from config.settings import config
from utils.logger import get_logger

log = get_logger(__name__)


class CSVExporter:
    """Export list FailureRecord ke file CSV format PTU Sumitomo."""

    # Header kolom sesuai format PTU
    COLUMNS = [
        "Block.No", "Year", "Month", "Day", "Hour", "Minute", "Second",
        "CarNo", "Train ID", "Occur/Recover", "Location[m]",
        "Failure Equipment", "Fault Code", "Notch", "Speed[km/h]",
        "Overhead Voltage[V]",
    ]

    def export(
        self,
        records: List[FailureRecord],
        rake_id: int,
        read_time: Optional[datetime] = None,
        output_dir: Optional[str] = None,
        filename: Optional[str] = None,
    ) -> str:
        """
        Export records ke CSV.

        Args:
            records:    List FailureRecord hasil download
            rake_id:    ID rangkaian kereta
            read_time:  Waktu download (default: sekarang)
            output_dir: Direktori output (default dari config)
            filename:   Nama file custom (default: auto-generate)

        Returns:
            Path file CSV yang dibuat
        """
        read_time  = read_time or datetime.now()
        output_dir = output_dir or config.output.output_dir
        os.makedirs(output_dir, exist_ok=True)

        filename = filename or self._generate_filename(read_time, rake_id)
        filepath = os.path.join(output_dir, filename)

        with open(filepath, "w", newline="", encoding=config.output.csv_encoding) as f:
            writer = csv.writer(f)

            # ── Metadata header (format identik PTU, ref: D260507_282.csv) ──
            writer.writerow(["Name:", "MRTJ Failure History(Formation)"])
            writer.writerow(["RakeID",    rake_id])
            writer.writerow(["CarID",     "-"])
            writer.writerow(["CarNo",     "-"])
            writer.writerow(["ReadTime",  read_time.strftime("%y-%m-%d %H:%M:%S")])
            writer.writerow(["DataSize",  len(self.COLUMNS) - 1])
            writer.writerow(["DataCount", len(records)])
            for _ in range(10):
                writer.writerow(["-", ""])
            writer.writerow([""] * (len(self.COLUMNS) + 1))

            # ── Header kolom ─────────────────────────────────────
            writer.writerow(self.COLUMNS + [""])

            # ── Data rows ────────────────────────────────────────
            for rec in records:
                writer.writerow(rec.to_csv_row() + [""])

        log.info(f"CSV disimpan: {filepath} ({len(records)} records)")
        return filepath

    def _generate_filename(self, dt: datetime, rake_id: int) -> str:
        """
        Generate nama file sesuai konvensi PTU.
        Format: D{YYMMDD}_{rake_id:03d}.csv
        Contoh: D260507_005.csv
        """
        prefix = config.output.filename_prefix
        date   = dt.strftime("%y%m%d")
        return f"{prefix}{date}_{rake_id:03d}.csv"

