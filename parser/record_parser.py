"""
parser/record_parser.py
========================
Parse raw bytes dari TIS menjadi FailureRecord dataclass yang terstruktur.

Struktur record 18 byte (dari analisis pcap dudu_sniffing_tis_ts5.pcapng):
  [0-5]   Timestamp BCD (YY MM DD HH MM SS)
  [6]     Car ID byte     → di-lookup ke Car number (1-6)
  [7]     Occur/Recover   → 0=Occur, 1=Recover
  [8-9]   Train ID        → uint16, 0xFFFF = depot/unknown
  [10-11] Location [m]    → int16 signed (km marker × 1000?)
  [12]    Equipment code  → di-lookup ke nama equipment
  [13]    Fault sub-index → internal index
  [14-15] Fault code      → uint16 numeric
  [16]    Speed [km/h]    → uint8
  [17]    Overhead V MSB  → high byte tegangan catenary
  (OV lengkap ada di bytes [17] dan byte pertama separator [FF])
  NOTE: Field Speed dan OV perlu konfirmasi lebih lanjut dari capture
        saat kereta bergerak. Saat ini semua = 0 (kereta di depo).
"""

from dataclasses import dataclass, field
from datetime import datetime
from typing import Optional, List

from parser.bcd import decode_timestamp, is_valid_timestamp
from config.equipment_map import (
    get_equipment_name, get_fault_name,
    get_car_number, get_notch_label
)


RECORD_SIZE = 18          # bytes per record
SEPARATOR   = b'\xff\xff' # delimiter antar record


# ─────────────────────────────────────────────
# DATA CLASS
# ─────────────────────────────────────────────
@dataclass
class FailureRecord:
    """Satu record failure dari TIS. Setara dengan satu baris di CSV/PDF output PTU."""

    # Sequence number (dari Block.No di CSV)
    block_no: int = 0

    # Timestamp kejadian
    timestamp: datetime = field(default_factory=datetime.now)

    # Car number (1-6 sesuai formation)
    car_no: int = 0

    # Occur=0 / Recover=1
    occur_recover: int = 0

    # ID rangkaian (Train Set ID), misal 1611, 0107. FFFF = depot
    train_id: int = 0xFFFF

    # Posisi di track [meter], signed
    location_m: int = 0

    # Kode equipment (numeric)
    equipment_code: int = 0

    # Fault sub-index (internal TIS)
    fault_sub: int = 0

    # Fault code (numeric), misal 806, 700, 212
    fault_code: int = 0

    # Notch/command byte (raw)
    notch_byte: int = 0

    # Kecepatan saat kejadian [km/h]
    speed_kmh: int = 0

    # Tegangan catenary [V]
    overhead_v: int = 0

    # Raw bytes untuk debugging
    raw_bytes: bytes = b''

    # ── Derived fields (nama yang sudah di-lookup) ──

    @property
    def equipment_name(self) -> str:
        return get_equipment_name(self.equipment_code)

    @property
    def fault_name(self) -> str:
        return get_fault_name(self.equipment_code, self.fault_code)

    @property
    def notch_label(self) -> str:
        return get_notch_label(self.notch_byte)

    @property
    def train_id_str(self) -> str:
        """Format TrainID: FFFF jika depot, atau 4-digit string."""
        if self.train_id == 0xFFFF:
            return "FFFF"
        return f"{self.train_id:04d}"

    @property
    def timestamp_str(self) -> str:
        """Format timestamp sesuai PTU output: DD/MM/YY HH:MM:SS"""
        return self.timestamp.strftime("%d/%m/%y %H:%M:%S")

    def to_dict(self) -> dict:
        """Serialize ke dict untuk JSON / cloud upload."""
        return {
            "block_no":       self.block_no,
            "timestamp":      self.timestamp.isoformat(),
            "car_no":         self.car_no,
            "occur_recover":  self.occur_recover,
            "train_id":       self.train_id_str,
            "location_m":     self.location_m,
            "equipment_code": self.equipment_code,
            "equipment_name": self.equipment_name,
            "fault_code":     self.fault_code,
            "fault_name":     self.fault_name,
            "notch":          self.notch_label,
            "speed_kmh":      self.speed_kmh,
            "overhead_v":     self.overhead_v,
        }

    def to_csv_row(self) -> List:
        """
        Serialize ke list untuk CSV row.
        Kolom sesuai format PTU: Block.No, Year, Month, Day, Hour, Min, Sec,
        CarNo, TrainID, Occur/Recover, Location, FailEquip, FaultCode,
        Notch, Speed, OV
        """
        ts = self.timestamp
        return [
            self.block_no,
            ts.strftime("%y"),  # YY (2-digit)
            ts.strftime("%m"),
            ts.strftime("%d"),
            ts.strftime("%H"),
            ts.strftime("%M"),
            ts.strftime("%S"),
            f"{self.car_no:02d}",
            self.train_id_str,
            self.occur_recover,
            self.location_m,
            self.equipment_code,
            self.fault_code,
            self.notch_label,
            self.speed_kmh,
            self.overhead_v,
        ]


