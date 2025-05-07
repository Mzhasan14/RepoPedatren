<?php

namespace Database\Seeders\Kewilayahan;

use Illuminate\Database\Seeder;
use App\Models\Kewilayahan\Wilayah;
use Database\Factories\Kewilayahan\WilayahFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class WilayahSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        (new WilayahFactory())->count(5)->create();
    }
}
