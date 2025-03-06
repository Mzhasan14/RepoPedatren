<?php

namespace Database\Seeders\Pegawai;

use Database\Factories\Pegawai\BerkasFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BerkasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        (new BerkasFactory())->count(5)->create();
        
    }
}
