<?php

namespace Database\Seeders\Pegawai;

use Database\Factories\Pegawai\EntitasPegawaiFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EntitasPegawai extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        (new EntitasPegawaiFactory())->count(10)->create();
    }
}
