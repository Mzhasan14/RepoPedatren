<?php

namespace Database\Seeders\Pegawai;

use Database\Factories\Pegawai\MateriAjarFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MateriAjarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        (new MateriAjarFactory())->count(10)->create();
    }
}
