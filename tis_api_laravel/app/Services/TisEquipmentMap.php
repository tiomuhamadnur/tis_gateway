<?php

namespace App\Services;

/**
 * Laravel mirror of `config/equipment_map.py`.
 * Keep this file aligned with the Python source so analysis and labeling
 * use the same reference data.
 */
class TisEquipmentMap
{
    private static array $equipmentMap = [
            1 => 'TIS',
            2 => 'ATO',
            3 => 'VVVF1',
            4 => 'VVVF2',
            5 => 'APS',
            6 => 'BECU',
            7 => 'ACE',
            8 => 'PID',
            9 => 'PA',
            10 => 'DOOR',
            11 => 'VMI',
            19 => 'Radio',
            20 => 'CCTV',
            21 => 'BatteryCharger',
            22 => 'Compressor',
            23 => 'DataRecorder',
        ];

    private static array $equipmentByFaultRange = [
            [100, 199, 1, 'TIS'],
            [200, 299, 2, 'ATO'],
            [300, 399, 3, 'VVVF'],
            [400, 499, 5, 'APS'],
            [500, 599, 6, 'BECU'],
            [600, 699, 7, 'ACE'],
            [700, 799, 8, 'PID'],
            [800, 899, 9, 'PA'],
            [900, 999, 10, 'DOOR'],
            [1000, 1099, 11, 'VMI'],
            [1100, 1199, 19, 'Radio'],
            [1200, 1299, 20, 'CCTV'],
            [1300, 1399, 21, 'BatteryCharger'],
            [1400, 1499, 22, 'Compressor'],
            [1500, 1599, 23, 'DataRecorder'],
        ];

