"""
parser/response_parser.py
==========================
Parse response packet dari TIS per command type.
Memisahkan header/checksum dari payload sebelum dikirim ke RecordParser.
"""

from dataclasses import dataclass
from typing import Optional, List

from utils.checksum import verify_checksum
from utils.logger import get_logger

log = get_logger(__name__)


# ─────────────────────────────────────────────
# PACKET HEADER CONSTANTS
# ─────────────────────────────────────────────
PACKET_PREFIX   = 0x02
CMD_HANDSHAKE   = 0x20
CMD_METADATA    = 0x32
CMD_DATASET_B   = 0x34
CMD_FAILURE     = 0x36
CMD_HEARTBEAT   = 0x00

HEADER_SIZE     = 8   # bytes header di awal tiap packet
CHECKSUM_SIZE   = 2   # bytes checksum di akhir packet
HEARTBEAT_SIZE  = 256 # bytes heartbeat packet (all zeros)


# ─────────────────────────────────────────────
# PARSED PACKET
# ─────────────────────────────────────────────
@dataclass
class ParsedPacket:
    """Hasil parse satu UDP packet dari TIS."""
    cmd: int           # Command byte (0x20, 0x32, dll)
    seq: int           # Sequence number (bytes 2-3)
    page: int          # Page index (bytes 6-7)
    payload: bytes     # Data tanpa header dan checksum
    raw: bytes         # Full raw bytes
    is_heartbeat: bool = False
    checksum_ok: bool  = True


class ResponseParser:
    """Parse raw UDP bytes dari TIS menjadi ParsedPacket."""

    def parse(self, data: bytes) -> Optional[ParsedPacket]:
        """
        Parse satu UDP datagram dari TIS.
        Kembalikan None jika data tidak valid.
        """
        if not data:
            return None

        # Deteksi heartbeat (256 byte semua nol)
        if len(data) == HEARTBEAT_SIZE and all(b == 0 for b in data):
            return ParsedPacket(
                cmd=CMD_HEARTBEAT,
                seq=0, page=0,
                payload=b'',
                raw=data,
                is_heartbeat=True,
            )

        # Minimal harus ada header + checksum
        if len(data) < HEADER_SIZE + CHECKSUM_SIZE:
            log.warning(f"Packet terlalu pendek: {len(data)} bytes")
            return None

        # Validasi prefix
        if data[0] != PACKET_PREFIX:
            log.warning(f"Prefix tidak valid: 0x{data[0]:02X}")
            return None

        cmd  = data[1]
        seq  = (data[2] << 8) | data[3]
        page = (data[6] << 8) | data[7]

        # Verifikasi checksum
        ck_ok = verify_checksum(data)
        if not ck_ok:
            log.warning(f"Checksum gagal pada CMD=0x{cmd:02X} seq={seq}")

        payload = data[HEADER_SIZE:-CHECKSUM_SIZE]

        return ParsedPacket(
            cmd=cmd,
            seq=seq,
            page=page,
            payload=payload,
            raw=data,
            checksum_ok=ck_ok,
        )

    def is_cmd36_last_page(self, packet: ParsedPacket, total_pages: int) -> bool:
        """Cek apakah ini page terakhir dari CMD 0x36."""
        return packet.cmd == CMD_FAILURE and packet.page >= (total_pages - 1)
