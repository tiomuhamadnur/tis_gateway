<?php

use App\Models\Session;
use App\Models\FailureRecord;

beforeEach(function () {
    config(['app.tis_api_key' => 'test_api_key']);
});

function validRecord(array $overrides = []): array
{
    return array_merge([
        'block_no' => 0,
        'timestamp' => now()->toDateTimeString(),
        'car_no' => 1,
        'occur_recover' => 0,
        'train_id' => 'TS01',
        'location_m' => 0,
        'equipment_code' => 1,
        'equipment_name' => 'Engine',
        'fault_code' => 100,
        'fault_name' => 'OVER',
        'notch' => 'EB',
        'speed_kmh' => 0,
        'overhead_v' => 0,
    ], $overrides);
}

test('can submit failure records with valid API key', function () {
    $response = $this->postJson('/api/failures', [
        'rake_id' => 'RAKE-001',
        'read_time' => now()->toDateTimeString(),
        'records' => [
            validRecord(['fault_name' => 'OVER', 'fault_code' => 100]),
            validRecord(['fault_name' => 'PRESS', 'fault_code' => 200, 'equipment_name' => 'Brake']),
        ],
    ], [
        'Authorization' => 'Bearer test_api_key',
    ]);

    $response->assertStatus(201);
    $response->assertJsonStructure([
        'session_id',
        'received',
        'status',
    ]);

    $this->assertDatabaseHas('failure_sessions', [
        'rake_id' => 'RAKE-001',
        'total_records' => 2,
    ]);
});

test('returns 401 without API key', function () {
    $response = $this->postJson('/api/failures', [
        'rake_id' => 'RAKE-001',
        'records' => [],
    ]);

    $response->assertStatus(401);
});

test('returns 401 with invalid API key', function () {
    $response = $this->postJson('/api/failures', [
        'rake_id' => 'RAKE-001',
        'records' => [],
    ], [
        'Authorization' => 'Bearer invalid_key',
    ]);

    $response->assertStatus(401);
});

test('can list failure sessions', function () {
    Session::create([
        'session_id' => 'test-session-123',
        'rake_id' => 'RAKE-001',
        'read_time' => now(),
        'download_date' => now(),
        'total_records' => 5,
        'status' => 'completed',
    ]);

    $response = $this->getJson('/api/failures', [
        'Authorization' => 'Bearer test_api_key',
    ]);

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data',
        'current_page',
        'per_page',
        'total',
    ]);
});

test('can get session detail', function () {
    $session = Session::create([
        'session_id' => 'test-session-456',
        'rake_id' => 'RAKE-002',
        'read_time' => now(),
        'download_date' => now(),
        'total_records' => 3,
        'status' => 'completed',
    ]);

    FailureRecord::create([
        'session_id' => $session->id,
        'block_no' => 1,
        'timestamp' => now(),
        'car_no' => 1,
        'occur_recover' => 0,
        'train_id' => 'TS01',
        'location_m' => 0,
        'equipment_code' => 1,
        'equipment_name' => 'Engine',
        'fault_code' => 100,
        'fault_abbrev' => 'OVER',
        'classification' => 'heavy',
        'notch' => 'EB',
        'speed_kmh' => 0,
        'overhead_v' => 0,
    ]);

    $response = $this->getJson('/api/failures/' . $session->session_id, [
        'Authorization' => 'Bearer test_api_key',
    ]);

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'session',
        'records',
    ]);
    $response->assertJsonCount(1, 'records');
});

test('can access dashboard API', function () {
    $response = $this->getJson('/api/dashboard', [
        'Authorization' => 'Bearer test_api_key',
    ]);

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'total_sessions',
        'total_records',
        'per_rake',
        'per_equipment',
        'per_classification',
        'recent_heavy_faults',
    ]);
});

test('health check endpoint works', function () {
    $response = $this->getJson('/api/health', [
        'Authorization' => 'Bearer test_api_key',
    ]);

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'status',
        'version',
    ]);
});
