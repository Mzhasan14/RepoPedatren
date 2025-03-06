<?php

namespace Database\Seeders\Pegawai;

use Database\Factories\Pegawai\PengurusFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PengurusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        (new PengurusFactory())->count(10)->create();
        
    }
}
