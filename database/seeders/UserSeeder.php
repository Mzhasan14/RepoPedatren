<?php

namespace Database\Seeders;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Biodata;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        /**
         * Pastikan semua role tersedia (role terbaru)
         */
        $roles = [
            'superadmin',
            'admin',
            'ustadz',
            'petugas',
            'pengasuh',
            'wali_asuh',
            'orang_tua',
            'biktren',
            'kamtib',
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate([
                'name' => $role,
                'guard_name' => 'web',
            ]);
        }

        /**
         * FIXED biodata untuk Pusdatren Super Admin
         */
        $createPusdatrenBiodata = function () {
            $id = (string) Str::uuid();
            $now = Carbon::now()->toDateTimeString();

            DB::table('biodata')->insert([
                'id' => $id,
                'jalan' => null,
                'kode_pos' => null,
                'nama' => 'Pusdatren Super Admin',
                'jenis_kelamin' => 'l',
                'tanggal_lahir' => '2000-01-01',
                'tempat_lahir' => 'Probolinggo',
                'nik' => null,
                'no_telepon' => null,
                'no_telepon_2' => null,
                'email' => 'pusdatren@gmail.com',
                'jenjang_pendidikan_terakhir' => 's1',
                'nama_pendidikan_terakhir' => 'Universitas Nurul Jadid',
                'anak_keberapa' => null,
                'dari_saudara' => null,
                'tinggal_bersama' => null,
                'status' => true,
                'wafat' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            return $id;
        };

        /**
         * FIXED biodata untuk Super Admin
         */
        $createSuperadminBiodata = function () {
            $id = (string) Str::uuid();
            $now = Carbon::now()->toDateTimeString();

            DB::table('biodata')->insert([
                'id' => $id,
                'jalan' => 'Jl. Raya PP Nurul Jadid',
                'kode_pos' => '67291',
                'nama' => 'Super Admin',
                'jenis_kelamin' => 'l',
                'tanggal_lahir' => '1990-01-01',
                'tempat_lahir' => 'Paiton',
                'nik' => '3512340101900001',
                'no_telepon' => '081333444555',
                'no_telepon_2' => '082222333444',
                'email' => 'superadmin@example.com',
                'jenjang_pendidikan_terakhir' => 's1',
                'nama_pendidikan_terakhir' => 'Institut Nurul Jadid',
                'anak_keberapa' => 1,
                'dari_saudara' => 4,
                'tinggal_bersama' => 'pondok',
                'status' => true,
                'wafat' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            return $id;
        };

        /**
         * RANDOM biodata sesuai role baru
         */
        $createRealBiodata = function (string $role) use ($faker) {
            $id = (string) Str::uuid();
            $now = Carbon::now()->toDateTimeString();

            switch ($role) {
                case 'ustadz':
                    $name = 'Ustadz ' . $faker->firstName;
                    $gender = 'l';
                    $education = $faker->randomElement(['s1', 's2']);
                    break;
                case 'petugas':
                    $name = 'Petugas ' . $faker->firstName;
                    $gender = $faker->randomElement(['l', 'p']);
                    $education = 'sma';
                    break;
                case 'pengasuh':
                    $name = 'Pengasuh ' . $faker->firstName;
                    $gender = $faker->randomElement(['l', 'p']);
                    $education = 'sma';
                    break;
                case 'wali_asuh':
                    $name = 'Wali Asuh ' . $faker->firstName;
                    $gender = $faker->randomElement(['l', 'p']);
                    $education = 'sma';
                    break;
                case 'biktren':
                    $name = 'Biktren ' . $faker->firstName;
                    $gender = $faker->randomElement(['l', 'p']);
                    $education = 'sma';
                    break;
                case 'kamtib':
                    $name = 'Kamtib ' . $faker->firstName;
                    $gender = $faker->randomElement(['l', 'p']);
                    $education = 'sma';
                    break;
                case 'admin':
                    $name = 'Admin ' . $faker->firstName;
                    $gender = $faker->randomElement(['l', 'p']);
                    $education = 's1';
                    break;
                default:
                    $name = $faker->name;
                    $gender = $faker->randomElement(['l', 'p']);
                    $education = 'sma';
            }

            DB::table('biodata')->insert([
                'id' => $id,
                'nama' => $name,
                'jenis_kelamin' => $gender,
                'tanggal_lahir' => $faker->date('Y-m-d', '2010-01-01'),
                'tempat_lahir' => $faker->city,
                'nik' => $faker->nik(),
                'no_telepon' => '08' . $faker->numerify('##########'),
                'no_telepon_2' => '08' . $faker->numerify('##########'),
                'email' => strtolower(str_replace(' ', '.', $name)) . rand(1, 999) . '@example.com',
                'jenjang_pendidikan_terakhir' => $education,
                'nama_pendidikan_terakhir' => $faker->company,
                'anak_keberapa' => $faker->numberBetween(1, 5),
                'dari_saudara' => $faker->numberBetween(1, 7),
                'tinggal_bersama' => $faker->randomElement(['orang_tua', 'wali', 'asrama']),
                'status' => true,
                'wafat' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            return $id;
        };

        try {
            DB::beginTransaction();

            // SUPER ADMIN
            $super = User::updateOrCreate(
                ['email' => 'superadmin@example.com'],
                [
                    'name' => 'Super Admin',
                    'password' => Hash::make('password'),
                    'biodata_id' => $createSuperadminBiodata(),
                ]
            );
            $super->assignRole('superadmin');

            // PUSDATREN ADMIN (juga superadmin)
            $pusdatren = User::updateOrCreate(
                ['email' => 'pusdatren@gmail.com'],
                [
                    'name' => 'Pusdatren Admin',
                    'password' => Hash::make('pusdatren'),
                    'biodata_id' => $createPusdatrenBiodata(),
                ]
            );
            $pusdatren->assignRole('superadmin');

            // USER DUMMY UNTUK ROLE LAIN
            foreach (['admin', 'ustadz', 'petugas', 'pengasuh', 'wali_asuh', 'biktren', 'kamtib'] as $role) {
                $user = User::updateOrCreate(
                    ['email' => $role . '@example.com'],
                    [
                        'name' => ucfirst($role) . ' NUJ',
                        'password' => Hash::make('password'),
                        'biodata_id' => $createRealBiodata($role),
                    ]
                );
                $user->assignRole($role);
            }

            // ORANG TUA (jika ada di tabel orang_tua_wali)
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
            echo "✅ UserRoleSeeder berhasil dijalankan.\n";
        } catch (\Throwable $e) {
            DB::rollBack();
            echo "❌ Seeder gagal: " . $e->getMessage() . "\n";
        }
    }
}
