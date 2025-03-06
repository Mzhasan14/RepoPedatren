<?php

namespace Database\Seeders\Pegawai;

use Database\Factories\Pegawai\KategoriGolonganFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class KategoriGolonganSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        (new KategoriGolonganFactory())->count(10)->create();
        
    }
}
