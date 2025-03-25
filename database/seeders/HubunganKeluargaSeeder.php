<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\HubunganKeluarga;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class HubunganKeluargaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('hubungan_keluarga')->insert([
            ['nama_status' => 'ayah', 'created_by' => 1, 'updated_by' => null, 'deleted_by' => null, 'created_at' => now(), 'updated_at' => now()],
            ['nama_status' => 'ibu', 'created_by' => 1, 'updated_by' => null, 'deleted_by' => null, 'created_at' => now(), 'updated_at' => now()],
            ['nama_status' => 'wali', 'created_by' => 1, 'updated_by' => null, 'deleted_by' => null, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
