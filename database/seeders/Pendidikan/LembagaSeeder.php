<?php

namespace Database\Seeders\Pendidikan;

use Database\Factories\Pendidikan\LembagaFactory;
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
