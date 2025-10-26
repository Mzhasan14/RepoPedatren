<?php

namespace Database\Seeders;

use Carbon\Carbon;
use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserNurulQuran extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        /**
         * Pastikan semua role tersedia (tanpa orang_tua)
         */
        $roles = [
            'superadmin',
            'admin',
            'ustadz',
            'petugas',
            'pengasuh',
            'wali_asuh',
            'biktren',
            'kamtib',
            'orang_tua',
            'keuangan'
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate([
                'name' => $role,
                'guard_name' => 'web',
            ]);
        }

        /**
         * Helper: buat biodata fixed dan random
         */
        $createPusdatrenBiodata = function () {
            $id = (string) Str::uuid();
            $now = Carbon::now()->toDateTimeString();

            DB::table('biodata')->insert([
                'id' => $id,
                'nama' => 'Pusdatren Super Admin',
                'jenis_kelamin' => 'l',
                'tanggal_lahir' => '2000-01-01',
                'tempat_lahir' => 'Probolinggo',
                'email' => 'pusdatren@gmail.com',
                'jenjang_pendidikan_terakhir' => 's1',
                'nama_pendidikan_terakhir' => 'Universitas Nurul Jadid',
                'status' => true,
                'wafat' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            return $id;
        };

        $createRealBiodata = function (string $role) use ($faker) {
            $id = (string) Str::uuid();
            $now = Carbon::now()->toDateTimeString();

            $name = ucfirst($role) . ' ' . $faker->firstName;
            $gender = $faker->randomElement(['l', 'p']);
            $education = 'sma';

            if ($role === 'admin') {
                $education = 's1';
            }

            DB::table('biodata')->insert([
                'id' => $id,
                'nama' => $name,
                'jenis_kelamin' => $gender,
                'tanggal_lahir' => $faker->date('Y-m-d', '2010-01-01'),
                'tempat_lahir' => $faker->city,
                'nik' => $faker->nik(),
                'no_telepon' => '08' . $faker->numerify('##########'),
                'email' => strtolower(str_replace(' ', '.', $name)) . rand(1, 999) . '@example.com',
                'jenjang_pendidikan_terakhir' => $education,
                'nama_pendidikan_terakhir' => $faker->company,
                'status' => true,
                'wafat' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            return $id;
        };

        try {
            DB::beginTransaction();

            // PUSDATREN ADMIN
            $pusdatren = User::updateOrCreate(
                ['email' => 'pusdatren@gmail.com'],
                [
                    'name' => 'Pusdatren Admin',
                    'password' => Hash::make('ppnurulquran'),
                    'biodata_id' => $createPusdatrenBiodata(),
                ]
            );
            $pusdatren->assignRole('superadmin');

            // USER DUMMY untuk role lain (tanpa orang_tua)
            foreach (['admin', 'ustadz', 'petugas', 'pengasuh', 'wali_asuh', 'biktren', 'kamtib', 'keuangan'] as $role) {
                $user = User::updateOrCreate(
                    ['email' => $role . '@example.com'],
                    [
                        'name' => ucfirst($role) . ' NUJ',
                        'password' => Hash::make('ppnurulquran'),
                        'biodata_id' => $createRealBiodata($role),
                    ]
                );
                $user->assignRole($role);
            }

            DB::commit();
            echo "✅ UserRoleSeeder berhasil dijalankan.\n";
        } catch (\Throwable $e) {
            DB::rollBack();
            echo "❌ Seeder gagal: " . $e->getMessage() . "\n";
        }
    }
}
