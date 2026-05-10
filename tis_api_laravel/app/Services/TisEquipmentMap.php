<?php

namespace App\Services;

/**
 * Port dari config/equipment_map.py (Python TIS Gateway).
 * Semua mapping bersumber dari:
 *   - Chapter 16 TIS Maintenance Manual (SRR-RST-GEN-0104-16D)
 *   - Attachment 8 — Failure Guidance List of TIS (BH22001)
 *   - Cross-reference dengan output PTU asli (D260507_282.csv)
 *
 * Confidence: C=Confirmed, E=Extracted, R=Range-only
 */
class TisEquipmentMap
{
    // ═══════════════════════════════════════════════════════
    // EQUIPMENT CODE → Nama
    // ═══════════════════════════════════════════════════════
    private static array $equipmentMap = [
        1  => 'TIS',
        2  => 'ATO',
        3  => 'VVVF1',
        4  => 'VVVF2',
        5  => 'APS',
        6  => 'BECU',
        7  => 'ACE',
        8  => 'PID',
        9  => 'PA',
        10 => 'DOOR',
        11 => 'VMI',
        19 => 'Radio',
        20 => 'CCTV',
        21 => 'BatteryCharger',
        22 => 'Compressor',
        23 => 'DataRecorder',
    ];

    // ═══════════════════════════════════════════════════════
    // FAULT CODE → [abbrev, description, confidence]
    // ═══════════════════════════════════════════════════════
    private static array $faultDict = [
        // TIS (100-199)
        100 => ['DDUAT',    'DDU abnormal transmission',                            'E'],
        101 => ['ETH1AT11', '1st RIO UNIT1 ETH1 abnormal transmission',             'E'],
        102 => ['ETH2AT11', '1st RIO UNIT1 ETH2 abnormal transmission',             'E'],
        103 => ['ETH1AT12', '2nd RIO UNIT1 ETH1 abnormal transmission',             'E'],
        104 => ['ETH2AT12', '2nd RIO UNIT1 ETH2 abnormal transmission',             'E'],
        105 => ['ETH1AT21', '1st RIO UNIT2 ETH1 abnormal transmission',             'E'],
        106 => ['ETH2AT21', '1st RIO UNIT2 ETH2 abnormal transmission',             'E'],
        107 => ['ETH1AT22', '2nd RIO UNIT2 ETH1 abnormal transmission',             'E'],
        108 => ['ETH2AT22', '2nd RIO UNIT2 ETH2 abnormal transmission',             'E'],
        109 => ['IBA1A1',   'RIO UNIT1 IBA137A1 PCB abnormality',                   'E'],
        110 => ['IBA1A2',   'RIO UNIT1 IBA137A2 PCB abnormality',                   'E'],
        111 => ['IBA1A3',   'RIO UNIT1 IBA137A3 PCB abnormality',                   'E'],
        112 => ['IBA1A4',   'RIO UNIT1 IBA137A4 PCB abnormality',                   'E'],
        113 => ['IBA1A5',   'RIO UNIT1 IBA137A5 PCB abnormality',                   'E'],
        114 => ['IBA2A1',   'RIO UNIT2 IBA137A1 PCB abnormality',                   'E'],
        115 => ['IBA2A2',   'RIO UNIT2 IBA137A2 PCB abnormality',                   'E'],
        116 => ['IBA2A3',   'RIO UNIT2 IBA137A3 PCB abnormality',                   'E'],
        117 => ['IBA2A4',   'RIO UNIT2 IBA137A4 PCB abnormality',                   'E'],
        118 => ['IBA2A5',   'RIO UNIT2 IBA137A5 PCB abnormality',                   'E'],
        119 => ['IBA2A6',   'RIO UNIT2 IBA137A6 PCB abnormality',                   'E'],
        120 => ['SDCA',     'CCU/MON UNIT SD Card abnormality',                     'E'],
        121 => ['ESA',      'CCU/MON UNIT Ethernet abnormality',                    'C'],
        122 => ['PS1RA',    'CCU/MON UNIT PS1R abnormality',                        'E'],
        123 => ['PS1LA',    'CCU/MON UNIT PS1L abnormality',                        'E'],
        124 => ['CPUA',     'CCU/MON UNIT CPU abnormality',                         'E'],
        125 => ['CMUSU',    'CCU/MON UNIT 1st & 2nd systems start up',              'E'],
        126 => ['CMUSB',    'CCU/MON UNIT 1st & 2nd systems standby',               'E'],
        127 => ['CCUAT',    'CCU/MON UNIT abnormal transmission',                   'E'],
        128 => ['RIO1AT1',  '1st RIO UNIT1 abnormal transmission',                  'E'],
        129 => ['RIO1AT2',  '2nd RIO UNIT1 abnormal transmission',                  'E'],
        130 => ['RIO2AT1',  '1st RIO UNIT2 abnormal transmission',                  'E'],
        131 => ['RIO2AT2',  '2nd RIO UNIT2 abnormal transmission',                  'E'],

        // ATO (200-299)
        200 => ['ATOAT',   'ATO abnormal transmission',                             'C'],
        201 => ['LBF',     'Tc1 logic block fault',                                 'E'],
        202 => ['IBF',     'Tc1 IF block fault',                                    'E'],
        203 => ['RBF',     'Tc1 relay block fault',                                 'E'],
        204 => ['TGF',     'Tc1 TG (Tachometer Generator) fault',                  'E'],
        205 => ['BA',      'Tc1 balise antenna fault',                              'E'],
        206 => ['DMIF',    'Tc1 DMI (Driver Machine Interface) fault',              'E'],
        207 => ['VRSF',    'Tc1 VRS (Vehicle Radio System, out of sync)',           'E'],
        208 => ['LBIBF',   'Tc1 logic block - IF block communication fault',        'E'],
        209 => ['LBRBF',   'Tc1 logic block - relay block communication',           'E'],
        210 => ['LBOIBF',  'Tc1 logic block - other IF block communication',        'E'],
        211 => ['LBVRS1F', 'Tc1 logic block - VRS1 communication fault',            'C'],
        212 => ['LBVRS2F', 'Tc1 logic block - VRS2 communication fault',            'C'],
        213 => ['LBDMIF',  'Tc1 logic block - DMI communication fault',             'E'],
        214 => ['LBTISF',  'Tc1 logic block - TIS communication fault',             'E'],
        215 => ['LBF2',    'Tc2 logic block fault',                                 'E'],
        216 => ['IBF2',    'Tc2 IF block fault',                                    'E'],
        217 => ['RBF2',    'Tc2 relay block fault',                                 'E'],
        218 => ['TGF2',    'Tc2 TG fault',                                          'E'],
        219 => ['BA2',     'Tc2 balise antenna fault',                              'E'],
        220 => ['DMIF2',   'Tc2 DMI fault',                                         'E'],
        221 => ['VRSF2',   'Tc2 VRS (out of sync)',                                 'E'],
        222 => ['LBIBF2',  'Tc2 logic block - IF block communication',              'E'],
        223 => ['IBOLBF',  'Tc2 IF block - other logic block',                      'E'],
        225 => ['LBVRS1F2','Tc2 logic block - VRS1 communication',                  'E'],
        226 => ['LBVRS2F2','Tc2 logic block - VRS2 communication',                  'E'],
        228 => ['LBTISF2', 'Tc2 logic block - TIS communication',                   'E'],

        // VVVF (300-399)
        300 => ['NVCPS',   'No VVVF1 control power supply',                         'C'],
        301 => ['NVCPS2',  'No VVVF2 control power supply',                         'C'],
        302 => ['VVVFCB',  'VVVF Circuit Breaker drive fault',                       'E'],
        306 => ['VVVFINV', 'VVVF inverter fault (cut-out required)',                  'E'],
        308 => ['VVVFTRC', 'VVVF traction fault (Neutral position required)',         'E'],
        377 => ['VVVFAT1', '2 bank VVVF abnormal transmission Bank 1',               'E'],

        // APS (400-499)
        400 => ['NAPCPS',  'No APS control power supply',                            'C'],
        401 => ['APSAT',   'APS abnormal transmission',                              'E'],
        402 => ['INOC',    'APS Input over current',                                 'E'],
        403 => ['IVNOC',   'APS Inverter over current',                              'E'],
        404 => ['STFD',    'APS Starting failure detection',                         'E'],
        405 => ['FCUNV',   'APS Filter capacitor unbalance',                         'E'],
        408 => ['OLD',     'APS Output over load detection',                         'E'],
        409 => ['OVD',     'APS Output over voltage',                                'E'],
        410 => ['IVLVD',   'APS Inverter output low voltage',                        'E'],
        411 => ['KAE',     'APS Contactor answer error',                             'E'],
        412 => ['PN15LVD', 'APS 3-phase main contactor PN15 low voltage',            'E'],
        416 => ['THYTHD',  'APS Thyristor thermal detection',                        'E'],
        417 => ['IVTHD',   'APS Inverter thermal detection',                         'E'],
        418 => ['TEST',    'APS fault for testing',                                  'E'],
        419 => ['IVFR',    'APS fault (Inverter Fault Relay)',                       'C'],

        // BECU (500-599)
        500 => ['NBPS',     'No BECU control power supply',                         'C'],
        501 => ['BECUREB',  'BECU reboot required',                                  'E'],
        502 => ['BECUMAJ',  'BECU major fault — operate to siding',                  'E'],
        503 => ['BECUCB1',  'BECU CB reboot required (group 1)',                     'E'],
        506 => ['BECUCB2',  'BECU CB reboot required (group 2)',                     'E'],
        507 => ['BECUCB3',  'BECU CB reboot required (group 3)',                     'E'],
        512 => ['BECUCB4',  'BECU CB reboot required (group 4)',                     'E'],
        513 => ['BRKUNREL', 'Brake unrelease — operate brake cut-out switch',        'E'],
        521 => ['BECUCB5',  'BECU CB reboot required (group 5)',                     'E'],
        525 => ['BECUF',    'BECU serious failure',                                  'E'],
        527 => ['BAT1',     'Brake abnormal transmission',                           'E'],

        // ACE (600-699)
        600 => ['NACCPS',  'No ACE control power supply',                            'C'],
        601 => ['ACEAT',   'ACE abnormal transmission',                              'E'],

        // PID (700-799)
        700 => ['NPICPS',  'No PID control power supply',                            'C'],
        701 => ['PIDAT',   'PID abnormal transmission',                              'E'],
        705 => ['PSD1F',   'PID PSD1 fault (Platform Screen Door 1)',                'E'],
        706 => ['PSD2F',   'PID PSD2 fault',                                         'E'],
        707 => ['PSD3F',   'PID PSD3 fault',                                         'E'],
        708 => ['PSD4F',   'PID PSD4 fault',                                         'E'],
        709 => ['PSD5F',   'PID PSD5 fault',                                         'E'],
        710 => ['PSD6F',   'PID PSD6 fault',                                         'E'],
        711 => ['PSD7F',   'PID PSD7 fault',                                         'E'],
        712 => ['PSD8F',   'PID PSD8 fault',                                         'E'],

        // PA (800-899)
        800 => ['NPACPS',  'No PA control power supply',                             'C'],
        801 => ['PAAT',    'PA abnormal transmission',                               'E'],
        802 => ['CFCA',    'PA CF card abnormality — please check CF card',          'E'],
        803 => ['OPA',     'PA Operation pattern abnormality',                       'E'],
        804 => ['CU',      'PA Code undefined',                                      'E'],
        805 => ['DATANS',  'PA data A or B not selected',                            'E'],
        806 => ['DATASA',  'PA data selection abnormal — select valid data',         'C'],

        // DOOR (900-999)
        900 => ['DOORAT',  'Door abnormal transmission',                             'E'],
        901 => ['IPM',     'Door IPM error',                                         'E'],
        905 => ['DLE',     'Door locking sensor switch error',                       'E'],
        906 => ['OPE',     'Door open time out error',                               'E'],
        907 => ['CLE',     'Door close time out error',                              'E'],
        908 => ['NRD',     'Door initial operation fault',                           'E'],

        // VMI (1000-1099)
        1000 => ['NVMCPS', 'No VMI control power supply',                            'E'],
        1001 => ['VMAT',   'VMI abnormal transmission',                              'E'],
        1002 => ['VMCPU',  'VMI CPU board fault',                                    'E'],
        1003 => ['VMCF',   'VMI CF card fault',                                      'E'],
        1004 => ['VMPAR',  'VMI parameter fault',                                    'E'],
        1005 => ['VMAC1',  'VMI AC1 fault',                                          'E'],
        1006 => ['VMAC2',  'VMI AC2 fault',                                          'E'],

        // Radio (1100-1199)
        1100 => ['NTRCPS', 'No Train Radio control power supply',                    'C'],
        1101 => ['TRAT',   'Train Radio abnormal transmission',                      'E'],
        1102 => ['RA',     'Train Radio abnormality',                                'E'],
        1103 => ['CMA',    'Train Radio control module abnormality',                 'C'],

        // CCTV (1200-1299)
        1200 => ['NCCPS',  'No CCTV control power supply',                          'C'],
        1201 => ['CCTVAT', 'CCTV abnormal transmission',                             'E'],
        1202 => ['RUA',    'CCTV Rx unit abnormality',                               'E'],
        1203 => ['WCE',    'CCTV WiFi connection error',                             'C'],
        1204 => ['CVE',    'CCTV video error',                                       'C'],

        // BatteryCharger (1300-1399)
        1300 => ['NBCCPS', 'No Battery Charger control power supply',                'R'],
        1301 => ['BCAT',   'Battery Charger abnormal transmission',                  'R'],

        // Compressor (1400-1499)
        1400 => ['NCMCPS', 'No Compressor control power supply',                     'R'],
        1401 => ['CMAT_C', 'Compressor abnormal transmission',                       'R'],
        1402 => ['CMTHD',  'Compressor thermal detection',                           'R'],

        // DataRecorder (1500-1599)
        1500 => ['DRAT',   'Data Recorder abnormal transmission',                    'E'],
        1501 => ['CFCF',   'Data Recorder CF card fault',                            'E'],
        1502 => ['CFCWF',  'Data Recorder CF card write fail',                       'E'],
        1503 => ['CFCRF',  'Data Recorder CF card read fail',                        'E'],
    ];

