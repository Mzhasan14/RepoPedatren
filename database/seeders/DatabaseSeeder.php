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
use Database\Seeders\Pegawai\GolonganJabatanSeeder;
use Database\Seeders\Pegawai\KategoriGolonganSeeder;
use Database\Seeders\Pegawai\RiwayatJabatanKaryawanSeeder;
use Spatie\Activitylog\Facades\Activity;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Activity::disableLogging();

        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            PendidikanSeeder::class,
            WilayahSeeder::class,
            BlokSeeder::class,
            KamarSeeder::class,
            AlamatSeeder::class,
            TahunAjaranSeeder::class,
            SemesterSeeder::class,
            AngkatanSeeder::class,
            // KabupatenSeeder::class,
            // KecamatanSeeder::class,
            // ProvinsiSeeder::class,
            // NegaraSeeder::class,
            HubunganKeluargaSeeder::class,
            BiodataSeeder::class,
            // PesertaDidikSeeder::class,
            // SantriSeeder::class,
            KategoriGolonganSeeder::class,
            GolonganSeeder::class,
            PegawaiSeeder::class,
            // EntitasPegawai::class,
            PengajarSeeder::class,
            KaryawanSeeder::class,
            WaliKelasSeeder::class,
            PengurusSeeder::class,
            DataKeluargaSeeder::class,
            JenisBerkasSeeder::class,
            BerkasSeeder::class,
            WaliAnakAsuhSeeder::class,
            PelanggaranSeeder::class,
            PerizinanSeeder::class,
            BerkasPerizinanSeeder::class,
            BerkasPelanggaranSeeder::class,
            KhadamSeeder::class,
            // Anak_AsuhSeeder::class,
            // Grup_WaliAsuhSeeder::class,
            // Wali_AsuhSeeder::class,
            CatatanAfektifSeeder::class,
            CatatanKognitifSeeder::class,
            MateriAjarSeeder::class,
            WargaPesantrenSeeder::class,
            PengunjungMahromSeeder::class,
            GolonganJabatanSeeder::class,
            AnakPegawaiSeeder::class,
            BiometricSeeder::class,
        ]);

        // Aktifkan kembali logging activity setelah seeding selesai
        Activity::enableLogging();
    }
}
