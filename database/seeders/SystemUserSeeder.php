<?php

namespace Database\Seeders;

use App\Models\Biodata;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
class SystemUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // 1. Buat Biodata dummy dulu
        $biodata = Biodata::firstOrCreate(
            ['email' => 'system@example.com'],
            [
                'id' => Str::uuid(),
                'negara_id' => 1,
                'provinsi_id' => 1,
                'kabupaten_id' => 1,
                'kecamatan_id' => 1,
                'jalan' => 'Seeder Street 123',
                'kode_pos' => '12345',
                'nama' => 'Seeder System Biodata',
                'jenis_kelamin' => 'l',
                'tanggal_lahir' => '1995-08-22',
                'tempat_lahir' => 'Seeder City',
                'nik' => '0000000000000000',
                'no_telepon' => '0800000000',
                'jenjang_pendidikan_terakhir' => 's1',
                'nama_pendidikan_terakhir' => 'Seeder University',
                'anak_keberapa' => 1,
                'dari_saudara' => 1,
                'tinggal_bersama' => 'Seeder',
                'status' => true,
                'wafat' => false,
                'created_by' => 1, // sementara isi 1
                'updated_by' => 1, // sementara isi 1
            ]
        );

        // 2. Buat User dengan biodata_id yg sudah ada
        $user = User::updateOrCreate(
            ['id' => 1],
            [
                'name' => 'Seeder System',
                'email' => 'system@example.com',
                'password' => Hash::make('password'),
                'biodata_id' => $biodata->id,
            ]
        );

        // 3. Update Biodata supaya created_by → id user yg baru dibuat
        $biodata->update([
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
