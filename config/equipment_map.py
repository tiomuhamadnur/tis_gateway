"""
config/equipment_map.py
========================
Mapping komprehensif untuk decoding data TIS:
  - Equipment code → nama equipment
  - Fault code     → abbreviation + deskripsi (125+ kode)
  - Notch code     → label command
  - Car ID         → Car number + tipe
  - Failure guidance → instruksi penanganan

Sumber data:
  1. Chapter 16 TIS Maintenance Manual (SRR-RST-GEN-0104-16D)
  2. Attachment 8 — Failure Guidance List of TIS (BH22001)
  3. Attachment 10 — Interface Specifications
  4. Cross-reference dengan CSV/PDF output PTU (D260507_282.csv, dudu_ts_5.pdf)

Tingkat kepercayaan setiap mapping ditandai dengan field `confidence`:
  - "C" (Confirmed) — terbukti dari cross-reference dengan output PTU asli
  - "E" (Extracted) — diekstrak dari dokumen manual
  - "R" (Range)     — hanya berdasarkan definisi range, detail belum ada
"""

from typing import Dict, Tuple, Optional, NamedTuple


# ═════════════════════════════════════════════════════════════════
# EQUIPMENT CODE → Nama
# ═════════════════════════════════════════════════════════════════
EQUIPMENT_MAP: Dict[int, str] = {
    1:  "TIS",              # Train Information System (codes 100-199)
    2:  "ATO",              # VOBC/ATO (codes 200-299)
    3:  "VVVF1",            # VVVF Inverter 1 (codes 300-x)
    4:  "VVVF2",            # VVVF Inverter 2 (codes 301-x)
    5:  "APS",              # Auxiliary Power Supply (codes 400-499)
    6:  "BECU",             # Brake Electric Control Unit (codes 500-599)
    7:  "ACE",              # Air Conditioning Equipment (codes 600-699)
    8:  "PID",              # Passenger Information Display (codes 700-799)
    9:  "PA",               # Public Address (codes 800-899)
    10: "DOOR",             # Door System (codes 900-999)
    11: "VMI",              # Vehicle Maintenance Information (codes 1000-1099)
    19: "Radio",            # Train Radio (codes 1100-1199)
    20: "CCTV",             # Closed Circuit TV (codes 1200-1299)
    21: "BatteryCharger",   # Battery Charger (codes 1300-1399)
    22: "Compressor",       # Air Compressor (codes 1400-1499)
    23: "DataRecorder",     # Data Recorder (codes 1500-1599)
}


# ═════════════════════════════════════════════════════════════════
# RANGE EQUIPMENT (untuk lookup berdasarkan fault code)
# ═════════════════════════════════════════════════════════════════
EQUIPMENT_BY_FAULT_RANGE = [
    (100,  199,  1,  "TIS"),
    (200,  299,  2,  "ATO"),
    (300,  399,  3,  "VVVF"),
    (400,  499,  5,  "APS"),
    (500,  599,  6,  "BECU"),
    (600,  699,  7,  "ACE"),
    (700,  799,  8,  "PID"),
    (800,  899,  9,  "PA"),
    (900,  999,  10, "DOOR"),
    (1000, 1099, 11, "VMI"),
    (1100, 1199, 19, "Radio"),
    (1200, 1299, 20, "CCTV"),
    (1300, 1399, 21, "BatteryCharger"),
    (1400, 1499, 22, "Compressor"),
    (1500, 1599, 23, "DataRecorder"),
]


# ═════════════════════════════════════════════════════════════════
# FAULT CODE → (Abbreviation, Deskripsi, Confidence)
# ═════════════════════════════════════════════════════════════════

class FaultInfo(NamedTuple):
    abbrev: str
    description: str
    confidence: str  # C=Confirmed, E=Extracted, R=Range-only


