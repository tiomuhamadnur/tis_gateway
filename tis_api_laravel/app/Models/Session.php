<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    use HasFactory;

    protected $table = 'failure_sessions';

    protected $fillable = [
        'session_id',
        'rake_id',
        'read_time',
        'download_date',
        'total_records',
        'status',
        'metadata',
    ];

    protected $casts = [
        'read_time'     => 'datetime',
        'download_date' => 'datetime',
        'metadata'      => 'array',
    ];

    public function failureRecords()
    {
        return $this->hasMany(FailureRecord::class);
    }
}
