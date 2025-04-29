<?php

namespace Database\Seeders\Pegawai;

use Database\Factories\Pegawai\RiwayatJabatanKaryawanFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RiwayatJabatanKaryawanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        (new RiwayatJabatanKaryawanFactory())->count(300)->create();
        
    }
}
