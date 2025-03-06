<?php

namespace Database\Seeders\Kewaliasuhan;

use Database\Factories\Kewaliasuhan\Anak_AsuhFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class Anak_AsuhSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        (new Anak_AsuhFactory())->count(5)->create();
        
    }
}
