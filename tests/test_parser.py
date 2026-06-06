"""
tests/test_parser.py
=====================
Unit test untuk parser — menggunakan data nyata dari pcap.
Jalankan: python -m pytest tests/ -v
"""

import sys
import os
sys.path.insert(0, os.path.dirname(os.path.dirname(__file__)))

from datetime import datetime
from parsers.bcd import bcd_byte, decode_timestamp
from parsers.record_parser import RecordParser
from parsers.response_parser import ResponseParser
from config.equipment_map import get_notch_label, decode_notch


# ─────────────────────────────────────────────
# BCD DECODER
# ─────────────────────────────────────────────
def test_bcd_byte_basic():
    assert bcd_byte(0x26) == 26
    assert bcd_byte(0x05) == 5
    assert bcd_byte(0x16) == 16
    assert bcd_byte(0x00) == 0
    assert bcd_byte(0x59) == 59

def test_decode_timestamp_from_pcap():
    """Test dengan bytes nyata dari pcap record 1."""
    raw = bytes([0x26, 0x05, 0x07, 0x16, 0x04, 0x07])
    ts  = decode_timestamp(raw)
    assert ts == datetime(2026, 5, 7, 16, 4, 7)

def test_decode_timestamp_offset():
    """Test dengan offset di tengah bytes."""
    raw = bytes([0x00, 0x26, 0x05, 0x07, 0x16, 0x04, 0x07])
    ts  = decode_timestamp(raw, offset=1)
    assert ts == datetime(2026, 5, 7, 16, 4, 7)


# ─────────────────────────────────────────────
# RECORD PARSER — Data Depot (dari PCAP TS5)
# ─────────────────────────────────────────────
# Payload CMD 0x36: StartMark(1B) + 5×Record(20B) + EndMark(1B) = 102B
# "ffff" di akhir tiap record = Train ID bytes [18-19] = 0xFFFF (depot), BUKAN separator
SAMPLE_PAYLOAD = bytes.fromhex(
    "00"
    "260507160407000001000006090503260100ffff"  # Record 1: ts=260507-160407 car=6 eq=9 fc=806
    "260507160405000011000006090503260800ffff"  # Record 2: ts=260507-160405 car=6 eq=9 fc=806 recover
    "260507160400000001000002080502bc0000ffff"  # Record 3: ts=260507-160400 car=2 eq=8 fc=700
    "260507160400000001000002070502580000ffff"  # Record 4: ts=260507-160400 car=2 eq=7 fc=600
    "260507160400000001000002060501f40000ffff"  # Record 5: ts=260507-160400 car=2 eq=6 fc=500
    "03"
)

def test_record_parser_count():
    """Parser harus menghasilkan 5 records dari sample payload."""
    parser  = RecordParser()
    records = parser.parse_payload(SAMPLE_PAYLOAD)
    assert len(records) == 5

def test_record_parser_timestamp():
    parser  = RecordParser()
    records = parser.parse_payload(SAMPLE_PAYLOAD)
    assert records[0].timestamp == datetime(2026, 5, 7, 16, 4, 7)
    assert records[1].timestamp == datetime(2026, 5, 7, 16, 4, 5)

def test_record_parser_equipment():
    parser  = RecordParser()
    records = parser.parse_payload(SAMPLE_PAYLOAD)
    assert records[0].equipment_code == 9
    assert records[0].equipment_name == "PA"
    assert records[2].equipment_code == 8
    assert records[2].equipment_name == "PID"

def test_record_parser_fault_code():
    parser  = RecordParser()
    records = parser.parse_payload(SAMPLE_PAYLOAD)
    assert records[0].fault_code == 806
    assert records[0].fault_name == "DATASA"
    assert records[2].fault_code == 700
    assert records[2].fault_name == "NPICPS"

def test_record_parser_block_numbers():
    """Block numbers harus increment mulai dari block_start."""
    parser  = RecordParser()
    records = parser.parse_payload(SAMPLE_PAYLOAD, block_start=10)
    assert records[0].block_no == 10
    assert records[4].block_no == 14

def test_record_to_dict():
    parser  = RecordParser()
    records = parser.parse_payload(SAMPLE_PAYLOAD)
    d = records[0].to_dict()
    assert d["fault_code"]     == 806
    assert d["equipment_name"] == "PA"
    assert d["fault_name"]     == "DATASA"

