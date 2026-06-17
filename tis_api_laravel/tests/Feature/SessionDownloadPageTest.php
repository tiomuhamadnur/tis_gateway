<?php

use App\Models\Session;
use App\Models\UploadedFile;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Storage::fake('public');
    Permission::firstOrCreate(['name' => 'view failures']);
    $viewer = Role::firstOrCreate(['name' => 'viewer']);
    $viewer->givePermissionTo('view failures');
});

test('authorized user can see session download page and download files', function () {
    $user = User::factory()->create();
    $user->assignRole('viewer');

    $session = Session::create([
        'session_id' => 'session-001',
        'session_hash' => 'hash-001',
        'rake_id' => '08',
        'read_time' => '2026-06-12 10:00:00',
        'download_date' => now(),
        'total_records' => 2,
        'status' => 'completed',
    ]);

    Storage::disk('public')->put('uploads/session-001/csv-file.csv', 'csv-content');
    Storage::disk('public')->put('uploads/session-001/pdf-file.pdf', 'pdf-content');

    UploadedFile::create([
        'file_id' => 'file-csv-001',
        'session_id' => $session->session_id,
        'rake_id' => $session->rake_id,
        'filename' => 'csv-file.csv',
        'original_filename' => 'report.csv',
        'path' => 'uploads/session-001/csv-file.csv',
        'mime_type' => 'text/csv',
        'size' => 11,
        'status' => 'uploaded',
    ]);

    UploadedFile::create([
        'file_id' => 'file-pdf-001',
        'session_id' => $session->session_id,
        'rake_id' => $session->rake_id,
        'filename' => 'pdf-file.pdf',
        'original_filename' => 'report.pdf',
        'path' => 'uploads/session-001/pdf-file.pdf',
        'mime_type' => 'application/pdf',
        'size' => 11,
        'status' => 'uploaded',
    ]);

    $this->actingAs($user)
        ->get(route('sessions.download.index'))
        ->assertStatus(200)
        ->assertSee('Session Downloads')
        ->assertSee('CSV')
        ->assertSee('PDF');

    $this->actingAs($user)
        ->get(route('sessions.download.csv', $session->session_id))
        ->assertStatus(200)
        ->assertDownload('report.csv');

    $this->actingAs($user)
        ->get(route('sessions.download.pdf', $session->session_id))
        ->assertStatus(200)
        ->assertDownload('report.pdf');
});
