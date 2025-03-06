<?php

namespace Database\Seeders;

use Database\Factories\OrangTuaFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrangTuaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        (new OrangTuaFactory())->count(5)->create();
        
    }
}
