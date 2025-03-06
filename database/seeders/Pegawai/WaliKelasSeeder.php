<?php

namespace Database\Seeders\Pegawai;

use Database\Factories\Pegawai\WaliKelasFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WaliKelasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        (new WaliKelasFactory())->count(10)->create();
        
    }
}
