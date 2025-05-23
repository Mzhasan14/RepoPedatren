<?php

namespace Database\Seeders\Pegawai;

use App\Models\Biodata;
use App\Models\Pegawai\Pegawai;
use Database\Factories\Pegawai\PegawaiFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PegawaiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil 250 biodata unik yang belum punya pegawai aktif
        $biodataList = Biodata::inRandomOrder()->limit(250)->get();

        foreach ($biodataList as $biodata) {
            // Tentukan jumlah pegawai per biodata
            $jumlahPegawai = rand(1, 3); // bisa lebih dari 1
            $indexAktif = rand(0, $jumlahPegawai - 1); // pilih 1 yang aktif

            for ($i = 0; $i < $jumlahPegawai; $i++) {
                Pegawai::factory()->create([
                    'biodata_id' => $biodata->id,
                    'status_aktif' => $i === $indexAktif ? 'aktif' : 'tidak aktif',
                ]);
            }
        }
    }
}
