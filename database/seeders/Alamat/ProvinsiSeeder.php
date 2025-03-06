<?php

namespace Database\Seeders\Alamat;

use Database\Factories\Alamat\ProvinsiFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProvinsiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        (new ProvinsiFactory())->count(5)->create();
        
    }
}
