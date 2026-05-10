"""
protocol/session.py
====================
Orkestrasi satu sesi download lengkap dari TIS.
Urutan: Handshake → CMD 0x32 → CMD 0x34 → CMD 0x36 (loop) → Done.

Rake ID auto-detection:
  TIS mengirim rake_id di handshake response payload[5].
  Dikonfirmasi dari PCAP TS5: payload[5] = 0x05 = Rake 5.
  Jika tidak terdeteksi (payload terlalu pendek / nilai 0), sesi berjalan
  tanpa rake_id dan caller harus menyediakan nilai fallback.
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
from parsers.response_parser import ResponseParser, ParsedPacket, CMD_HEARTBEAT, CMD_FAILURE
from parsers.record_parser import RecordParser, FailureRecord
from config.settings import config
from utils.logger import get_logger

log = get_logger(__name__)

# Offset dalam handshake response payload (setelah header 8B) di mana rake_id tersimpan.
# Dikonfirmasi dari PCAP TS5: full_packet[13] = payload[5] = 0x05 = Rake 5.
HANDSHAKE_RAKE_OFFSET = 5


# ─────────────────────────────────────────────
# SESSION RESULT
# ─────────────────────────────────────────────
@dataclass
class SessionResult:
    """Hasil satu sesi download dari TIS."""
    rake_id: int                            # rake_id yang dipakai (dari user atau auto-detect)
    discovered_rake_id: Optional[int] = None  # rake_id yang dideteksi dari TIS handshake
    records: List[FailureRecord] = field(default_factory=list)
    success: bool = False
    error_msg: str = ""
    pages_downloaded: int = 0
    duration_sec: float = 0.0

    @property
    def record_count(self) -> int:
        return len(self.records)

    def summary_line(self) -> str:
        """Satu baris ringkas untuk log — mudah dibaca Claude."""
        rake_src = "user" if self.discovered_rake_id is None else (
            "auto" if self.rake_id == self.discovered_rake_id else "user(override)"
        )
        return (
            "[SESSION_RESULT] success=%s rake_id=%d(%s) discovered=%s "
            "records=%d pages=%d duration=%.1fs error=%r"
        ) % (
            self.success, self.rake_id, rake_src,
            str(self.discovered_rake_id),
            self.record_count, self.pages_downloaded,
            self.duration_sec, self.error_msg or "none",
        )


# ─────────────────────────────────────────────
# SESSION
# ─────────────────────────────────────────────
class TISSession:
    """
    Satu sesi komunikasi lengkap ke TIS.
    Instantiate, panggil run(), ambil hasilnya.

    rake_id opsional — jika None, akan di-detect dari handshake TIS response.
    Jika TIS tidak mengirim rake_id valid, rake_id akan bernilai 0 dan
    caller wajib menyediakan fallback.

    Contoh:
        session = TISSession()           # auto-detect rake_id
        session = TISSession(rake_id=5)  # pakai rake_id manual
        result  = session.run()
        print(result.discovered_rake_id) # rake yang dilaporkan TIS
    """

    def __init__(
        self,
        rake_id: Optional[int] = None,
        host: Optional[str] = None,
        port: Optional[int] = None,
        local_port: Optional[int] = None,
    ):
        self._user_rake_id  = rake_id
        self.host           = host       or config.network.tis_host
        self.port           = port       or config.network.tis_port
        self.local_port     = local_port or config.network.local_port
        self._resp_parser   = ResponseParser()
        self._rec_parser    = RecordParser()
        self._discovered_rake_id: Optional[int] = None

    @property
    def rake_id(self) -> int:
        """rake_id efektif: user override jika ada, otherwise discovered, otherwise 0."""
        if self._user_rake_id is not None:
            return self._user_rake_id
        return self._discovered_rake_id or 0

    # ── Public ────────────────────────────────────────────────────
    def run(self) -> SessionResult:
        """Jalankan sesi lengkap. Kembalikan SessionResult."""
        t_start = time.time()

        log.info("=" * 60)
        log.info("[SESSION_START] host=%s:%d local=%d rake_id=%s",
                 self.host, self.port, self.local_port,
                 str(self._user_rake_id) if self._user_rake_id is not None else "auto-detect")
        log.info("=" * 60)

        result = SessionResult(rake_id=0)

        try:
            with UDPClient(self.host, self.port, self.local_port) as client:
                client.drain()

                # Fase 1: Handshake (juga auto-detect rake_id)
                if not self._do_handshake(client):
                    result.error_msg = "Handshake gagal"
                    result.duration_sec = time.time() - t_start
                    log.error(result.summary_line())
                    return result

                result.rake_id = self.rake_id
                result.discovered_rake_id = self._discovered_rake_id

                # Fase 2-3: metadata & dataset B
                self._do_metadata_download(client)
                self._do_dataset_b_download(client)

                # Fase 4: failure records
                records, pages = self._do_failure_download(client)
                result.records          = records
                result.pages_downloaded = pages
                result.success          = True

        except Exception as e:
            log.exception("[SESSION_ERROR] %s", e)
            result.error_msg = str(e)

        result.duration_sec = time.time() - t_start
        log.info(result.summary_line())
        return result

    # ── Fase 1: Handshake ─────────────────────────────────────────
    def _do_handshake(self, client: UDPClient) -> bool:
        log.info("[HS] Kirim handshake CMD 0x20...")
        resp_bytes = client.send_and_receive(build_handshake())

        if not resp_bytes:
            log.error("[HS] Tidak ada response dari TIS")
            return False

        log.debug("[HS_RAW] len=%d hex=%s", len(resp_bytes), resp_bytes.hex())

        packet = self._resp_parser.parse(resp_bytes)
        if not packet or packet.cmd != 0x20:
            log.error("[HS] Response tidak valid: %s", resp_bytes[:8].hex())
            return False

        # Auto-detect rake_id dari payload[HANDSHAKE_RAKE_OFFSET]
        payload = packet.payload
        if len(payload) > HANDSHAKE_RAKE_OFFSET:
            raw_rake = payload[HANDSHAKE_RAKE_OFFSET]
            if 1 <= raw_rake <= 99:
                self._discovered_rake_id = raw_rake
                log.info("[HS] rake_id auto-detected = %d (payload[%d]=0x%02x)",
                         raw_rake, HANDSHAKE_RAKE_OFFSET, raw_rake)
            else:
                log.warning(
                    "[HS] rake_id byte di payload[%d]=0x%02x di luar range 1-99 — "
                    "tidak bisa auto-detect; gunakan --rake-id manual",
                    HANDSHAKE_RAKE_OFFSET, raw_rake,
                )
        else:
            log.warning("[HS] Payload handshake terlalu pendek (%dB) untuk baca rake_id", len(payload))

        # Log ringkas state rake_id setelah handshake
        if self._user_rake_id is not None and self._discovered_rake_id is not None:
            if self._user_rake_id != self._discovered_rake_id:
                log.warning(
                    "[HS] rake_id mismatch: user=%d TIS=%d — pakai nilai user",
                    self._user_rake_id, self._discovered_rake_id,
                )
            else:
                log.info("[HS] rake_id user=%d cocok dengan TIS", self._user_rake_id)
        elif self._user_rake_id is None:
            log.info("[HS] rake_id efektif = %d (dari TIS)", self.rake_id)

        log.info("[HS] OK — response %dB checksum_ok=%s", len(resp_bytes), packet.checksum_ok)
        time.sleep(config.session.post_handshake_delay_sec)
        return True

    # ── Fase 2: CMD 0x32 metadata ─────────────────────────────────
    def _do_metadata_download(self, client: UDPClient):
        log.info("[CMD32] Metadata %d pages...", config.session.cmd32_pages)
        ok = 0
        for page in range(1, config.session.cmd32_pages + 1):
            resp = client.send_and_receive(build_metadata_request(page))
            if resp:
                log.debug("[CMD32] page=%d len=%dB hex=%s", page, len(resp), resp.hex())
                ok += 1
            else:
                log.warning("[CMD32] page=%d: tidak ada response", page)
        log.info("[CMD32] selesai %d/%d pages OK", ok, config.session.cmd32_pages)

    # ── Fase 3: CMD 0x34 dataset B ────────────────────────────────
    def _do_dataset_b_download(self, client: UDPClient):
        log.info("[CMD34] Dataset B %d pages...", config.session.cmd34_pages)
        ok = 0
        for page in range(1, config.session.cmd34_pages + 1):
            resp = client.send_and_receive(build_dataset_b_request(page))
            if resp:
                log.debug("[CMD34] page=%d len=%dB hex=%s", page, len(resp), resp.hex())
                ok += 1
            else:
                log.warning("[CMD34] page=%d: tidak ada response", page)
        log.info("[CMD34] selesai %d/%d pages OK", ok, config.session.cmd34_pages)

    # ── Fase 4: CMD 0x36 failure records ──────────────────────────
    def _do_failure_download(
        self, client: UDPClient
    ) -> Tuple[List[FailureRecord], int]:
        cfg      = config.session
        records: List[FailureRecord] = []
        pages_ok = 0

        log.info("[CMD36] Failure records %d pages × %d polls...",
                 cfg.cmd36_pages, cfg.polls_per_page)

        block_no = 0
        for page in range(cfg.cmd36_pages):
            page_records = self._poll_one_page(client, page, block_no)

            if page_records:
                records.extend(page_records)
                log.debug(
                    "[CMD36] page=0x%02x records=%d blk=%d..%d",
                    page, len(page_records), block_no, block_no + len(page_records) - 1,
                )
                block_no += len(page_records)
                pages_ok += 1
            else:
                log.warning("[CMD36] page=0x%02x: tidak ada data", page)

        log.info("[CMD36] selesai — total %d records dari %d/%d pages",
                 len(records), pages_ok, cfg.cmd36_pages)
        return records, pages_ok

    def _poll_one_page(
        self,
        client: UDPClient,
        page: int,
        block_start: int,
    ) -> List[FailureRecord]:
        cfg = config.session
        cmd = build_failure_poll(page)
        best_records: List[FailureRecord] = []

        for poll_no in range(cfg.polls_per_page):
            resp = client.send_and_receive(cmd)

            if not resp:
                time.sleep(cfg.poll_interval_sec)
                continue

            packet = self._resp_parser.parse(resp)
            if not packet or packet.is_heartbeat:
                time.sleep(cfg.poll_interval_sec)
                continue

            if packet.cmd == CMD_FAILURE and not best_records:
                page_records = self._rec_parser.parse_payload(
                    packet.payload, block_start
                )
                if page_records:
                    best_records = page_records

            time.sleep(cfg.poll_interval_sec)

        return best_records
