<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Factories\PesertaDidikFactory;
use Database\Factories\Peserta_didikFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PesertaDidikSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        (new PesertaDidikFactory())->count(200)->create();
        
    }
}
