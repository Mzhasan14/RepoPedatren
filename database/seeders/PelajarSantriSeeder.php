<?php

namespace Database\Seeders;


use Faker\Factory as Faker;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PelajarSantriSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');
    
        // Ambil ID dari tabel terkait
        $pesertaDidikIds = DB::table('peserta_didik')->pluck('id')->toArray();
        $lembagaIds = DB::table('lembaga')->pluck('id')->toArray();
        $jurusanIds = DB::table('jurusan')->pluck('id')->toArray();
        $kelasIds = DB::table('kelas')->pluck('id')->toArray();
        $rombelIds = DB::table('rombel')->pluck('id')->toArray();
        $wilayahIds = DB::table('wilayah')->pluck('id')->toArray();
        $blokIds = DB::table('blok')->pluck('id')->toArray();
        $kamarIds = DB::table('kamar')->pluck('id')->toArray();
    
        foreach ($pesertaDidikIds as $pesertaDidikId) {
            // Tentukan apakah peserta_didik akan menjadi pelajar, santri, atau keduanya
            $isPelajar = $faker->boolean(50);
            $isSantri = $faker->boolean(50);
    
            // Jika keduanya false, paksa salah satu menjadi true
            if (!$isPelajar && !$isSantri) {
                $isPelajar = true; // Secara default dijadikan pelajar jika tidak ada relasi
            }
    
            $pelajarId = null;
            $santriId = null;
    
            if ($isPelajar) {
                $pelajarUuid = (string) Str::uuid();
    
                // Tentukan status pelajar
                $statusPelajar = $faker->randomElement(['aktif', 'alumni']);
    
                // Buat data pelajar
                DB::table('pelajar')->insertGetId([
                    'id' => $pelajarUuid,
                    'id_peserta_didik' => $pesertaDidikId,
                    'tanggal_masuk' => $faker->date(),
                    // Set tanggal_keluar hanya jika status alumni
                    'tanggal_keluar' => $statusPelajar === 'alumni' ? $faker->date() : null,
                    'status' => $statusPelajar,
                    'created_by' => 1,
                    'updated_by' => null,
                    'deleted_by' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
    
                // Buat satu data riwayat_pendidikan dengan id_peserta_didik
                DB::table('riwayat_pendidikan')->insert([
                    'id_peserta_didik' => $pesertaDidikId,
                    'no_induk' => $faker->unique()->numerify('###########'),
                    'id_lembaga' => $faker->randomElement($lembagaIds),
                    'id_jurusan' => $faker->randomElement($jurusanIds),
                    'id_kelas' => $faker->randomElement($kelasIds),
                    'id_rombel' => $faker->randomElement($rombelIds),
                    'tanggal_masuk' => $faker->date(),
                    // Set tanggal_keluar hanya jika status pelajar alumni
                    'tanggal_keluar' => $statusPelajar === 'alumni' ? $faker->date() : null,
                    'status' => $statusPelajar === 'alumni' ? 'alumni' : 'aktif',
                    'created_by' => 1,
                    'updated_by' => null,
                    'deleted_by' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
    
            if ($isSantri) {
                $santriUuid = (string) Str::uuid();
    
                // Tentukan status santri
                $statusSantri = $faker->randomElement(['aktif', 'alumni']);
    
                // Buat data santri
                DB::table('santri')->insertGetId([
                    'id' => $santriUuid,
                    'id_peserta_didik' => $pesertaDidikId,
                    'nis' => $faker->unique()->numerify('###########'),
                    'tanggal_masuk' => $faker->date(),
                    // Set tanggal_keluar hanya jika status alumni
                    'tanggal_keluar' => $statusSantri === 'alumni' ? $faker->date() : null,
                    'status' =>  $statusSantri,
                    'created_by' => 1,
                    'updated_by' => null,
                    'deleted_by' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
    
                // Buat satu data riwayat_domisili dengan id_peserta_didik
                DB::table('riwayat_domisili')->insert([
                    'id_peserta_didik' => $pesertaDidikId,
                    'id_wilayah' => $faker->randomElement($wilayahIds),
                    'id_blok' => $faker->randomElement($blokIds),
                    'id_kamar' => $faker->randomElement($kamarIds),
                    'tanggal_masuk' => $faker->dateTime(),
                    // Set tanggal_keluar hanya jika status santri alumni
                    'tanggal_keluar' => $statusSantri === 'alumni' ? $faker->dateTime() : null,
                    'status' => $statusSantri  === 'alumni' ? 'alumni' : 'aktif',
                    'created_by' => 1,
                    'updated_by' => null,
                    'deleted_by' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
    // {
    //     $faker = Faker::create('id_ID');
    
    //     // Ambil ID dari tabel terkait
    //     $pesertaDidikIds = DB::table('peserta_didik')->pluck('id')->toArray();
    //     $lembagaIds = DB::table('lembaga')->pluck('id')->toArray();
    //     $jurusanIds = DB::table('jurusan')->pluck('id')->toArray();
    //     $kelasIds = DB::table('kelas')->pluck('id')->toArray();
    //     $rombelIds = DB::table('rombel')->pluck('id')->toArray();
    //     $wilayahIds = DB::table('wilayah')->pluck('id')->toArray();
    //     $blokIds = DB::table('blok')->pluck('id')->toArray();
    //     $kamarIds = DB::table('kamar')->pluck('id')->toArray();
    
    //     foreach ($pesertaDidikIds as $pesertaDidikId) {
    //         // Tentukan apakah peserta_didik akan menjadi pelajar, santri, atau keduanya
    //         $isPelajar = $faker->boolean(50);
    //         $isSantri = $faker->boolean(50);
    
    //         // Jika keduanya false, paksa salah satu menjadi true
    //         if (!$isPelajar && !$isSantri) {
    //             $isPelajar = true; // Secara default dijadikan pelajar jika tidak ada relasi
    //         }
    
    //         $pelajarId = null;
    //         $santriId = null;
    
    //         if ($isPelajar) {
    //             $pelajarUuid = (string) Str::uuid();
    
    //             // Tentukan status pelajar
    //             $statusPelajar = $faker->randomElement(['aktif', 'alumni']);
    
    //             // Buat data pelajar
    //             DB::table('pelajar')->insertGetId([
    //                 'id' => $pelajarUuid,
    //                 'id_peserta_didik' => $pesertaDidikId,
    //                 'tanggal_masuk' => $faker->date(),
    //                 // Set tanggal_keluar hanya jika status alumni
    //                 'tanggal_keluar' => $statusPelajar === 'alumni' ? $faker->date() : null,
    //                 'status' => $statusPelajar,
    //                 'created_by' => 1,
    //                 'updated_by' => null,
    //                 'deleted_by' => null,
    //                 'created_at' => now(),
    //                 'updated_at' => now(),
    //             ]);
    
    //             // Buat satu data riwayat_pendidikan dengan id_peserta_didik
    //             DB::table('riwayat_pendidikan')->insert([
    //                 'id_peserta_didik' => $pesertaDidikId,
    //                 'no_induk' => $faker->unique()->numerify('###########'),
    //                 'id_lembaga' => $faker->randomElement($lembagaIds),
    //                 'id_jurusan' => $faker->randomElement($jurusanIds),
    //                 'id_kelas' => $faker->randomElement($kelasIds),
    //                 'id_rombel' => $faker->randomElement($rombelIds),
    //                 'tanggal_masuk' => $faker->date(),
    //                 // Set tanggal_keluar hanya jika status pelajar alumni
    //                 'tanggal_keluar' => $statusPelajar === 'alumni' ? $faker->date() : null,
    //                 'status' => $statusPelajar === 'alumni' ? 'alumni' : 'aktif',
    //                 'created_by' => 1,
    //                 'updated_by' => null,
    //                 'deleted_by' => null,
    //                 'created_at' => now(),
    //                 'updated_at' => now(),
    //             ]);
    //         }
    
    //         if ($isSantri) {
    //             $santriUuid = (string) Str::uuid();
    
    //             // Tentukan status santri
    //             $statusSantri = $faker->randomElement(['aktif', 'alumni']);
    
    //             // Buat data santri
    //             DB::table('santri')->insertGetId([
    //                 'id' => $santriUuid,
    //                 'id_peserta_didik' => $pesertaDidikId,
    //                 'nis' => $faker->unique()->numerify('###########'),
    //                 'tanggal_masuk' => $faker->date(),
    //                 // Set tanggal_keluar hanya jika status alumni
    //                 'tanggal_keluar' => $statusSantri === 'alumni' ? $faker->date() : null,
    //                 'status' =>  $statusSantri,
    //                 'created_by' => 1,
    //                 'updated_by' => null,
    //                 'deleted_by' => null,
    //                 'created_at' => now(),
    //                 'updated_at' => now(),
    //             ]);
    
    //             // Buat satu data riwayat_domisili dengan id_peserta_didik
    //             DB::table('riwayat_domisili')->insert([
    //                 'id_peserta_didik' => $pesertaDidikId,
    //                 'id_wilayah' => $faker->randomElement($wilayahIds),
    //                 'id_blok' => $faker->randomElement($blokIds),
    //                 'id_kamar' => $faker->randomElement($kamarIds),
    //                 'tanggal_masuk' => $faker->dateTime(),
    //                 // Set tanggal_keluar hanya jika status santri alumni
    //                 'tanggal_keluar' => $statusSantri === 'alumni' ? $faker->dateTime() : null,
    //                 'status' => $statusSantri  === 'alumni' ? 'alumni' : 'aktif',
    //                 'created_by' => 1,
    //                 'updated_by' => null,
    //                 'deleted_by' => null,
    //                 'created_at' => now(),
    //                 'updated_at' => now(),
    //             ]);
    //         }
    //     }
    // }
    
}
