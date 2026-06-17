<?php

use App\Models\User;
use App\Models\Session;
use App\Models\FailureRecord;
use Livewire\Livewire;

test('dashboard component loads for authenticated user', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/dashboard');
    $response->assertStatus(200);
});

test('redirects to login when not authenticated', function () {
    $response = $this->get('/dashboard');
    $response->assertStatus(302);
    $response->assertRedirectToRoute('login');
});

function makeFailureRecord(array $overrides = []): array
{
    return array_merge([
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
    ], $overrides);
}

function makeSession(array $overrides = []): Session
{
    $attrs = array_merge([
        'session_id' => 'test-dash-' . uniqid(),
        'rake_id' => 'RAKE-001',
        'read_time' => now(),
        'download_date' => now(),
        'total_records' => 0,
        'status' => 'completed',
    ], $overrides);

    return Session::create($attrs);
}

test('dashboard displays statistics', function () {
    $user = User::factory()->create();

    $session = makeSession(['session_id' => 'test-dash-001', 'total_records' => 1]);

    FailureRecord::create(makeFailureRecord([
        'session_id' => $session->id,
        'classification' => 'heavy',
    ]));

    Livewire::actingAs($user)
        ->test('dashboard')
        ->assertSet('totalSessions', 1)
        ->assertSet('totalRecords', 1);
});

test('dashboard loads failure records by classification', function () {
    $user = User::factory()->create();

    $session = makeSession(['session_id' => 'test-dash-002', 'total_records' => 2]);

    FailureRecord::create(makeFailureRecord([
        'session_id' => $session->id,
        'classification' => 'heavy',
    ]));

    FailureRecord::create(makeFailureRecord([
        'session_id' => $session->id,
        'equipment_name' => 'Brake',
        'fault_name' => 'Pressure Loss',
        'fault_abbrev' => 'PRESS',
        'classification' => 'moderate',
    ]));

    Livewire::actingAs($user)
        ->test('dashboard')
        ->call('loadData')
        ->assertSet('totalSessions', 1);
});
