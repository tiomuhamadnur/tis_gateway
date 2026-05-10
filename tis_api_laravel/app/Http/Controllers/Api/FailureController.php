<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Session;
use App\Models\FailureRecord;
use App\Services\TisEquipmentMap;
use App\Services\FaultPairingService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FailureController extends Controller
{
    /**
     * Terima upload failure records dari TIS Gateway.
     *
     * Payload format (dari cloud_uploader.py):
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

        $session = Session::create([
            'session_id'    => $sessionId,
            'rake_id'       => (string) $request->input('rake_id'),
            'read_time'     => $request->input('read_time'),
            'download_date' => now(),
            'total_records' => \count($records),
            'status'        => 'completed',
        ]);

        $inserts = [];
        $now = now()->toDateTimeString();

        foreach ($records as $rec) {
            $resolved = TisEquipmentMap::resolveFault(
                (int) $rec['equipment_code'],
                (int) $rec['fault_code'],
                $rec['equipment_name'],
                $rec['fault_name'],
            );

            $inserts[] = [
                'session_id'       => $session->id,
                'block_no'         => (int) $rec['block_no'],
                'timestamp'        => $rec['timestamp'],
                'car_no'           => (int) $rec['car_no'],
                'occur_recover'    => (int) $rec['occur_recover'],
                'train_id'         => $rec['train_id'],
                'location_m'       => (int) $rec['location_m'],
                'equipment_code'   => (int) $rec['equipment_code'],
                'equipment_name'   => $resolved['equipment_name'],
                'fault_code'       => (int) $rec['fault_code'],
                'fault_abbrev'     => $resolved['fault_abbrev'],
                'fault_description'=> $resolved['fault_description'],
                'classification'   => $resolved['classification'],
                'guidance'         => $resolved['guidance'],
                'notch'            => $rec['notch'],
                'speed_kmh'        => (int) $rec['speed_kmh'],
                'overhead_v'       => (int) $rec['overhead_v'],
                'created_at'       => $now,
                'updated_at'       => $now,
            ];
        }

        FailureRecord::insert($inserts);

        // Pair Occur ↔ Recover untuk seluruh rake ini (termasuk sesi lama yang belum terpair)
        (new FaultPairingService())->pairForRake((int) $session->rake_id);

        return response()->json([
            'session_id' => $sessionId,
            'received'   => \count($inserts),
            'status'     => 'success',
        ], 201);
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
