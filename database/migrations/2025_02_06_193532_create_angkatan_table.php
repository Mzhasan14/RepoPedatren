<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Faker\Factory as Faker;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function run()
    {
        $faker = Faker::create();

        // Ambil data tahun ajaran sebagai referensi
        $tahunAjaranMap = DB::table('tahun_ajaran')
            ->get()
            ->keyBy(function ($item) {
                return substr($item->tahun_ajaran, 0, 4); // ambil tahun awal misal 2023 dari '2023/2024'
            });

        $angkatanData = [];

        foreach ($tahunAjaranMap as $year => $tahunAjaran) {
            $angkatanData[] = [
                'angkatan' => 'Angkatan ' . $year,
                'kategori' => $faker->randomElement(['santri', 'pelajar']),
                'tahun_ajaran_id' => $tahunAjaran->id,
                'status' => $faker->boolean(90),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('angkatan')->insert($angkatanData);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('angkatan');
    }
};
