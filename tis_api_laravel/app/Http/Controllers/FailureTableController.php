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
            ->select('failure_records.*')
            ->where('failure_records.occur_recover', 0); // hanya tampilkan Occur

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
            ->addColumn('recovered_at', fn($r) => $r->paired?->timestamp?->toDateTimeString())
            ->addColumn('duration_label', fn($r) => $r->duration_label)
            ->addColumn('actions', fn($r) => $r->id)
            ->rawColumns(['actions'])
            ->make(true);
    }
}
