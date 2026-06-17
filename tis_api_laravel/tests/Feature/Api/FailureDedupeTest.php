<?php

use App\Models\Session;

beforeEach(function () {
    config(['app.tis_api_key' => 'test_api_key']);
});

test('duplicate records across uploads are not re-inserted', function () {
    $records = [
        [
            'block_no' => 1,
            'timestamp' => '2026-06-12 10:00:00',
            'car_no' => 1,
            'occur_recover' => 0,
            'train_id' => 'TS08',
            'location_m' => 0,
            'equipment_code' => 9,
            'equipment_name' => 'PA',
            'fault_code' => 801,
            'fault_name' => 'PAAT',
            'notch' => 'EB',
            'speed_kmh' => 0,
            'overhead_v' => 0,
        ],
    ];

    $first = $this->postJson('/api/failures', [
        'rake_id' => 'TS08',
        'read_time' => '2026-06-12 10:00:00',
        'records' => $records,
    ], [
        'Authorization' => 'Bearer test_api_key',
    ]);

    $first->assertStatus(201);
    $first->assertJson([
        'status' => 'success',
        'received' => 1,
    ]);

    // Upload kedua: session baru tetap dibuat, tapi record duplikat tidak di-insert
    $second = $this->postJson('/api/failures', [
        'rake_id' => 'TS08',
        'read_time' => '2026-06-12 10:00:15',
        'records' => $records,
    ], [
        'Authorization' => 'Bearer test_api_key',
    ]);

    $second->assertStatus(201);
    $second->assertJson([
        'status' => 'success',
        'received' => 0, // 0 record baru karena semua duplikat
    ]);

    // Session baru tetap dibuat (2 session total)
    expect(Session::count())->toBe(2);
});
