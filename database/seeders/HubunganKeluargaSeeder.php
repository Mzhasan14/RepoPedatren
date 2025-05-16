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
            ['nama_status' => 'ayah kandung', 'created_by' => 1, 'updated_by' => null, 'deleted_by' => null, 'created_at' => now(), 'updated_at' => now()],
            ['nama_status' => 'ibu kandung', 'created_by' => 1, 'updated_by' => null, 'deleted_by' => null, 'created_at' => now(), 'updated_at' => now()],
            ['nama_status' => 'kakak kandung', 'created_by' => 1, 'updated_by' => null, 'deleted_by' => null, 'created_at' => now(), 'updated_at' => now()],
            ['nama_status' => 'adik kandung', 'created_by' => 1, 'updated_by' => null, 'deleted_by' => null, 'created_at' => now(), 'updated_at' => now()],
            ['nama_status' => 'kakek kandung', 'created_by' => 1, 'updated_by' => null, 'deleted_by' => null, 'created_at' => now(), 'updated_at' => now()],
            ['nama_status' => 'nenek kandung', 'created_by' => 1, 'updated_by' => null, 'deleted_by' => null, 'created_at' => now(), 'updated_at' => now()],
            ['nama_status' => 'paman dari ayah/ibu', 'created_by' => 1, 'updated_by' => null, 'deleted_by' => null, 'created_at' => now(), 'updated_at' => now()],
            ['nama_status' => 'bibi dari ayah/ibu', 'created_by' => 1, 'updated_by' => null, 'deleted_by' => null, 'created_at' => now(), 'updated_at' => now()],
            ['nama_status' => 'ayah sambung', 'created_by' => 1, 'updated_by' => null, 'deleted_by' => null, 'created_at' => now(), 'updated_at' => now()],
            ['nama_status' => 'ibu sambung', 'created_by' => 1, 'updated_by' => null, 'deleted_by' => null, 'created_at' => now(), 'updated_at' => now()],
            ['nama_status' => 'istri', 'created_by' => 1, 'updated_by' => null, 'deleted_by' => null, 'created_at' => now(), 'updated_at' => now()],
            ['nama_status' => 'suami', 'created_by' => 1, 'updated_by' => null, 'deleted_by' => null, 'created_at' => now(), 'updated_at' => now()],
            ['nama_status' => 'ayah mertua', 'created_by' => 1, 'updated_by' => null, 'deleted_by' => null, 'created_at' => now(), 'updated_at' => now()],
            ['nama_status' => 'ibu mertua', 'created_by' => 1, 'updated_by' => null, 'deleted_by' => null, 'created_at' => now(), 'updated_at' => now()],
            ['nama_status' => 'ayah', 'created_by' => 1, 'updated_by' => null, 'deleted_by' => null, 'created_at' => now(), 'updated_at' => now()],
            ['nama_status' => 'ibu', 'created_by' => 1, 'updated_by' => null, 'deleted_by' => null, 'created_at' => now(), 'updated_at' => now()],
            ['nama_status' => 'wali', 'created_by' => 1, 'updated_by' => null, 'deleted_by' => null, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
