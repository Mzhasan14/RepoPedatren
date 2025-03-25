<?php

namespace Database\Seeders;

use App\Models\Biodata;
use App\Models\Peserta_didik;
use Illuminate\Database\Seeder;
use Database\Factories\Alamat\NegaraFactory;
use Database\Factories\Alamat\ProvinsiFactory;
use Database\Factories\Alamat\KabupatenFactory;
use Database\Factories\Alamat\KecamatanFactory;
use Database\Factories\BiodataFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Faker\Factory as Faker;

class BiodataSeeder extends Seeder
{
    
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        (new BiodataFactory())->count(100)->create();
    }
}