def test_record_to_csv_row():
    """to_csv_row() harus mengembalikan 16 kolom (trailing '' ada di exporter, bukan di sini)."""
    parser  = RecordParser()
    records = parser.parse_payload(SAMPLE_PAYLOAD)
    row = records[0].to_csv_row()
    assert len(row) == 16   # 16 kolom dari to_csv_row(); exporter tambahkan '' secara terpisah
    assert row[11] == 9     # equipment code di index 11
    assert row[12] == 806   # fault code di index 12


# ─────────────────────────────────────────────
# RECORD PARSER — Location signed int16 BE bytes[6-7]
# ─────────────────────────────────────────────
def _make_payload(record_bytes: bytes) -> bytes:
    """Bungkus 20-byte record dengan StartMark/EndMark."""
    assert len(record_bytes) == 20
    return b'\x00' + record_bytes + b'\x03'

# 20-byte template: lokasi depot (bytes[6-7]=0x0000)
_DEPOT_RECORD = bytes.fromhex("260507160407000001000006090503260100ffff")

def test_location_depot_zero():
    """Bytes[6-7]=0x0000 → location=0m (depot/stationary)."""
    rec = RecordParser().parse_payload(_make_payload(_DEPOT_RECORD))[0]
    assert rec.location_m == 0

def test_location_negative_signed_int16():
    """Bytes[6-7]=0xFFBE → signed int16 BE = -66m (Block-10 TS5 PCAP)."""
    raw = bytearray(_DEPOT_RECORD)
    raw[6] = 0xFF
    raw[7] = 0xBE
    rec = RecordParser().parse_payload(_make_payload(bytes(raw)))[0]
    assert rec.location_m == -66

def test_location_positive_kilometric():
    """Bytes[6-7]=0x2173 → 8563m (TS13 PCAP confirmed dari original PTU CSV)."""
    raw = bytearray(_DEPOT_RECORD)
    raw[6] = 0x21
    raw[7] = 0x73
    rec = RecordParser().parse_payload(_make_payload(bytes(raw)))[0]
    assert rec.location_m == 8563

def test_location_negative_double_byte():
    """Bytes[6-7]=0xFFA7 → -89m (TS13 PCAP, train ID 0207)."""
    raw = bytearray(_DEPOT_RECORD)
    raw[6] = 0xFF
    raw[7] = 0xA7
    rec = RecordParser().parse_payload(_make_payload(bytes(raw)))[0]
    assert rec.location_m == -89

def test_location_max_negative():
    """Bytes[6-7]=0x8000 → -32768 (batas bawah signed int16)."""
    raw = bytearray(_DEPOT_RECORD)
    raw[6] = 0x80
    raw[7] = 0x00
    rec = RecordParser().parse_payload(_make_payload(bytes(raw)))[0]
    assert rec.location_m == -32768

def test_location_max_positive():
    """Bytes[6-7]=0x7FFF → +32767 (batas atas signed int16)."""
    raw = bytearray(_DEPOT_RECORD)
    raw[6] = 0x7F
    raw[7] = 0xFF
    rec = RecordParser().parse_payload(_make_payload(bytes(raw)))[0]
    assert rec.location_m == 32767


# ─────────────────────────────────────────────
# RECORD PARSER — Train ID BCD + 0xFFFF sentinel
# ─────────────────────────────────────────────
def test_train_id_depot_ffff():
    """Bytes [18-19]=0xFF 0xFF → train_id=0xFFFF → train_id_str='FFFF' (depot/unknown)."""
    rec = RecordParser().parse_payload(_make_payload(_DEPOT_RECORD))[0]
    assert rec.train_id == 0xFFFF
    assert rec.train_id_str == "FFFF"

def test_train_id_bcd_decode():
    """Bytes [18-19]=0x07 0x29 → BCD → 7*100+29=729 → '0729'."""
    raw = bytearray(_DEPOT_RECORD)
    raw[18] = 0x07   # OFF_TRAIN_ID hi
    raw[19] = 0x29   # OFF_TRAIN_ID lo
    rec = RecordParser().parse_payload(_make_payload(bytes(raw)))[0]
    assert rec.train_id == 729
    assert rec.train_id_str == "0729"

def test_train_id_bcd_leading_zero():
    """Bytes [18-19]=0x00 0x05 → BCD → 0*100+5=5 → '0005'."""
    raw = bytearray(_DEPOT_RECORD)
    raw[18] = 0x00
    raw[19] = 0x05
    rec = RecordParser().parse_payload(_make_payload(bytes(raw)))[0]
    assert rec.train_id == 5
    assert rec.train_id_str == "0005"


