<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UploadedFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'rake_id' => 'required|string',
            'file' => 'required|file|mimes:csv,pdf|max:10240', // 10MB max
        ]);

        $file = $request->file('file');
        $fileId = Str::uuid()->toString();
        $filename = $fileId . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('uploads', $filename, 'public');

        $uploadedFile = UploadedFile::create([
            'file_id' => $fileId,
            'rake_id' => $request->rake_id,
            'filename' => $filename,
            'original_filename' => $file->getClientOriginalName(),
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'status' => 'uploaded',
        ]);

        return response()->json([
            'file_id' => $fileId,
            'filename' => $uploadedFile->original_filename,
            'status' => 'uploaded'
        ], 201);
    }
}