FAULT_DICT: Dict[int, FaultInfo] = {
    # ──────────────── TIS (100-199) ────────────────
    100: FaultInfo("DDUAT",     "DDU abnormal transmission",                       "E"),
    101: FaultInfo("ETH1AT11",  "1st RIO UNIT1 ETH1 abnormal transmission",        "E"),
    102: FaultInfo("ETH2AT11",  "1st RIO UNIT1 ETH2 abnormal transmission",        "E"),
    103: FaultInfo("ETH1AT12",  "2nd RIO UNIT1 ETH1 abnormal transmission",        "E"),
    104: FaultInfo("ETH2AT12",  "2nd RIO UNIT1 ETH2 abnormal transmission",        "E"),
    105: FaultInfo("ETH1AT21",  "1st RIO UNIT2 ETH1 abnormal transmission",        "E"),
    106: FaultInfo("ETH2AT21",  "1st RIO UNIT2 ETH2 abnormal transmission",        "E"),
    107: FaultInfo("ETH1AT22",  "2nd RIO UNIT2 ETH1 abnormal transmission",        "E"),
    108: FaultInfo("ETH2AT22",  "2nd RIO UNIT2 ETH2 abnormal transmission",        "E"),
    109: FaultInfo("IBA1A1",    "RIO UNIT1 IBA137A1 PCB abnormality",              "E"),
    110: FaultInfo("IBA1A2",    "RIO UNIT1 IBA137A2 PCB abnormality",              "E"),
    111: FaultInfo("IBA1A3",    "RIO UNIT1 IBA137A3 PCB abnormality",              "E"),
    112: FaultInfo("IBA1A4",    "RIO UNIT1 IBA137A4 PCB abnormality",              "E"),
    113: FaultInfo("IBA1A5",    "RIO UNIT1 IBA137A5 PCB abnormality",              "E"),
    114: FaultInfo("IBA2A1",    "RIO UNIT2 IBA137A1 PCB abnormality",              "E"),
    115: FaultInfo("IBA2A2",    "RIO UNIT2 IBA137A2 PCB abnormality",              "E"),
    116: FaultInfo("IBA2A3",    "RIO UNIT2 IBA137A3 PCB abnormality",              "E"),
    117: FaultInfo("IBA2A4",    "RIO UNIT2 IBA137A4 PCB abnormality",              "E"),
    118: FaultInfo("IBA2A5",    "RIO UNIT2 IBA137A5 PCB abnormality",              "E"),
    119: FaultInfo("IBA2A6",    "RIO UNIT2 IBA137A6 PCB abnormality",              "E"),
    120: FaultInfo("SDCA",      "CCU/MON UNIT SD Card abnormality",                "E"),
    121: FaultInfo("ESA",       "CCU/MON UNIT Ethernet abnormality",               "C"),
    122: FaultInfo("PS1RA",     "CCU/MON UNIT PS1R abnormality",                   "E"),
    123: FaultInfo("PS1LA",     "CCU/MON UNIT PS1L abnormality",                   "E"),
    124: FaultInfo("CPUA",      "CCU/MON UNIT CPU abnormality",                    "E"),
    125: FaultInfo("CMUSU",     "CCU/MON UNIT 1st & 2nd systems start up",         "E"),
    126: FaultInfo("CMUSB",     "CCU/MON UNIT 1st & 2nd systems standby",          "E"),
    127: FaultInfo("CCUAT",     "CCU/MON UNIT abnormal transmission",              "E"),
    128: FaultInfo("RIO1AT1",   "1st RIO UNIT1 abnormal transmission",             "E"),
    129: FaultInfo("RIO1AT2",   "2nd RIO UNIT1 abnormal transmission",             "E"),
    130: FaultInfo("RIO2AT1",   "1st RIO UNIT2 abnormal transmission",             "E"),
    131: FaultInfo("RIO2AT2",   "2nd RIO UNIT2 abnormal transmission",             "E"),

    # ──────────────── VOBC / ATO (200-299) ────────────────
    200: FaultInfo("ATOAT",     "ATO abnormal transmission",                       "C"),
    201: FaultInfo("LBF",       "Tc1 logic block fault",                           "E"),
    202: FaultInfo("IBF",       "Tc1 IF block fault",                              "E"),
    203: FaultInfo("RBF",       "Tc1 relay block fault",                           "E"),
    204: FaultInfo("TGF",       "Tc1 TG (Tachometer Generator) fault",             "E"),
    205: FaultInfo("BA",        "Tc1 balise antenna fault",                        "E"),
    206: FaultInfo("DMIF",      "Tc1 DMI (Driver Machine Interface) fault",        "E"),
    207: FaultInfo("VRSF",      "Tc1 VRS (Vehicle Radio System, out of sync)",     "E"),
    208: FaultInfo("LBIBF",     "Tc1 logic block - IF block communication fault",  "E"),
    209: FaultInfo("LBRBF",     "Tc1 logic block - relay block communication",     "E"),
    210: FaultInfo("LBOIBF",    "Tc1 logic block - other IF block communication",  "E"),
    211: FaultInfo("LBVRS1F",   "Tc1 logic block - VRS1 communication fault",      "C"),
    212: FaultInfo("LBVRS2F",   "Tc1 logic block - VRS2 communication fault",      "C"),
    213: FaultInfo("LBDMIF",    "Tc1 logic block - DMI communication fault",       "E"),
    214: FaultInfo("LBTISF",    "Tc1 logic block - TIS communication fault",       "E"),
    215: FaultInfo("LBF2",      "Tc2 logic block fault",                           "E"),
    216: FaultInfo("IBF2",      "Tc2 IF block fault",                              "E"),
    217: FaultInfo("RBF2",      "Tc2 relay block fault",                           "E"),
    218: FaultInfo("TGF2",      "Tc2 TG fault",                                    "E"),
    219: FaultInfo("BA2",       "Tc2 balise antenna fault",                        "E"),
    220: FaultInfo("DMIF2",     "Tc2 DMI fault",                                   "E"),
    221: FaultInfo("VRSF2",     "Tc2 VRS (out of sync)",                           "E"),
    222: FaultInfo("LBIBF2",    "Tc2 logic block - IF block communication",        "E"),
    223: FaultInfo("IBOLBF",    "Tc2 IF block - other logic block",                "E"),
    225: FaultInfo("LBVRS1F2",  "Tc2 logic block - VRS1 communication",            "E"),
    226: FaultInfo("LBVRS2F2",  "Tc2 logic block - VRS2 communication",            "E"),
    228: FaultInfo("LBTISF2",   "Tc2 logic block - TIS communication",             "E"),

    # ──────────────── VVVF (300-399) ────────────────
    300: FaultInfo("NVCPS",     "No VVVF1 control power supply",                   "C"),
    301: FaultInfo("NVCPS2",    "No VVVF2 control power supply",                   "C"),
    302: FaultInfo("VVVFCB",    "VVVF Circuit Breaker drive fault",                "E"),
    306: FaultInfo("VVVFINV",   "VVVF inverter fault (cut-out required)",          "E"),
    308: FaultInfo("VVVFTRC",   "VVVF traction fault (Neutral position required)", "E"),
    377: FaultInfo("VVVFAT1",   "2 bank VVVF abnormal transmission Bank 1",        "E"),

    # ──────────────── APS (400-499) ────────────────
    400: FaultInfo("NAPCPS",    "No APS control power supply",                     "C"),
    401: FaultInfo("APSAT",     "APS abnormal transmission",                       "E"),
    402: FaultInfo("INOC",      "APS Input over current",                          "E"),
    403: FaultInfo("IVNOC",     "APS Inverter over current",                       "E"),
    404: FaultInfo("STFD",      "APS Starting failure detection",                  "E"),
    405: FaultInfo("FCUNV",     "APS Filter capacitor unbalance",                  "E"),
    408: FaultInfo("OLD",       "APS Output over load detection",                  "E"),
    409: FaultInfo("OVD",       "APS Output over voltage",                         "E"),
    410: FaultInfo("IVLVD",     "APS Inverter output low voltage",                 "E"),
    411: FaultInfo("KAE",       "APS Contactor answer error",                      "E"),
    412: FaultInfo("PN15LVD",   "APS 3-phase main contactor PN15 low voltage",     "E"),
    416: FaultInfo("THYTHD",    "APS Thyristor thermal detection",                 "E"),
    417: FaultInfo("IVTHD",     "APS Inverter thermal detection",                  "E"),
    418: FaultInfo("TEST",      "APS fault for testing",                           "E"),
    419: FaultInfo("IVFR",      "APS fault (Inverter Fault Relay)",                "C"),

    # ──────────────── BECU (500-599) ────────────────
    500: FaultInfo("NBPS",      "No brake power supply",                          "C"),
    501: FaultInfo("BAT",       "Brake abnormal transmission",                    "E"),
    502: FaultInfo("SAVF",      "Service brake valve AV failure",                 "E"),
    503: FaultInfo("SRVF",      "Service brake valve RV failure",                 "E"),
    504: FaultInfo("AS1PF",     "AS1 pressure sensor failure",                    "E"),
    505: FaultInfo("AS2PF",     "AS2 pressure sensor failure",                    "E"),
    506: FaultInfo("BCPF",      "BC pressure sensor failure",                     "E"),
    507: FaultInfo("ACPF",      "AC pressure sensor failure",                     "E"),
    508: FaultInfo("MRPF",      "MR pressure sensor failure",                     "E"),
    509: FaultInfo("LSA",       "Load signal abnormality",                        "E"),
    510: FaultInfo("RBSA",      "Regenerative brake pattern signal abnormality",  "E"),
    511: FaultInfo("RFBSA",     "Regenerative brake feedback signal abnormality", "E"),
    512: FaultInfo("ISBD",      "Insufficient service brake detection",           "E"),
    513: FaultInfo("NRBD",      "Non-release brake detection",                    "E"),
    514: FaultInfo("SS1F",      "Speed sensor1 failure",                          "E"),
    515: FaultInfo("SS2F",      "Speed sensor2 failure",                          "E"),
    516: FaultInfo("SS3F",      "Speed sensor3 failure",                          "E"),
    517: FaultInfo("SS4F",      "Speed sensor4 failure",                          "E"),
    518: FaultInfo("SSPF",      "Speed sensor power failure",                     "E"),
    519: FaultInfo("WSP1F",     "Wheel slide protection valve1 failure",          "E"),
    520: FaultInfo("WSP2F",     "Wheel slide protection valve2 failure",          "E"),
    521: FaultInfo("TRCE",      "Test run check error",                           "E"),
    522: FaultInfo("MTCF",      "M-T car unit control failure",                   "E"),
    523: FaultInfo("RS1F",      "RS485 1st system failure",                      "E"),
    524: FaultInfo("RS2F",      "RS485 2nd system failure",                      "E"),
    525: FaultInfo("BECUF",     "BECU fault",                                     "E"),
    526: FaultInfo("BAT1",      "Brake 1st abnormal transmission",               "E"),
    527: FaultInfo("BAT2",      "Brake 2nd abnormal transmission",               "E"),

    # ──────────────── ACE (600-699) ────────────────
    600: FaultInfo("NACCPS",    "No ACE control power supply",                     "C"),
    601: FaultInfo("ACEAT",     "ACE abnormal transmission",                       "E"),
    602: FaultInfo("EF11NG",    "Air Conditioning Unit 1 Evaporator Fan 1 Failure", "E"),
    603: FaultInfo("EF12NG",    "Air Conditioning Unit 1 Evaporator Fan 2 Failure", "E"),
    604: FaultInfo("CF11NG",    "Air Conditioning Unit 1 Condenser Fan 1 Failure",  "E"),
    605: FaultInfo("CF12NG",    "Air Conditioning Unit 1 Condenser Fan 2 Failure",  "E"),
    606: FaultInfo("CR11NG",    "Air Conditioning Unit 1 Over Load Relay for Compressor 11 Failure", "E"),
    607: FaultInfo("CR12NG",    "Air Conditioning Unit 1 Over Load Relay for Compressor 12 Failure", "E"),
    608: FaultInfo("CR13NG",    "Air Conditioning Unit 1 Over Load Relay for Compressor 13 Failure", "E"),
    609: FaultInfo("HP11NG",    "Air Conditioning Unit 1 High Pressure Switch for Compressor 11 Failure", "E"),
    610: FaultInfo("HP12NG",    "Air Conditioning Unit 1 High Pressure Switch for Compressor 12 Failure", "E"),
    611: FaultInfo("HP13NG",    "Air Conditioning Unit 1 High Pressure Switch for Compressor 13 Failure", "E"),
    612: FaultInfo("LP11NG",    "Air Conditioning Unit 1 Low Pressure Switch for Compressor 11 Failure", "E"),
    613: FaultInfo("LP12NG",    "Air Conditioning Unit 1 Low Pressure Switch for Compressor 12 Failure", "E"),
    614: FaultInfo("LP13NG",    "Air Conditioning Unit 1 Low Pressure Switch for Compressor 13 Failure", "E"),
    615: FaultInfo("Th11NG",    "Air Conditioning Unit 1 Inner Thermo Relay for Compressor 11 Failure", "E"),
    616: FaultInfo("Th12NG",    "Air Conditioning Unit 1 Inner Thermo Relay for Compressor 12 Failure", "E"),
    617: FaultInfo("Th13NG",    "Air Conditioning Unit 1 Inner Thermo Relay for Compressor 13 Failure", "E"),
    618: FaultInfo("EF11LK",    "Air Conditioning Unit 1 Evaporator Fan 1 Lock Out", "E"),
    619: FaultInfo("EF12LK",    "Air Conditioning Unit 1 Evaporator Fan 2 Lock Out", "E"),
    620: FaultInfo("CF11LK",    "Air Conditioning Unit 1 Condenser Fan 1 Lock Out", "E"),
    621: FaultInfo("CF12LK",    "Air Conditioning Unit 1 Condenser Fan 2 Lock Out", "E"),
    622: FaultInfo("CR11LK",    "Air Conditioning Unit 1 Over Load Relay for Compressor 11 Lock Out", "E"),
    623: FaultInfo("CR12LK",    "Air Conditioning Unit 1 Over Load Relay for Compressor 12 Lock Out", "E"),
    624: FaultInfo("CR13LK",    "Air Conditioning Unit 1 Over Load Relay for Compressor 13 Lock Out", "E"),
    625: FaultInfo("HP11LK",    "Air Conditioning Unit 1 High Pressure Switch for Compressor 11 Lock Out", "E"),
    626: FaultInfo("HP12LK",    "Air Conditioning Unit 1 High Pressure Switch for Compressor 12 Lock Out", "E"),
    627: FaultInfo("HP13LK",    "Air Conditioning Unit 1 High Pressure Switch for Compressor 13 Lock Out", "E"),
    628: FaultInfo("LP11LK",    "Air Conditioning Unit 1 Low Pressure Switch for Compressor 11 Lock Out", "E"),
    629: FaultInfo("LP12LK",    "Air Conditioning Unit 1 Low Pressure Switch for Compressor 12 Lock Out", "E"),
    630: FaultInfo("LP13LK",    "Air Conditioning Unit 1 Low Pressure Switch for Compressor 13 Lock Out", "E"),
    631: FaultInfo("Th11LK",    "Air Conditioning Unit 1 Inner Thermo Relay for Compressor 11 Lock Out", "E"),
    632: FaultInfo("Th12LK",    "Air Conditioning Unit 1 Inner Thermo Relay for Compressor 12 Lock Out", "E"),
    633: FaultInfo("Th13LK",    "Air Conditioning Unit 1 Inner Thermo Relay for Compressor 13 Lock Out", "E"),
    634: FaultInfo("EF21NG",    "Air Conditioning Unit 2 Evaporator Fan 1 Failure", "E"),
    635: FaultInfo("EF22NG",    "Air Conditioning Unit 2 Evaporator Fan 2 Failure", "E"),
    636: FaultInfo("CF21NG",    "Air Conditioning Unit 2 Condenser Fan 1 Failure",  "E"),
    637: FaultInfo("CF22NG",    "Air Conditioning Unit 2 Condenser Fan 2 Failure",  "E"),
    638: FaultInfo("CR21NG",    "Air Conditioning Unit 2 Over Load Relay for Compressor 21 Failure", "E"),
    639: FaultInfo("CR22NG",    "Air Conditioning Unit 2 Over Load Relay for Compressor 22 Failure", "E"),
    640: FaultInfo("CR23NG",    "Air Conditioning Unit 2 Over Load Relay for Compressor 23 Failure", "E"),
    641: FaultInfo("HP21NG",    "Air Conditioning Unit 2 High Pressure Switch for Compressor 21 Failure", "E"),
    642: FaultInfo("HP22NG",    "Air Conditioning Unit 2 High Pressure Switch for Compressor 22 Failure", "E"),
    643: FaultInfo("HP23NG",    "Air Conditioning Unit 2 High Pressure Switch for Compressor 23 Failure", "E"),
    644: FaultInfo("LP21NG",    "Air Conditioning Unit 2 Low Pressure Switch for Compressor 21 Failure", "E"),
    645: FaultInfo("LP22NG",    "Air Conditioning Unit 2 Low Pressure Switch for Compressor 22 Failure", "E"),
    646: FaultInfo("LP23NG",    "Air Conditioning Unit 2 Low Pressure Switch for Compressor 23 Failure", "E"),
    647: FaultInfo("Th21NG",    "Air Conditioning Unit 2 Inner Thermo Relay for Compressor 21 Failure", "E"),
    648: FaultInfo("Th22NG",    "Air Conditioning Unit 2 Inner Thermo Relay for Compressor 22 Failure", "E"),
    649: FaultInfo("Th23NG",    "Air Conditioning Unit 2 Inner Thermo Relay for Compressor 23 Failure", "E"),
    650: FaultInfo("LFCNG",     "Contactor for Linear Fan Failure",                "E"),
    651: FaultInfo("EF21LK",    "Air Conditioning Unit 2 Evaporator Fan 1 Lock Out", "E"),
    652: FaultInfo("EF22LK",    "Air Conditioning Unit 2 Evaporator Fan 2 Lock Out", "E"),
    653: FaultInfo("CF21LK",    "Air Conditioning Unit 2 Condenser Fan 1 Lock Out", "E"),
    654: FaultInfo("CF22LK",    "Air Conditioning Unit 2 Condenser Fan 2 Lock Out", "E"),
    655: FaultInfo("CR21LK",    "Air Conditioning Unit 2 Over Load Relay for Compressor 21 Lock Out", "E"),
    656: FaultInfo("CR22LK",    "Air Conditioning Unit 2 Over Load Relay for Compressor 22 Lock Out", "E"),
    657: FaultInfo("CR23LK",    "Air Conditioning Unit 2 Over Load Relay for Compressor 23 Lock Out", "E"),
    658: FaultInfo("HP21LK",    "Air Conditioning Unit 2 High Pressure Switch for Compressor 21 Lock Out", "E"),
    659: FaultInfo("HP22LK",    "Air Conditioning Unit 2 High Pressure Switch for Compressor 22 Lock Out", "E"),
    660: FaultInfo("HP23LK",    "Air Conditioning Unit 2 High Pressure Switch for Compressor 23 Lock Out", "E"),
    661: FaultInfo("LP21LK",    "Air Conditioning Unit 2 Low Pressure Switch for Compressor 21 Lock Out", "E"),
    662: FaultInfo("LP22LK",    "Air Conditioning Unit 2 Low Pressure Switch for Compressor 22 Lock Out", "E"),
    663: FaultInfo("LP23LK",    "Air Conditioning Unit 2 Low Pressure Switch for Compressor 23 Lock Out", "E"),
    664: FaultInfo("Th21LK",    "Air Conditioning Unit 2 Inner Thermo Relay for Compressor 21 Lock Out", "E"),
    665: FaultInfo("Th22LK",    "Air Conditioning Unit 2 Inner Thermo Relay for Compressor 22 Lock Out", "E"),
    666: FaultInfo("Th23LK",    "Air Conditioning Unit 2 Inner Thermo Relay for Compressor 23 Lock Out", "E"),
    667: FaultInfo("LFCLK",     "Contactor for Linear Fan Lock Out",               "E"),
    668: FaultInfo("TR1NG",     "Return Air Temperature Sensor 1 Abnormal",        "E"),
    669: FaultInfo("TR2NG",     "Return Air Temperature Sensor 2 Abnormal",        "E"),
    670: FaultInfo("ACB1TR",    "Air Conditioning Unit Circuit Breaker 1 Trip",    "E"),
    671: FaultInfo("ACB2TR",    "Air Conditioning Unit Circuit Breaker 2 Trip",    "E"),
    672: FaultInfo("LFCBTR",    "Linear Fan circuit breaker Trip",                 "E"),

    # ──────────────── PID (700-799) ────────────────
    700: FaultInfo("NPICPS",    "No PID control power supply",                     "C"),
    701: FaultInfo("PIDAT",     "PID abnormal transmission",                       "E"),
    702: FaultInfo("SDD1F",    "SDD1 fault",                                     "E"),
    703: FaultInfo("SDD2F",    "SDD2 fault",                                     "E"),
    704: FaultInfo("EDDF",      "EDD fault",                                      "E"),
    705: FaultInfo("PSD1F",     "PSD1 fault",                                     "E"),
    706: FaultInfo("PSD2F",     "PSD2 fault",                                     "E"),
    707: FaultInfo("PSD3F",     "PSD3 fault",                                     "E"),
    708: FaultInfo("PSD4F",     "PSD4 fault",                                     "E"),
    709: FaultInfo("PSD5F",     "PSD5 fault",                                     "E"),
    710: FaultInfo("PSD6F",     "PSD6 fault",                                     "E"),
    711: FaultInfo("PSD7F",     "PSD7 fault",                                     "E"),
    712: FaultInfo("PSD8F",     "PSD8 fault",                                     "E"),

    # ──────────────── PA (800-899) ────────────────
    800: FaultInfo("NPACPS",    "No APA control power supply",                    "C"),
    801: FaultInfo("PAAT",      "APA abnormal transmission",                      "E"),
    802: FaultInfo("CFCA",      "CF card abnormality",                             "E"),
    803: FaultInfo("OPA",       "Operation pattern abnormality",                  "E"),
    804: FaultInfo("CU",        "Code undefined",                                  "E"),
    805: FaultInfo("DATANS",    "Data A/B not selected",                          "E"),
    806: FaultInfo("DATASA",    "Data A/B select abnormality",                    "C"),

    # ──────────────── DOOR (900-999) ────────────────
    900: FaultInfo("DOORAT",    "Door abnormal transmission",                      "E"),
    901: FaultInfo("IPM",       "IPM error",                                      "E"),
    902: FaultInfo("CT",        "Current sensor abnormality",                     "E"),
    903: FaultInfo("ET",        "Encoder abnormality",                            "E"),
    904: FaultInfo("ME",        "EEPROM memory abnormality",                      "E"),
    905: FaultInfo("DLE",       "Locking sensor switch error",                    "E"),
    906: FaultInfo("OPE",       "Open time out error",                            "E"),
    907: FaultInfo("CLE",       "Close time out error",                           "E"),
    908: FaultInfo("NRD",       "Initial operation un-completing",                 "E"),
    909: FaultInfo("OV",        "Overvoltage",                                    "E"),

    # ──────────────── VMI (1000-1099) ────────────────
    1000: FaultInfo("NVMCPS",   "No VMI control power supply",                     "E"),
    1001: FaultInfo("VMAT",     "VMI abnormal transmission",                       "E"),
    1002: FaultInfo("VMCPU",    "CPU board fault",                                 "E"),
    1003: FaultInfo("VMCF",     "CF card fault",                                   "E"),
    1004: FaultInfo("VMPAR",    "Parameter fault",                                 "E"),
    1005: FaultInfo("VMAC1",    "No.1 acceleration sensor fault",                 "E"),
    1006: FaultInfo("VMAC2",    "No.2 acceleration sensor fault",                 "E"),

    # ──────────────── Train Radio (1100-1199) ────────────────
    1100: FaultInfo("NTRCPS",   "No Train Radio control power supply",             "C"),
    1101: FaultInfo("TRAT",     "Train Radio abnormal transmission",               "E"),
    1102: FaultInfo("RA",       "Train Radio abnormality",                         "E"),
    1103: FaultInfo("CMA",      "Train Radio control module abnormality",          "C"),

    # ──────────────── CCTV (1200-1299) ────────────────
    1200: FaultInfo("NCCPS",    "No CCTV control power supply",                    "C"),
    1201: FaultInfo("CCTVAT",   "CCTV abnormal transmission",                      "E"),
    1202: FaultInfo("RUA",      "CCTV Rx unit abnormality",                        "E"),
    1203: FaultInfo("WCE",      "CCTV WiFi connection error",                      "C"),
    1204: FaultInfo("CVE",      "CCTV video error",                                "C"),

    # ──────────────── Battery Charger (1300-1399) ────────────────
    1300: FaultInfo("NBCCPS",   "No Battery Charger control power supply",         "R"),
    1301: FaultInfo("BCAT",     "Battery Charger abnormal transmission",           "R"),
    1302: FaultInfo("BOT",      "Battery over temperature",                        "R"),

    # ──────────────── Compressor (1400-1499) ────────────────
    1400: FaultInfo("NCMCPS",   "No Compressor control power supply",              "R"),
    1401: FaultInfo("CMAT_C",   "Compressor abnormal transmission",                "R"),
    1402: FaultInfo("CMTHD",    "Compressor thermal detection",                    "R"),
    1403: FaultInfo("CPSLVD",   "Compressor power supply low voltage detection",    "R"),

    # ──────────────── Data Recorder (1500-1599) ────────────────
    1500: FaultInfo("DRAT",     "Data Recorder abnormal transmission",             "E"),
    1501: FaultInfo("CFCF",     "Data Recorder CF card fault",                     "E"),
    1502: FaultInfo("CFCWF",    "Data Recorder CF card write fail",                "E"),
    1503: FaultInfo("CFCRF",    "Data Recorder CF card read fail",                 "E"),
}


