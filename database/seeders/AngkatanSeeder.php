<?php

namespace Database\Seeders;

use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AngkatanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $faker = Faker::create();

        // Ambil data tahun ajaran secara terurut
        $tahunList = DB::table('tahun_ajaran')
            ->orderBy('tahun_ajaran')
            ->get()
            ->values();

        // Pastikan ada minimal 10 tahun ajaran (5 santri + 5 pelajar)
        if ($tahunList->count() < 10) {
            echo "Seeder error: jumlah tahun ajaran minimal harus 10 untuk membuat 5 angkatan santri dan 5 angkatan pelajar.\n";

            return;
        }

        $angkatanData = [];

        // 5 angkatan santri
        for ($i = 0; $i < 5; $i++) {
            $angkatanData[] = [
                'angkatan' => 'Angkatan '.substr($tahunList[$i]->tahun_ajaran, 0, 4),
                'kategori' => 'santri',
                'tahun_ajaran_id' => $tahunList[$i]->id,
                'status' => true,
                'created_at' => now(),
                'created_by' => 1,
                'updated_at' => now(),
            ];
        }

        // 5 angkatan pelajar
        for ($i = 5; $i < 10; $i++) {
            $angkatanData[] = [
                'angkatan' => 'Angkatan '.substr($tahunList[$i]->tahun_ajaran, 0, 4),
                'kategori' => 'pelajar',
                'tahun_ajaran_id' => $tahunList[$i]->id,
                'status' => true,
                'created_at' => now(),
                'created_by' => 1,
                'updated_at' => now(),
            ];
        }

        // Insert ke tabel angkatan
        DB::table('angkatan')->insert($angkatanData);

        echo "Seeder sukses: 5 angkatan santri dan 5 angkatan pelajar berhasil dibuat.\n";
    }
}
