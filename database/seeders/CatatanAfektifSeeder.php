<?php

namespace Database\Seeders;

use Database\Factories\CatatanAfektifFactory;
use Illuminate\Database\Seeder;

class CatatanAfektifSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        (new CatatanAfektifFactory)->count(5)->create();

    }
}
