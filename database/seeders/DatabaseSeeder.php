<?php

namespace Database\Seeders;

use Database\Seeders\Pegawai\DropPegawaiNoEntitas;
use Database\Seeders\Pegawai\GolonganJabatanSeeder;
use Database\Seeders\Pegawai\GolonganSeeder;
use Database\Seeders\Pegawai\JadwalPelajaranSeeder;
use Database\Seeders\Pegawai\JamPelajaranSeeder;
use Database\Seeders\Pegawai\KaryawanSeeder;
use Database\Seeders\Pegawai\KategoriGolonganSeeder;
use Database\Seeders\Pegawai\MataPelajaranSeeder;
use Database\Seeders\Pegawai\MateriAjarSeeder;
use Database\Seeders\Pegawai\PegawaiSeeder;
use Database\Seeders\Pegawai\PengajarSeeder;
use Database\Seeders\Pegawai\PengurusSeeder;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Database\Seeders\Pegawai\WaliKelasSeeder;
use Illuminate\Database\Seeder;
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
            TahunAjaranSeeder::class,
            SemesterSeeder::class,
            AngkatanSeeder::class,
            PendidikanSeeder::class,
            WilayahSeeder::class,
            AlamatSeeder::class,
            HubunganKeluargaSeeder::class,
            BiodataSeeder::class,
            KategoriGolonganSeeder::class,
            GolonganSeeder::class,
            GolonganJabatanSeeder::class,
            PegawaiSeeder::class,
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
            CatatanAfektifSeeder::class,
            CatatanKognitifSeeder::class,
            MateriAjarSeeder::class,
            WargaPesantrenSeeder::class,
            PengunjungMahromSeeder::class,
            // BiometricSeeder::class,
            PresensiSantriSeeder::class,
            DropPegawaiNoEntitas::class,
            MataPelajaranSeeder::class,
            JamPelajaranSeeder::class,
            JadwalPelajaranSeeder::class,

        ]);

        // Aktifkan kembali logging activity setelah seeding selesai
        Activity::enableLogging();
    }
}
