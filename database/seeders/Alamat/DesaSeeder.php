<?php

namespace Database\Seeders\Alamat;

use Database\Factories\Alamat\DesaFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DesaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        (new DesaFactory())->count(5)->create();
        
    }
}
