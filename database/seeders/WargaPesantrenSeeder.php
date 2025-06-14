<?php

namespace Database\Seeders;

use Database\Factories\WargaPesantrenFactory;
use Illuminate\Database\Seeder;

class WargaPesantrenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        (new WargaPesantrenFactory)->count(200)->create();
    }
}
