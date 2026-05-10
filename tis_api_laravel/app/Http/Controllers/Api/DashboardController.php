<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Session;
use App\Models\FailureRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Ringkasan statistik keseluruhan.
     */
    public function index()
    {
        $totalSessions = Session::count();
        $totalRecords  = FailureRecord::count();
        $recentSessions = Session::orderByDesc('read_time')->take(5)->get();

        $perRake = Session::select('rake_id', DB::raw('count(*) as count'))
            ->groupBy('rake_id')
            ->get();

        // Agregasi per equipment (gunakan equipment_code + name)
        $perEquipment = FailureRecord::select(
                'equipment_code',
                'equipment_name',
                DB::raw('count(*) as count')
            )
            ->groupBy('equipment_code', 'equipment_name')
            ->orderByDesc('count')
            ->take(10)
            ->get();

        $perClassification = FailureRecord::select('classification', DB::raw('count(*) as count'))
            ->groupBy('classification')
            ->get();

        // 10 heavy faults terbaru
        $heavyFaults = FailureRecord::where('classification', 'Heavy')
            ->orderByDesc('timestamp')
            ->take(10)
            ->get([
                'id', 'block_no', 'timestamp', 'car_no', 'occur_recover',
                'train_id', 'equipment_code', 'equipment_name',
                'fault_code', 'fault_abbrev', 'classification', 'notch',
                'speed_kmh', 'overhead_v',
            ]);

        return response()->json([
            'total_sessions'      => $totalSessions,
            'total_records'       => $totalRecords,
            'recent_sessions'     => $recentSessions,
            'per_rake'            => $perRake,
            'per_equipment'       => $perEquipment,
            'per_classification'  => $perClassification,
            'recent_heavy_faults' => $heavyFaults,
        ]);
    }

    /**
     * Trend jumlah fault dari waktu ke waktu.
     *
     * Query params: from (required), to (required), group_by (day|week|month), rake_id
     */
    public function trend(Request $request)
    {
        $request->validate([
            'from'     => 'required|date',
            'to'       => 'required|date|after_or_equal:from',
            'group_by' => 'in:day,week,month',
        ]);

        $groupBy = $request->input('group_by', 'day');

        // SQLite & MySQL masing-masing punya format fungsi tanggal berbeda
        $isSqlite = config('database.default') === 'sqlite';
        $dateExpr = $isSqlite
            ? match ($groupBy) {
                'week'  => "strftime('%Y-%W', timestamp)",
                'month' => "strftime('%Y-%m', timestamp)",
                default => "strftime('%Y-%m-%d', timestamp)",
            }
            : match ($groupBy) {
                'week'  => "DATE_FORMAT(timestamp, '%Y-%u')",
                'month' => "DATE_FORMAT(timestamp, '%Y-%m')",
                default => "DATE_FORMAT(timestamp, '%Y-%m-%d')",
            };

        $query = FailureRecord::select(
                DB::raw("$dateExpr as period"),
                DB::raw('count(*) as count')
            )
            ->whereBetween('timestamp', [$request->input('from'), $request->input('to')]);

        if ($request->filled('rake_id')) {
            $query->whereHas('session', function ($q) use ($request) {
                $q->where('rake_id', $request->input('rake_id'));
            });
        }

        $trends = $query->groupBy('period')->orderBy('period')->get();

        return response()->json($trends);
    }

    /**
     * Analisis Pareto fault code — frekuensi + persentase kumulatif.
     *
     * Query params: start_date, end_date, start_time, end_time,
     *               equipment_code (filter per equipment), rake_id, classification
     */
    public function pareto(Request $request)
    {
        $query = FailureRecord::select(
                'fault_code',
                'fault_abbrev',
                'fault_description',
                'equipment_name',
                'classification',
                DB::raw('count(*) as frequency')
            )
            ->groupBy('fault_code', 'fault_abbrev', 'fault_description', 'equipment_name', 'classification')
            ->orderByDesc('frequency');

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('timestamp', [
                $request->input('start_date'),
                $request->input('end_date'),
            ]);
        }

        if ($request->filled('start_time') && $request->filled('end_time')) {
            $query->whereTime('timestamp', '>=', $request->input('start_time'))
                  ->whereTime('timestamp', '<=', $request->input('end_time'));
        }

        if ($request->filled('equipment_code')) {
            $query->where('equipment_code', (int) $request->input('equipment_code'));
        }

        if ($request->filled('classification')) {
            $query->where('classification', $request->input('classification'));
        }

        if ($request->filled('rake_id')) {
            $query->whereHas('session', function ($q) use ($request) {
                $q->where('rake_id', $request->input('rake_id'));
            });
        }

        $data  = $query->get();
        $total = $data->sum('frequency');
        $cumulative = 0;

        $pareto = $data->map(function ($item) use (&$cumulative, $total) {
            $cumulative += $item->frequency;
            return [
                'fault_code'             => $item->fault_code,
                'fault_abbrev'           => $item->fault_abbrev,
                'fault_description'      => $item->fault_description,
                'equipment_name'         => $item->equipment_name,
                'classification'         => $item->classification,
                'frequency'              => $item->frequency,
                'cumulative_percentage'  => $total > 0
                    ? round(($cumulative / $total) * 100, 2)
                    : 0,
            ];
        });

        return response()->json($pareto);
    }
}
