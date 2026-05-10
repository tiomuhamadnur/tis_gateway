<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Session;
use App\Models\FailureRecord;
use Illuminate\Support\Str;

class FailureRecordSeeder extends Seeder
{
    /**
     * Seed contoh failure sessions & records untuk development/demo.
     *
     * Data menggunakan equipment code dan fault code aktual dari
     * TIS Maintenance Manual Sumitomo (Chapter 16, SRR-RST-GEN-0104-16D).
     */
    public function run(): void
    {
        // Fault catalog: [equipment_code, equipment_name, fault_code, fault_abbrev, fault_description, classification, notch]
        $faults = [
            [9,  'PA',    806,  'DATASA',  'PA data selection abnormal',              'Light', 'EB'],
            [9,  'PA',    801,  'PAAT',    'PA abnormal transmission',                'Light', 'EB'],
            [2,  'ATO',   200,  'ATOAT',   'ATO abnormal transmission',               'Heavy', 'B4'],
            [2,  'ATO',   211,  'LBVRS1F', 'Tc1 logic block – VRS1 communication',   'Heavy', 'N'],
            [2,  'ATO',   212,  'LBVRS2F', 'Tc1 logic block – VRS2 communication',   'Heavy', 'P2'],
            [10, 'DOOR',  905,  'DLE',     'Door locking sensor switch error',        'Heavy', 'EB'],
            [10, 'DOOR',  907,  'CLE',     'Door close time out error',               'Heavy', 'EB'],
            [1,  'TIS',   121,  'ESA',     'CCU/MON UNIT Ethernet abnormality',       'Heavy', 'B2'],
            [6,  'BECU',  500,  'NBPS',    'No BECU control power supply',            'Heavy', 'EB'],
            [20, 'CCTV',  1203, 'WCE',     'CCTV WiFi connection error',              'Light', 'P1'],
            [19, 'Radio', 1103, 'CMA',     'Train Radio control module abnormality',  'Light', 'B6'],
            [3,  'VVVF1', 300,  'NVCPS',   'No VVVF1 control power supply',           'Heavy', 'EB'],
            [8,  'PID',   700,  'NPICPS',  'No PID control power supply',             'Light', 'EB'],
            [5,  'APS',   400,  'NAPCPS',  'No APS control power supply',             'Heavy', 'EB'],
            [7,  'ACE',   600,  'NACCPS',  'No ACE control power supply',             'Light', 'EB'],
        ];

        // 5 sesi contoh tersebar di 5 trainset berbeda
        $sessions = [
            ['rake_id' => '5',  'days_ago' => 3,  'count' => 10],
            ['rake_id' => '1',  'days_ago' => 7,  'count' => 8],
            ['rake_id' => '9',  'days_ago' => 14, 'count' => 15],
            ['rake_id' => '13', 'days_ago' => 20, 'count' => 6],
            ['rake_id' => '16', 'days_ago' => 28, 'count' => 12],
        ];

        foreach ($sessions as $s) {
            $readTime = now()->subDays($s['days_ago'])->setTime(8, 30, 0);

            $session = Session::create([
                'session_id'    => Str::uuid()->toString(),
                'rake_id'       => $s['rake_id'],
                'read_time'     => $readTime,
                'download_date' => $readTime->copy()->addMinutes(2),
                'total_records' => $s['count'],
                'status'        => 'completed',
            ]);

            $inserts = [];
            $now     = now()->toDateTimeString();

            for ($i = 0; $i < $s['count']; $i++) {
                [$eq, $eqName, $fc, $abbrev, $desc, $cls, $notch] = $faults[array_rand($faults)];
                $ts = $readTime->copy()->subMinutes($i * 3 + random_int(0, 2));

                $inserts[] = [
                    'session_id'        => $session->id,
                    'block_no'          => $i,
                    'timestamp'         => $ts,
                    'car_no'            => random_int(1, 6),
                    'occur_recover'     => (random_int(0, 3) === 0) ? 1 : 0,
                    'train_id'          => 'FFFF',
                    'location_m'        => 0,
                    'equipment_code'    => $eq,
                    'equipment_name'    => $eqName,
                    'fault_code'        => $fc,
                    'fault_abbrev'      => $abbrev,
                    'fault_description' => $desc,
                    'classification'    => $cls,
                    'guidance'          => 'Please report the occurrence of the failure to the OCC.',
                    'notch'             => $notch,
                    'speed_kmh'         => 0,
                    'overhead_v'        => random_int(0, 1) ? random_int(1, 10) * 10 : 0,
                    'created_at'        => $now,
                    'updated_at'        => $now,
                ];
            }

            FailureRecord::insert($inserts);
        }

        $this->command->info('Seeded 5 sesi failure records contoh untuk MRT Jakarta.');
    }
}