    // ═══════════════════════════════════════════════════════
    // FAULT CLASSIFICATION — Heavy / Light / Info
    // ═══════════════════════════════════════════════════════
    private static array $faultClassByRange = [
        [100,  199,  'Heavy'],  // TIS
        [200,  299,  'Heavy'],  // ATO — safety critical
        [300,  399,  'Heavy'],  // VVVF — propulsion
        [400,  499,  'Heavy'],  // APS — power supply
        [500,  599,  'Heavy'],  // BECU — brake (safety critical)
        [600,  699,  'Light'],  // ACE — air conditioning
        [700,  799,  'Light'],  // PID — passenger info display
        [800,  899,  'Light'],  // PA — public address
        [900,  999,  'Heavy'],  // DOOR — safety critical
        [1000, 1099, 'Light'],  // VMI
        [1100, 1199, 'Light'],  // Radio
        [1200, 1299, 'Light'],  // CCTV
        [1300, 1399, 'Heavy'],  // BatteryCharger
        [1400, 1499, 'Heavy'],  // Compressor (brake air supply)
        [1500, 1599, 'Light'],  // DataRecorder
    ];

    // ═══════════════════════════════════════════════════════
    // FAILURE GUIDANCE TABLE (Table 6.1-2)
    // ═══════════════════════════════════════════════════════
    private static array $failureGuidance = [
        [100,  228,  'Please report the occurrence of the failure to the OCC.'],
        [300,  301,  'Please confirm the CB (Circuit Breaker) is drive.'],
        [302,  305,  "Set Master Controller to 'Emergency' position; then press 'Reset Switch'."],
        [306,  307,  "Cut out VVVF inverter. Set Master Controller to 'Emergency' position; then press 'Cut-out Switch'. Once VVVF inverter failure released, press 'Reset Switch'."],
        [308,  337,  "Set Master Controller to 'Neutral' position; then press 'Reset Switch'."],
        [338,  365,  "Set Master Controller to 'Neutral' position; then press 'Reset Switch'."],
        [366,  367,  "Set Master Controller to 'Neutral' position; then press 'Reset Switch'."],
        [368,  377,  'N/A (No indication required).'],
        [400,  419,  'Please report the occurrence of the failure to the OCC.'],
        [500,  500,  'Please confirm if the Circuit Breaker for BECU is tripped or not.'],
        [501,  501,  'Please reboot the BECU and/or the TIS. If failure persists, operate train to a station where the train can be changed.'],
        [502,  502,  'If failure on 1 car: operate to station for train exchange. If failure on 2+ cars: operate to station with sidetrack.'],
        [503,  503,  'Please reboot the Circuit Breaker of the failed BECU.'],
        [504,  505,  'Operate the train to a station where the train can be changed and suspend the service operation.'],
        [506,  507,  'Please reboot the Circuit Breaker of the failed BECU.'],
        [508,  511,  'Operate the train to a station where the train can be changed and suspend the service operation.'],
        [512,  512,  'Please reboot the Circuit Breaker of the failed BECU.'],
        [513,  513,  'Please operate the brake cut-out switch and confirm that brake unrelease light turns off.'],
        [514,  520,  'Operate the train to a station where the train can be changed.'],
        [521,  521,  'Please reboot the Circuit Breaker of the failed BECU.'],
        [522,  522,  'Operate the train to a station where the train can be changed.'],
        [523,  524,  'When TIS is available, operate normally. If TIS disabled, operate by emergency operation.'],
        [525,  525,  'A serious failure occurred. Please confirm details of the failure.'],
        [526,  527,  'Please report the occurrence of the failure to the OCC.'],
        [600,  802,  'Please report the occurrence of the failure to the OCC.'],
        [803,  804,  'Please check the CF card.'],
        [805,  805,  'Please select data A or B on the PA Data Select Screen.'],
        [806,  806,  'The selected data is unable. Please select valid data on the PA Data Select Screen.'],
        [900,  909,  'Please report the occurrence of the failure to the OCC.'],
        [1000, 1006, 'Please report the occurrence of the failure to the OCC.'],
        [1100, 1400, 'Please report the occurrence of the failure to the OCC.'],
        [1401, 1401, 'Please report the occurrence of the failure to the OCC.'],
        [1402, 1403, 'Please report the occurrence of the failure to the OCC.'],
        [1500, 1503, 'Please report the occurrence of the failure to the OCC.'],
    ];

