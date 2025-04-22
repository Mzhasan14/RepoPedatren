<?php

namespace Database\Seeders;

use Database\Factories\PelanggaranFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PelanggaranSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        (new PelanggaranFactory())->count(50)->create();
        
    }
}
