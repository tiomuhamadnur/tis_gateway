<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FailureRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'block_no',
        'timestamp',
        'car_no',
        'occur_recover',
        'paired_record_id',
        'duration_seconds',
        'train_id',
        'location_m',
        'equipment_code',
        'equipment_name',
        'fault_code',
        'fault_abbrev',
        'fault_description',
        'classification',
        'guidance',
        'notch',
        'speed_kmh',
        'overhead_v',
    ];

    protected $casts = [
        'timestamp'        => 'datetime',
        'occur_recover'    => 'integer',
        'block_no'         => 'integer',
        'car_no'           => 'integer',
        'location_m'       => 'integer',
        'equipment_code'   => 'integer',
        'fault_code'       => 'integer',
        'speed_kmh'        => 'integer',
        'overhead_v'       => 'integer',
        'paired_record_id' => 'integer',
        'duration_seconds' => 'integer',
    ];

    public function session()
    {
        return $this->belongsTo(Session::class);
    }

    public function paired()
    {
        return $this->belongsTo(FailureRecord::class, 'paired_record_id');
    }

    public function getOccurRecoverLabelAttribute(): string
    {
        return $this->occur_recover === 0 ? 'Occur' : 'Recover';
    }

    public function getDurationLabelAttribute(): string
    {
        if ($this->duration_seconds === null) {
            return '-';
        }
        $s = $this->duration_seconds;
        if ($s < 60) {
            return "{$s}s";
        }
        if ($s < 3600) {
            $m = intdiv($s, 60);
            $r = $s % 60;
            return $r > 0 ? "{$m}m {$r}s" : "{$m}m";
        }
        $h = intdiv($s, 3600);
        $m = intdiv($s % 3600, 60);
        return $m > 0 ? "{$h}h {$m}m" : "{$h}h";
    }
}
