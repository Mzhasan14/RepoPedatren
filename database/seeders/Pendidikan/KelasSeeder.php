<?php

namespace Database\Seeders\Pendidikan;

use Database\Factories\Pendidikan\KelasFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class KelasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        (new KelasFactory())->count(5)->create();
        
    }
}
