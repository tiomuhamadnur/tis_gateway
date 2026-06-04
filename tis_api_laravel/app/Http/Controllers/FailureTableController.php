<?php

namespace App\Http\Controllers;

use App\Models\FailureRecord;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;

class FailureTableController extends Controller
{
    public function getData(Request $request)
    {
        $query = FailureRecord::with(['session', 'paired'])
            ->select('failure_records.*');

        if ($request->filled('classification')) {
            $query->where('classification', ucfirst(strtolower($request->classification)));
        }

        if ($request->filled('from') && $request->filled('to')) {
            $query->whereBetween('timestamp', [$request->from . ' 00:00:00', $request->to . ' 23:59:59']);
        }

        if ($request->filled('rake_id')) {
            $query->whereHas('session', fn($q) => $q->where('rake_id', 'like', '%' . $request->rake_id . '%'));
        }

        return DataTables::of($query)
            ->addColumn('session_rake_id', fn($r) => $r->session->rake_id ?? 'N/A')
            ->addColumn('status', fn($r) => $r->occur_recover_label)
            ->addColumn('pair_info', function ($r) {
                if (! $r->paired) {
                    return $r->occur_recover === 0
                        ? '<span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium bg-yellow-100 text-yellow-800">Active Occur</span>'
                        : '<span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium bg-zinc-100 text-zinc-700">Unpaired Recover</span>';
                }

                $other = $r->paired;
                $label = $r->occur_recover === 0 ? 'Resolved by Recover' : 'Matched with Occur';
                return sprintf(
                    '<div class="space-y-1 text-xs allow-wrap"><div class="font-medium">%s</div><div class="text-zinc-500">#%s · %s</div></div>',
                    $label,
                    $other->id,
                    $other->timestamp?->format('Y-m-d H:i:s') ?: '-'
                );
            })
            ->addColumn('duration_label', fn($r) => $r->duration_label)
            ->rawColumns(['pair_info'])
            ->make(true);
    }
}
