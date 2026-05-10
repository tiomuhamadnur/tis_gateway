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

test('dashboard displays statistics', function () {
    $user = User::factory()->create();

    // Create test data
    $session = Session::create([
        'session_id' => 'test-dash-001',
        'rake_id' => 'RAKE-001',
        'download_date' => now(),
        'total_records' => 5,
        'status' => 'completed',
    ]);

    FailureRecord::create([
        'session_id' => $session->id,
        'timestamp' => now(),
        'equipment_name' => 'Engine',
        'fault_name' => 'Overheating',
        'classification' => 'heavy',
    ]);

    Livewire::actingAs($user)
        ->test('dashboard')
        ->assertSet('totalSessions', 1)
        ->assertSet('totalRecords', 1);
});

test('dashboard loads failure records by classification', function () {
    $user = User::factory()->create();

    $session = Session::create([
        'session_id' => 'test-dash-002',
        'rake_id' => 'RAKE-001',
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

    FailureRecord::create([
        'session_id' => $session->id,
        'timestamp' => now(),
        'equipment_name' => 'Brake',
        'fault_name' => 'Pressure Loss',
        'classification' => 'moderate',
    ]);

    Livewire::actingAs($user)
        ->test('dashboard')
        ->call('loadData')
        ->assertSet('totalSessions', 1);
});
