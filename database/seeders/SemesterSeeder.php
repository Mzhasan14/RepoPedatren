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
        $tahunAjaranIds = DB::table('tahun_ajaran')->pluck('id');

        $data = [];
        foreach ($tahunAjaranIds as $id) {
            $data[] = [
                'tahun_ajaran_id' => $id,
                'semester' => 'ganjil',
                'created_at' => now(),
                'updated_at' => now(),
            ];
            $data[] = [
                'tahun_ajaran_id' => $id,
                'semester' => 'genap',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('semester')->insert($data);
    }
}