# ═════════════════════════════════════════════════════════════════
# FAILURE GUIDANCE TABLE (Table 6.1-2)
# ═════════════════════════════════════════════════════════════════
FAILURE_GUIDANCE: Dict[Tuple[int, int], str] = {
    (100, 228):    "Please report the occurrence of the failure to the OCC.",
    (300, 301):    "Please confirm the CB (Circuit Breaker) is drive.",
    (302, 305):    "Set Master Controller to 'Emergency' position; then press 'Reset Switch'.",
    (306, 307):    ("Cut out VVVF inverter. Set Master Controller to 'Emergency' position; "
                    "then press 'Cut-out Switch'. Once VVVF inverter failure released, press 'Reset Switch'."),
    (308, 337):    "Set Master Controller to 'Neutral' position; then press 'Reset Switch'.",
    (338, 365):    "Set Master Controller to 'Neutral' position; then press 'Reset Switch'.",
    (366, 367):    "Set Master Controller to 'Neutral' position; then press 'Reset Switch'.",
    (368, 377):    "N/A (No indication required).",
    (400, 419):    "Please report the occurrence of the failure to the OCC.",
    (500, 500):    "Please confirm if the Circuit Breaker for BECU is tripped or not.",
    (501, 501):    ("Please reboot the BECU and/or the TIS. If failure persists, "
                    "operate train to a station where the train can be changed."),
    (502, 502):    ("If failure on 1 car: operate to station for train exchange. "
                    "If failure on 2+ cars: operate to station with sidetrack."),
    (503, 503):    "Please reboot the Circuit Breaker of the failed BECU.",
    (504, 505):    "Operate the train to a station where the train can be changed and suspend the service operation.",
    (506, 507):    "Please reboot the Circuit Breaker of the failed BECU.",
    (508, 511):    "Operate the train to a station where the train can be changed and suspend the service operation.",
    (512, 512):    "Please reboot the Circuit Breaker of the failed BECU.",
    (513, 513):    "Please operate the brake cut-out switch and confirm that brake unrelease light turns off.",
    (514, 520):    "Operate the train to a station where the train can be changed.",
    (521, 521):    "Please reboot the Circuit Breaker of the failed BECU.",
    (522, 522):    "Operate the train to a station where the train can be changed.",
    (523, 524):    "When TIS is available, operate normally. If TIS disabled, operate by emergency operation.",
    (525, 525):    "A serious failure occurred. Please confirm details of the failure.",
    (526, 527):    "Please report the occurrence of the failure to the OCC.",
    (600, 802):    "Please report the occurrence of the failure to the OCC.",
    (803, 804):    "Please check the CF card.",
    (805, 805):    "Please select data A or B on the PA Data Select Screen.",
    (806, 806):    "The selected data is unable. Please select valid data on the PA Data Select Screen.",
    (900, 909):    "Please report the occurrence of the failure to the OCC.",
    (1000, 1006):  "Please report the occurrence of the failure to the OCC.",
    (1100, 1400):  "Please report the occurrence of the failure to the OCC.",
    (1401, 1401):  "Please report the occurrence of the failure to the OCC.",
    (1402, 1403):  "Please report the occurrence of the failure to the OCC.",
    (1500, 1503):  "Please report the occurrence of the failure to the OCC.",
}


