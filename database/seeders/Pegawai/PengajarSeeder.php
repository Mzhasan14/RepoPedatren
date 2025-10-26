<?php

namespace Database\Seeders\Pegawai;

use App\Models\Pegawai\Pegawai;
use App\Models\Pegawai\Pengajar;
use Illuminate\Database\Seeder;

class PengajarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pegawaiList = Pegawai::whereDoesntHave('pengajar')->inRandomOrder()->limit(50)->get();

        foreach ($pegawaiList as $pegawai) {
            Pengajar::factory()->create([
                'pegawai_id' => $pegawai->id,
                'status_aktif' => 'aktif',
            ]);
        }
    }

}
