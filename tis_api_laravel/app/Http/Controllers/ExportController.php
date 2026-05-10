<?php

namespace App\Http\Controllers;

use App\Exports\FailureRecordsExport;
use App\Models\FailureRecord;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class ExportController extends Controller
{
    public function exportExcel()
    {
        return Excel::download(new FailureRecordsExport, 'failure_records_' . now()->format('Y-m-d_H-i-s') . '.xlsx');
    }

    public function exportPdf()
    {
        $records = FailureRecord::with('session')->get();

        $pdf = Pdf::loadView('exports.failure-records-pdf', compact('records'));

        return $pdf->download('failure_records_' . now()->format('Y-m-d_H-i-s') . '.pdf');
    }
}
