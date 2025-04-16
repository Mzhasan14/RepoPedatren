<?php

namespace Database\Seeders;

use Database\Factories\KhadamFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class KhadamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        (new KhadamFactory())->count(100)->create();
        
    }
}
