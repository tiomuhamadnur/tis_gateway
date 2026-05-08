"""
utils/checksum.py
==================
Kalkulasi dan verifikasi checksum paket TIS.

Dari observasi pcap, checksum = 2 byte terakhir tiap packet.
Algoritma: belum 100% terkonfirmasi — hipotesis sum of bytes modulo 0x10000.
Perlu validasi lebih lanjut dengan packet bergerak.
"""


def calculate_checksum(data: bytes) -> int:
    """
    Hitung checksum untuk data (tanpa 2 byte checksum terakhir).
    Returns: uint16
    """
    # TODO: Konfirmasi algoritma checksum dengan lebih banyak sampel pcap.
    # Hipotesis: simple sum modulo 65536
    return sum(data[:-2]) & 0xFFFF


def checksum_bytes(data: bytes) -> bytes:
    """Kembalikan 2 byte checksum untuk data."""
    ck = calculate_checksum(data)
    return bytes([(ck >> 8) & 0xFF, ck & 0xFF])


def verify_checksum(data: bytes) -> bool:
    """
    Verifikasi checksum packet.
    Kembalikan True jika valid, False jika tidak.
    """
    if len(data) < 3:
        return False
    expected = (data[-2] << 8) | data[-1]
    calculated = calculate_checksum(data)
    return expected == calculated
