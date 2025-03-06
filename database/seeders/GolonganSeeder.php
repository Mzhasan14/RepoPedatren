<?php

namespace Database\Seeders;

use Database\Factories\GolonganFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GolonganSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        (new GolonganFactory())->count(5)->create();
    }
}
