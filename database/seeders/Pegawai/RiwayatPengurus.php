<?php

namespace Database\Seeders\Pegawai;

use Database\Factories\Pegawai\RiwayatPengurusFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RiwayatPengurus extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        (new RiwayatPengurusFactory())->count(10)->create();
        
    }
}
