<?php

namespace Database\Seeders\Pegawai;

use Database\Factories\Pegawai\JenisBerkasFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class JenisBerkasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        (new JenisBerkasFactory())->count(5)->create();
        
    }
}
