<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UploadedFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_id',
        'rake_id',
        'filename',
        'original_filename',
        'path',
        'mime_type',
        'size',
        'status',
    ];
}
