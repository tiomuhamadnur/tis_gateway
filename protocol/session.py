"""
protocol/session.py
====================
Orkestrasi satu sesi download lengkap dari TIS.
Urutan: Handshake → CMD 0x32 → CMD 0x34 → CMD 0x36 (loop) → Done.

Class ini tidak tahu soal CSV/PDF/Cloud — tugasnya hanya
mengambil data mentah dan mengembalikan list FailureRecord.
"""

import time
from typing import List, Optional, Tuple
from dataclasses import dataclass, field

from protocol.udp_client import UDPClient
from protocol.commands import (
    build_handshake,
    build_metadata_request,
    build_dataset_b_request,
    build_failure_poll,
)
from parser.response_parser import ResponseParser, ParsedPacket, CMD_HEARTBEAT, CMD_FAILURE
from parser.record_parser import RecordParser, FailureRecord
from config.settings import config
from utils.logger import get_logger

log = get_logger(__name__)


# ─────────────────────────────────────────────
# SESSION RESULT
# ─────────────────────────────────────────────
@dataclass
class SessionResult:
    """Hasil satu sesi download dari TIS."""
    rake_id: int
    records: List[FailureRecord] = field(default_factory=list)
    success: bool = False
    error_msg: str = ""
    pages_downloaded: int = 0
    duration_sec: float = 0.0

    @property
    def record_count(self) -> int:
        return len(self.records)


# ─────────────────────────────────────────────
# SESSION
# ─────────────────────────────────────────────
class TISSession:
    """
    Satu sesi komunikasi lengkap ke TIS.
    Instantiate, panggil run(), ambil hasilnya.

    Contoh:
        session = TISSession(rake_id=5)
        result  = session.run()
        records = result.records
    """

    def __init__(
        self,
        rake_id: int,
        host: Optional[str] = None,
        port: Optional[int] = None,
        local_port: Optional[int] = None,
    ):
        self.rake_id      = rake_id
        self.host         = host       or config.network.tis_host
        self.port         = port       or config.network.tis_port
        self.local_port   = local_port or config.network.local_port
        self._resp_parser = ResponseParser()
        self._rec_parser  = RecordParser()

    # ── Public ────────────────────────────────────────────────────
    def run(self) -> SessionResult:
        """Jalankan sesi lengkap. Kembalikan SessionResult."""
        result = SessionResult(rake_id=self.rake_id)
        t_start = time.time()

        log.info(f"═══ Mulai sesi TIS — RakeID={self.rake_id} ═══")

        try:
            with UDPClient(self.host, self.port, self.local_port) as client:
                # Bersihkan buffer lama
                client.drain()

                # Fase 1: Handshake
                if not self._do_handshake(client):
                    result.error_msg = "Handshake gagal"
                    return result

                # Fase 2: CMD 0x32 — metadata
                self._do_metadata_download(client)

                # Fase 3: CMD 0x34 — dataset B
                self._do_dataset_b_download(client)

                # Fase 4: CMD 0x36 — failure records (data utama)
                records, pages = self._do_failure_download(client)
                result.records          = records
                result.pages_downloaded = pages
                result.success          = True

        except Exception as e:
            log.exception(f"Session error: {e}")
            result.error_msg = str(e)

        result.duration_sec = time.time() - t_start
        log.info(
            f"═══ Sesi selesai — {result.record_count} records, "
            f"{result.pages_downloaded} pages, "
            f"{result.duration_sec:.1f}s ═══"
        )
        return result

    # ── Fase 1: Handshake ─────────────────────────────────────────
    def _do_handshake(self, client: UDPClient) -> bool:
        log.info("Fase 1: Handshake (CMD 0x20)...")
        resp_bytes = client.send_and_receive(build_handshake())
        if not resp_bytes:
            log.error("Tidak ada response handshake dari TIS")
            return False

        packet = self._resp_parser.parse(resp_bytes)
        if not packet or packet.cmd != 0x20:
            log.error(f"Response handshake tidak valid: {resp_bytes[:8].hex()}")
            return False

        log.info(f"Handshake OK — TIS response {len(resp_bytes)}B")
        time.sleep(config.session.post_handshake_delay_sec)
        return True

    # ── Fase 2: CMD 0x32 metadata ─────────────────────────────────
    def _do_metadata_download(self, client: UDPClient):
        log.info(f"Fase 2: Metadata CMD 0x32 ({config.session.cmd32_pages} pages)...")
        for page in range(1, config.session.cmd32_pages + 1):
            resp = client.send_and_receive(build_metadata_request(page))
            if resp:
                log.debug(f"  0x32 page {page}: {len(resp)}B")
            else:
                log.warning(f"  0x32 page {page}: tidak ada response")

    # ── Fase 3: CMD 0x34 dataset B ────────────────────────────────
    def _do_dataset_b_download(self, client: UDPClient):
        log.info(f"Fase 3: Dataset B CMD 0x34 ({config.session.cmd34_pages} pages)...")
        for page in range(1, config.session.cmd34_pages + 1):
            resp = client.send_and_receive(build_dataset_b_request(page))
            if resp:
                log.debug(f"  0x34 page {page}: {len(resp)}B")
            else:
                log.warning(f"  0x34 page {page}: tidak ada response")

    # ── Fase 4: CMD 0x36 failure records ──────────────────────────
    def _do_failure_download(
        self, client: UDPClient
    ) -> Tuple[List[FailureRecord], int]:
        """
        Poll seluruh failure records.
        Tiap page di-poll sebanyak polls_per_page kali.
        Returns: (list_of_records, pages_downloaded)
        """
        cfg     = config.session
        records: List[FailureRecord] = []
        pages_ok = 0

        log.info(
            f"Fase 4: Failure records CMD 0x36 "
            f"({cfg.cmd36_pages} pages × {cfg.polls_per_page} polls)..."
        )

        block_no = 1

        for page in range(cfg.cmd36_pages):
            page_records = self._poll_one_page(client, page, block_no)

            if page_records:
                records.extend(page_records)
                block_no += len(page_records)
                pages_ok += 1
                log.debug(
                    f"  Page 0x{page:02X}: {len(page_records)} records "
                    f"(total: {len(records)})"
                )
            else:
                log.warning(f"  Page 0x{page:02X}: tidak ada data")

        return records, pages_ok

    def _poll_one_page(
        self,
        client: UDPClient,
        page: int,
        block_start: int,
    ) -> List[FailureRecord]:
        """
        Poll satu page sebanyak polls_per_page kali.
        Ambil data dari poll pertama yang berhasil.
        """
        cfg = config.session
        cmd = build_failure_poll(page)
        best_records: List[FailureRecord] = []

        for poll_no in range(cfg.polls_per_page):
            resp = client.send_and_receive(cmd)

            if not resp:
                time.sleep(cfg.poll_interval_sec)
                continue

            # Skip heartbeat
            packet = self._resp_parser.parse(resp)
            if not packet or packet.is_heartbeat:
                time.sleep(cfg.poll_interval_sec)
                continue

            if packet.cmd == CMD_FAILURE:
                page_records = self._rec_parser.parse_payload(
                    packet.payload, block_start
                )
                if page_records and not best_records:
                    best_records = page_records  # ambil dari poll pertama

            time.sleep(cfg.poll_interval_sec)

        return best_records
