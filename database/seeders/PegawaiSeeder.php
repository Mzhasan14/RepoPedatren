<?php

namespace Database\Seeders;

use Database\Factories\PegawaiFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PegawaiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        (new PegawaiFactory())->count(10)->create();
    }
}