# ═════════════════════════════════════════════════════════════════
# FAILURE CLASSIFICATION (Heavy / Light)
# ═════════════════════════════════════════════════════════════════
class FaultClass:
    HEAVY = "Heavy"
    LIGHT = "Light"
    INFO  = "Info"


FAULT_CLASS_BY_RANGE = [
    (100, 199, FaultClass.HEAVY),   # TIS — komunikasi error
    (200, 299, FaultClass.HEAVY),   # ATO — safety critical
    (300, 399, FaultClass.HEAVY),   # VVVF — propulsion
    (400, 499, FaultClass.HEAVY),   # APS — power supply
    (500, 599, FaultClass.HEAVY),   # BECU — brake (safety critical)
    (600, 699, FaultClass.LIGHT),   # ACE — air conditioning
    (700, 799, FaultClass.LIGHT),   # PID — passenger info display
    (800, 899, FaultClass.LIGHT),   # PA — public address
    (900, 999, FaultClass.HEAVY),   # DOOR — safety critical
    (1000, 1099, FaultClass.LIGHT), # VMI — maintenance info
    (1100, 1199, FaultClass.LIGHT), # Train Radio
    (1200, 1299, FaultClass.LIGHT), # CCTV
    (1300, 1399, FaultClass.HEAVY), # Battery Charger
    (1400, 1499, FaultClass.HEAVY), # Compressor (brake air supply)
    (1500, 1599, FaultClass.LIGHT), # Data Recorder
]


