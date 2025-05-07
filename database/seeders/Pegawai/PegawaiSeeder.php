<?php

namespace Database\Seeders\Pegawai;

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
<<<<<<< HEAD
        (new PegawaiFactory())->count(100)->create();
=======
        (new PegawaiFactory())->count(25)->create();
>>>>>>> bb4b1d91a94e4f1acbc0ac142e17d66598353cc5
        
    }
}
