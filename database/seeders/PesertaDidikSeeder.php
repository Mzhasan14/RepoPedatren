<?php

namespace Database\Seeders;

use Database\Factories\Peserta_didikFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PesertaDidikSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        (new Peserta_didikFactory())->count(5)->create();
        
    }
}
