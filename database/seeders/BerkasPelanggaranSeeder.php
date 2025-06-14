<?php

namespace Database\Seeders;

use Database\Factories\BerkasPelanggaranFactory;
use Illuminate\Database\Seeder;

class BerkasPelanggaranSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        (new BerkasPelanggaranFactory)->count(50)->create();
    }
}
