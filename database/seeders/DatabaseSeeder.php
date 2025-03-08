<?php

namespace Database\Seeders;

use App\Models\User;
use Database\Seeders\Alamat\DesaSeeder;
use Database\Seeders\Alamat\KabupatenSeeder;
use Database\Seeders\Alamat\KecamatanSeeder;
use Database\Seeders\Alamat\NegaraSeeder;
use Database\Seeders\Alamat\ProvinsiSeeder;
use Database\Seeders\Kewaliasuhan\Anak_AsuhSeeder;
use Database\Seeders\Kewaliasuhan\Grup_WaliAsuhSeeder;
use Database\Seeders\Kewaliasuhan\Wali_AsuhSeeder;
use Database\Seeders\Kewilayahan\BlokSeeder;
use Database\Seeders\Kewilayahan\DomisiliSeeder;
use Database\Seeders\Kewilayahan\KamarSeeder;
use Database\Seeders\Kewilayahan\WilayahSeeder;
use Database\Seeders\Pegawai\BerkasSeeder;
use Database\Seeders\Pegawai\EntitasPegawai;
use Database\Seeders\Pegawai\GolonganSeeder;
use Database\Seeders\Pegawai\JenisBerkasSeeder;
use Database\Seeders\Pegawai\KaryawanSeeder;
use Database\Seeders\Pegawai\KategoriGolonganSeeder;
use Database\Seeders\Pegawai\PegawaiSeeder;
use Database\Seeders\Pegawai\PengajarSeeder;
use Database\Seeders\Pegawai\PengurusSeeder;
use Database\Seeders\Pegawai\WaliKelasSeeder;
use Database\Seeders\Pendidikan\JurusanSeeder;
use Database\Seeders\Pendidikan\KelasSeeder;
use Database\Seeders\Pendidikan\LembagaSeeder;
use Database\Seeders\Pendidikan\RombelSeeder;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
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
            BerkasSeeder::class,
            PengurusSeeder::class,
            JenisBerkasSeeder::class,
            KeluargaSeeder::class,
            KhadamSeeder::class,
            OrangTuaSeeder::class,
            PelanggaranSeeder::class,
            PerizinanSeeder::class,
            PesertaDidikSeeder::class,
            RencanaPendidikanSeeder::class,
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

        ]);
    }
}
