<?php

use App\Models\Session;
use App\Models\UploadedFile;
use Illuminate\Support\Carbon;

test('backfill session files attaches historical files to matching sessions', function () {
    Carbon::setTestNow('2026-06-12 10:01:00');

    $session = Session::create([
        'session_id' => 'session-legacy-001',
        'session_hash' => 'hash-legacy-001',
        'rake_id' => '08',
        'read_time' => '2026-06-12 10:00:00',
        'download_date' => '2026-06-12 10:00:30',
        'total_records' => 2,
        'status' => 'completed',
    ]);

    UploadedFile::create([
        'file_id' => 'file-legacy-csv',
        'rake_id' => '08',
        'filename' => 'legacy.csv',
        'original_filename' => 'D260612_TS08_100000.csv',
        'path' => 'uploads/legacy.csv',
        'mime_type' => 'text/csv',
        'size' => 10,
        'status' => 'uploaded',
    ]);

    UploadedFile::create([
        'file_id' => 'file-legacy-pdf',
        'rake_id' => '08',
        'filename' => 'legacy.pdf',
        'original_filename' => 'D260612_TS08_100000.pdf',
        'path' => 'uploads/legacy.pdf',
        'mime_type' => 'application/pdf',
        'size' => 10,
        'status' => 'uploaded',
    ]);

    $this->artisan('files:backfill-session-files')
        ->expectsOutput('Files scanned: 2')
        ->expectsOutput('Files matched: 2')
        ->expectsOutput('Files skipped: 0')
        ->expectsOutput('Ambiguous groups: 0')
        ->expectsOutput('Mode: applied')
        ->assertExitCode(0);

    expect(UploadedFile::where('session_id', $session->session_id)->count())->toBe(2);

    Carbon::setTestNow();
});

test('backfill skips files when session already has same filename', function () {
    Carbon::setTestNow('2026-06-12 10:01:00');

    $session = Session::create([
        'session_id' => 'session-legacy-002',
        'session_hash' => 'hash-legacy-002',
        'rake_id' => '08',
        'read_time' => '2026-06-12 10:00:00',
        'download_date' => '2026-06-12 10:00:30',
        'total_records' => 2,
        'status' => 'completed',
    ]);

    UploadedFile::create([
        'file_id' => 'file-legacy-csv-1',
        'rake_id' => '08',
        'filename' => 'legacy-1.csv',
        'original_filename' => 'D260612_008.csv',
        'path' => 'uploads/legacy-1.csv',
        'mime_type' => 'text/csv',
        'size' => 10,
        'status' => 'uploaded',
    ]);

    UploadedFile::create([
        'file_id' => 'file-legacy-csv-2',
        'rake_id' => '08',
        'filename' => 'legacy-2.csv',
        'original_filename' => 'D260612_008.csv',
        'path' => 'uploads/legacy-2.csv',
        'mime_type' => 'text/csv',
        'size' => 10,
        'status' => 'uploaded',
    ]);

    $this->artisan('files:backfill-session-files')
        ->expectsOutput('Mode: applied')
        ->assertExitCode(0);

    expect(UploadedFile::where('session_id', $session->session_id)->count())->toBe(1);

    Carbon::setTestNow();
});
