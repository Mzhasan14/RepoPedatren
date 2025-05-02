<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Factories\BerkasPelanggaranFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class BerkasPelanggaranSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        (new BerkasPelanggaranFactory())->count(50)->create();
    }
}
