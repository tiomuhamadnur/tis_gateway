<?php

namespace App\Http\Controllers;

use App\Models\Session;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;

class SessionTableController extends Controller
{
    public function getData(Request $request)
    {
        $query = Session::query()
            ->with(['uploadedFiles:id,session_id,original_filename,path'])
            ->withCount('uploadedFiles');

        if ($request->filled('q')) {
            $search = '%' . $request->q . '%';
            $query->where(function ($q) use ($search) {
                $q->where('session_id', 'like', $search)
                  ->orWhere('rake_id', 'like', $search)
                  ->orWhereHas('uploadedFiles', function ($fq) use ($search) {
                      $fq->where('original_filename', 'like', $search);
                  });
            });
        }

        if ($request->filled('from') && $request->filled('to')) {
            $query->whereBetween('read_time', [$request->from . ' 00:00:00', $request->to . ' 23:59:59']);
        }

        return DataTables::of($query)
            ->addColumn('session_date', function ($session) {
                return $session->read_time?->format('d M Y H:i:s') ?? '-';
            })
            ->addColumn('trainset', function ($session) {
                return 'TS-' . str_pad((int) $session->rake_id, 2, '0', STR_PAD_LEFT);
            })
            ->addColumn('file_count_label', function ($session) {
                return $session->uploaded_files_count . ' file';
            })
            ->addColumn('files', function ($session) {
                $csvFile = $session->uploadedFiles->first(fn ($file) => strtolower(pathinfo($file->original_filename, PATHINFO_EXTENSION)) === 'csv');
                $pdfFile = $session->uploadedFiles->first(fn ($file) => strtolower(pathinfo($file->original_filename, PATHINFO_EXTENSION)) === 'pdf');

                $html = '<div class="space-y-1">';
                $html .= '<div>CSV: ' . e($csvFile?->original_filename ?? '-') . '</div>';
                $html .= '<div>PDF: ' . e($pdfFile?->original_filename ?? '-') . '</div>';
                $html .= '</div>';
                return $html;
            })
            ->addColumn('actions', function ($session) {
                $csvUrl = route('sessions.download.csv', $session->session_id);
                $pdfUrl = route('sessions.download.pdf', $session->session_id);

                return '<div class="inline-flex flex-wrap justify-end gap-2">'
                    . '<a href="' . $csvUrl . '" class="inline-flex items-center rounded-md border border-green-200 bg-green-50 px-3 py-1.5 text-xs font-medium text-green-700 hover:bg-green-100 dark:border-green-800 dark:bg-green-900/20 dark:text-green-400">CSV</a>'
                    . '<a href="' . $pdfUrl . '" class="inline-flex items-center rounded-md border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-medium text-red-700 hover:bg-red-100 dark:border-red-800 dark:bg-red-900/20 dark:text-red-400">PDF</a>'
                    . '</div>';
            })
            ->rawColumns(['files', 'actions'])
            ->make(true);
    }
}