    private static array $faultDict = [
            100 => ['DDUAT', 'DDU abnormal transmission', 'E'],
            101 => ['ETH1AT11', '1st RIO UNIT1 ETH1 abnormal transmission', 'E'],
            102 => ['ETH2AT11', '1st RIO UNIT1 ETH2 abnormal transmission', 'E'],
            103 => ['ETH1AT12', '2nd RIO UNIT1 ETH1 abnormal transmission', 'E'],
            104 => ['ETH2AT12', '2nd RIO UNIT1 ETH2 abnormal transmission', 'E'],
            105 => ['ETH1AT21', '1st RIO UNIT2 ETH1 abnormal transmission', 'E'],
            106 => ['ETH2AT21', '1st RIO UNIT2 ETH2 abnormal transmission', 'E'],
            107 => ['ETH1AT22', '2nd RIO UNIT2 ETH1 abnormal transmission', 'E'],
            108 => ['ETH2AT22', '2nd RIO UNIT2 ETH2 abnormal transmission', 'E'],
            109 => ['IBA1A1', 'RIO UNIT1 IBA137A1 PCB abnormality', 'E'],
            110 => ['IBA1A2', 'RIO UNIT1 IBA137A2 PCB abnormality', 'E'],
            111 => ['IBA1A3', 'RIO UNIT1 IBA137A3 PCB abnormality', 'E'],
            112 => ['IBA1A4', 'RIO UNIT1 IBA137A4 PCB abnormality', 'E'],
            113 => ['IBA1A5', 'RIO UNIT1 IBA137A5 PCB abnormality', 'E'],
            114 => ['IBA2A1', 'RIO UNIT2 IBA137A1 PCB abnormality', 'E'],
            115 => ['IBA2A2', 'RIO UNIT2 IBA137A2 PCB abnormality', 'E'],
            116 => ['IBA2A3', 'RIO UNIT2 IBA137A3 PCB abnormality', 'E'],
            117 => ['IBA2A4', 'RIO UNIT2 IBA137A4 PCB abnormality', 'E'],
            118 => ['IBA2A5', 'RIO UNIT2 IBA137A5 PCB abnormality', 'E'],
            119 => ['IBA2A6', 'RIO UNIT2 IBA137A6 PCB abnormality', 'E'],
            120 => ['SDCA', 'CCU/MON UNIT SD Card abnormality', 'E'],
            121 => ['ESA', 'CCU/MON UNIT Ethernet abnormality', 'C'],
            122 => ['PS1RA', 'CCU/MON UNIT PS1R abnormality', 'E'],
            123 => ['PS1LA', 'CCU/MON UNIT PS1L abnormality', 'E'],
            124 => ['CPUA', 'CCU/MON UNIT CPU abnormality', 'E'],
            125 => ['CMUSU', 'CCU/MON UNIT 1st & 2nd systems start up', 'E'],
            126 => ['CMUSB', 'CCU/MON UNIT 1st & 2nd systems standby', 'E'],
            127 => ['CCUAT', 'CCU/MON UNIT abnormal transmission', 'E'],
            128 => ['RIO1AT1', '1st RIO UNIT1 abnormal transmission', 'E'],
            129 => ['RIO1AT2', '2nd RIO UNIT1 abnormal transmission', 'E'],
            130 => ['RIO2AT1', '1st RIO UNIT2 abnormal transmission', 'E'],
            131 => ['RIO2AT2', '2nd RIO UNIT2 abnormal transmission', 'E'],
            200 => ['ATOAT', 'ATO abnormal transmission', 'C'],
            201 => ['LBF', 'Tc1 logic block fault', 'E'],
            202 => ['IBF', 'Tc1 IF block fault', 'E'],
            203 => ['RBF', 'Tc1 relay block fault', 'E'],
            204 => ['TGF', 'Tc1 TG (Tachometer Generator) fault', 'E'],
            205 => ['BA', 'Tc1 balise antenna fault', 'E'],
            206 => ['DMIF', 'Tc1 DMI (Driver Machine Interface) fault', 'E'],
            207 => ['VRSF', 'Tc1 VRS (Vehicle Radio System, out of sync)', 'E'],
            208 => ['LBIBF', 'Tc1 logic block - IF block communication fault', 'E'],
            209 => ['LBRBF', 'Tc1 logic block - relay block communication', 'E'],
            210 => ['LBOIBF', 'Tc1 logic block - other IF block communication', 'E'],
            211 => ['LBVRS1F', 'Tc1 logic block - VRS1 communication fault', 'C'],
            212 => ['LBVRS2F', 'Tc1 logic block - VRS2 communication fault', 'C'],
            213 => ['LBDMIF', 'Tc1 logic block - DMI communication fault', 'E'],
            214 => ['LBTISF', 'Tc1 logic block - TIS communication fault', 'E'],
            215 => ['LBF2', 'Tc2 logic block fault', 'E'],
            216 => ['IBF2', 'Tc2 IF block fault', 'E'],
            217 => ['RBF2', 'Tc2 relay block fault', 'E'],
            218 => ['TGF2', 'Tc2 TG fault', 'E'],
            219 => ['BA2', 'Tc2 balise antenna fault', 'E'],
            220 => ['DMIF2', 'Tc2 DMI fault', 'E'],
            221 => ['VRSF2', 'Tc2 VRS (out of sync)', 'E'],
            222 => ['LBIBF2', 'Tc2 logic block - IF block communication', 'E'],
            223 => ['IBOLBF', 'Tc2 IF block - other logic block', 'E'],
            225 => ['LBVRS1F2', 'Tc2 logic block - VRS1 communication', 'E'],
            226 => ['LBVRS2F2', 'Tc2 logic block - VRS2 communication', 'E'],
            228 => ['LBTISF2', 'Tc2 logic block - TIS communication', 'E'],
            300 => ['NVCPS', 'No VVVF1 control power supply', 'C'],
            301 => ['NVCPS2', 'No VVVF2 control power supply', 'C'],
            302 => ['VVVFCB', 'VVVF Circuit Breaker drive fault', 'E'],
            306 => ['VVVFINV', 'VVVF inverter fault (cut-out required)', 'E'],
            308 => ['VVVFTRC', 'VVVF traction fault (Neutral position required)', 'E'],
            377 => ['VVVFAT1', '2 bank VVVF abnormal transmission Bank 1', 'E'],
            400 => ['NAPCPS', 'No APS control power supply', 'C'],
            401 => ['APSAT', 'APS abnormal transmission', 'E'],
            402 => ['INOC', 'APS Input over current', 'E'],
            403 => ['IVNOC', 'APS Inverter over current', 'E'],
            404 => ['STFD', 'APS Starting failure detection', 'E'],
            405 => ['FCUNV', 'APS Filter capacitor unbalance', 'E'],
            408 => ['OLD', 'APS Output over load detection', 'E'],
            409 => ['OVD', 'APS Output over voltage', 'E'],
            410 => ['IVLVD', 'APS Inverter output low voltage', 'E'],
            411 => ['KAE', 'APS Contactor answer error', 'E'],
            412 => ['PN15LVD', 'APS 3-phase main contactor PN15 low voltage', 'E'],
            416 => ['THYTHD', 'APS Thyristor thermal detection', 'E'],
            417 => ['IVTHD', 'APS Inverter thermal detection', 'E'],
            418 => ['TEST', 'APS fault for testing', 'E'],
            419 => ['IVFR', 'APS fault (Inverter Fault Relay)', 'C'],
            500 => ['NBPS', 'No brake power supply', 'C'],
            501 => ['BAT', 'Brake abnormal transmission', 'E'],
            502 => ['SAVF', 'Service brake valve AV failure', 'E'],
            503 => ['SRVF', 'Service brake valve RV failure', 'E'],
            504 => ['AS1PF', 'AS1 pressure sensor failure', 'E'],
            505 => ['AS2PF', 'AS2 pressure sensor failure', 'E'],
            506 => ['BCPF', 'BC pressure sensor failure', 'E'],
            507 => ['ACPF', 'AC pressure sensor failure', 'E'],
            508 => ['MRPF', 'MR pressure sensor failure', 'E'],
            509 => ['LSA', 'Load signal abnormality', 'E'],
            510 => ['RBSA', 'Regenerative brake pattern signal abnormality', 'E'],
            511 => ['RFBSA', 'Regenerative brake feedback signal abnormality', 'E'],
            512 => ['ISBD', 'Insufficient service brake detection', 'E'],
            513 => ['NRBD', 'Non-release brake detection', 'E'],
            514 => ['SS1F', 'Speed sensor1 failure', 'E'],
            515 => ['SS2F', 'Speed sensor2 failure', 'E'],
            516 => ['SS3F', 'Speed sensor3 failure', 'E'],
            517 => ['SS4F', 'Speed sensor4 failure', 'E'],
            518 => ['SSPF', 'Speed sensor power failure', 'E'],
            519 => ['WSP1F', 'Wheel slide protection valve1 failure', 'E'],
            520 => ['WSP2F', 'Wheel slide protection valve2 failure', 'E'],
            521 => ['TRCE', 'Test run check error', 'E'],
            522 => ['MTCF', 'M-T car unit control failure', 'E'],
            523 => ['RS1F', 'RS485 1st system failure', 'E'],
            524 => ['RS2F', 'RS485 2nd system failure', 'E'],
            525 => ['BECUF', 'BECU fault', 'E'],
            526 => ['BAT1', 'Brake 1st abnormal transmission', 'E'],
            527 => ['BAT2', 'Brake 2nd abnormal transmission', 'E'],
            600 => ['NACCPS', 'No ACE control power supply', 'C'],
            601 => ['ACEAT', 'ACE abnormal transmission', 'E'],
            602 => ['EF11NG', 'Air Conditioning Unit 1 Evaporator Fan 1 Failure', 'E'],
            603 => ['EF12NG', 'Air Conditioning Unit 1 Evaporator Fan 2 Failure', 'E'],
            604 => ['CF11NG', 'Air Conditioning Unit 1 Condenser Fan 1 Failure', 'E'],
            605 => ['CF12NG', 'Air Conditioning Unit 1 Condenser Fan 2 Failure', 'E'],
            606 => ['CR11NG', 'Air Conditioning Unit 1 Over Load Relay for Compressor 11 Failure', 'E'],
            607 => ['CR12NG', 'Air Conditioning Unit 1 Over Load Relay for Compressor 12 Failure', 'E'],
            608 => ['CR13NG', 'Air Conditioning Unit 1 Over Load Relay for Compressor 13 Failure', 'E'],
            609 => ['HP11NG', 'Air Conditioning Unit 1 High Pressure Switch for Compressor 11 Failure', 'E'],
            610 => ['HP12NG', 'Air Conditioning Unit 1 High Pressure Switch for Compressor 12 Failure', 'E'],
            611 => ['HP13NG', 'Air Conditioning Unit 1 High Pressure Switch for Compressor 13 Failure', 'E'],
            612 => ['LP11NG', 'Air Conditioning Unit 1 Low Pressure Switch for Compressor 11 Failure', 'E'],
            613 => ['LP12NG', 'Air Conditioning Unit 1 Low Pressure Switch for Compressor 12 Failure', 'E'],
            614 => ['LP13NG', 'Air Conditioning Unit 1 Low Pressure Switch for Compressor 13 Failure', 'E'],
            615 => ['Th11NG', 'Air Conditioning Unit 1 Inner Thermo Relay for Compressor 11 Failure', 'E'],
            616 => ['Th12NG', 'Air Conditioning Unit 1 Inner Thermo Relay for Compressor 12 Failure', 'E'],
            617 => ['Th13NG', 'Air Conditioning Unit 1 Inner Thermo Relay for Compressor 13 Failure', 'E'],
            618 => ['EF11LK', 'Air Conditioning Unit 1 Evaporator Fan 1 Lock Out', 'E'],
            619 => ['EF12LK', 'Air Conditioning Unit 1 Evaporator Fan 2 Lock Out', 'E'],
            620 => ['CF11LK', 'Air Conditioning Unit 1 Condenser Fan 1 Lock Out', 'E'],
            621 => ['CF12LK', 'Air Conditioning Unit 1 Condenser Fan 2 Lock Out', 'E'],
            622 => ['CR11LK', 'Air Conditioning Unit 1 Over Load Relay for Compressor 11 Lock Out', 'E'],
            623 => ['CR12LK', 'Air Conditioning Unit 1 Over Load Relay for Compressor 12 Lock Out', 'E'],
            624 => ['CR13LK', 'Air Conditioning Unit 1 Over Load Relay for Compressor 13 Lock Out', 'E'],
            625 => ['HP11LK', 'Air Conditioning Unit 1 High Pressure Switch for Compressor 11 Lock Out', 'E'],
            626 => ['HP12LK', 'Air Conditioning Unit 1 High Pressure Switch for Compressor 12 Lock Out', 'E'],
            627 => ['HP13LK', 'Air Conditioning Unit 1 High Pressure Switch for Compressor 13 Lock Out', 'E'],
            628 => ['LP11LK', 'Air Conditioning Unit 1 Low Pressure Switch for Compressor 11 Lock Out', 'E'],
            629 => ['LP12LK', 'Air Conditioning Unit 1 Low Pressure Switch for Compressor 12 Lock Out', 'E'],
            630 => ['LP13LK', 'Air Conditioning Unit 1 Low Pressure Switch for Compressor 13 Lock Out', 'E'],
            631 => ['Th11LK', 'Air Conditioning Unit 1 Inner Thermo Relay for Compressor 11 Lock Out', 'E'],
            632 => ['Th12LK', 'Air Conditioning Unit 1 Inner Thermo Relay for Compressor 12 Lock Out', 'E'],
            633 => ['Th13LK', 'Air Conditioning Unit 1 Inner Thermo Relay for Compressor 13 Lock Out', 'E'],
            634 => ['EF21NG', 'Air Conditioning Unit 2 Evaporator Fan 1 Failure', 'E'],
            635 => ['EF22NG', 'Air Conditioning Unit 2 Evaporator Fan 2 Failure', 'E'],
            636 => ['CF21NG', 'Air Conditioning Unit 2 Condenser Fan 1 Failure', 'E'],
            637 => ['CF22NG', 'Air Conditioning Unit 2 Condenser Fan 2 Failure', 'E'],
            638 => ['CR21NG', 'Air Conditioning Unit 2 Over Load Relay for Compressor 21 Failure', 'E'],
            639 => ['CR22NG', 'Air Conditioning Unit 2 Over Load Relay for Compressor 22 Failure', 'E'],
            640 => ['CR23NG', 'Air Conditioning Unit 2 Over Load Relay for Compressor 23 Failure', 'E'],
            641 => ['HP21NG', 'Air Conditioning Unit 2 High Pressure Switch for Compressor 21 Failure', 'E'],
            642 => ['HP22NG', 'Air Conditioning Unit 2 High Pressure Switch for Compressor 22 Failure', 'E'],
            643 => ['HP23NG', 'Air Conditioning Unit 2 High Pressure Switch for Compressor 23 Failure', 'E'],
            644 => ['LP21NG', 'Air Conditioning Unit 2 Low Pressure Switch for Compressor 21 Failure', 'E'],
            645 => ['LP22NG', 'Air Conditioning Unit 2 Low Pressure Switch for Compressor 22 Failure', 'E'],
            646 => ['LP23NG', 'Air Conditioning Unit 2 Low Pressure Switch for Compressor 23 Failure', 'E'],
            647 => ['Th21NG', 'Air Conditioning Unit 2 Inner Thermo Relay for Compressor 21 Failure', 'E'],
            648 => ['Th22NG', 'Air Conditioning Unit 2 Inner Thermo Relay for Compressor 22 Failure', 'E'],
            649 => ['Th23NG', 'Air Conditioning Unit 2 Inner Thermo Relay for Compressor 23 Failure', 'E'],
            650 => ['LFCNG', 'Contactor for Linear Fan Failure', 'E'],
            651 => ['EF21LK', 'Air Conditioning Unit 2 Evaporator Fan 1 Lock Out', 'E'],
            652 => ['EF22LK', 'Air Conditioning Unit 2 Evaporator Fan 2 Lock Out', 'E'],
            653 => ['CF21LK', 'Air Conditioning Unit 2 Condenser Fan 1 Lock Out', 'E'],
            654 => ['CF22LK', 'Air Conditioning Unit 2 Condenser Fan 2 Lock Out', 'E'],
            655 => ['CR21LK', 'Air Conditioning Unit 2 Over Load Relay for Compressor 21 Lock Out', 'E'],
            656 => ['CR22LK', 'Air Conditioning Unit 2 Over Load Relay for Compressor 22 Lock Out', 'E'],
            657 => ['CR23LK', 'Air Conditioning Unit 2 Over Load Relay for Compressor 23 Lock Out', 'E'],
            658 => ['HP21LK', 'Air Conditioning Unit 2 High Pressure Switch for Compressor 21 Lock Out', 'E'],
            659 => ['HP22LK', 'Air Conditioning Unit 2 High Pressure Switch for Compressor 22 Lock Out', 'E'],
            660 => ['HP23LK', 'Air Conditioning Unit 2 High Pressure Switch for Compressor 23 Lock Out', 'E'],
            661 => ['LP21LK', 'Air Conditioning Unit 2 Low Pressure Switch for Compressor 21 Lock Out', 'E'],
            662 => ['LP22LK', 'Air Conditioning Unit 2 Low Pressure Switch for Compressor 22 Lock Out', 'E'],
            663 => ['LP23LK', 'Air Conditioning Unit 2 Low Pressure Switch for Compressor 23 Lock Out', 'E'],
            664 => ['Th21LK', 'Air Conditioning Unit 2 Inner Thermo Relay for Compressor 21 Lock Out', 'E'],
            665 => ['Th22LK', 'Air Conditioning Unit 2 Inner Thermo Relay for Compressor 22 Lock Out', 'E'],
            666 => ['Th23LK', 'Air Conditioning Unit 2 Inner Thermo Relay for Compressor 23 Lock Out', 'E'],
            667 => ['LFCLK', 'Contactor for Linear Fan Lock Out', 'E'],
            668 => ['TR1NG', 'Return Air Temperature Sensor 1 Abnormal', 'E'],
            669 => ['TR2NG', 'Return Air Temperature Sensor 2 Abnormal', 'E'],
            670 => ['ACB1TR', 'Air Conditioning Unit Circuit Breaker 1 Trip', 'E'],
            671 => ['ACB2TR', 'Air Conditioning Unit Circuit Breaker 2 Trip', 'E'],
            672 => ['LFCBTR', 'Linear Fan circuit breaker Trip', 'E'],
            700 => ['NPICPS', 'No PID control power supply', 'C'],
            701 => ['PIDAT', 'PID abnormal transmission', 'E'],
            702 => ['SDD1F', 'SDD1 fault', 'E'],
            703 => ['SDD2F', 'SDD2 fault', 'E'],
            704 => ['EDDF', 'EDD fault', 'E'],
            705 => ['PSD1F', 'PSD1 fault', 'E'],
            706 => ['PSD2F', 'PSD2 fault', 'E'],
            707 => ['PSD3F', 'PSD3 fault', 'E'],
            708 => ['PSD4F', 'PSD4 fault', 'E'],
            709 => ['PSD5F', 'PSD5 fault', 'E'],
            710 => ['PSD6F', 'PSD6 fault', 'E'],
            711 => ['PSD7F', 'PSD7 fault', 'E'],
            712 => ['PSD8F', 'PSD8 fault', 'E'],
            800 => ['NPACPS', 'No APA control power supply', 'C'],
            801 => ['PAAT', 'APA abnormal transmission', 'E'],
            802 => ['CFCA', 'CF card abnormality', 'E'],
            803 => ['OPA', 'Operation pattern abnormality', 'E'],
            804 => ['CU', 'Code undefined', 'E'],
            805 => ['DATANS', 'Data A/B not selected', 'E'],
            806 => ['DATASA', 'Data A/B select abnormality', 'C'],
            900 => ['DOORAT', 'Door abnormal transmission', 'E'],
            901 => ['IPM', 'IPM error', 'E'],
            902 => ['CT', 'Current sensor abnormality', 'E'],
            903 => ['ET', 'Encoder abnormality', 'E'],
            904 => ['ME', 'EEPROM memory abnormality', 'E'],
            905 => ['DLE', 'Locking sensor switch error', 'E'],
            906 => ['OPE', 'Open time out error', 'E'],
            907 => ['CLE', 'Close time out error', 'E'],
            908 => ['NRD', 'Initial operation un-completing', 'E'],
            909 => ['OV', 'Overvoltage', 'E'],
            1000 => ['NVMCPS', 'No VMI control power supply', 'E'],
            1001 => ['VMAT', 'VMI abnormal transmission', 'E'],
            1002 => ['VMCPU', 'CPU board fault', 'E'],
            1003 => ['VMCF', 'CF card fault', 'E'],
            1004 => ['VMPAR', 'Parameter fault', 'E'],
            1005 => ['VMAC1', 'No.1 acceleration sensor fault', 'E'],
            1006 => ['VMAC2', 'No.2 acceleration sensor fault', 'E'],
            1100 => ['NTRCPS', 'No Train Radio control power supply', 'C'],
            1101 => ['TRAT', 'Train Radio abnormal transmission', 'E'],
            1102 => ['RA', 'Train Radio abnormality', 'E'],
            1103 => ['CMA', 'Train Radio control module abnormality', 'C'],
            1200 => ['NCCPS', 'No CCTV control power supply', 'C'],
            1201 => ['CCTVAT', 'CCTV abnormal transmission', 'E'],
            1202 => ['RUA', 'CCTV Rx unit abnormality', 'E'],
            1203 => ['WCE', 'CCTV WiFi connection error', 'C'],
            1204 => ['CVE', 'CCTV video error', 'C'],
            1300 => ['NBCCPS', 'No Battery Charger control power supply', 'R'],
            1301 => ['BCAT', 'Battery Charger abnormal transmission', 'R'],
            1302 => ['BOT', 'Battery over temperature', 'R'],
            1400 => ['NCMCPS', 'No Compressor control power supply', 'R'],
            1401 => ['CMAT_C', 'Compressor abnormal transmission', 'R'],
            1402 => ['CMTHD', 'Compressor thermal detection', 'R'],
            1403 => ['CPSLVD', 'Compressor power supply low voltage detection', 'R'],
            1500 => ['DRAT', 'Data Recorder abnormal transmission', 'E'],
            1501 => ['CFCF', 'Data Recorder CF card fault', 'E'],
            1502 => ['CFCWF', 'Data Recorder CF card write fail', 'E'],
            1503 => ['CFCRF', 'Data Recorder CF card read fail', 'E'],
        ];

