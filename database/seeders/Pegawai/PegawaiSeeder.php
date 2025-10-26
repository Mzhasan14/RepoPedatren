<?php

namespace Database\Seeders\Pegawai;

use App\Models\Biodata;
use App\Models\Pegawai\Pegawai;
use Illuminate\Database\Seeder;

class PegawaiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil 250 biodata yang belum punya pegawai
        $biodataList = Biodata::doesntHave('pegawai')->inRandomOrder()->limit(250)->get();

        foreach ($biodataList as $biodata) {
            Pegawai::factory()->create([
                'biodata_id' => $biodata->id,
                'status_aktif' => 'aktif',
            ]);
        }
    }
}
