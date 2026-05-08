"""
uploader/cloud_uploader.py
===========================
Upload data ke cloud REST API.
Mendukung: JSON payload, multipart file upload.
"""

import json
import os
import time
from datetime import datetime
from typing import List, Optional, Dict, Any

import urllib.request
import urllib.error

from parser.record_parser import FailureRecord
from config.settings import config
from utils.logger import get_logger

log = get_logger(__name__)


class CloudUploader:
    """
    Upload failure records dan file (CSV/PDF) ke cloud API.

    Gunakan environment variable TIS_API_KEY untuk API key.
    Atau set langsung: config.cloud.api_key = "..."
    """

    def __init__(self):
        self.base_url = config.cloud.api_base_url.rstrip("/")
        self.api_key  = config.cloud.api_key
        self.timeout  = config.cloud.http_timeout_sec

    # ── Upload records (JSON) ──────────────────────────────────────
    def upload_records(
        self,
        records: List[FailureRecord],
        rake_id: int,
        read_time: Optional[datetime] = None,
    ) -> bool:
        """
        Upload list FailureRecord ke cloud sebagai JSON.

        Payload format:
        {
          "rake_id": 5,
          "read_time": "2026-05-07T16:08:16",
          "record_count": 200,
          "records": [ {...}, {...}, ... ]
        }
        """
        if not config.cloud.enabled:
            log.info("Cloud upload dinonaktifkan (config.cloud.enabled=False)")
            return True

        read_time = read_time or datetime.now()
        url       = f"{self.base_url}{config.cloud.endpoint_failures}"

        payload = {
            "rake_id":      rake_id,
            "read_time":    read_time.isoformat(),
            "record_count": len(records),
            "records":      [r.to_dict() for r in records],
        }

        log.info(f"Upload {len(records)} records ke {url}...")
        return self._post_json(url, payload)

    # ── Upload file (CSV / PDF) ────────────────────────────────────
    def upload_file(self, filepath: str, rake_id: int) -> bool:
        """
        Upload file CSV atau PDF ke cloud.
        """
        if not config.cloud.enabled:
            log.info("Cloud upload dinonaktifkan")
            return True

        if not os.path.exists(filepath):
            log.error(f"File tidak ditemukan: {filepath}")
            return False

        url      = f"{self.base_url}{config.cloud.endpoint_files}"
        filename = os.path.basename(filepath)

        log.info(f"Upload file {filename} ke {url}...")
        return self._post_file(url, filepath, filename, rake_id)

    # ── Internal HTTP helpers ──────────────────────────────────────
    def _post_json(self, url: str, payload: dict) -> bool:
        """HTTP POST JSON dengan retry."""
        body    = json.dumps(payload).encode("utf-8")
        headers = {
            "Content-Type":  "application/json",
            "Authorization": f"Bearer {self.api_key}",
            "User-Agent":    "TIS-Gateway/1.0",
        }

        return self._request_with_retry(url, body, headers)

    def _post_file(
        self, url: str, filepath: str, filename: str, rake_id: int
    ) -> bool:
        """HTTP POST multipart/form-data."""
        boundary = "TISGatewayBoundary"
        content_type, body = self._build_multipart(
            filepath, filename, rake_id, boundary
        )
        headers = {
            "Content-Type":  content_type,
            "Authorization": f"Bearer {self.api_key}",
            "User-Agent":    "TIS-Gateway/1.0",
        }
        return self._request_with_retry(url, body, headers)

    def _build_multipart(
        self, filepath: str, filename: str, rake_id: int, boundary: str
    ):
        """Build multipart/form-data body."""
        with open(filepath, "rb") as f:
            file_data = f.read()

        ext         = os.path.splitext(filename)[1].lower()
        mime        = "text/csv" if ext == ".csv" else "application/pdf"
        body_parts  = []

        # Field: rake_id
        body_parts.append(
            f"--{boundary}\r\n"
            f'Content-Disposition: form-data; name="rake_id"\r\n\r\n'
            f"{rake_id}\r\n"
        )

        # Field: file
        body_parts.append(
            f"--{boundary}\r\n"
            f'Content-Disposition: form-data; name="file"; filename="{filename}"\r\n'
            f"Content-Type: {mime}\r\n\r\n"
        )

        body = (
            "".join(body_parts).encode("utf-8")
            + file_data
            + f"\r\n--{boundary}--\r\n".encode("utf-8")
        )

        return f"multipart/form-data; boundary={boundary}", body

    def _request_with_retry(
        self, url: str, body: bytes, headers: dict
    ) -> bool:
        """Kirim HTTP request dengan retry."""
        max_retries = config.cloud.upload_max_retries

        for attempt in range(1, max_retries + 1):
            try:
                req  = urllib.request.Request(url, data=body, headers=headers, method="POST")
                with urllib.request.urlopen(req, timeout=self.timeout) as resp:
                    status = resp.status
                    if 200 <= status < 300:
                        log.info(f"Upload berhasil — HTTP {status}")
                        return True
                    else:
                        log.warning(f"Upload gagal — HTTP {status} (attempt {attempt})")

            except urllib.error.HTTPError as e:
                log.warning(f"HTTP error {e.code}: {e.reason} (attempt {attempt})")
            except urllib.error.URLError as e:
                log.warning(f"URL error: {e.reason} (attempt {attempt})")
            except Exception as e:
                log.warning(f"Request error: {e} (attempt {attempt})")

            if attempt < max_retries:
                wait = 2 ** attempt  # exponential backoff: 2, 4, 8 detik
                log.info(f"Retry dalam {wait}s...")
                time.sleep(wait)

        log.error(f"Upload gagal setelah {max_retries} percobaan")
        return False
