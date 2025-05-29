<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class SemesterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $activeTahunAjaran = DB::table('tahun_ajaran')->where('status', true)->first();

        if ($activeTahunAjaran) {
            DB::table('semester')->insert([
                [
                    'tahun_ajaran_id' => $activeTahunAjaran->id,
                    'semester' => 'ganjil',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'tahun_ajaran_id' => $activeTahunAjaran->id,
                    'semester' => 'genap',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }
    }
}