    private static array $faultClassByRange = [
            [100, 199, 'Heavy'],
            [200, 299, 'Heavy'],
            [300, 399, 'Heavy'],
            [400, 499, 'Heavy'],
            [500, 599, 'Heavy'],
            [600, 699, 'Light'],
            [700, 799, 'Light'],
            [800, 899, 'Light'],
            [900, 999, 'Heavy'],
            [1000, 1099, 'Light'],
            [1100, 1199, 'Light'],
            [1200, 1299, 'Light'],
            [1300, 1399, 'Heavy'],
            [1400, 1499, 'Heavy'],
            [1500, 1599, 'Light'],
        ];

    private static array $notchMap = [
            0 => 'EB',
            1 => 'A_P1',
            2 => 'A_P2',
            3 => 'A_P3',
            4 => 'A_P4',
            5 => 'A_P5',
            6 => 'A_P6',
            7 => 'A_P7',
            8 => 'A_P8',
            9 => 'A_P9',
            10 => 'A_P10',
            11 => 'A_P11',
            12 => 'A_P12',
            13 => 'A_P13',
            14 => 'A_P14',
            15 => 'A_P15',
            16 => 'A_P16',
            128 => 'Neutral',
            129 => 'A_B1',
            130 => 'A_B2',
            131 => 'A_B3',
            132 => 'A_B4',
            133 => 'A_B5',
            134 => 'A_B6',
            135 => 'A_B7',
            136 => 'A_B8',
            137 => 'A_B9',
            138 => 'A_B10',
            139 => 'A_B11',
            140 => 'A_B12',
            141 => 'A_B13',
            142 => 'A_B14',
            143 => 'A_B15',
            144 => 'A_B16',
        ];

