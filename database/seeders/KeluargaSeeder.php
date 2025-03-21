<?php

namespace Database\Seeders;

use Database\Factories\KeluargaFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class KeluargaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        (new KeluargaFactory())->count(100)->create();
        
    }
}
