<?php

namespace Database\Seeders\Kewilayahan;

use Database\Factories\Kewilayahan\WilayahFactory;
use Illuminate\Database\Seeder;

class WilayahSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        (new WilayahFactory)->count(5)->create();
    }
}
