<?php

namespace App\Http\Controllers;

use App\Models\Session;
use Illuminate\Support\Facades\Storage;

class SessionDownloadController extends Controller
{
    public function index()
    {
        $sessions = Session::with([
                'uploadedFiles' => function ($query) {
                    $query->orderBy('original_filename');
                },
            ])
            ->withCount('uploadedFiles')
            ->orderByDesc('read_time')
            ->paginate(15);

        return view('cms.session-downloads', compact('sessions'));
    }

    public function downloadCsv(string $sessionId)
    {
        return $this->downloadByKind($sessionId, 'csv');
    }

    public function downloadPdf(string $sessionId)
    {
        return $this->downloadByKind($sessionId, 'pdf');
    }

    private function downloadByKind(string $sessionId, string $kind)
    {
        $session = Session::with('uploadedFiles')
            ->where('session_id', $sessionId)
            ->firstOrFail();

        $file = $session->uploadedFiles
            ->first(fn ($uploadedFile) => strtolower(pathinfo($uploadedFile->original_filename, PATHINFO_EXTENSION)) === $kind);

        abort_if(! $file, 404, 'File tidak ditemukan untuk session ini.');

        return Storage::disk('public')->download($file->path, $file->original_filename);
    }
}
