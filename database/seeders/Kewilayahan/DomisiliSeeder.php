<?php

namespace Database\Seeders\Kewilayahan;

use Database\Factories\Kewilayahan\DomisiliFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DomisiliSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        (new DomisiliFactory())->count(5)->create();
        
    }
}