    private static array $carIdMap = [
            1 => 1,
            2 => 2,
            3 => 3,
            4 => 4,
            5 => 5,
            6 => 6,
        ];

    private static array $carTypeMap = [
            1 => 'Tc1',
            2 => 'M1',
            3 => 'M2',
            4 => 'M1\'',
            5 => 'M2\'',
            6 => 'Tc2',
        ];

    private static array $rioPcbModules = [
            'MCB119' => 'CPU module',
            'IBA137' => 'Digital Input module (24VDC, photo-coupler)',
            'OBA59' => 'Digital Output module (24VDC, photo-coupler)',
            'IFB159' => 'Serial communication module (RS-485)',
            'PW99' => 'Power Supply module (110VDC -> 24VDC)',
        ];

    private static array $occurRecoverMap = [
            0 => 'Occur',
            1 => 'Recover',
        ];

    private static array $failureGuidance = [
            [100, 228, 'Please report the occurrence of the failure to the OCC.'],
            [300, 301, 'Please confirm the CB (Circuit Breaker) is drive.'],
            [302, 305, 'Set Master Controller to \'Emergency\' position; then press \'Reset Switch\'.'],
            [306, 307, 'Cut out VVVF inverter. Set Master Controller to \'Emergency\' position; then press \'Cut-out Switch\'. Once VVVF inverter failure released, press \'Reset Switch\'.'],
            [308, 337, 'Set Master Controller to \'Neutral\' position; then press \'Reset Switch\'.'],
            [338, 365, 'Set Master Controller to \'Neutral\' position; then press \'Reset Switch\'.'],
            [366, 367, 'Set Master Controller to \'Neutral\' position; then press \'Reset Switch\'.'],
            [368, 377, 'N/A (No indication required).'],
            [400, 419, 'Please report the occurrence of the failure to the OCC.'],
            [500, 500, 'Please confirm if the Circuit Breaker for BECU is tripped or not.'],
            [501, 501, 'Please reboot the BECU and/or the TIS. If failure persists, operate train to a station where the train can be changed.'],
            [502, 502, 'If failure on 1 car: operate to station for train exchange. If failure on 2+ cars: operate to station with sidetrack.'],
            [503, 503, 'Please reboot the Circuit Breaker of the failed BECU.'],
            [504, 505, 'Operate the train to a station where the train can be changed and suspend the service operation.'],
            [506, 507, 'Please reboot the Circuit Breaker of the failed BECU.'],
            [508, 511, 'Operate the train to a station where the train can be changed and suspend the service operation.'],
            [512, 512, 'Please reboot the Circuit Breaker of the failed BECU.'],
            [513, 513, 'Please operate the brake cut-out switch and confirm that brake unrelease light turns off.'],
            [514, 520, 'Operate the train to a station where the train can be changed.'],
            [521, 521, 'Please reboot the Circuit Breaker of the failed BECU.'],
            [522, 522, 'Operate the train to a station where the train can be changed.'],
            [523, 524, 'When TIS is available, operate normally. If TIS disabled, operate by emergency operation.'],
            [525, 525, 'A serious failure occurred. Please confirm details of the failure.'],
            [526, 527, 'Please report the occurrence of the failure to the OCC.'],
            [600, 802, 'Please report the occurrence of the failure to the OCC.'],
            [803, 804, 'Please check the CF card.'],
            [805, 805, 'Please select data A or B on the PA Data Select Screen.'],
            [806, 806, 'The selected data is unable. Please select valid data on the PA Data Select Screen.'],
            [900, 909, 'Please report the occurrence of the failure to the OCC.'],
            [1000, 1006, 'Please report the occurrence of the failure to the OCC.'],
            [1100, 1400, 'Please report the occurrence of the failure to the OCC.'],
            [1401, 1401, 'Please report the occurrence of the failure to the OCC.'],
            [1402, 1403, 'Please report the occurrence of the failure to the OCC.'],
            [1500, 1503, 'Please report the occurrence of the failure to the OCC.'],
        ];

