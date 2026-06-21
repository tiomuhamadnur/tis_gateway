"""
config/settings.py
==================
Semua konfigurasi gateway terpusat di sini.
Edit file .env di root project, tidak perlu ubah file ini.
"""

from dataclasses import dataclass, field
from typing import Optional
import os

# Load .env jika ada (opsional - tidak error jika file tidak ada)
try:
    from dotenv import load_dotenv
    load_dotenv(override=False)  # env OS lebih prioritas
except ImportError:
    pass  # python-dotenv belum install, skip


# -----------------------------------------------------------------------------
# NETWORK - TIS Connection
# -----------------------------------------------------------------------------
@dataclass
class NetworkConfig:
    # IP CCU/MON Unit (TIS)
    tis_host: str = "127.0.0.1"

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


# -----------------------------------------------------------------------------
# SESSION - Download Parameters
# -----------------------------------------------------------------------------
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

    # Berapa kali tiap page di-poll sebelum lanjut
    polls_per_page: int = 2

    # Delay antar poll (detik)
    poll_interval_sec: float = 0.05

    # Delay setelah handshake sebelum mulai download
    post_handshake_delay_sec: float = 0.1

    @property
    def total_records(self) -> int:
        return self.cmd36_pages * self.records_per_page  # 200


# -----------------------------------------------------------------------------
# OUTPUT - File Export
# -----------------------------------------------------------------------------
@dataclass
class OutputConfig:
    # Direktori output default
    output_dir: str = "./output"

    # Format nama file: D{YYMMDD}_TS{rake_id:02d}_{HHMMSS}
    # Contoh: D260612_TS08_143522.csv / D260612_TS08_143522.pdf
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


# -----------------------------------------------------------------------------
# CLOUD - Upload API
# -----------------------------------------------------------------------------
@dataclass
class CloudConfig:
    # Upload ke cloud? Set False untuk disable
    enabled: bool = False

    # Base URL REST API cloud
    api_base_url: str = "http://127.0.0.1:8000"

    # Endpoint untuk upload failure records
    endpoint_failures: str = "/api/failures"

    # Endpoint untuk upload file (CSV/PDF)
    endpoint_files: str = "/api/files"

    # API key - sebaiknya diambil dari environment variable
    api_key: str = field(default_factory=lambda: os.getenv("TIS_API_KEY", "tiomuhamadnur"))

    # Timeout HTTP request (detik)
    http_timeout_sec: float = 30.0

    # Retry upload jika gagal
    upload_max_retries: int = 3

    # Format upload: "json" | "multipart"
    upload_format: str = "json"


# -----------------------------------------------------------------------------
# INDICATOR - Status LED / GPIO
# -----------------------------------------------------------------------------
@dataclass
class IndicatorConfig:
    # Master switch untuk seluruh LED status.
    enabled: bool = False

    # Backend GPIO: "mock" | "sysfs"
    backend: str = "mock"

    # Polarity pin: True jika output aktif-rendah.
    active_low: bool = False

    # Mapping pin GPIO. None = tidak dipakai.
    red_pin: Optional[int] = None
    yellow_pin: Optional[int] = None
    green_pin: Optional[int] = None

    # Timing visual.
    blink_interval_sec: float = 0.5
    success_pulse_sec: float = 1.5
    error_hold_sec: float = 2.5


# -----------------------------------------------------------------------------
# DAEMON - Loop & Session Management
# -----------------------------------------------------------------------------
@dataclass
class DaemonConfig:
    # Interval antar loop utama (detik)
    loop_interval_sec: int = 10

    # Minimal jarak antar sesi download (menit).
    # Jika sejak last_read_time belum melewati interval ini, download di-skip.
    tis_interval_read_data_minutes: int = 4320  # 3 hari

    # Maksimum folder yang tersimpan di output/raw/ sebelum di-prune
    max_session_raw: int = 50

    # Maksimum folder yang tersimpan di output/sent-cloud/ sebelum di-prune
    max_session_sent: int = 200

    # Maksimum retry upload cloud per sesi (di luar retry HTTP bawaan CloudUploader)
    upload_max_retries: int = 5


