<?php

namespace Database\Seeders\Pegawai;

use Database\Factories\Pegawai\GolonganJabatanFactory;
use Illuminate\Database\Seeder;

class GolonganJabatanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        (new GolonganJabatanFactory)->count(300)->create();

    }
}
