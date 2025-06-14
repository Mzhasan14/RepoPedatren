<?php

namespace Database\Seeders\Pegawai;

use Database\Factories\Pegawai\GolonganFactory;
use Illuminate\Database\Seeder;

class GolonganSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        (new GolonganFactory)->count(300)->create();

    }
}