    public static function getEquipmentName(int $code): string
    {
        return self::$equipmentMap[$code] ?? sprintf('EQ%02d', $code);
    }

    public static function getEquipmentByFaultCode(int $faultCode): array
    {
        foreach (self::$equipmentByFaultRange as [$lo, $hi, $equipmentCode, $equipmentName]) {
            if ($faultCode >= $lo && $faultCode <= $hi) {
                return [$equipmentCode, $equipmentName];
            }
        }

        return [0, 'Unknown'];
    }

    public static function getFaultInfo(int $faultCode): array
    {
        if (isset(self::$faultDict[$faultCode])) {
            [$abbrev, $description, $confidence] = self::$faultDict[$faultCode];

            return [
                'abbrev' => $abbrev,
                'description' => $description,
                'confidence' => $confidence,
            ];
        }

        [$equipmentCode, $equipmentName] = self::getEquipmentByFaultCode($faultCode);

        return [
            'abbrev' => sprintf('FC%04d', $faultCode),
            'description' => sprintf('%s fault code %d', $equipmentName, $faultCode),
            'confidence' => 'R',
        ];
    }

    public static function getFaultAbbrev(int $faultCode): string
    {
        return self::getFaultInfo($faultCode)['abbrev'];
    }

    public static function getFaultDescription(int $faultCode): ?string
    {
        return self::getFaultInfo($faultCode)['description'];
    }

