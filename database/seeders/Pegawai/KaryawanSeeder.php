<?php

namespace Database\Seeders\Pegawai;

use Database\Factories\Pegawai\KaryawanFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class KaryawanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        (new KaryawanFactory())->count(300)->create();
        
    }
}
