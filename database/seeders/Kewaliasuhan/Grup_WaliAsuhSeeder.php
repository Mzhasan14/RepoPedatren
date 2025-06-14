<?php

namespace Database\Seeders\Kewaliasuhan;

use Database\Factories\Kewaliasuhan\Grup_WaliAsuhhFactory;
use Illuminate\Database\Seeder;

class Grup_WaliAsuhSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        (new Grup_WaliAsuhhFactory)->count(5)->create();

    }
}
