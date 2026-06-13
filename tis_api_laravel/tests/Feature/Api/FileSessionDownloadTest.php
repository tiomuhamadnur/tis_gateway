<?php

use App\Models\Session;
use Illuminate\Http\UploadedFile as HttpUploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    config(['app.tis_api_key' => 'test_api_key']);
    Storage::fake('public');
});

test('can upload and download files by session', function () {
    $session = Session::create([
        'session_id' => 'session-001',
        'session_hash' => 'hash-001',
        'rake_id' => 'TS08',
        'read_time' => now(),
        'download_date' => now(),
        'total_records' => 2,
        'status' => 'completed',
    ]);

    $csv = HttpUploadedFile::fake()->create('report.csv', 10, 'text/csv');
    $pdf = HttpUploadedFile::fake()->create('report.pdf', 10, 'application/pdf');

    $this->postJson('/api/files', [
        'session_id' => $session->session_id,
        'rake_id' => $session->rake_id,
        'file' => $csv,
    ], [
        'Authorization' => 'Bearer test_api_key',
    ])->assertStatus(201);

    $this->postJson('/api/files', [
        'session_id' => $session->session_id,
        'rake_id' => $session->rake_id,
        'file' => $pdf,
    ], [
        'Authorization' => 'Bearer test_api_key',
    ])->assertStatus(201);

    $list = $this->getJson('/api/files/sessions/' . $session->session_id, [
        'Authorization' => 'Bearer test_api_key',
    ]);

    $list->assertStatus(200);
    $list->assertJsonCount(2, 'files');

    $csvDownload = $this->get('/api/files/sessions/' . $session->session_id . '/csv', [
        'Authorization' => 'Bearer test_api_key',
    ]);

    $csvDownload->assertStatus(200);
    $csvDownload->assertDownload('report.csv');
});
