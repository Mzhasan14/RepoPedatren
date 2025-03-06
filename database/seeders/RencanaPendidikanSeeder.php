<?php

namespace Database\Seeders;

use Database\Factories\Rencana_PendidikanFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RencanaPendidikanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        (new Rencana_PendidikanFactory())->count(5)->create();
        
    }
}
