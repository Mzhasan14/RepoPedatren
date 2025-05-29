<?php

namespace Database\Seeders;

use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AngkatanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        $tahunAjaranIds = DB::table('tahun_ajaran')->pluck('id')->toArray();

        $angkatanSet = [];
        for ($i = 0; $i < 20; $i++) {
            $angkatan = 'ANGK-' . strtoupper($faker->unique()->bothify('??##'));
            $angkatanSet[] = [
                'angkatan' => $angkatan,
                'kategori' => $faker->randomElement(['santri', 'pelajar']),
                'tahun_ajaran_id' => $faker->randomElement($tahunAjaranIds),
                'status' => $faker->boolean(90), // 90% aktif
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('angkatan')->insert($angkatanSet);
    }
}
