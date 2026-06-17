<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Session;
use App\Models\FailureRecord;
use App\Services\TisEquipmentMap;
use App\Services\FaultPairingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FailureController extends Controller
{
    /**
     * Receive failure records upload from TIS Gateway.
     *
     * Payload format (from cloud_uploader.py):
     * {
     *   "rake_id":      5,
     *   "read_time":    "2026-05-07T16:08:16",
     *   "record_count": 200,
     *   "records": [{
     *     "block_no": 0, "timestamp": "...", "car_no": 6,
     *     "occur_recover": 0, "train_id": "FFFF", "location_m": 0,
     *     "equipment_code": 9, "equipment_name": "PA",
     *     "fault_code": 806, "fault_name": "DATASA",
     *     "notch": "EB", "speed_kmh": 0, "overhead_v": 10
     *   }]
     * }
     *
     * Session selalu dibuat untuk mencatat setiap upload.
     * Record duplikat (berdasarkan hash seluruh field TIS) untuk rake yang
     * sama tidak akan di-insert — validasi unik di level record, bukan session.
     */
    public function store(Request $request)
    {
        $request->validate([
            'rake_id'                    => 'required',
            'read_time'                  => 'required|date',
            'records'                    => 'required|array|min:1',
            'records.*.block_no'         => 'required|integer|min:0',
            'records.*.timestamp'        => 'required|date',
            'records.*.car_no'           => 'required|integer|min:1|max:6',
            'records.*.occur_recover'    => 'required|integer|in:0,1',
            'records.*.train_id'         => 'required|string|max:10',
            'records.*.location_m'       => 'required|integer',
            'records.*.equipment_code'   => 'required|integer|min:1',
            'records.*.equipment_name'   => 'required|string|max:50',
            'records.*.fault_code'       => 'required|integer|min:0',
            'records.*.fault_name'       => 'required|string|max:20',
            'records.*.notch'            => 'required|string|max:10',
            'records.*.speed_kmh'        => 'required|integer|min:0',
            'records.*.overhead_v'       => 'required|integer|min:0',
        ]);

        $sessionId = Str::uuid()->toString();
        $records = $request->input('records');

        $session = DB::transaction(function () use ($sessionId, $records, $request) {
            $session = Session::create([
                'session_id'    => $sessionId,
                'rake_id'       => (string) $request->input('rake_id'),
                'read_time'     => $request->input('read_time'),
                'download_date' => now(),
                'total_records' => 0,
                'status'        => 'completed',
            ]);

            // Ambil semua record_hash yang sudah ada untuk rake ini
            $existingHashes = FailureRecord::whereIn('session_id', function ($q) use ($request) {
                $q->select('id')
                  ->from('failure_sessions')
                  ->where('rake_id', (string) $request->input('rake_id'));
            })->pluck('record_hash')->filter()->values()->toArray();

            $existingHashSet = array_flip($existingHashes);

            $inserts = [];
            $now = now()->toDateTimeString();
            $newCount = 0;

            foreach ($records as $rec) {
                $hash = $this->computeRecordHash($rec);

                if (isset($existingHashSet[$hash])) {
                    continue;
                }
                $existingHashSet[$hash] = true;

                $resolved = TisEquipmentMap::resolveFault(
                    (int) $rec['equipment_code'],
                    (int) $rec['fault_code'],
                    $rec['equipment_name'],
                    $rec['fault_name'],
                );

                $inserts[] = [
                    'session_id'        => $session->id,
                    'record_hash'       => $hash,
                    'block_no'          => (int) $rec['block_no'],
                    'timestamp'         => $rec['timestamp'],
                    'car_no'            => (int) $rec['car_no'],
                    'occur_recover'     => (int) $rec['occur_recover'],
                    'train_id'          => $rec['train_id'],
                    'location_m'        => (int) $rec['location_m'],
                    'equipment_code'    => (int) $rec['equipment_code'],
                    'equipment_name'    => $resolved['equipment_name'],
                    'fault_code'        => (int) $rec['fault_code'],
                    'fault_abbrev'      => $resolved['fault_abbrev'],
                    'fault_description' => $resolved['fault_description'],
                    'classification'    => $resolved['classification'],
                    'guidance'          => $resolved['guidance'],
                    'notch'             => $rec['notch'],
                    'speed_kmh'         => (int) $rec['speed_kmh'],
                    'overhead_v'        => (int) $rec['overhead_v'],
                    'created_at'        => $now,
                    'updated_at'        => $now,
                ];
                $newCount++;
            }

            if (!empty($inserts)) {
                FailureRecord::insert($inserts);
            }

            $session->update(['total_records' => $newCount]);

            return $session;
        });

        // Pair Occur ↔ Recover untuk seluruh rake ini (termasuk sesi lama yang belum terpair)
        (new FaultPairingService())->pairForRake((int) $session->rake_id);

        return response()->json([
            'session_id' => $sessionId,
            'received'   => $session->total_records,
            'status'     => 'success',
        ], 201);
    }

    /**
     * Hash untuk satu record TIS — menentukan keunikan data di level record.
     */
    private function computeRecordHash(array $record): string
    {
        $normalized = [
            'block_no'       => (int) ($record['block_no'] ?? 0),
            'timestamp'      => (string) ($record['timestamp'] ?? ''),
            'car_no'         => (int) ($record['car_no'] ?? 0),
            'occur_recover'  => (int) ($record['occur_recover'] ?? 0),
            'train_id'       => (string) ($record['train_id'] ?? ''),
            'location_m'     => (int) ($record['location_m'] ?? 0),
            'equipment_code' => (int) ($record['equipment_code'] ?? 0),
            'equipment_name' => (string) ($record['equipment_name'] ?? ''),
            'fault_code'     => (int) ($record['fault_code'] ?? 0),
            'fault_name'     => (string) ($record['fault_name'] ?? ''),
            'notch'          => (string) ($record['notch'] ?? ''),
            'speed_kmh'      => (int) ($record['speed_kmh'] ?? 0),
            'overhead_v'     => (int) ($record['overhead_v'] ?? 0),
        ];

        return hash('sha256', json_encode($normalized, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    /**
     * List sesi download dengan filter opsional.
     *
     * Query params: rake_id, from, to (filter read_time), per_page
     */
    public function index(Request $request)
    {
        $query = Session::withCount('failureRecords');

        if ($request->filled('rake_id')) {
            $query->where('rake_id', $request->input('rake_id'));
        }

        if ($request->filled('from') && $request->filled('to')) {
            $query->whereBetween('read_time', [$request->input('from'), $request->input('to')]);
        }

        $sessions = $query->orderByDesc('read_time')
                          ->paginate($request->integer('per_page', 15));

        return response()->json($sessions);
    }

    /**
     * Detail satu sesi beserta seluruh records-nya.
     */
    public function show(string $sessionId)
    {
        $session = Session::with('failureRecords')
                          ->where('session_id', $sessionId)
                          ->firstOrFail();

        return response()->json([
            'session' => $session,
            'records' => $session->failureRecords,
        ]);
    }
}
