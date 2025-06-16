<?php

namespace Database\Seeders\Pegawai;

use App\Models\Pegawai\Pegawai;
use App\Models\Pegawai\WaliKelas;
use Illuminate\Database\Seeder;

class WaliKelasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pegawaiList = Pegawai::whereDoesntHave('wali_kelas')->inRandomOrder()->limit(50)->get();

        foreach ($pegawaiList as $pegawai) {
            WaliKelas::factory()->create([
                'pegawai_id' => $pegawai->id,
                'status_aktif' => 'aktif',
            ]);
        }
    }
}
