<?php

use App\Models\Session;
use App\Models\FailureRecord;

beforeEach(function () {
    config(['app.tis_api_key' => 'test_api_key']);
});

test('can submit failure records with valid API key', function () {
    $response = $this->postJson('/api/failures', [
        'rake_id' => 'RAKE-001',
        'records' => [
            [
                'timestamp' => now()->toDateTimeString(),
                'equipment_name' => 'Engine',
                'fault_name' => 'Overheating',
                'classification' => 'heavy',
                'description' => 'Engine temperature exceeded limit',
            ],
            [
                'timestamp' => now()->toDateTimeString(),
                'equipment_name' => 'Brake System',
                'fault_name' => 'Pressure Loss',
                'classification' => 'moderate',
            ],
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
        'download_date' => now(),
        'total_records' => 3,
        'status' => 'completed',
    ]);

    FailureRecord::create([
        'session_id' => $session->id,
        'timestamp' => now(),
        'equipment_name' => 'Engine',
        'fault_name' => 'Overheating',
        'classification' => 'heavy',
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
