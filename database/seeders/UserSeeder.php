<?php

namespace Database\Seeders;

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

        // Superadmin
        $superadmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'password' => Hash::make('password'),
        ]);
        $superadmin->assignRole('superadmin');

        // Supervisor
        $supervisor = User::create([
            'name' => 'Super Visor',
            'email' => 'supervisor@example.com',
            'password' => Hash::make('password'),
        ]);
        $supervisor->assignRole('supervisor');

        // Staff
        $staff = User::create([
            'name' => 'Ustadz',
            'email' => 'Ustadz@example.com',
            'password' => Hash::make('password'),
        ]);
        $staff->assignRole('ustadz');

        // Santri
        $WaliAsuh = User::create([
            'name' => 'Wali Asuh User',
            'email' => 'waliasuh@example.com',
            'password' => Hash::make('password'),
        ]);
        $WaliAsuh->assignRole('waliasuh');

        $kamtib = User::create([
            'name' => 'Kamtib',
            'email' => 'kamtib@example.com',
            'password' => Hash::make('password'),
        ]);
        $kamtib->assignRole('kamtib');

        $biktren = User::create([
            'name' => 'Biktren',
            'email' => 'biktren@example.com',
            'password' => Hash::make('password'),
        ]);
        $biktren->assignRole('biktren');

        $pengasuh = User::create([
            'name' => 'Pengasuh',
            'email' => 'pengasuh@example.com',
            'password' => Hash::make('password'),
        ]);
        $pengasuh->assignRole('pengasuh');

        $creator = User::create([
            'name' => 'Petugas',
            'email' => 'petugas@example.com',
            'password' => Hash::make('password'),
        ]);

        $creator->assignRole('petugas');
    }
}
