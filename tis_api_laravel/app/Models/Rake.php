<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rake extends Model
{
    use HasFactory;

    protected $fillable = [
        'rake_id',
        'name',
        'description',
        'active',
        'metadata',
    ];

    protected $casts = [
        'active' => 'boolean',
        'metadata' => 'array',
    ];
}
