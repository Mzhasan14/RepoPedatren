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

        (new PegawaiFactory())->count(250)->create();
        
    }
}