# ═════════════════════════════════════════════════════════════════
# NOTCH / COMMAND CODE → Label
# ═════════════════════════════════════════════════════════════════
# Encoding actually spans 3 bytes (b8/b9/b10) — bukan satu byte saja.
# Confirmed via TS13 PCAP (200 records, 19 unique (b8,b9,b10) combos) vs PTU asli.
#
# Decoding rule (semua 19 combos cocok 100%):
#   1. Jika b8 bit-0 = 1 → notch = "EB" (override; berasal dari wire 657R Emergency Brake)
#   2. Selain itu, decode dari (b9, b10):
#        b10 = 0x80  → Auto mode      → b9 menentukan: 0x00/0x80=Neutral, 0x01..0x10=A_Pn, 0x81..0x90=A_Bn
#        b10 = 0x00  → Auto mode juga → b9 menentukan (idem, hanya beda untuk A_Pn/A_Bn yang sesekali muncul)
#        b10 = 0x40  → Manual Brake   → step di b9 (TS13 hanya lihat M_B1 → step encoding belum penuh dikonfirmasi)
#        b10 = 0x08  → Manual Power   → step di b9 (TS13 hanya lihat M_P1)
#
# Sumber wire:
#   - 657R (EB asserted)  → b8 bit-0
#   - 657S (Neutral wire) → b9 bit-7 = 0x80
#   - ATO step (0..16)    → b9 low nibble + bit-7 untuk brake
#   - Manual P1-P4        → 648A-D 4-wire binary (b10=0x08 family)
#   - Manual B1-B7        → 648E-G Gray code (b10=0x40 family)

