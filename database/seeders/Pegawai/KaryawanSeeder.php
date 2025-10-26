<?php

namespace Database\Seeders\Pegawai;

use App\Models\Pegawai\Karyawan;
use App\Models\Pegawai\Pegawai;
use Illuminate\Database\Seeder;

class KaryawanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pegawaiList = Pegawai::whereDoesntHave('karyawan')->inRandomOrder()->limit(50)->get();

        foreach ($pegawaiList as $pegawai) {
            Karyawan::factory()->create([
                'pegawai_id' => $pegawai->id,
                'status_aktif' => 'aktif',
            ]);
        }
    }
}
