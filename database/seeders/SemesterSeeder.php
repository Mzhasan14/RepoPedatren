<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

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
                    'status' => true, 
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'tahun_ajaran_id' => $activeTahunAjaran->id,
                    'semester' => 'genap',
                    'status' => false, 
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }
    }
}
