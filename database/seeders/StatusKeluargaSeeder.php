<?php

namespace Database\Seeders;

use Database\Factories\Status_keluargaFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StatusKeluargaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        (new Status_keluargaFactory())->count(4)->create();
        
    }
}
