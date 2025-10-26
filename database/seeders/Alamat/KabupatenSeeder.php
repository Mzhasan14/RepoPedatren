<?php

namespace Database\Seeders\Alamat;

use Database\Factories\Alamat\KabupatenFactory;
use Illuminate\Database\Seeder;

class KabupatenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        (new KabupatenFactory)->count(5)->create();

    }
}
