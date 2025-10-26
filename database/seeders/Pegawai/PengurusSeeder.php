<?php

namespace Database\Seeders\Pegawai;

use App\Models\Pegawai\Pegawai;
use App\Models\Pegawai\Pengurus;
use Illuminate\Database\Seeder;

class PengurusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pegawaiList = Pegawai::whereDoesntHave('pengurus')->inRandomOrder()->limit(50)->get();

        foreach ($pegawaiList as $pegawai) {
            Pengurus::factory()->create([
                'pegawai_id' => $pegawai->id,
                'status_aktif' => 'aktif',
            ]);
        }
    }

}
