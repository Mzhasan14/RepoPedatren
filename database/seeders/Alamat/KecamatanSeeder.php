<?php

namespace Database\Seeders\Alamat;

use Database\Factories\Alamat\KecamatanFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class KecamatanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        (new KecamatanFactory())->count(5)->create();
        
    }
}
