"""
parser/bcd.py
=============
Decode BCD (Binary Coded Decimal) encoding yang dipakai TIS untuk timestamp.

Contoh:
  0x26 → 26 (tahun 2026)
  0x05 → 5  (bulan Mei)
  0x16 → 16 (jam 16 = 4 PM)
"""

from datetime import datetime


def bcd_byte(b: int) -> int:
    """Decode satu byte BCD ke integer. Contoh: 0x26 → 26."""
    high = (b >> 4) & 0x0F
    low  =  b       & 0x0F
    return high * 10 + low


def decode_timestamp(data: bytes, offset: int = 0) -> datetime:
    """
    Decode 6 byte BCD timestamp mulai dari offset.
    Format: YY MM DD HH MM SS
    Contoh: 26 05 07 16 04 07 → 2026-05-07 16:04:07
    """
    year   = 2000 + bcd_byte(data[offset])
    month  = bcd_byte(data[offset + 1])
    day    = bcd_byte(data[offset + 2])
    hour   = bcd_byte(data[offset + 3])
    minute = bcd_byte(data[offset + 4])
    second = bcd_byte(data[offset + 5])

    # Validasi dasar — jika data korup (misal 00/00/00), kembalikan None-safe default
    try:
        return datetime(year, month, day, hour, minute, second)
    except ValueError:
        return datetime(2000, 1, 1, 0, 0, 0)


def is_valid_timestamp(data: bytes, offset: int = 0) -> bool:
    """Cek apakah 6 byte di offset adalah timestamp BCD yang valid."""
    try:
        ts = decode_timestamp(data, offset)
        return ts.year > 2000
    except Exception:
        return False
