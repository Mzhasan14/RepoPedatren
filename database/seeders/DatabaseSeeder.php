<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Database\Seeders\BerkasSeeder;
use Illuminate\Support\Facades\Hash;
use Database\Seeders\JenisBerkasSeeder;
use Database\Seeders\DataKeluargaSeeder;
use Database\Seeders\Alamat\NegaraSeeder;
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
use Database\Seeders\Pegawai\MateriAjarSeeder;
use Database\Seeders\Pendidikan\JurusanSeeder;
use Database\Seeders\Pendidikan\LembagaSeeder;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Database\Seeders\Kewilayahan\WilayahSeeder;
use Database\Seeders\Pegawai\AnakPegawaiSeeder;
use Database\Seeders\Pegawai\KategoriGolonganSeeder;
use Database\Seeders\Pegawai\RiwayatJabatanKaryawanSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Super Admin',
        //     'email' => 'superadmin@example.com',
        //     'password' => Hash::make('superadmin'), // Password: superadmin
        //     'role' => 'superadmin',
        //     'email_verified_at' => now(),
        //     'remember_token' => Str::random(10),
        // ]);

        // User::factory()->create([
        //     'name' => 'Admin',
        //     'email' => 'admin@example.com',
        //     'password' => Hash::make('admin'), // Password: admin
        //     'role' => 'admin',
        //     'email_verified_at' => now(),
        //     'remember_token' => Str::random(10),
        // ]);

        // User::factory()->create([
        //     'name' => 'Staff',
        //     'email' => 'staff@example.com',
        //     'password' => Hash::make('staff'), // Password: staff
        //     'role' => 'staff',
        //     'email_verified_at' => now(),
        //     'remember_token' => Str::random(10),
        // ]);

        // User::factory()->create([
        //     'name' => 'Santri',
        //     'email' => 'santri@example.com',
        //     'password' => Hash::make('santri'), // Password: santri
        //     'role' => 'santri',
        //     'email_verified_at' => now(),
        //     'remember_token' => Str::random(10),
        // ]);

        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            LembagaSeeder::class,
            JurusanSeeder::class,
            KelasSeeder::class,
            RombelSeeder::class,
            WilayahSeeder::class,
            BlokSeeder::class,
            KamarSeeder::class,
            KabupatenSeeder::class,
            KecamatanSeeder::class,
            ProvinsiSeeder::class,
            NegaraSeeder::class,
            HubunganKeluargaSeeder::class,
            BiodataSeeder::class,
            DataKeluargaSeeder::class,
            // PesertaDidikSeeder::class,
            SantriSeeder::class,
            KategoriGolonganSeeder::class,
            GolonganSeeder::class,
            PegawaiSeeder::class,
            // EntitasPegawai::class,
            PengajarSeeder::class,
            KaryawanSeeder::class,
            WaliKelasSeeder::class,
            PengurusSeeder::class,
            JenisBerkasSeeder::class,
            BerkasSeeder::class,
            PelanggaranSeeder::class,
            WaliAnakAsuhSeeder::class,
            PerizinanSeeder::class,
            KhadamSeeder::class,
            // Anak_AsuhSeeder::class,
            // Grup_WaliAsuhSeeder::class,
            // Wali_AsuhSeeder::class,
            CatatanAfektifSeeder::class,
            CatatanKognitifSeeder::class,
            MateriAjarSeeder::class,
            WargaPesantrenSeeder::class,
            PengunjungMahrom::class,
            RiwayatJabatanKaryawanSeeder::class,
            
        ]);
    }
}