    public static function getFaultName(int $equipmentCode, int $faultCode): string
    {
        return self::getFaultAbbrev($faultCode);
    }

    public static function getFailureGuidance(int $faultCode): string
    {
        foreach (self::$failureGuidance as [$lo, $hi, $text]) {
            if ($faultCode >= $lo && $faultCode <= $hi) {
                return $text;
            }
        }

        return 'Refer to maintenance manual or contact OCC.';
    }

    public static function getGuidance(int $faultCode): string
    {
        return self::getFailureGuidance($faultCode);
    }

    public static function getFaultClassification(int $faultCode): string
    {
        foreach (self::$faultClassByRange as [$lo, $hi, $classification]) {
            if ($faultCode >= $lo && $faultCode <= $hi) {
                return $classification;
            }
        }

        return 'Info';
    }

    public static function getClassification(int $faultCode): string
    {
        return self::getFaultClassification($faultCode);
    }

    public static function getNotchLabel(int $notchByte): string
    {
        return self::$notchMap[$notchByte] ?? sprintf('N%02X', $notchByte);
    }

    public static function decodeNotch(int $statusByte, int $notchStep, int $notchMode): string
    {
        if (($statusByte & 0x01) === 1) {
            return 'EB';
        }

        if ($notchMode === 0x40) {
            $step = $notchStep & 0x0F;
            return sprintf('M_B%d', $step ?: 1);
        }

        if ($notchMode === 0x08) {
            $step = $notchStep & 0x0F;
            return sprintf('M_P%d', $step ?: 1);
        }

        if ($notchStep === 0x00 || $notchStep === 0x80) {
            return 'Neutral';
        }

        if ($notchStep >= 0x01 && $notchStep <= 0x10) {
            return sprintf('A_P%d', $notchStep);
        }

        if ($notchStep >= 0x81 && $notchStep <= 0x90) {
            return sprintf('A_B%d', $notchStep & 0x0F);
        }

        return sprintf('N%02X_%02X', $notchStep, $notchMode);
    }

