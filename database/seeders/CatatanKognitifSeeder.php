<?php

namespace Database\Seeders;

use Database\Factories\CatatanAfektifFactory;
use Database\Factories\CatatanKognitifFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CatatanKognitifSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        (new CatatanKognitifFactory())->count(5)->create();
        
    }
}
