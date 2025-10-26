<?php

namespace Database\Seeders\Alamat;

use Database\Factories\Alamat\NegaraFactory;
use Illuminate\Database\Seeder;

class NegaraSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        (new NegaraFactory)->count(5)->create();
    }
}
