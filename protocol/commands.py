"""
protocol/commands.py
=====================
Builder untuk semua jenis command packet yang dikirim gateway ke TIS.
Sumber: reverse engineering pcap dudu_sniffing_tis_ts5.pcapng.

Semua packet diawali prefix 0x02.
"""


# ─────────────────────────────────────────────
# OBSERVED PACKET PATTERNS FROM PCAP
# ─────────────────────────────────────────────
# CMD 0x20 (Handshake):
#   PTU→TIS: 02 20 00 00 00 00 00 00 00 03 23 00  (12 bytes)
#
# CMD 0x32 (Metadata request, page N):
#   PTU→TIS: 02 32 [page] 00 00 03 [0x32-page] [page]  (8 bytes)
#   Page 1:  02 32 01 00 00 03 31 01
#   Page 2:  02 32 02 00 00 03 30 02
#
# CMD 0x34 (Dataset B request, page N):
#   PTU→TIS: 02 34 [page] [page-1] 00 03 [37-page] [page]
#   Page 1:  02 34 01 00 00 03 37 01
#   Page 2:  02 34 02 01 00 03 36 02
#
# CMD 0x36 (Failure poll, page N):
#   PTU→TIS: 02 36 00 00 00 00 [page] 03 35 [page]  (10 bytes)
#   Page 0:  02 36 00 00 00 00 00 03 35 00
#   Page 1:  02 36 00 00 00 00 01 03 35 01
# ─────────────────────────────────────────────


def build_handshake() -> bytes:
    """
    CMD 0x20 — Handshake/Init.
    Dikirim sekali di awal sesi.
    """
    return bytes([0x02, 0x20, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00,
                  0x00, 0x03, 0x23, 0x00])


def build_metadata_request(page: int) -> bytes:
    """
    CMD 0x32 — Request metadata page N (1-based).
    Pattern dari pcap: 02 32 [page] 00 00 03 [0x32-page] [page]
    """
    if not (1 <= page <= 6):
        raise ValueError(f"Page CMD 0x32 harus 1-6, dapat: {page}")
    b3 = 0x31 - (page - 1)  # 0x31 untuk page 1, turun 1 tiap page
    return bytes([0x02, 0x32, page, 0x00, 0x00, 0x03, b3, page])


def build_dataset_b_request(page: int) -> bytes:
    """
    CMD 0x34 — Request dataset B page N (1-based).
    Pattern dari pcap: 02 34 [page] [page-1] 00 03 [0x37-page] [page]
    """
    if not (1 <= page <= 6):
        raise ValueError(f"Page CMD 0x34 harus 1-6, dapat: {page}")
    b3 = page - 1
    b6 = 0x37 - (page - 1)  # 0x37 untuk page 1, turun 1 tiap page
    return bytes([0x02, 0x34, page, b3, 0x00, 0x03, b6, page])


def build_failure_poll(page: int) -> bytes:
    """
    CMD 0x36 — Poll failure records page N (0-based, 0x00–0x27).
    Pattern dari pcap: 02 36 00 00 00 00 [page] 03 35 [page]
    """
    if not (0x00 <= page <= 0x27):
        raise ValueError(f"Page CMD 0x36 harus 0x00-0x27, dapat: {page:#04x}")
    return bytes([0x02, 0x36, 0x00, 0x00, 0x00, 0x00, page, 0x03, 0x35, page])


def build_heartbeat_ack() -> bytes:
    """
    Heartbeat ACK — dikirim sebagai respons heartbeat 0x00 dari TIS.
    Dari pcap: PTU tidak kirim apa-apa khusus, cukup lanjut poll berikutnya.
    Placeholder untuk kebutuhan masa depan jika diperlukan.
    """
    return b''  # Tidak perlu kirim apapun untuk heartbeat
