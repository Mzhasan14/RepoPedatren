<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PesantrenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('pesantren')->insert([
            [
                'kode_pesantren'  => '01',
                'nama_pesantren'  => 'Pondok Pesantren Kanzus Sholawat',
                // 'negara_id'       => 1, // Indonesia
                // 'provinsi_id'     => 35, // Jawa Timur
                // 'kabupaten_id'    => 3513, // Probolinggo
                // 'kecamatan_id'    => 3513050, // Paiton
                // 'jalan'           => 'Karanganyar, Paiton',
                // 'kode_pos'        => '67291',
                // 'no_telp'         => '0335-771218',
                // 'email'           => 'info@nuruljadid.net',
                // 'website'         => 'https://www.nuruljadid.net',
                'is_active'       => true,
                'created_at'      => Carbon::now(),
                'updated_at'      => Carbon::now(),
            ],
            [
                'kode_pesantren'  => '02',
                'nama_pesantren'  => "Pondok Pesantren Ar-Rofi'iyyah",
                // 'negara_id'       => 1,
                // 'provinsi_id'     => 33, // Jawa Tengah
                // 'kabupaten_id'    => 3325, // Pekalongan
                // 'kecamatan_id'    => 3325050, // Kedungwuni
                // 'jalan'           => 'Jl. KH. Ahmad Dahlan No. 10',
                // 'kode_pos'        => '51173',
                // 'no_telp'         => '0285-441234',
                // 'email'           => 'admin@nurulquran.id',
                // 'website'         => 'https://www.nurulquran.id',
                'is_active'       => true,
                'created_at'      => Carbon::now(),
                'updated_at'      => Carbon::now(),
            ],
            [
                'kode_pesantren'  => '03',
                'nama_pesantren'  => "Pondok Pesantren Nurul Qur'an",
                // 'negara_id'       => 1,
                // 'provinsi_id'     => 36, // Banten
                // 'kabupaten_id'    => 3603, // Tangerang
                // 'kecamatan_id'    => 3603010, // Tigaraksa
                // 'jalan'           => 'Jl. Pesantren Al-Dzikro No. 5',
                // 'kode_pos'        => '15720',
                // 'no_telp'         => '021-5522345',
                // 'email'           => 'info@aldzikro.sch.id',
                // 'website'         => 'https://www.aldzikro.sch.id',
                'is_active'       => true,
                'created_at'      => Carbon::now(),
                'updated_at'      => Carbon::now(),
            ],
        ]);
    }
}
