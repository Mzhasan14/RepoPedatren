<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class WilayahSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $now = Carbon::now();
        $admin = 1; // ID admin default

        $wilayahs = [
            'Wilayah K (Maliki)' => [
                'Asrama Al-Farabi',
                'Asrama Ibnu Sina',
                'Asrama Ar-Razi'
            ],
            'Wilayah K (Zaid Bin Tsabit)' => [
                'Asrama Aisyah',
                'Asrama Hafsah',
                'Asrama Zainab'
            ],
            'Wilayah J (Al-Amin)' => [
                'Asrama Al-Kindi',
                'Asrama Al-Jurjani'
            ],
            'Wilayah J (Al‑Lathifiyyah)' => [
                'Asrama Fatimah',
                'Asrama Maryam'
            ],
            'Dalbar (Az‑Zainiyah)' => [
                'Asrama Imam Syafi’i',
                'Asrama Imam Malik'
            ],
            'Daltim (Al‑Hasyimiyah)' => [
                'Asrama Al-Ghazali',
                'Asrama Asy-Syathibi'
            ],
            'Dalsel (Fatimatuzzahro)' => [
                'Asrama Ummu Salamah',
                'Asrama Rabi’ah Adawiyah'
            ],
            'Wilayah Al‑Mawaddah' => [
                'Asrama Al-Mawaddah 1',
                'Asrama Al-Mawaddah 2'
            ],
            'Ma’had Aly' => [
                'Asrama Al-Hikam',
                'Asrama Al-Munawwir'
            ],
        ];

        $kamarNames = [
            'Kamar Umar',
            'Kamar Ali',
            'Kamar Utsman',
            'Kamar Bilal',
            'Kamar Salman',
            'Kamar Abu Bakar',
        ];

        foreach ($wilayahs as $wilayahName => $blokList) {
            $wilayahId = DB::table('wilayah')->insertGetId([
                'nama_wilayah' => $wilayahName,
                'created_by' => $admin,
                'status' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            foreach ($blokList as $blokName) {
                $blokId = DB::table('blok')->insertGetId([
                    'wilayah_id' => $wilayahId,
                    'nama_blok' => $blokName,
                    'created_by' => $admin,
                    'status' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                foreach ($kamarNames as $kamarName) {
                    DB::table('kamar')->insert([
                        'blok_id' => $blokId,
                        'nama_kamar' => $kamarName,
                        'created_by' => $admin,
                        'status' => true,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
        }
    }
}
