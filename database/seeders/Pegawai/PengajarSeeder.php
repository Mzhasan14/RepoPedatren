<?php

namespace Database\Seeders\Pegawai;

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
<<<<<<< HEAD
        (new PengajarFactory())->count(100)->create();
=======
        (new PengajarFactory())->count(25)->create();
>>>>>>> bb4b1d91a94e4f1acbc0ac142e17d66598353cc5
        
    }
}
