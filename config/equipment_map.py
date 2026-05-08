"""
config/equipment_map.py
========================
Mapping lengkap:
  - Equipment code (numeric) → nama equipment
  - Fault code (numeric)     → nama fault (command string)
  - Notch code (byte)        → label command
  - Car ID (pcap byte)       → Car number

Sumber: Chapter 16 TIS Maintenance Manual + cross-reference CSV/PDF output PTU.
"""

from typing import Dict, Tuple, Optional


# ─────────────────────────────────────────────
# EQUIPMENT CODE → Nama
# Sumber: cross-reference CSV column "Failure Equipment" vs PDF column "Equipment"
# ─────────────────────────────────────────────
EQUIPMENT_MAP: Dict[int, str] = {
    1:  "TIS",
    2:  "ATO",
    3:  "VVVF1",
    4:  "VVVF2",
    5:  "APS",
    6:  "BECU",
    7:  "ACE",
    8:  "PID",
    9:  "PA",
    19: "Radio",
    20: "CCTV",
}


# ─────────────────────────────────────────────
# FAULT CODE (numeric) → Nama fault (command string)
# Format: {equipment_code: {fault_numeric: fault_name}}
# Sumber: cross-reference CSV "Fault Code" vs PDF "Failure Code" column
# ─────────────────────────────────────────────
FAULT_MAP: Dict[int, Dict[int, str]] = {
    1: {   # TIS
        121: "ESA",
    },
    2: {   # ATO
        200: "ATOAT",
        211: "LBVRS1F",
        212: "LBVRS2F",
    },
    3: {   # VVVF1
        300: "NVCPS",
    },
    4: {   # VVVF2
        301: "NVCPS",
    },
    5: {   # APS
        400: "NAPCPS",
        419: "IVFR",
    },
    6: {   # BECU
        500: "NBPS",
    },
    7: {   # ACE
        600: "NACCPS",
    },
    8: {   # PID
        700: "NPICPS",
    },
    9: {   # PA
        800: "NPACPS",
        806: "DATASA",
    },
    19: {  # Radio
        1100: "NTRCPS",
        1103: "CMA",
    },
    20: {  # CCTV
        1200: "NCCPS",
        1203: "WCE",
        1204: "CVE",
    },
}


# ─────────────────────────────────────────────
# NOTCH/COMMAND CODE → Label
# Sumber: pcap observation + CSV "Notch" column
# ─────────────────────────────────────────────
NOTCH_MAP: Dict[int, str] = {
    0x00: "EB",       # Emergency Brake
    0x01: "Neutral",
    0x02: "B8",
    0x03: "B7",
    0x04: "B6",
    0x05: "B5",
    0x06: "B4",
    0x07: "B3",
    0x08: "B2",
    0x09: "B1",
    0x10: "N",        # Neutral traction
    0x11: "P1",
    0x12: "P2",
    0x13: "P3",
    0x14: "P4",
    0x15: "A_B6",     # ATO brake
    0x16: "A_P16",    # ATO power
}


# ─────────────────────────────────────────────
# CAR ID (pcap byte[8]) → Car Number
# Sumber: cross-reference pcap vs CSV CarNo
# Formation: Car01(Tc1) - Car02(M1) - Car03(M2) - Car04(M1') - Car05(M2') - Car06(Tc2)
#
# NOTE: Mapping ini PERLU DIKONFIRMASI dengan capture pcap saat kereta bergerak.
#       Saat ini hanya Car01 dan Car06 yang terkonfirmasi dari pcap depot.
# ─────────────────────────────────────────────
CAR_ID_MAP: Dict[int, int] = {
    0x01: 1,   # ✅ Terkonfirmasi dari pcap — Tc1 (Head A)
    0x02: 2,   # ❓ Belum terkonfirmasi — M1
    0x03: 3,   # ❓ Belum terkonfirmasi — M2
    0x04: 4,   # ❓ Belum terkonfirmasi — M1'
    0x05: 5,   # ❓ Belum terkonfirmasi — M2'
    0x11: 6,   # ✅ Terkonfirmasi dari pcap — Tc2 (Head B)
}

CAR_TYPE_MAP: Dict[int, str] = {
    1: "Tc1",
    2: "M1",
    3: "M2",
    4: "M1'",
    5: "M2'",
    6: "Tc2",
}


# ─────────────────────────────────────────────
# LOOKUP HELPERS
# ─────────────────────────────────────────────
def get_equipment_name(code: int) -> str:
    """Kembalikan nama equipment dari kode numeric. Fallback ke 'EQ{code}'."""
    return EQUIPMENT_MAP.get(code, f"EQ{code:02d}")


def get_fault_name(equipment_code: int, fault_code: int) -> str:
    """Kembalikan nama fault dari equipment code + fault code numeric."""
    equip_faults = FAULT_MAP.get(equipment_code, {})
    return equip_faults.get(fault_code, f"FC{fault_code:04d}")


def get_notch_label(notch_byte: int) -> str:
    """Kembalikan label notch/command dari byte value."""
    return NOTCH_MAP.get(notch_byte, f"N{notch_byte:02X}")


def get_car_number(car_id_byte: int) -> int:
    """Kembalikan car number (1-6) dari byte pcap. Fallback ke byte value itu sendiri."""
    return CAR_ID_MAP.get(car_id_byte, car_id_byte)


def get_car_type(car_number: int) -> str:
    """Kembalikan tipe car (Tc1, M1, dll) dari car number."""
    return CAR_TYPE_MAP.get(car_number, f"Car{car_number:02d}")


def lookup_full(equipment_code: int, fault_code: int, car_id_byte: int,
                notch_byte: int) -> Tuple[str, str, int, str]:
    """
    Lookup semua field sekaligus.
    Returns: (equipment_name, fault_name, car_number, notch_label)
    """
    return (
        get_equipment_name(equipment_code),
        get_fault_name(equipment_code, fault_code),
        get_car_number(car_id_byte),
        get_notch_label(notch_byte),
    )
