"""
exporter/json_exporter.py
==========================
Export hasil download TIS ke file JSON.
File JSON ini menjadi source of truth untuk upload ke cloud.
"""

import json
import os
from datetime import datetime
from typing import List, Optional

from parsers.record_parser import FailureRecord
from config.settings import config
from utils.logger import get_logger

log = get_logger(__name__)


class JSONExporter:
    """Export list FailureRecord ke file JSON."""

    def export(
        self,
        records: List[FailureRecord],
        rake_id: int,
        read_time: Optional[datetime] = None,
        output_dir: Optional[str] = None,
    ) -> str:
        read_time = read_time or datetime.now()
        output_dir = output_dir or config.output.output_dir
        os.makedirs(output_dir, exist_ok=True)

        filename = self._generate_filename(read_time, rake_id)
        filepath = os.path.join(output_dir, filename)

        payload = {
            "rake_id": rake_id,
            "read_time": read_time.isoformat(),
            "record_count": len(records),
            "records": [r.to_dict() for r in records],
        }

        with open(filepath, "w", encoding="utf-8") as f:
            json.dump(payload, f, indent=2, ensure_ascii=False)

        log.info(f"JSON disimpan: {filepath} ({len(records)} records)")
        return filepath

    @staticmethod
    def load(filepath: str) -> Optional[dict]:
        """Load file JSON hasil export kembali ke dict."""
        try:
            with open(filepath, "r", encoding="utf-8") as f:
                return json.load(f)
        except Exception as e:
            log.error(f"Gagal load JSON {filepath}: {e}")
            return None

    @staticmethod
    def _generate_filename(dt: datetime, rake_id: int) -> str:
        date = dt.strftime("%y%m%d")
        time = dt.strftime("%H%M%S")
        return f"records_{date}_{time}_rake{rake_id:02d}.json"
