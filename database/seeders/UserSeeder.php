<?php

namespace Database\Seeders;

use App\Models\Biodata;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tambah semua role
        $roles = [
            'superadmin',
            'admin',
            'supervisor',
            'ustadz',
            'petugas',
            'kamtib',
            'biktren',
            'pengasuh',
            'waliasuh',
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }
        $getRandomBiodataId = function () {
            return Biodata::inRandomOrder()->value('id'); // langsung ambil ID random
        };
        // Superadmin
        $superadmin = User::find(1);
        $superadmin->update([
            'name'       => 'Super Admin',
            'email'      => 'superadmin@example.com',
            'password'   => Hash::make('password'),
            'biodata_id' => $getRandomBiodataId(),
        ]);

        $superadmin->assignRole('superadmin');

        // Admin
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'biodata_id'  => $getRandomBiodataId(),
        ]);
        $admin->assignRole('admin');

        // Supervisor
        $supervisor = User::create([
            'name' => 'Super Visor',
            'email' => 'supervisor@example.com',
            'password' => Hash::make('password'),
            'biodata_id'  => $getRandomBiodataId(),
        ]);
        $supervisor->assignRole('supervisor');

        // ustadz
        for ($i = 0; $i < 3; $i++) {
            $ustadz = User::create([
                'name' => "Ustadz {$i}",
                'email' => "ustadz{$i}@example.com",
                'password' => Hash::make('password'),
                'biodata_id'  => $getRandomBiodataId(),
            ]);
            $ustadz->assignRole('ustadz');
        }

        // Santri
        $WaliAsuh = User::create([
            'name' => 'Wali Asuh User',
            'email' => 'waliasuh@example.com',
            'password' => Hash::make('password'),
            'biodata_id'  => $getRandomBiodataId(),
        ]);
        $WaliAsuh->assignRole('waliasuh');

        $kamtib = User::create([
            'name' => 'Kamtib',
            'email' => 'kamtib@example.com',
            'password' => Hash::make('password'),
            'biodata_id'  => $getRandomBiodataId(),
        ]);
        $kamtib->assignRole('kamtib');

        $biktren = User::create([
            'name' => 'Biktren',
            'email' => 'biktren@example.com',
            'password' => Hash::make('password'),
            'biodata_id'  => $getRandomBiodataId(),
        ]);
        $biktren->assignRole('biktren');

        $pengasuh = User::create([
            'name' => 'Pengasuh',
            'email' => 'pengasuh@example.com',
            'password' => Hash::make('password'),
            'biodata_id'  => $getRandomBiodataId(),
        ]);
        $pengasuh->assignRole('pengasuh');

        // Pusdatren admin
        $pusdatren = User::create([
            'name' => 'Pusdatren Admin',
            'email' => 'pusdatren@gmail.com',
            'password' => Hash::make('password'),
            'biodata_id'  => $getRandomBiodataId(),
        ]);
        $pusdatren->assignRole('superadmin');

        for ($i = 1; $i <= 5; $i++) {
            $user = User::create([
                'name'     => "Petugas {$i}",
                'email'    => "petugas{$i}@example.com",
                'password' => Hash::make('password'),
                'biodata_id'  => $getRandomBiodataId(),
            ]);

            $user->assignRole('petugas');
        }
    }
}
