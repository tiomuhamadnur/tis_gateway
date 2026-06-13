<?php

use App\Services\TisEquipmentMap;

test('equipment and fault dictionary stay aligned with python source', function () {
    expect(TisEquipmentMap::getEquipmentName(1))->toBe('TIS');
    expect(TisEquipmentMap::getEquipmentByFaultCode(410))->toBe([5, 'APS']);
    expect(TisEquipmentMap::getFaultAbbrev(121))->toBe('ESA');
    expect(TisEquipmentMap::getFaultDescription(121))->toBe('CCU/MON UNIT Ethernet abnormality');
    expect(TisEquipmentMap::getClassification(700))->toBe('Light');

    $stats = TisEquipmentMap::getDictionaryStats();

    expect($stats['total_equipment_codes'])->toBe(16);
    expect($stats['total_fault_codes'])->toBe(238);
    expect($stats['total_guidance_ranges'])->toBe(34);
});
