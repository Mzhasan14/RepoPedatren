<?php

namespace Database\Seeders\Kewilayahan;

use Database\Factories\Kewilayahan\KamarFactory;
use Illuminate\Database\Seeder;

class KamarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        (new KamarFactory)->count(5)->create();

    }
}
