<?php

namespace Database\Seeders;

use Database\Factories\PerizinanFactory;
use Illuminate\Database\Seeder;

class PerizinanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        (new PerizinanFactory)->count(50)->create();

    }
}
