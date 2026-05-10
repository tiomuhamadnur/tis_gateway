<?php

namespace App\Services;

use App\Models\FailureRecord;
use Illuminate\Support\Facades\DB;

class FaultPairingService
{
    /**
     * Pair semua record Occur(0) ↔ Recover(1) untuk satu rake_id.
     *
     * Algoritma greedy: untuk setiap Occur, cari Recover tercepat (timestamp > occur)
     * dengan fault_code + car_no yang sama dan belum dipasangkan.
     *
     * Dipanggil setiap kali session baru diupload, sehingga record lama yang
     * sebelumnya belum punya pasangan (masih aktif) bisa terpair dengan Recover
     * yang baru masuk.
     */
    public function pairForRake(int $rakeId): void
    {
        // Ambil semua record yang belum dipasangkan untuk rake ini, urut timestamp ASC
        $records = FailureRecord::select('failure_records.*')
            ->join('failure_sessions', 'failure_records.session_id', '=', 'failure_sessions.id')
            ->where('failure_sessions.rake_id', $rakeId)
            ->whereNull('failure_records.paired_record_id')
            ->orderBy('failure_records.timestamp')
            ->get('failure_records.*');

        // Pisah Occur dan Recover
        $occurs   = $records->where('occur_recover', 0)->values();
        $recovers = $records->where('occur_recover', 1)->values();

        $pairedRecoverIds = [];
        $updates = [];

        foreach ($occurs as $occur) {
            // Cari Recover paling awal setelah timestamp Occur, belum dipakai
            $match = $recovers->first(function ($r) use ($occur, $pairedRecoverIds) {
                return $r->fault_code === $occur->fault_code
                    && $r->car_no    === $occur->car_no
                    && $r->timestamp >  $occur->timestamp
                    && !in_array($r->id, $pairedRecoverIds);
            });

            if ($match) {
                $pairedRecoverIds[] = $match->id;
                $duration = $occur->timestamp->diffInSeconds($match->timestamp);

                $updates[] = ['id' => $occur->id,  'paired_record_id' => $match->id,  'duration_seconds' => $duration];
                $updates[] = ['id' => $match->id,  'paired_record_id' => $occur->id,  'duration_seconds' => $duration];
            }
        }

        // Bulk update dalam satu transaksi
        if (!empty($updates)) {
            DB::transaction(function () use ($updates) {
                foreach ($updates as $u) {
                    FailureRecord::where('id', $u['id'])->update([
                        'paired_record_id' => $u['paired_record_id'],
                        'duration_seconds' => $u['duration_seconds'],
                    ]);
                }
            });
        }
    }
}
