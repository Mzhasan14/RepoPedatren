<?php

namespace Database\Seeders;

use Database\Factories\PelajarFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PelajarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        (new PelajarFactory())->count(50)->create();
    }
}
