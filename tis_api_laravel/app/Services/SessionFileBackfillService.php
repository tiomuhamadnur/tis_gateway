<?php

namespace App\Services;

use App\Models\Session;
use App\Models\UploadedFile;
use Illuminate\Support\Carbon;

class SessionFileBackfillService
{
    public function backfill(bool $dryRun = false): array
    {
        $files = UploadedFile::query()
            ->whereNull('session_id')
            ->orderBy('id')
            ->get();

        $stats = [
            'files_scanned' => $files->count(),
            'files_matched' => 0,
            'files_skipped' => 0,
            'ambiguous_groups' => 0,
            'dry_run' => $dryRun,
        ];

        $filesByKey = $files->groupBy(function (UploadedFile $file): string {
            $createdAt = $file->created_at ? Carbon::parse($file->created_at) : now();
            return $this->groupKey($file->rake_id, $createdAt->toDateString());
        });

        foreach ($filesByKey as $groupKey => $groupFiles) {
            [$rakeId, $date] = explode('|', $groupKey, 2);

            $sessions = Session::query()
                ->where('rake_id', $rakeId)
                ->whereDate('download_date', $date)
                ->orderBy('download_date')
                ->get();

            if ($sessions->isEmpty()) {
                $stats['files_skipped'] += $groupFiles->count();
                continue;
            }

            $session = $this->pickBestSession($sessions, $groupFiles->first());

            if (! $session) {
                $stats['ambiguous_groups']++;
                $stats['files_skipped'] += $groupFiles->count();
                continue;
            }

            foreach ($groupFiles as $file) {
                if ($dryRun) {
                    $stats['files_matched']++;
                    continue;
                }

                $alreadyAttached = UploadedFile::query()
                    ->where('session_id', $session->session_id)
                    ->where('original_filename', $file->original_filename)
                    ->exists();

                if ($alreadyAttached) {
                    $stats['files_skipped']++;
                    continue;
                }

                $file->session_id = $session->session_id;
                $file->save();
                $stats['files_matched']++;
            }
        }

        return $stats;
    }

    private function groupKey(string $rakeId, string $date): string
    {
        return $rakeId . '|' . $date;
    }

    private function pickBestSession($sessions, UploadedFile $file): ?Session
    {
        if ($sessions->count() === 1) {
            return $sessions->first();
        }

        $fileTime = $file->created_at ? Carbon::parse($file->created_at) : now();

        return $sessions
            ->sortBy(function (Session $session) use ($fileTime) {
                return abs($fileTime->diffInSeconds(Carbon::parse($session->download_date), false));
            })
            ->first();
    }
}
