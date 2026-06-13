<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Services\SessionFileBackfillService;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Artisan::command('files:backfill-session-files {--dry-run}', function (SessionFileBackfillService $service) {
    $stats = $service->backfill((bool) $this->option('dry-run'));

    $this->info('Files scanned: ' . $stats['files_scanned']);
    $this->info('Files matched: ' . $stats['files_matched']);
    $this->info('Files skipped: ' . $stats['files_skipped']);
    $this->info('Ambiguous groups: ' . $stats['ambiguous_groups']);
    $this->info($stats['dry_run'] ? 'Mode: dry-run' : 'Mode: applied');
})->purpose('Backfill uploaded files to historical sessions');