    public static function getCarNumber(int $carIdByte): int
    {
        return self::$carIdMap[$carIdByte] ?? $carIdByte;
    }

    public static function getCarType(int $carNumber): string
    {
        return self::$carTypeMap[$carNumber] ?? sprintf('Car%02d', $carNumber);
    }

    public static function getOccurRecover(int $value): string
    {
        return self::$occurRecoverMap[$value] ?? sprintf('State%d', $value);
    }

    public static function getRioPcbModule(string $code): string
    {
        return self::$rioPcbModules[$code] ?? 'Unknown module';
    }

    public static function lookupFull(int $equipmentCode, int $faultCode, int $carIdByte, int $notchByte): array
    {
        return [
            self::getEquipmentName($equipmentCode),
            self::getFaultAbbrev($faultCode),
            self::getCarNumber($carIdByte),
            self::getNotchLabel($notchByte),
        ];
    }

    public static function lookupComplete(int $faultCode, int $carIdByte, int $notchByte, int $occurValue): array
    {
        [$equipmentCode, $equipmentName] = self::getEquipmentByFaultCode($faultCode);
        $faultInfo = self::getFaultInfo($faultCode);

        return [
            'equipment_code' => $equipmentCode,
            'equipment_name' => $equipmentName,
            'fault_code' => $faultCode,
            'fault_abbrev' => $faultInfo['abbrev'],
            'fault_description' => $faultInfo['description'],
            'fault_classification' => self::getFaultClassification($faultCode),
            'fault_guidance' => self::getFailureGuidance($faultCode),
            'fault_confidence' => $faultInfo['confidence'],
            'car_number' => self::getCarNumber($carIdByte),
            'car_type' => self::getCarType(self::getCarNumber($carIdByte)),
            'notch_label' => self::getNotchLabel($notchByte),
            'occur_recover' => self::getOccurRecover($occurValue),
        ];
    }