# ─────────────────────────────────────────────
# RECORD PARSER — Occur/Recover dari byte[8]
# ─────────────────────────────────────────────
def test_occur_recover_occur():
    """Byte[8]=0x00 → high nibble=0 → occur_recover=0 (Occur)."""
    raw = bytearray(_DEPOT_RECORD)
    raw[8] = 0x00
    rec = RecordParser().parse_payload(_make_payload(bytes(raw)))[0]
    assert rec.occur_recover == 0

def test_occur_recover_recover():
    """Byte[8]=0x11 → high nibble=1 → occur_recover=1 (Recover). Dari record 2 PCAP depot."""
    raw = bytearray(_DEPOT_RECORD)
    raw[8] = 0x11
    rec = RecordParser().parse_payload(_make_payload(bytes(raw)))[0]
    assert rec.occur_recover == 1


# ─────────────────────────────────────────────
# NOTCH_MAP — single-byte lookup (backwards-compat)
# ─────────────────────────────────────────────
def test_notch_single_byte_lookup():
    """get_notch_label() = single-byte lookup, untuk legacy callers / UI tooltip."""
    assert get_notch_label(0x00) == "EB"
    assert get_notch_label(0x80) == "Neutral"
    assert get_notch_label(0x86) == "A_B6"
    assert get_notch_label(0x04) == "A_P4"
    assert get_notch_label(0x10) == "A_P16"

def test_notch_single_byte_fallback():
    """Byte tak dikenal → 'N{hex}'."""
    assert get_notch_label(0x91) == "N91"
    assert get_notch_label(0x11) == "N11"


# ─────────────────────────────────────────────
# decode_notch — full 3-byte tuple (b8, b9, b10) per PTU asli
# ─────────────────────────────────────────────
def test_decode_notch_eb_via_status_bit0():
    """b8 bit-0 = 1 → EB (override; wire 657R Emergency Brake)."""
    # Semua 7 kombinasi di TS13 PCAP dengan b8 bit-0 set
    assert decode_notch(0x01, 0x00, 0x00) == "EB"
    assert decode_notch(0x01, 0x80, 0x00) == "EB"
    assert decode_notch(0x11, 0x00, 0x00) == "EB"
    assert decode_notch(0x11, 0x80, 0x00) == "EB"
    assert decode_notch(0x81, 0x86, 0x00) == "EB"   # bit-0 menang dari A_B6
    assert decode_notch(0x91, 0x86, 0x00) == "EB"

def test_decode_notch_neutral():
    """b9 = 0x00/0x80, b10 = 0x80/0x00, tanpa EB → Neutral."""
    assert decode_notch(0x00, 0x00, 0x80) == "Neutral"
    assert decode_notch(0x10, 0x00, 0x80) == "Neutral"
    assert decode_notch(0x00, 0x80, 0x80) == "Neutral"
    assert decode_notch(0x10, 0x80, 0x80) == "Neutral"

def test_decode_notch_ato_power():
    """b9 = 0x01..0x10 → A_P1..A_P16 (TS13 confirmed: b9=0x10 → A_P16)."""
    assert decode_notch(0x80, 0x10, 0x80) == "A_P16"
    assert decode_notch(0x80, 0x10, 0x00) == "A_P16"   # TS13 blk=112
    for n in range(1, 17):
        assert decode_notch(0x00, n, 0x80) == f"A_P{n}"

def test_decode_notch_ato_brake():
    """b9 = 0x81..0x90 → A_B1..A_B16 (TS13 confirmed: b9=0x86 → A_B6)."""
    assert decode_notch(0x80, 0x86, 0x80) == "A_B6"
    assert decode_notch(0x90, 0x86, 0x00) == "A_B6"    # TS13 blk=117 (b8 bit-0 clear)
    assert decode_notch(0x90, 0x86, 0x80) == "A_B6"
    assert decode_notch(0x00, 0x85, 0x80) == "A_B5"

def test_decode_notch_manual_brake():
    """b10 = 0x40 → Manual Brake (TS13: b9=0x80 → M_B1)."""
    assert decode_notch(0x00, 0x80, 0x40) == "M_B1"
    assert decode_notch(0x10, 0x80, 0x40) == "M_B1"

def test_decode_notch_manual_power():
    """b10 = 0x08 → Manual Power (TS13: b9=0x80 → M_P1)."""
    assert decode_notch(0x00, 0x80, 0x08) == "M_P1"
    assert decode_notch(0x10, 0x80, 0x08) == "M_P1"

