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
            'admin',
            'staff',
            'santri',
            'kamtib',
            'biktren',
            'pengasuh',
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

        // Admin
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
        ]);
        $admin->assignRole('admin');

        // Staff
        $staff = User::create([
            'name' => 'Staff User',
            'email' => 'staff@example.com',
            'password' => Hash::make('password'),
        ]);
        $staff->assignRole('staff');

        // Santri
        $santri = User::create([
            'name' => 'Santri User',
            'email' => 'santri@example.com',
            'password' => Hash::make('password'),
        ]);
        $santri->assignRole('santri');

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

        // Creator tambahan
        $creator = User::create([
            'name' => 'Orang Dalam',
            'email' => 'pedatrennurja@gmail.com',
            'password' => Hash::make('password'),
        ]);

        $creator->assignRole('superadmin');

        $creator = User::create([
            'name' => 'Admin Sipatren',
            'email' => 'sipatren@gmail.com',
            'password' => Hash::make('sipatren'),
        ]);

        $creator->assignRole('superadmin');
    }
}
