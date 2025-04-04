<?php

namespace Database\Seeders;

use Faker\Factory as Faker;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AlumniSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    // public function run(): void
    // {
    //     $faker = Faker::create('id_ID');
    
    //     // Ambil data relasi yang diperlukan
    //     $lembagaIds = DB::table('lembaga')->pluck('id')->toArray();
    //     $jurusanIds = DB::table('jurusan')->pluck('id')->toArray();
    //     $kelasIds = DB::table('kelas')->pluck('id')->toArray();
    //     $rombelIds = DB::table('rombel')->pluck('id')->toArray();
    //     $wilayahIds = DB::table('wilayah')->pluck('id')->toArray();
    //     $blokIds = DB::table('blok')->pluck('id')->toArray();
    //     $kamarIds = DB::table('kamar')->pluck('id')->toArray();
    
    //     // Ambil semua peserta didik
    //     $pesertaDidikIds = DB::table('peserta_didik')->pluck('id')->toArray();
    
    //     foreach ($pesertaDidikIds as $pesertaDidikId) {
    //         // Tentukan secara independen peran pelajar dan santri:
    //         // Pilihan: 'aktif', 'alumni', atau null (tidak dibuat datanya)
    //         $pelajarRole = $faker->randomElement(['aktif', 'alumni', null]);
    //         $santriRole  = $faker->randomElement(['aktif', 'alumni', null]);
    
    //         // Jika kedua peran null, maka default buat pelajar aktif
    //         if (is_null($pelajarRole) && is_null($santriRole)) {
    //             $pelajarRole = 'aktif';
    //         }
    
    //         // --- Proses Data Pelajar ---
    //         if (!is_null($pelajarRole)) {
    //             if ($pelajarRole === 'aktif') {
    //                 $pelajarUuid = (string) Str::uuid();
    
    //                 // Tabel pelajar (data aktif)
    //                 DB::table('pelajar')->insert([
    //                     'id' => $pelajarUuid,
    //                     'id_peserta_didik' => $pesertaDidikId,
    //                     'no_induk' => $faker->unique()->numerify('###########'),
    //                     'angkatan_pelajar' => $faker->year,
    //                     'tanggal_masuk_pelajar' => $faker->date(),
    //                     'tanggal_keluar_pelajar' => null,
    //                     'created_by' => 1,
    //                     'updated_by' => null,
    //                     'deleted_by' => null,
    //                     'created_at' => now(),
    //                     'updated_at' => now(),
    //                 ]);
    
    //                 // Tabel pendidikan_pelajar (data aktif)
    //                 DB::table('pendidikan_pelajar')->insert([
    //                     'id_pelajar' => $pelajarUuid,
    //                     'id_lembaga' => $faker->randomElement($lembagaIds),
    //                     'id_jurusan' => $faker->randomElement($jurusanIds),
    //                     'id_kelas' => $faker->randomElement($kelasIds),
    //                     'id_rombel' => $faker->randomElement($rombelIds),
    //                     'status' => 'aktif',
    //                     'tanggal_masuk' => $faker->dateTime(),
    //                     'tanggal_keluar' => null,
    //                     'created_by' => 1,
    //                     'updated_by' => null,
    //                     'deleted_by' => null,
    //                     'created_at' => now(),
    //                     'updated_at' => now(),
    //                 ]);
    //             } elseif ($pelajarRole === 'alumni') {
    //                 $alumniPelajarUuid = (string) Str::uuid();
    
    //                 // Tabel alumni_pelajar (data alumni)
    //                 DB::table('alumni_pelajar')->insert([
    //                     'id' => $alumniPelajarUuid,
    //                     'id_peserta_didik' => $pesertaDidikId,
    //                     'no_induk' => $faker->unique()->numerify('###########'),
    //                     'angkatan_pelajar' => $faker->year,
    //                     'tanggal_masuk_pelajar' => $faker->date(),
    //                     'tanggal_keluar_pelajar' => $faker->date(), // tidak null untuk alumni
    //                     'created_by' => 1,
    //                     'updated_by' => null,
    //                     'deleted_by' => null,
    //                     'created_at' => now(),
    //                     'updated_at' => now(),
    //                 ]);
    
    //                 // Tabel pendidikan_alumni (data alumni)
    //                 DB::table('pendidikan_alumni')->insert([
    //                     'id_alumni_pelajar' => $alumniPelajarUuid,
    //                     'id_lembaga' => $faker->randomElement($lembagaIds),
    //                     'id_jurusan' => $faker->randomElement($jurusanIds),
    //                     'id_kelas' => $faker->randomElement($kelasIds),
    //                     'id_rombel' => $faker->randomElement($rombelIds),
    //                     'status' => 'keluar',
    //                     'tanggal_masuk' => $faker->dateTime(),
    //                     'tanggal_keluar' => $faker->dateTime(), // tidak null untuk alumni
    //                     'created_by' => 1,
    //                     'updated_by' => null,
    //                     'deleted_by' => null,
    //                     'created_at' => now(),
    //                     'updated_at' => now(),
    //                 ]);
    //             }
    //         }
    
    //         // --- Proses Data Santri ---
    //         if (!is_null($santriRole)) {
    //             if ($santriRole === 'aktif') {
    //                 $santriUuid = (string) Str::uuid();
    
    //                 // Tabel santri (data aktif)
    //                 DB::table('santri')->insert([
    //                     'id' => $santriUuid,
    //                     'id_peserta_didik' => $pesertaDidikId,
    //                     'nis' => $faker->unique()->numerify('###########'),
    //                     'angkatan_santri' => $faker->year,
    //                     'tanggal_masuk_santri' => $faker->date(),
    //                     'tanggal_keluar_santri' => null,
    //                     'status_santri' => 'aktif',
    //                     'created_by' => 1,
    //                     'updated_by' => null,
    //                     'deleted_by' => null,
    //                     'created_at' => now(),
    //                     'updated_at' => now(),
    //                 ]);
    
    //                 // Tabel domisili_santri (data aktif)
    //                 DB::table('domisili_santri')->insert([
    //                     'id_santri' => $santriUuid,
    //                     'id_wilayah' => $faker->randomElement($wilayahIds),
    //                     'id_blok' => $faker->randomElement($blokIds),
    //                     'id_kamar' => $faker->randomElement($kamarIds),
    //                     'tanggal_masuk' => $faker->dateTime(),
    //                     'tanggal_keluar' => null,
    //                     'status' => 'aktif',
    //                     'created_by' => 1,
    //                     'updated_by' => null,
    //                     'deleted_by' => null,
    //                     'created_at' => now(),
    //                     'updated_at' => now(),
    //                 ]);
    //             } elseif ($santriRole === 'alumni') {
    //                 $alumniSantriUuid = (string) Str::uuid();
    
    //                 // Tabel alumni_santri (data alumni)
    //                 DB::table('alumni_santri')->insert([
    //                     'id' => $alumniSantriUuid,
    //                     'id_peserta_didik' => $pesertaDidikId,
    //                     'nis' => $faker->unique()->numerify('###########'),
    //                     'angkatan_santri' => $faker->year,
    //                     'tanggal_masuk_santri' => $faker->date(),
    //                     'tanggal_keluar_santri' => $faker->date(), // tidak null untuk alumni
    //                     'created_by' => 1,
    //                     'updated_by' => null,
    //                     'deleted_by' => null,
    //                     'created_at' => now(),
    //                     'updated_at' => now(),
    //                 ]);
    
    //                 // Tabel domisili_alumni (data alumni)
    //                 DB::table('domisili_alumni')->insert([
    //                     'id_alumni_santri' => $alumniSantriUuid,
    //                     'id_wilayah' => $faker->randomElement($wilayahIds),
    //                     'id_blok' => $faker->randomElement($blokIds),
    //                     'id_kamar' => $faker->randomElement($kamarIds),
    //                     'tanggal_masuk' => $faker->dateTime(),
    //                     'tanggal_keluar' => $faker->dateTime(), // tidak null untuk alumni
    //                     'status' => 'keluar',
    //                     'created_by' => 1,
    //                     'updated_by' => null,
    //                     'deleted_by' => null,
    //                     'created_at' => now(),
    //                     'updated_at' => now(),
    //                 ]);
    //             }
    //         }
    //     }
    // }
    
}
