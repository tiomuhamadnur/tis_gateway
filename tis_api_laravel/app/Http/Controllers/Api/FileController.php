<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Session;
use App\Models\UploadedFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'session_id' => 'required|string|exists:failure_sessions,session_id',
            'rake_id' => 'required|string',
            'file' => 'required|file|mimes:csv,pdf|max:10240',
        ]);

        $sessionId = $request->input('session_id');
        $file = $request->file('file');
        $originalFilename = $file->getClientOriginalName();

        $existing = UploadedFile::where('session_id', $sessionId)
            ->where('original_filename', $originalFilename)
            ->first();

        if ($existing) {
            return response()->json([
                'file_id' => $existing->file_id,
                'filename' => $existing->original_filename,
                'status' => 'duplicate',
            ], 200);
        }

        $fileId = Str::uuid()->toString();
        $filename = $fileId . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('uploads/' . $sessionId, $filename, 'public');

        $uploadedFile = UploadedFile::create([
            'file_id' => $fileId,
            'session_id' => $sessionId,
            'rake_id' => $request->rake_id,
            'filename' => $filename,
            'original_filename' => $originalFilename,
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'status' => 'uploaded',
        ]);

        return response()->json([
            'file_id' => $fileId,
            'filename' => $uploadedFile->original_filename,
            'status' => 'uploaded',
        ], 201);
    }

    public function indexBySession(string $sessionId)
    {
        $session = Session::where('session_id', $sessionId)->firstOrFail();
        $files = UploadedFile::where('session_id', $session->session_id)
            ->orderBy('created_at')
            ->get();

        return response()->json([
            'session_id' => $session->session_id,
            'rake_id' => $session->rake_id,
            'files' => $files,
        ]);
    }

    public function downloadBySession(string $sessionId, string $kind)
    {
        $file = UploadedFile::where('session_id', $sessionId)
            ->orderByDesc('created_at')
            ->get()
            ->first(function (UploadedFile $uploadedFile) use ($kind) {
                return strtolower(pathinfo($uploadedFile->original_filename, PATHINFO_EXTENSION)) === strtolower($kind);
            });

        abort_if(! $file, 404, 'File tidak ditemukan untuk sesi ini.');

        return Storage::disk('public')->download($file->path, $file->original_filename);
    }

    public function download(string $fileId)
    {
        $file = UploadedFile::where('file_id', $fileId)->firstOrFail();

        return Storage::disk('public')->download($file->path, $file->original_filename);
    }
}