    // ═══════════════════════════════════════════════════════
    // PUBLIC LOOKUP METHODS
    // ═══════════════════════════════════════════════════════

    public static function getEquipmentName(int $code): string
    {
        return self::$equipmentMap[$code] ?? sprintf('EQ%02d', $code);
    }

    public static function getFaultAbbrev(int $faultCode): string
    {
        return self::$faultDict[$faultCode][0] ?? sprintf('FC%04d', $faultCode);
    }

    public static function getFaultDescription(int $faultCode): ?string
    {
        return self::$faultDict[$faultCode][1] ?? null;
    }

    public static function getClassification(int $faultCode): string
    {
        foreach (self::$faultClassByRange as [$lo, $hi, $cls]) {
            if ($faultCode >= $lo && $faultCode <= $hi) {
                return $cls;
            }
        }
        return 'Info';
    }

    public static function getGuidance(int $faultCode): ?string
    {
        foreach (self::$failureGuidance as [$lo, $hi, $text]) {
            if ($faultCode >= $lo && $faultCode <= $hi) {
                return $text;
            }
        }
        return 'Refer to maintenance manual or contact OCC.';
    }

    /**
     * Kembalikan semua info fault dalam satu call — dipakai oleh FailureController.
     */
    public static function resolveFault(int $equipmentCode, int $faultCode, string $equipmentNameFromGateway, string $faultAbbrevFromGateway): array
    {
        return [
            'equipment_name'    => $equipmentNameFromGateway ?: self::getEquipmentName($equipmentCode),
            'fault_abbrev'      => $faultAbbrevFromGateway   ?: self::getFaultAbbrev($faultCode),
            'fault_description' => self::getFaultDescription($faultCode),
            'classification'    => self::getClassification($faultCode),
            'guidance'          => self::getGuidance($faultCode),
        ];
    }
}
