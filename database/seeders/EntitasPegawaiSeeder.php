<?php

namespace Database\Seeders;

use Database\Factories\EntitasPegawaiFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EntitasPegawaiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        (new EntitasPegawaiFactory())->count(10)->create();
    }
}
