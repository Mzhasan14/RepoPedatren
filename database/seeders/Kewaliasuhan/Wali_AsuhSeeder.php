<?php

namespace Database\Seeders\Kewaliasuhan;

use Database\Factories\Kewaliasuhan\Wali_asuhFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class Wali_AsuhSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        (new Wali_asuhFactory())->count(5)->create();
        
    }
}
