<?php

namespace Database\Seeders;

use Database\Factories\BerkasPerizinanFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BerkasPerizinanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        (new BerkasPerizinanFactory())->count(50)->create();
    }
}
