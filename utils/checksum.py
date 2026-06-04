"""
utils/checksum.py
==================
Kalkulasi dan verifikasi checksum paket TIS.

Dari observasi pcap, checksum = 2 byte terakhir tiap packet.
Algoritma: belum 100% terkonfirmasi — hipotesis sum of bytes modulo 0x10000.
Perlu validasi lebih lanjut dengan packet bergerak.
"""

from typing import Dict


def _sum16(data: bytes) -> int:
    """Simple sum modulo 65536 (hipotesis utama)."""
    return sum(data) & 0xFFFF


def _xor16(data: bytes) -> int:
    """XOR semua byte, hasilnya di-zero-extend ke uint16."""
    result = 0
    for b in data:
        result ^= b
    return result


def _crc16_ccitt(data: bytes) -> int:
    """CRC-16/CCITT-FALSE (poly=0x1021, init=0xFFFF)."""
    crc = 0xFFFF
    for b in data:
        crc ^= b << 8
        for _ in range(8):
            if crc & 0x8000:
                crc = (crc << 1) ^ 0x1021
            else:
                crc <<= 1
        crc &= 0xFFFF
    return crc


def _crc16_ibm(data: bytes) -> int:
    """CRC-16/IBM (poly=0x8005, init=0x0000)."""
    crc = 0x0000
    for b in data:
        crc ^= b
        for _ in range(8):
            if crc & 0x0001:
                crc = (crc >> 1) ^ 0xA001
            else:
                crc >>= 1
    return crc


def probe_algorithms(data: bytes) -> Dict[str, bool]:
    """
    Coba semua algoritma checksum yang dikenal terhadap packet.
    Data termasuk 2 byte checksum di akhir.
    Returns dict {nama_algoritma: match}.
    Digunakan saat debug sesi live untuk mengidentifikasi algoritma yang benar.
    """
    if len(data) < 3:
        return {}
    body     = data[:-2]
    expected = (data[-2] << 8) | data[-1]
    return {
        "sum16":       _sum16(body)      == expected,
        "xor16":       _xor16(body)      == expected,
        "crc16_ccitt": _crc16_ccitt(body) == expected,
        "crc16_ibm":   _crc16_ibm(body)  == expected,
    }


def calculate_checksum(data: bytes) -> int:
    """
    Hitung checksum untuk data (tanpa 2 byte checksum terakhir).
    Returns: uint16
    """
    return _sum16(data[:-2])


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
