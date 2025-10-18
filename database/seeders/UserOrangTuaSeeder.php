<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserOrangTuaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pastikan role orang_tua ada
        Role::firstOrCreate([
            'name' => 'orang_tua',
            'guard_name' => 'web',
        ]);

        try {
            DB::beginTransaction();

            // Ambil salah satu biodata orang tua dari tabel orang_tua_wali
            $orangtuaWali = DB::table('orang_tua_wali')->first();

            if ($orangtuaWali) {
                $ortuUser = User::updateOrCreate(
                    ['email' => 'orangtua@example.com'],
                    [
                        'name' => 'Orangtua Santri',
                        'password' => Hash::make('password'),
                        'biodata_id' => $orangtuaWali->id_biodata,
                    ]
                );
                $ortuUser->assignRole('orang_tua');
            }

            DB::commit();
            echo "✅ OrangTuaUserSeeder berhasil dijalankan.\n";
        } catch (\Throwable $e) {
            DB::rollBack();
            echo "❌ Seeder gagal: " . $e->getMessage() . "\n";
        }
    }
}
