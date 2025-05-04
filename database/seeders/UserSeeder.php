<?php

namespace Database\Seeders;


use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pastikan role sudah ada sebelum menetapkan ke user
        $roles = ['superadmin','supervisor', 'admin', 'staff', 'santri'];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        // Buat Superadmin
        $superadmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'password' => Hash::make('password'),
        ]);
        $superadmin->assignRole('superadmin');
        
        // Buat Supervisor
        $supervisor = User::create([
            'name' => 'Super Visor',
            'email' => 'supervisor@example.com',
            'password' => Hash::make('password'),
        ]);
        $supervisor->assignRole('supervisor');

        // Buat Admin
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
        ]);
        $admin->assignRole('admin');

        // Buat Staff
        $staff = User::create([
            'name' => 'Staff User',
            'email' => 'staff@example.com',
            'password' => Hash::make('password'),
        ]);
        $staff->assignRole('staff');

        // Buat Santri
        $santri = User::create([
            'name' => 'Santri User',
            'email' => 'santri@example.com',
            'password' => Hash::make('password'),
        ]);
        $santri->assignRole('santri');

        // Buat Kamtib
        $kamtib = User::create([
            'name' => 'Kamtib User',
            'email' => 'kamtib@example.com',
            'password' => Hash::make('password'),
        ]);
        $kamtib->assignRole('kamtib');

        // Buat biktren
        $biktren = User::create([
            'name' => 'Biktren User',
            'email' => 'biktren@example.com',
            'password' => Hash::make('password'),
        ]);
        $biktren->assignRole('biktren');

        // Buat pengasuh
        $pengasuh = User::create([
            'name' => 'Pengasuh User',
            'email' => 'pengasuh@example.com',
            'password' => Hash::make('password'),
        ]);
        $pengasuh->assignRole('pengasuh');

        // Buat pengasuh
        $pengasuh = User::create([
            'name' => 'Orang Dalam',
            'email' => 'pedatrennurja@gmail.com',
            'password' => Hash::make('password'),
        ]);
        $pengasuh->assignRole('superadmin');
    }
}