# Single-byte map (untuk b9-only lookup, backwards-compat dan UI tooltip).
# Untuk decoding penuh sesuai PTU pakai decode_notch(b8, b9, b10).
NOTCH_MAP: Dict[int, str] = {
    0x00: "EB",         # depot resting (legacy single-byte lookup)
    0x01: "A_P1",  0x02: "A_P2",  0x03: "A_P3",  0x04: "A_P4",
    0x05: "A_P5",  0x06: "A_P6",  0x07: "A_P7",  0x08: "A_P8",
    0x09: "A_P9",  0x0A: "A_P10", 0x0B: "A_P11", 0x0C: "A_P12",
    0x0D: "A_P13", 0x0E: "A_P14", 0x0F: "A_P15", 0x10: "A_P16",
    0x80: "Neutral",
    0x81: "A_B1",  0x82: "A_B2",  0x83: "A_B3",  0x84: "A_B4",
    0x85: "A_B5",  0x86: "A_B6",  0x87: "A_B7",  0x88: "A_B8",
    0x89: "A_B9",  0x8A: "A_B10", 0x8B: "A_B11", 0x8C: "A_B12",
    0x8D: "A_B13", 0x8E: "A_B14", 0x8F: "A_B15", 0x90: "A_B16",
}


# ═════════════════════════════════════════════════════════════════
# CAR FORMATION & CAR ID MAPPING
# ═════════════════════════════════════════════════════════════════
# Formation rangkaian MRTJ CP108 (6 car):
#   Tc1 - M1 - M2 - M1' - M2' - Tc2
CAR_ID_MAP: Dict[int, int] = {
    0x01: 1,   # ✅ Confirmed — Tc1 (Head A)
    0x02: 2,   # ❓ Belum confirmed — M1
    0x03: 3,   # ❓ Belum confirmed — M2
    0x04: 4,   # ❓ Belum confirmed — M1'
    0x05: 5,   # ❓ Belum confirmed — M2'
    0x06: 6,   # ✅ Confirmed — Tc2 (Head B), direct value dari record parser (PCAP TS5)
    # 0x11: 6 pernah dicatat dari sumber lain; belum bisa dikonfirmasi encoding-nya.
    #        record_parser.py menggunakan raw byte langsung (0x01-0x06), bukan melalui map ini.
}

