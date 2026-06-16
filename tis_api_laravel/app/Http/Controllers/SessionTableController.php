<?php

namespace App\Http\Controllers;

use App\Models\Session;
use App\Models\UploadedFile;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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

        $isSuperAdmin = $request->user()?->hasRole('superadmin') ?? false;

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
            ->addColumn('actions', function ($session) use ($isSuperAdmin) {
                $csvUrl = route('sessions.download.csv', $session->session_id);
                $pdfUrl = route('sessions.download.pdf', $session->session_id);

                $deleteUrl = route('sessions.destroy', $session->id);

                $html = '<div class="inline-flex flex-wrap justify-end gap-2">'
                    . '<button type="button" onclick="confirmAction(\'confirm-modal\', \'Download CSV for session ' . e($session->session_id) . '?\', \'' . $csvUrl . '\')" class="inline-flex items-center gap-1 rounded-md border border-green-200 bg-green-50 px-3 py-1.5 text-xs font-medium text-green-700 hover:bg-green-100 dark:border-green-800 dark:bg-green-900/20 dark:text-green-400">'
                    . '<svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17v2a2 2 0 002 2h14a2 2 0 002-2v-2M7 11l5 5 5-5M7 7l5 5 5-5"/></svg>'
                    . 'CSV</button>'
                    . '<button type="button" onclick="confirmAction(\'confirm-modal\', \'Download PDF for session ' . e($session->session_id) . '?\', \'' . $pdfUrl . '\')" class="inline-flex items-center gap-1 rounded-md border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-medium text-blue-700 hover:bg-blue-100 dark:border-blue-800 dark:bg-blue-900/20 dark:text-blue-400">'
                    . '<svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>'
                    . 'PDF</button>';

                if ($isSuperAdmin) {
                    $html .= '<button type="button" onclick="confirmAction(\'confirm-modal\', \'Delete session ' . e($session->session_id) . '? All failure records and uploaded files will be permanently deleted.\', \'' . $deleteUrl . '\', \'DELETE\')" class="inline-flex items-center gap-1 rounded-md border border-red-300 bg-red-50 px-3 py-1.5 text-xs font-medium text-red-700 hover:bg-red-100 dark:border-red-700 dark:bg-red-900/20 dark:text-red-400">'
                        . '<svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>'
                        . 'Delete</button>';
                }

                $html .= '</div>';
                return $html;
            })
            ->rawColumns(['files', 'actions'])
            ->make(true);
    }

    public function destroy(string $id)
    {
        $user = request()->user();
        if (!$user || !$user->hasRole('superadmin')) {
            abort(403, 'Only superadmin can delete sessions.');
        }

        $session = Session::findOrFail($id);

        DB::transaction(function () use ($session) {
            foreach ($session->uploadedFiles as $file) {
                Storage::disk('public')->delete($file->path);
                $file->delete();
            }

            $directory = 'uploads/' . $session->session_id;
            if (Storage::disk('public')->directoryExists($directory)) {
                Storage::disk('public')->deleteDirectory($directory);
            }

            $session->failureRecords()->whereNotNull('paired_record_id')->update(['paired_record_id' => null]);
            $session->failureRecords()->delete();
            $session->delete();
        });

        return redirect()->back()->with('success', 'Session ' . e($session->session_id) . ' has been permanently deleted.');
    }

    public function cleanupOrphans()
    {
        $user = request()->user();
        if (!$user || !$user->hasRole('superadmin')) {
            abort(403, 'Only superadmin can clean up orphan files.');
        }

        $deletedFileCount = 0;
        $freedBytes = 0;

        DB::transaction(function () use (&$deletedFileCount, &$freedBytes) {
            $orphans = UploadedFile::whereDoesntHave('session')->get();

            foreach ($orphans as $file) {
                $path = $file->path;
                if ($path && Storage::disk('public')->exists($path)) {
                    $freedBytes += Storage::disk('public')->size($path);
                    Storage::disk('public')->delete($path);
                }
                $file->delete();
                $deletedFileCount++;
            }

            $directories = Storage::disk('public')->directories('uploads');
            foreach ($directories as $dir) {
                $sessionId = basename($dir);
                if (!Session::where('session_id', $sessionId)->exists()) {
                    $filesInDir = Storage::disk('public')->allFiles($dir);
                    foreach ($filesInDir as $f) {
                        $freedBytes += Storage::disk('public')->size($f);
                    }
                    Storage::disk('public')->deleteDirectory($dir);
                }
            }
        });

        $freedMb = number_format($freedBytes / 1048576, 2);
        return redirect()->back()->with('success', "Cleanup complete. {$deletedFileCount} orphan file(s) deleted, {$freedMb} MB storage freed.");
    }
}