def test_decode_notch_via_record():
    """End-to-end via RecordParser dengan kombinasi Neutral murni (no EB)."""
    raw = bytearray(_DEPOT_RECORD)
    raw[8] = 0x00   # clear bit-0 → no EB override
    raw[9] = 0x80   # OFF_NOTCH_STEP
    raw[10] = 0x80  # OFF_NOTCH_MODE = Auto
    rec = RecordParser().parse_payload(_make_payload(bytes(raw)))[0]
    assert rec.notch_label == "Neutral"
    assert rec.notch_step == 0x80
    assert rec.notch_mode == 0x80


# ─────────────────────────────────────────────
# OVERHEAD VOLTAGE scaling
# ─────────────────────────────────────────────
def test_overhead_voltage_scaling():
    """Byte[16] = OV_raw × 10 = Volt. Manual: 'Line Voltage [10V:bit]'."""
    raw = bytearray(_DEPOT_RECORD)
    raw[16] = 0x01   # OFF_OV_RAW = 1 → 10V
    rec = RecordParser().parse_payload(_make_payload(bytes(raw)))[0]
    assert rec.overhead_v == 10

    raw[16] = 0x08   # 8 → 80V
    rec = RecordParser().parse_payload(_make_payload(bytes(raw)))[0]
    assert rec.overhead_v == 80

    raw[16] = 0x00   # 0 → 0V (depot/no overhead)
    rec = RecordParser().parse_payload(_make_payload(bytes(raw)))[0]
    assert rec.overhead_v == 0


# ─────────────────────────────────────────────
# RESPONSE PARSER
# ─────────────────────────────────────────────
# Full 112B packet: Header(8) + StartMark(1) + 5×Record(20) + EndMark(1) + Checksum(2)
SAMPLE_CMD36_PACKET = bytes.fromhex(
    "023600610005000000"
    "260507160407000001000006090503260100ffff"
    "260507160405000011000006090503260800ffff"
    "260507160400000001000002080502bc0000ffff"
    "260507160400000001000002070502580000ffff"
    "260507160400000001000002060501f40000ffff"
    "039b00"
)

def test_response_parser_cmd36():
    parser = ResponseParser()
    packet = parser.parse(SAMPLE_CMD36_PACKET)
    assert packet is not None
    assert packet.cmd  == 0x36
    assert packet.seq  == 0x0061
    assert packet.page == 0x0000
    assert not packet.is_heartbeat

def test_response_parser_heartbeat():
    parser    = ResponseParser()
    heartbeat = b'\x00' * 256
    packet    = parser.parse(heartbeat)
    assert packet is not None
    assert packet.is_heartbeat

def test_response_parser_invalid():
    parser = ResponseParser()
    assert parser.parse(b'') is None
    assert parser.parse(b'\x01\x02') is None  # prefix salah


if __name__ == "__main__":
    # Jalankan tanpa pytest
    tests = [
        test_bcd_byte_basic,
        test_decode_timestamp_from_pcap,
        test_decode_timestamp_offset,
        test_record_parser_count,
        test_record_parser_timestamp,
        test_record_parser_equipment,
        test_record_parser_fault_code,
        test_record_parser_block_numbers,
        test_record_to_dict,
        test_record_to_csv_row,
        test_location_depot_zero,
        test_location_negative_signed_int16,
        test_location_positive_kilometric,
        test_location_negative_double_byte,
        test_location_max_negative,
        test_location_max_positive,
        test_train_id_depot_ffff,
        test_train_id_bcd_decode,
        test_train_id_bcd_leading_zero,
        test_occur_recover_occur,
        test_occur_recover_recover,
        test_notch_single_byte_lookup,
        test_notch_single_byte_fallback,
        test_decode_notch_eb_via_status_bit0,
        test_decode_notch_neutral,
        test_decode_notch_ato_power,
        test_decode_notch_ato_brake,
        test_decode_notch_manual_brake,
        test_decode_notch_manual_power,
        test_decode_notch_via_record,
        test_overhead_voltage_scaling,
        test_response_parser_cmd36,
        test_response_parser_heartbeat,
        test_response_parser_invalid,
    ]
    passed = 0
    for t in tests:
        try:
            t()
            print(f"  OK {t.__name__}")
            passed += 1
        except AssertionError as e:
            print(f"  FAIL {t.__name__}: {e}")
        except Exception as e:
            print(f"  ERROR {t.__name__}: {e}")

    print(f"\n{passed}/{len(tests)} tests passed")
