<?php

namespace Database\Seeders\Pegawai;

use Database\Factories\Pegawai\AnakPegawaiFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AnakPegawaiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        (new AnakPegawaiFactory())->count(10)->create();
        
    }
}
