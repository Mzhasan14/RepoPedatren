<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class TahunAjaranSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $startYear = 2020;
        $data = [];

        for ($i = 0; $i < 5; $i++) {
            $tahunAjaran = ($startYear + $i) . '/' . ($startYear + $i + 1);
            $data[] = [
                'tahun_ajaran' => $tahunAjaran,
                'tanggal_mulai' => Carbon::create($startYear + $i, 7, 1),
                'tanggal_selesai' => Carbon::create($startYear + $i + 1, 6, 30),
                'status' => $i === 4, // aktifkan hanya yang terakhir
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('tahun_ajaran')->insert($data);
    }
}