CAR_TYPE_MAP: Dict[int, str] = {
    1: "Tc1",
    2: "M1",
    3: "M2",
    4: "M1'",
    5: "M2'",
    6: "Tc2",
}


# ═════════════════════════════════════════════════════════════════
# RIO UNIT — PCB MODULE MAPPING
# ═════════════════════════════════════════════════════════════════
RIO_PCB_MODULES: Dict[str, str] = {
    "MCB119":  "CPU module",
    "IBA137":  "Digital Input module (24VDC, photo-coupler)",
    "OBA59":   "Digital Output module (24VDC, photo-coupler)",
    "IFB159":  "Serial communication module (RS-485)",
    "PW99":    "Power Supply module (110VDC -> 24VDC)",
}


# ═════════════════════════════════════════════════════════════════
# OCCUR / RECOVER CODE
# ═════════════════════════════════════════════════════════════════
OCCUR_RECOVER_MAP: Dict[int, str] = {
    0: "Occur",     # Fault terjadi
    1: "Recover",   # Fault pulih
}


# ═════════════════════════════════════════════════════════════════
# LOOKUP HELPERS
# ═════════════════════════════════════════════════════════════════

def get_equipment_name(code: int) -> str:
    """Kembalikan nama equipment dari kode numeric."""
    return EQUIPMENT_MAP.get(code, f"EQ{code:02d}")


def get_equipment_by_fault_code(fault_code: int) -> Tuple[int, str]:
    """Tentukan equipment dari fault code berdasarkan range."""
    for lo, hi, eq_code, eq_name in EQUIPMENT_BY_FAULT_RANGE:
        if lo <= fault_code <= hi:
            return eq_code, eq_name
    return 0, "Unknown"


def get_fault_info(fault_code: int) -> FaultInfo:
    """Kembalikan FaultInfo lengkap. Fallback ke generic jika tidak terdaftar."""
    if fault_code in FAULT_DICT:
        return FAULT_DICT[fault_code]
    _, eq_name = get_equipment_by_fault_code(fault_code)
    return FaultInfo(
        abbrev=f"FC{fault_code:04d}",
        description=f"{eq_name} fault code {fault_code}",
        confidence="R",
    )


def get_fault_abbrev(fault_code: int) -> str:
    """Kembalikan abbreviation fault."""
    return get_fault_info(fault_code).abbrev


def get_fault_description(fault_code: int) -> str:
    """Kembalikan deskripsi fault."""
    return get_fault_info(fault_code).description


def get_fault_name(_equipment_code: int, fault_code: int) -> str:
    """Backward-compat helper untuk record_parser."""
    return get_fault_abbrev(fault_code)


def get_failure_guidance(fault_code: int) -> str:
    """Kembalikan instruksi penanganan untuk fault code tertentu."""
    for (lo, hi), guidance in FAILURE_GUIDANCE.items():
        if lo <= fault_code <= hi:
            return guidance
    return "Refer to maintenance manual or contact OCC."