# ─────────────────────────────────────────────
# PARSER
# ─────────────────────────────────────────────
class RecordParser:
    """Parse raw payload CMD 0x36 menjadi list FailureRecord."""

    # Byte offsets dalam record 18 byte
    OFF_TIMESTAMP   = 0   # 6 bytes BCD
    OFF_CAR_ID      = 6   # 1 byte
    OFF_OCCUR       = 7   # 1 byte (0=occur, 1=recover)
    OFF_TRAIN_ID    = 8   # 2 bytes uint16
    OFF_LOCATION    = 10  # 2 bytes int16
    OFF_EQUIP       = 12  # 1 byte
    OFF_FAULT_SUB   = 13  # 1 byte
    OFF_FAULT_CODE  = 14  # 2 bytes uint16
    OFF_SPEED       = 16  # 1 byte
    OFF_OVERHEAD_V  = 17  # 1 byte (MSB, perlu konfirmasi)

    def parse_payload(self, payload: bytes, block_start: int = 1) -> List[FailureRecord]:
        """
        Parse payload CMD 0x36 (tanpa header 8 byte dan checksum 2 byte).
        Struktur payload: 0x00 + [18B record + FF FF] × N + 0x03

        Args:
            payload:     Raw payload bytes
            block_start: Block.No awal untuk sequence numbering

        Returns:
            List of FailureRecord
        """
        records = []

        # Lewati byte pertama (0x00 start marker)
        pos = 1
        block_no = block_start

        while pos + RECORD_SIZE <= len(payload) - 1:
            raw = payload[pos:pos + RECORD_SIZE]

            # Validasi: skip jika timestamp tidak valid
            if not is_valid_timestamp(raw, self.OFF_TIMESTAMP):
                pos += RECORD_SIZE + len(SEPARATOR)
                continue

            rec = self._parse_record(raw, block_no)
            records.append(rec)

            block_no += 1
            pos += RECORD_SIZE + len(SEPARATOR)  # lewati juga FF FF

        return records

    def _parse_record(self, raw: bytes, block_no: int) -> FailureRecord:
        """Parse satu record 18 byte."""
        timestamp   = decode_timestamp(raw, self.OFF_TIMESTAMP)
        car_id_byte = raw[self.OFF_CAR_ID]
        occur       = raw[self.OFF_OCCUR]
        train_id    = (raw[self.OFF_TRAIN_ID] << 8) | raw[self.OFF_TRAIN_ID + 1]
        location    = (raw[self.OFF_LOCATION] << 8) | raw[self.OFF_LOCATION + 1]
        if location > 32767:
            location -= 65536  # signed int16
        equip_code  = raw[self.OFF_EQUIP]
        fault_sub   = raw[self.OFF_FAULT_SUB]
        fault_code  = (raw[self.OFF_FAULT_CODE] << 8) | raw[self.OFF_FAULT_CODE + 1]
        speed       = raw[self.OFF_SPEED]
        overhead_v  = raw[self.OFF_OVERHEAD_V]  # NOTE: perlu konfirmasi 2-byte

        return FailureRecord(
            block_no       = block_no,
            timestamp      = timestamp,
            car_no         = get_car_number(car_id_byte),
            occur_recover  = occur,
            train_id       = train_id,
            location_m     = location,
            equipment_code = equip_code,
            fault_sub      = fault_sub,
            fault_code     = fault_code,
            notch_byte     = 0x00,  # TODO: decode dari packet context
            speed_kmh      = speed,
            overhead_v     = overhead_v,
            raw_bytes      = raw,
        )
