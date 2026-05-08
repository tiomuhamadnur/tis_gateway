"""
config/settings.py
==================
Semua konfigurasi gateway terpusat di sini.
Edit file ini untuk menyesuaikan environment tanpa ubah kode.
"""

from dataclasses import dataclass, field
from typing import Optional
import os


# ─────────────────────────────────────────────
# NETWORK — TIS Connection
# ─────────────────────────────────────────────
@dataclass
class NetworkConfig:
    # IP CCU/MON Unit (TIS)
    tis_host: str = "192.168.1.1"

    # Port TIS mengirim data
    tis_port: int = 262

    # Port lokal gateway (PTU listen di 263)
    local_port: int = 263

    # Timeout menunggu response TIS (detik)
    recv_timeout_sec: float = 3.0

    # Max retry jika TIS tidak response
    max_retries: int = 3

    # Delay antar retry (detik)
    retry_delay_sec: float = 0.5

    # Buffer size UDP socket (bytes)
    recv_buffer_size: int = 4096


# ─────────────────────────────────────────────
# SESSION — Download Parameters
# ─────────────────────────────────────────────
@dataclass
class SessionConfig:
    # Jumlah pages untuk CMD 0x32 (metadata)
    cmd32_pages: int = 6

    # Jumlah pages untuk CMD 0x34 (data set B)
    cmd34_pages: int = 6

    # Jumlah pages untuk CMD 0x36 (failure records)
    cmd36_pages: int = 40

    # Records per page (CMD 0x36)
    records_per_page: int = 5

    # Berapa kali tiap page di-poll sebelum lanjut (dari pcap: 3x)
    polls_per_page: int = 3

    # Delay antar poll (detik) — jangan terlalu cepat
    poll_interval_sec: float = 0.1

    # Delay setelah handshake sebelum mulai download
    post_handshake_delay_sec: float = 0.1

    @property
    def total_records(self) -> int:
        return self.cmd36_pages * self.records_per_page  # 200


# ─────────────────────────────────────────────
# OUTPUT — File Export
# ─────────────────────────────────────────────
@dataclass
class OutputConfig:
    # Direktori output default
    output_dir: str = "./output"

    # Format nama file: {rake_id}_{date}_{time}
    # Contoh: D260507_005.csv / D260507_005.pdf
    filename_prefix: str = "D"

    # Generate CSV?
    export_csv: bool = True

    # Generate PDF?
    export_pdf: bool = True

    # Simpan raw bytes ke file .bin untuk debugging?
    export_raw: bool = False

    # Encoding CSV
    csv_encoding: str = "utf-8"

    # PDF page size
    pdf_page_size: str = "A4"


# ─────────────────────────────────────────────
# CLOUD — Upload API
# ─────────────────────────────────────────────
@dataclass
class CloudConfig:
    # Upload ke cloud? Set False untuk disable
    enabled: bool = False

    # Base URL REST API cloud
    api_base_url: str = "https://api.example.com"

    # Endpoint untuk upload failure records
    endpoint_failures: str = "/v1/failures"

    # Endpoint untuk upload file (CSV/PDF)
    endpoint_files: str = "/v1/files"

    # API key — sebaiknya diambil dari environment variable
    api_key: str = field(default_factory=lambda: os.getenv("TIS_API_KEY", ""))

    # Timeout HTTP request (detik)
    http_timeout_sec: float = 30.0

    # Retry upload jika gagal
    upload_max_retries: int = 3

    # Format upload: "json" | "multipart"
    upload_format: str = "json"


# ─────────────────────────────────────────────
# LOGGING
# ─────────────────────────────────────────────
@dataclass
class LogConfig:
    # Level: DEBUG | INFO | WARNING | ERROR
    level: str = "INFO"

    # Log ke file?
    log_to_file: bool = True

    # Direktori log
    log_dir: str = "./logs"

    # Format: "text" | "json"
    log_format: str = "text"

    # Rotasi log harian, simpan N hari
    log_retention_days: int = 30


# ─────────────────────────────────────────────
# MASTER CONFIG — gabungan semua
# ─────────────────────────────────────────────
@dataclass
class GatewayConfig:
    network: NetworkConfig = field(default_factory=NetworkConfig)
    session: SessionConfig = field(default_factory=SessionConfig)
    output: OutputConfig   = field(default_factory=OutputConfig)
    cloud: CloudConfig     = field(default_factory=CloudConfig)
    log: LogConfig         = field(default_factory=LogConfig)


# Singleton instance — import ini di mana saja
config = GatewayConfig()


# ─────────────────────────────────────────────
# Override dari environment variable (opsional)
# ─────────────────────────────────────────────
def load_from_env():
    """Override config dari environment variable jika ada."""
    if val := os.getenv("TIS_HOST"):
        config.network.tis_host = val
    if val := os.getenv("TIS_PORT"):
        config.network.tis_port = int(val)
    if val := os.getenv("LOCAL_PORT"):
        config.network.local_port = int(val)
    if val := os.getenv("OUTPUT_DIR"):
        config.output.output_dir = val
    if val := os.getenv("CLOUD_API_URL"):
        config.cloud.api_base_url = val
        config.cloud.enabled = True
    if val := os.getenv("LOG_LEVEL"):
        config.log.level = val


load_from_env()
