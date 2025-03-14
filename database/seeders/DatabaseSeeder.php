<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Database\Seeders\Alamat\DesaSeeder;
use Database\Seeders\Alamat\NegaraSeeder;
use Database\Seeders\BerkasSeeder;
use Database\Seeders\Alamat\ProvinsiSeeder;
use Database\Seeders\Pegawai\PegawaiSeeder;
use Database\Seeders\Alamat\KabupatenSeeder;
use Database\Seeders\Alamat\KecamatanSeeder;
use Database\Seeders\Kewilayahan\BlokSeeder;
use Database\Seeders\Pegawai\EntitasPegawai;
use Database\Seeders\Pegawai\GolonganSeeder;
use Database\Seeders\Pegawai\KaryawanSeeder;
use Database\Seeders\Pegawai\PengajarSeeder;
use Database\Seeders\Pegawai\PengurusSeeder;
use Database\Seeders\Pendidikan\KelasSeeder;
use Database\Seeders\Kewilayahan\KamarSeeder;
use Database\Seeders\Pegawai\WaliKelasSeeder;
use Database\Seeders\Pendidikan\RombelSeeder;
use Database\Seeders\Pendidikan\JurusanSeeder;
use Database\Seeders\Pendidikan\LembagaSeeder;
use Database\Seeders\Kewilayahan\WilayahSeeder;
use Database\Seeders\JenisBerkasSeeder;
use Database\Seeders\Kewilayahan\DomisiliSeeder;
use Database\Seeders\Kewaliasuhan\Anak_AsuhSeeder;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Database\Seeders\Kewaliasuhan\Wali_AsuhSeeder;
use Database\Seeders\Pegawai\KategoriGolonganSeeder;
use Database\Seeders\Kewaliasuhan\Grup_WaliAsuhSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'password' => Hash::make('superadmin'), // Password: superadmin
            'role' => 'superadmin',
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
        ]);

        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('admin'), // Password: admin
            'role' => 'admin',
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
        ]);

        User::factory()->create([
            'name' => 'Staff',
            'email' => 'staff@example.com',
            'password' => Hash::make('staff'), // Password: staff
            'role' => 'staff',
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
        ]);

        User::factory()->create([
            'name' => 'Santri',
            'email' => 'santri@example.com',
            'password' => Hash::make('santri'), // Password: santri
            'role' => 'santri',
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
        ]);
        
        $this->call([
            BiodataSeeder::class,
            KategoriGolonganSeeder::class,
            GolonganSeeder::class,
            PegawaiSeeder::class,
            LembagaSeeder::class,
            EntitasPegawai::class,
            PengajarSeeder::class,
            JurusanSeeder::class,
            KelasSeeder::class,
            RombelSeeder::class,
            KaryawanSeeder::class,
            WaliKelasSeeder::class,
            PengurusSeeder::class,
            JenisBerkasSeeder::class,
            BerkasSeeder::class,
            KeluargaSeeder::class,
            KhadamSeeder::class,
            OrangTuaSeeder::class,
            PelanggaranSeeder::class,
            PerizinanSeeder::class,
            PesertaDidikSeeder::class,
            PelajarSeeder::class,
            SantriSeeder::class,
            StatusKeluargaSeeder::class,
            BlokSeeder::class,
            DomisiliSeeder::class,
            KamarSeeder::class,
            WilayahSeeder::class,
            Anak_AsuhSeeder::class,
            Grup_WaliAsuhSeeder::class,
            Wali_AsuhSeeder::class,
            DesaSeeder::class,
            KabupatenSeeder::class,
            KecamatanSeeder::class,
            ProvinsiSeeder::class,
            NegaraSeeder::class,
            CatatanAfektifSeeder::class,
            CatatanKognitifSeeder::class,
        ]);
    }
}
