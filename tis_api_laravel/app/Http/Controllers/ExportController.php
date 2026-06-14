<?php

namespace App\Http\Controllers;

use App\Exports\FailureRecordsExport;
use App\Models\FailureRecord;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class ExportController extends Controller
{
    private function applyFilters(Request $request, $query)
    {
        if ($request->filled('classification')) {
            $query->where('classification', ucfirst(strtolower($request->classification)));
        }
        if ($request->filled('from') && $request->filled('to')) {
            $query->whereBetween('timestamp', [$request->from . ' 00:00:00', $request->to . ' 23:59:59']);
        }
        if ($request->filled('rake_id')) {
            $query->whereHas('session', fn($q) => $q->where('rake_id', 'like', '%' . $request->rake_id . '%'));
        }
    }

    public function exportExcel(Request $request)
    {
        return Excel::download(new FailureRecordsExport($request->only(['rake_id', 'classification', 'from', 'to'])), 'failure_records_' . now()->format('Y-m-d_H-i-s') . '.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $query = FailureRecord::with('session');
        $this->applyFilters($request, $query);

        if ($query->count() > 200) {
            return redirect()->to(route('failures.index'))->with('error', 'PDF export dibatasi maksimal 200 records. Gunakan filter untuk mempersempit data.');
        }

        ini_set('max_execution_time', 300);

        $head = '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Failure Records Report</title>
            <style>
                body { font-family: Arial, sans-serif; font-size: 10px; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th, td { border: 1px solid #ddd; padding: 4px 6px; text-align: left; }
                th { background-color: #f2f2f2; }
                h1 { color: #333; font-size: 18px; }
            </style></head><body>
            <h1>Failure Records Report</h1>
            <p>Generated on: ' . now()->format('Y-m-d H:i:s') . '</p>
            <table>
                <thead><tr>
                    <th>Timestamp</th><th>Trainset ID</th><th>Equipment</th><th>Fault</th>
                    <th>Fault Desc</th><th>Fault Code</th><th>Class</th><th>Car</th>
                    <th>Speed</th><th>Overhead</th><th>Notch</th><th>Duration</th>
                </tr></thead><tbody>';

        $body = '';
        $query->chunk(200, function ($records) use (&$body) {
            foreach ($records as $r) {
                $body .= '<tr>'
                    . '<td>' . ($r->timestamp?->format('Y-m-d H:i:s') ?? '-') . '</td>'
                    . '<td>' . ($r->session->rake_id ?? 'N/A') . '</td>'
                    . '<td>' . e($r->equipment_name) . '</td>'
                    . '<td>' . e($r->fault_abbrev) . '</td>'
                    . '<td>' . e($r->fault_description) . '</td>'
                    . '<td>' . e($r->fault_code) . '</td>'
                    . '<td>' . e($r->classification) . '</td>'
                    . '<td>' . e($r->car_no) . '</td>'
                    . '<td>' . ($r->speed_kmh ?? '-') . '</td>'
                    . '<td>' . ($r->overhead_v ?? '-') . '</td>'
                    . '<td>' . e($r->notch) . '</td>'
                    . '<td>' . e($r->duration_label) . '</td>'
                    . '</tr>';
            }
        });

        $foot = '</tbody></table></body></html>';

        $pdf = Pdf::loadHtml($head . $body . $foot);

        return $pdf->download('failure_records_' . now()->format('Y-m-d_H-i-s') . '.pdf');
    }
}
