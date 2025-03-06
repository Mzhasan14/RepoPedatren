<?php

namespace Database\Seeders;

use Database\Factories\LembagaFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LembagaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        (new LembagaFactory())->count(5)->create();
    }
}
