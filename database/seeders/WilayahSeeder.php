<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WilayahSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $now = Carbon::now();
        $admin = 1; // ID admin default

        // Data wilayah lengkap dengan kategori
        $wilayahs = [
            ['nama' => 'Wilayah K (Maliki)', 'kategori' => 'putra', 'bloks' => [
                'Asrama Al-Farabi',
                'Asrama Ibnu Sina',
                'Asrama Ar-Razi'
            ]],
            ['nama' => 'Wilayah K (Zaid Bin Tsabit)', 'kategori' => 'putri', 'bloks' => [
                'Asrama Aisyah',
                'Asrama Hafsah',
                'Asrama Zainab'
            ]],
            ['nama' => 'Wilayah J (Al-Amin)', 'kategori' => 'putra', 'bloks' => [
                'Asrama Al-Kindi',
                'Asrama Al-Jurjani'
            ]],
            ['nama' => 'Wilayah J (Al‑Lathifiyyah)', 'kategori' => 'putri', 'bloks' => [
                'Asrama Fatimah',
                'Asrama Maryam'
            ]],
            ['nama' => 'Dalbar (Az‑Zainiyah)', 'kategori' => 'putra', 'bloks' => [
                'Asrama Imam Syafi’i',
                'Asrama Imam Malik'
            ]],
            ['nama' => 'Daltim (Al‑Hasyimiyah)', 'kategori' => 'putra', 'bloks' => [
                'Asrama Al-Ghazali',
                'Asrama Asy-Syathibi'
            ]],
            ['nama' => 'Dalsel (Fatimatuzzahro)', 'kategori' => 'putri', 'bloks' => [
                'Asrama Ummu Salamah',
                'Asrama Rabi’ah Adawiyah'
            ]],
            ['nama' => 'Wilayah Al‑Mawaddah', 'kategori' => 'putri', 'bloks' => [
                'Asrama Al-Mawaddah 1',
                'Asrama Al-Mawaddah 2'
            ]],
            ['nama' => 'Ma’had Aly', 'kategori' => 'putra', 'bloks' => [
                'Asrama Al-Hikam',
                'Asrama Al-Munawwir'
            ]],
        ];

        $kamarNamesPutra = [
            'Kamar Umar',
            'Kamar Ali',
            'Kamar Utsman',
            'Kamar Bilal',
            'Kamar Salman',
            'Kamar Abu Bakar'
        ];

        $kamarNamesPutri = [
            'Kamar Aisyah',
            'Kamar Hafsah',
            'Kamar Zainab',
            'Kamar Fatimah',
            'Kamar Maryam',
            'Kamar Khadijah'
        ];

        $kapasitasDefault = 12; // Bisa disesuaikan

        foreach ($wilayahs as $wilayah) {
            $wilayahId = DB::table('wilayah')->insertGetId([
                'nama_wilayah' => $wilayah['nama'],
                'kategori' => $wilayah['kategori'],
                'created_by' => $admin,
                'status' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            foreach ($wilayah['bloks'] as $blokName) {
                $blokId = DB::table('blok')->insertGetId([
                    'wilayah_id' => $wilayahId,
                    'nama_blok' => $blokName,
                    'created_by' => $admin,
                    'status' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                $kamarNames = $wilayah['kategori'] === 'putra' ? $kamarNamesPutra : $kamarNamesPutri;

                foreach ($kamarNames as $kamarName) {
                    DB::table('kamar')->insert([
                        'blok_id' => $blokId,
                        'nama_kamar' => $kamarName,
                        'kapasitas' => $kapasitasDefault,
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