    public static function resolveFault(int $equipmentCode, int $faultCode, string $equipmentNameFromGateway, string $faultAbbrevFromGateway): array
    {
        return [
            'equipment_name' => $equipmentNameFromGateway ?: self::getEquipmentName($equipmentCode),
            'fault_abbrev' => $faultAbbrevFromGateway ?: self::getFaultAbbrev($faultCode),
            'fault_description' => self::getFaultDescription($faultCode),
            'classification' => self::getFaultClassification($faultCode),
            'guidance' => self::getFailureGuidance($faultCode),
        ];
    }

    public static function getDictionaryStats(): array
    {
        $confidence = ['C' => 0, 'E' => 0, 'R' => 0];

        foreach (self::$faultDict as $info) {
            $confidence[$info[2]] = ($confidence[$info[2]] ?? 0) + 1;
        }

        return [
            'total_equipment_codes' => count(self::$equipmentMap),
            'total_fault_codes' => count(self::$faultDict),
            'total_notch_codes' => count(self::$notchMap),
            'total_guidance_ranges' => count(self::$failureGuidance),
            'fault_codes_by_confidence' => [
                'Confirmed (CSV/PDF)' => $confidence['C'],
                'Extracted (manual)' => $confidence['E'],
                'Range-only' => $confidence['R'],
            ],
        ];
    }
}
