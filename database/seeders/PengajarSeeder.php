<?php

namespace Database\Seeders;

use Database\Factories\PengajarFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PengajarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        (new PengajarFactory())->count(10)->create();
    }
}
