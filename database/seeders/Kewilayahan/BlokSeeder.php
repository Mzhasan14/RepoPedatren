<?php

namespace Database\Seeders\Kewilayahan;

use Database\Factories\Kewilayahan\BlokFactory;
use Illuminate\Database\Seeder;

class BlokSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        (new BlokFactory)->count(5)->create();

    }
}
