<?php

namespace Database\Seeders;

use App\Models\Biodata;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BiodataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Biodata::factory()->count(10)->create();
    }
}