def get_fault_classification(fault_code: int) -> str:
    """Kembalikan klasifikasi fault: Heavy/Light/Info."""
    for lo, hi, cls in FAULT_CLASS_BY_RANGE:
        if lo <= fault_code <= hi:
            return cls
    return FaultClass.INFO


def get_notch_label(notch_byte: int) -> str:
    """Single-byte lookup (b9-only). Untuk decoding penuh PTU-compatible pakai decode_notch()."""
    return NOTCH_MAP.get(notch_byte, f"N{notch_byte:02X}")


def decode_notch(status_byte: int, notch_step: int, notch_mode: int) -> str:
    """
    Decode notch dari 3-byte tuple (byte[8], byte[9], byte[10]) per record TIS.

    Rule (confirmed via TS13 PCAP 200 records vs PTU asli, 19/19 combo match):
      1. status_byte bit-0 = 1   → "EB" (override; wire 657R Emergency Brake asserted)
      2. notch_mode = 0x80 atau 0x00 → ATO mode (b9 menentukan step):
           b9 = 0x00 atau 0x80 → "Neutral"
           b9 = 0x01..0x10     → "A_P{1..16}"
           b9 = 0x81..0x90     → "A_B{1..16}"
      3. notch_mode = 0x40        → Manual Brake (TS13: hanya M_B1 dilihat)
      4. notch_mode = 0x08        → Manual Power (TS13: hanya M_P1 dilihat)
      5. fallback: "N{b9:02X}_{b10:02X}"
    """
    # Rule 1 — EB asserted via 657R wire
    if status_byte & 0x01:
        return "EB"

    # Rule 3/4 — Manual mode (TS13 confirmed: b10=0x40 → M_B, b10=0x08 → M_P)
    # Step encoding belum penuh dikonfirmasi (hanya step 1 muncul di TS13).
    # Manual brake/power dari wiring: 648A-D (4-wire bin) / 648E-G (Gray code) → step di b9 lower bits.
    if notch_mode == 0x40:
        step = notch_step & 0x0F  # asumsi step di low-nibble; perlu PCAP M_B>1 untuk konfirmasi
        return f"M_B{step or 1}"
    if notch_mode == 0x08:
        step = notch_step & 0x0F
        return f"M_P{step or 1}"

    # Rule 2 — ATO mode (notch_mode = 0x80 Auto, atau 0x00 saat EB-mode tapi bit-0 b8 belum aktif)
    if notch_step == 0x00 or notch_step == 0x80:
        # 0x00 + b10=0x00 secara teknis "EB-mode tapi 657R belum asserted" — di TS13 tidak pernah muncul
        # (b8 bit-0 selalu set saat kombinasi ini). Kalau muncul, label "Neutral" konsisten dengan b10=0x80.
        return "Neutral"
    if 0x01 <= notch_step <= 0x10:
        return f"A_P{notch_step}"
    if 0x81 <= notch_step <= 0x90:
        return f"A_B{notch_step & 0x0F}"

    return f"N{notch_step:02X}_{notch_mode:02X}"


def get_car_number(car_id_byte: int) -> int:
    """Kembalikan car number (1-6) dari byte pcap."""
    return CAR_ID_MAP.get(car_id_byte, car_id_byte)


def get_car_type(car_number: int) -> str:
    """Kembalikan tipe car (Tc1, M1, dll) dari car number."""
    return CAR_TYPE_MAP.get(car_number, f"Car{car_number:02d}")


def get_occur_recover(value: int) -> str:
    """Kembalikan label Occur/Recover dari byte value."""
    return OCCUR_RECOVER_MAP.get(value, f"State{value}")


def lookup_full(equipment_code: int, fault_code: int, car_id_byte: int,
                notch_byte: int) -> Tuple[str, str, int, str]:
    """Lookup semua field sekaligus (compatibility wrapper)."""
    return (
        get_equipment_name(equipment_code),
        get_fault_abbrev(fault_code),
        get_car_number(car_id_byte),
        get_notch_label(notch_byte),
    )


def lookup_complete(fault_code: int, car_id_byte: int, notch_byte: int,
                    occur_value: int) -> Dict:
    """Lookup semua informasi dengan detail lengkap."""
    eq_code, eq_name = get_equipment_by_fault_code(fault_code)
    fault_info       = get_fault_info(fault_code)
    return {
        "equipment_code":       eq_code,
        "equipment_name":       eq_name,
        "fault_code":           fault_code,
        "fault_abbrev":         fault_info.abbrev,
        "fault_description":    fault_info.description,
        "fault_classification": get_fault_classification(fault_code),
        "fault_guidance":       get_failure_guidance(fault_code),
        "fault_confidence":     fault_info.confidence,
        "car_number":           get_car_number(car_id_byte),
        "car_type":             get_car_type(get_car_number(car_id_byte)),
        "notch_label":          get_notch_label(notch_byte),
        "occur_recover":        get_occur_recover(occur_value),
    }


# ═════════════════════════════════════════════════════════════════
# STATISTICS
# ═════════════════════════════════════════════════════════════════
def get_dictionary_stats() -> Dict:
    """Statistik dictionary untuk debugging/laporan."""
    by_conf = {"C": 0, "E": 0, "R": 0}
    for info in FAULT_DICT.values():
        by_conf[info.confidence] = by_conf.get(info.confidence, 0) + 1
    return {
        "total_equipment_codes": len(EQUIPMENT_MAP),
        "total_fault_codes":     len(FAULT_DICT),
        "total_notch_codes":     len(NOTCH_MAP),
        "total_guidance_ranges": len(FAILURE_GUIDANCE),
        "fault_codes_by_confidence": {
            "Confirmed (CSV/PDF)":  by_conf["C"],
            "Extracted (manual)":   by_conf["E"],
            "Range-only":           by_conf["R"],
        },
    }


if __name__ == "__main__":
    import json
    print("Equipment Map Statistics:")
    print(json.dumps(get_dictionary_stats(), indent=2))
