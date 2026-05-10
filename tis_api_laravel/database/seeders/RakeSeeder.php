<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Rake;

class RakeSeeder extends Seeder
{
    /**
     * Seed 16 trainset aktual MRT Jakarta CP108.
     *
     * rake_id = nomor integer (sebagai string) yang dikirim TIS Gateway
     * saat auto-detect handshake. TS-01 → rake_id "1", dst.
     *
     * Formasi 6 car: Tc1 – M1 – M2 – M1' – M2' – Tc2
     * Jalur: Koridor 1 Lebak Bulus Grab – Bundaran HI
     * Manufaktur: Sumitomo Corporation (kontrak CP108)
     */
    public function run(): void
    {
        // Bersihkan data lama agar tidak ada duplikat format lama (RAKE-001, dsb.)
        DB::table('rakes')->truncate();

        $rakes = [
            [
                'rake_id'     => '1',
                'name'        => 'Trainset 01 (TS-01)',
                'description' => 'Unit perdana CP108. 6-car EMU Sumitomo, formasi Tc1–M1–M2–M1\'–M2\'–Tc2. Beroperasi sejak 2019.',
                'active'      => true,
            ],
            [
                'rake_id'     => '2',
                'name'        => 'Trainset 02 (TS-02)',
                'description' => '6-car EMU Sumitomo CP108. Koridor 1 MRT Jakarta, kapasitas ±1.950 penumpang.',
                'active'      => true,
            ],
            [
                'rake_id'     => '3',
                'name'        => 'Trainset 03 (TS-03)',
                'description' => '6-car EMU Sumitomo CP108. Headway 5 menit pada jam sibuk.',
                'active'      => true,
            ],
            [
                'rake_id'     => '4',
                'name'        => 'Trainset 04 (TS-04)',
                'description' => '6-car EMU Sumitomo CP108. Dilengkapi CCTV 48 kamera dan sistem PA digital.',
                'active'      => true,
            ],
            [
                'rake_id'     => '5',
                'name'        => 'Trainset 05 (TS-05)',
                'description' => '6-car EMU Sumitomo CP108. Referensi PCAP sniffing TIS — rake_id=5 di-confirm dari pcap TS5.',
                'active'      => true,
            ],
            [
                'rake_id'     => '6',
                'name'        => 'Trainset 06 (TS-06)',
                'description' => '6-car EMU Sumitomo CP108. Dilengkapi sistem ATO (Automatic Train Operation) VOBC.',
                'active'      => true,
            ],
            [
                'rake_id'     => '7',
                'name'        => 'Trainset 07 (TS-07)',
                'description' => '6-car EMU Sumitomo CP108. Sistem pengereman regeneratif BECU.',
                'active'      => true,
            ],
            [
                'rake_id'     => '8',
                'name'        => 'Trainset 08 (TS-08)',
                'description' => '6-car EMU Sumitomo CP108. Termasuk batch pengiriman gelombang pertama.',
                'active'      => true,
            ],
            [
                'rake_id'     => '9',
                'name'        => 'Trainset 09 (TS-09)',
                'description' => '6-car EMU Sumitomo CP108. Kapasitas VVVF inverter 2×1.500 kW per bogies.',
                'active'      => true,
            ],
            [
                'rake_id'     => '10',
                'name'        => 'Trainset 10 (TS-10)',
                'description' => '6-car EMU Sumitomo CP108. Sistem APS (Auxiliary Power Supply) 110 VDC.',
                'active'      => true,
            ],
            [
                'rake_id'     => '11',
                'name'        => 'Trainset 11 (TS-11)',
                'description' => '6-car EMU Sumitomo CP108. Termasuk batch pengiriman gelombang kedua.',
                'active'      => true,
            ],
            [
                'rake_id'     => '12',
                'name'        => 'Trainset 12 (TS-12)',
                'description' => '6-car EMU Sumitomo CP108. Sistem pintu 6 set per gerbong, lebar 1.400 mm.',
                'active'      => true,
            ],
            [
                'rake_id'     => '13',
                'name'        => 'Trainset 13 (TS-13)',
                'description' => '6-car EMU Sumitomo CP108. Kecepatan maks 100 km/h, kecepatan desain 90 km/h.',
                'active'      => true,
            ],
            [
                'rake_id'     => '14',
                'name'        => 'Trainset 14 (TS-14)',
                'description' => '6-car EMU Sumitomo CP108. Sistem radio komunikasi VHF/UHF terintegrasi.',
                'active'      => true,
            ],
            [
                'rake_id'     => '15',
                'name'        => 'Trainset 15 (TS-15)',
                'description' => '6-car EMU Sumitomo CP108. Pengisian baterai regeneratif di setiap stasiun.',
                'active'      => true,
            ],
            [
                'rake_id'     => '16',
                'name'        => 'Trainset 16 (TS-16)',
                'description' => '6-car EMU Sumitomo CP108. Unit ke-16, pengiriman terakhir batch CP108 MRT Jakarta.',
                'active'      => true,
            ],
        ];

        foreach ($rakes as $rake) {
            Rake::create($rake);
        }

        $this->command->info('Seeded 16 trainset aktual MRT Jakarta CP108 (TS-01 s/d TS-16).');
    }
}
