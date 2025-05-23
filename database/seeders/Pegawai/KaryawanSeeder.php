<?php

namespace Database\Seeders\Pegawai;

use App\Models\Pegawai\Karyawan;
use App\Models\Pegawai\Pegawai;
use Database\Factories\Pegawai\KaryawanFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class KaryawanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil pegawai unik yang belum memiliki entri karyawan
        $pegawaiList = Pegawai::whereDoesntHave('karyawan')->inRandomOrder()->limit(50)->get();

        foreach ($pegawaiList as $pegawai) {
            $jumlahKaryawan = fake()->numberBetween(1, 3);
            $aktifIndex = fake()->numberBetween(0, $jumlahKaryawan - 1);

            for ($i = 0; $i < $jumlahKaryawan; $i++) {
                Karyawan::factory()->create([
                    'pegawai_id' => $pegawai->id,
                    'status_aktif' => $i === $aktifIndex ? 'aktif' : 'tidak aktif',
                ]);
            }
        }

    }
}