# -----------------------------------------------------------------------------
# LOGGING
# -----------------------------------------------------------------------------
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

    # Ukuran maksimum file log sebelum rotate (fallback jika timed rotate gagal)
    max_log_bytes: int = 10 * 1024 * 1024  # 10 MB

    # Jumlah backup file log (size-based rotation)
    log_backup_count: int = 5


# -----------------------------------------------------------------------------
# MASTER CONFIG - gabungan semua
# -----------------------------------------------------------------------------
@dataclass
class GatewayConfig:
    network: NetworkConfig = field(default_factory=NetworkConfig)
    session: SessionConfig = field(default_factory=SessionConfig)
    output: OutputConfig = field(default_factory=OutputConfig)
    cloud: CloudConfig = field(default_factory=CloudConfig)
    indicator: IndicatorConfig = field(default_factory=IndicatorConfig)
    daemon: DaemonConfig = field(default_factory=DaemonConfig)
    log: LogConfig = field(default_factory=LogConfig)


# Singleton instance - import ini di mana saja
config = GatewayConfig()


# -----------------------------------------------------------------------------
# Override dari environment variable (opsional)
# -----------------------------------------------------------------------------
def load_from_env():
    """Override config dari environment variable jika ada."""

    def _as_bool(value: str) -> bool:
        return value.lower() in ("1", "true", "yes", "on")

    val = os.getenv("TIS_HOST")
    if val:
        config.network.tis_host = val
    val = os.getenv("TIS_PORT")
    if val:
        config.network.tis_port = int(val)
    val = os.getenv("LOCAL_PORT")
    if val:
        config.network.local_port = int(val)
    val = os.getenv("OUTPUT_DIR")
    if val:
        config.output.output_dir = val
    val = os.getenv("CLOUD_API_URL")
    if val:
        config.cloud.api_base_url = val
    val = os.getenv("CLOUD_ENABLED")
    if val and _as_bool(val):
        config.cloud.enabled = True
    val = os.getenv("LED_ENABLED")
    if val:
        config.indicator.enabled = _as_bool(val)
    val = os.getenv("GPIO_BACKEND")
    if val:
        config.indicator.backend = val.strip().lower()
    val = os.getenv("GPIO_ACTIVE_LOW")
    if val:
        config.indicator.active_low = _as_bool(val)
    val = os.getenv("LED_RED_PIN")
    if val:
        config.indicator.red_pin = int(val)
    val = os.getenv("LED_YELLOW_PIN")
    if val:
        config.indicator.yellow_pin = int(val)
    val = os.getenv("LED_GREEN_PIN")
    if val:
        config.indicator.green_pin = int(val)
    val = os.getenv("LED_BLINK_INTERVAL_SEC")
    if val:
        config.indicator.blink_interval_sec = float(val)
    val = os.getenv("LED_SUCCESS_PULSE_SEC")
    if val:
        config.indicator.success_pulse_sec = float(val)
    val = os.getenv("LED_ERROR_HOLD_SEC")
    if val:
        config.indicator.error_hold_sec = float(val)
    val = os.getenv("TIS_INTERVAL_READ_DATA")
    if val:
        config.daemon.tis_interval_read_data_minutes = int(val)
    val = os.getenv("MAX_SESSION_RAW")
    if val:
        config.daemon.max_session_raw = int(val)
    val = os.getenv("MAX_SESSION_SENT")
    if val:
        config.daemon.max_session_sent = int(val)
    val = os.getenv("LOOP_INTERVAL_SEC")
    if val:
        config.daemon.loop_interval_sec = int(val)
    val = os.getenv("UPLOAD_MAX_RETRIES")
    if val:
        config.daemon.upload_max_retries = int(val)
    val = os.getenv("LOG_LEVEL")
    if val:
        config.log.level = val
    val = os.getenv("LOG_RETENTION_DAYS")
    if val:
        config.log.log_retention_days = int(val)
    val = os.getenv("LOG_MAX_BYTES")
    if val:
        config.log.max_log_bytes = int(val)
    val = os.getenv("LOG_BACKUP_COUNT")
    if val:
        config.log.log_backup_count = int(val)


load_from_env()
