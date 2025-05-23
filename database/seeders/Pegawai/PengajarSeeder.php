<?php

namespace Database\Seeders\Pegawai;

use App\Models\Pegawai\Pegawai;
use App\Models\Pegawai\Pengajar;
use Database\Factories\Pegawai\PengajarFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PengajarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil pegawai unik yang belum memiliki entri karyawan
        $pegawaiList = Pegawai::whereDoesntHave('pengajar')->inRandomOrder()->limit(50)->get();

        foreach ($pegawaiList as $pegawai) {
            $jumlahKaryawan = fake()->numberBetween(1, 3);
            $aktifIndex = fake()->numberBetween(0, $jumlahKaryawan - 1);

            for ($i = 0; $i < $jumlahKaryawan; $i++) {
                Pengajar::factory()->create([
                    'pegawai_id' => $pegawai->id,
                    'status_aktif' => $i === $aktifIndex ? 'aktif' : 'tidak aktif',
                ]);
            }
        }

    }
}
