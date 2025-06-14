<?php

namespace Database\Seeders;

use Faker\Factory;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SantriSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    // public function run(): void
    // {
    //     $faker = Faker::create('id_ID');

    //     // Ambil ID dari tabel terkait
    //     // Ambil hanya 200 ID peserta_didik
    //     $bioSantris =  DB::table('santri')->pluck('biodata_id')->toArray();
    //     $lembagaIds = DB::table('lembaga')->pluck('id')->toArray();
    //     $jurusanIds = DB::table('jurusan')->pluck('id')->toArray();
    //     $kelasIds = DB::table('kelas')->pluck('id')->toArray();
    //     $rombelIds = DB::table('rombel')->pluck('id')->toArray();
    //     $wilayahIds = DB::table('wilayah')->pluck('id')->toArray();
    //     $blokIds = DB::table('blok')->pluck('id')->toArray();
    //     $kamarIds = DB::table('kamar')->pluck('id')->toArray();

    //     foreach ($bioSantris as $bioSantri) {

    //         $santriUuid = (string) Str::uuid();

    //         // Tentukan status santri
    //         $statusSantri = $faker->randomElement(['aktif', 'alumni']);

    //         // Buat data santri
    //         DB::table('santri')->insertGetId([
    //             'id' => $santriUuid,
    //             'biodata_id' => $bioSantri,
    //             'nis' => $faker->unique()->numerify('###########'),
    //             'tanggal_masuk' => $faker->date(),
    //             // Set tanggal_keluar hanya jika status alumni
    //             'tanggal_keluar' => $statusSantri === 'alumni' ? $faker->date() : null,
    //             'status' =>  $statusSantri,
    //             'created_by' => 1,
    //             'updated_by' => null,
    //             'deleted_by' => null,
    //             'created_at' => now(),
    //             'updated_at' => now(),
    //         ]);

    //         // Buat satu data riwayat_domisili dengan id_peserta_didik
    //         DB::table('riwayat_domisili')->insert([
    //             'santri_id' => $santriUuid,
    //             'wilayah_id' => $faker->randomElement($wilayahIds),
    //             'blok_id' => $faker->randomElement($blokIds),
    //             'kamar_id' => $faker->randomElement($kamarIds),
    //             'tanggal_masuk' => $faker->dateTime(),
    //             // Set tanggal_keluar hanya jika status santri alumni
    //             'tanggal_keluar' => $statusSantri === 'alumni' ? $faker->dateTime() : null,
    //             'status' => $statusSantri  === 'alumni' ? 'alumni' : 'aktif',
    //             'created_by' => 1,
    //             'updated_by' => null,
    //             'deleted_by' => null,
    //             'created_at' => now(),
    //             'updated_at' => now(),
    //         ]);

    //         // Buat satu data riwayat_pendidikan dengan id_peserta_didik
    //         DB::table('riwayat_pendidikan')->insert([
    //             'santri_id' => $santriUuid,
    //             'no_induk' => $faker->unique()->numerify('###########'),
    //             'lembaga_id' => $faker->randomElement($lembagaIds),
    //             'jurusan_id' => $faker->randomElement($jurusanIds),
    //             'kelas_id' => $faker->randomElement($kelasIds),
    //             'rombel_id' => $faker->randomElement($rombelIds),
    //             'tanggal_masuk' => $faker->date(),
    //             // Set tanggal_keluar hanya jika status pelajar alumni
    //             'tanggal_keluar' => $statusSantri === 'alumni' ? $faker->date() : null,
    //             'status' => $statusSantri === 'alumni' ? 'alumni' : 'aktif',
    //             'created_by' => 1,
    //             'updated_by' => null,
    //             'deleted_by' => null,
    //             'created_at' => now(),
    //             'updated_at' => now(),
    //         ]);
    //     }
    // }

    public function run(): void
    {
        $faker = Factory::create('id_ID');
        $bioIds = DB::table('biodata')->pluck('id')->toArray();
        $lembagaIds = DB::table('lembaga')->pluck('id')->toArray();
        $jurusanIds = DB::table('jurusan')->pluck('id')->toArray();
        $kelasIds = DB::table('kelas')->pluck('id')->toArray();
        $rombelIds = DB::table('rombel')->pluck('id')->toArray();
        $wilayahIds = DB::table('wilayah')->pluck('id')->toArray();
        $blokIds = DB::table('blok')->pluck('id')->toArray();
        $kamarIds = DB::table('kamar')->pluck('id')->toArray();

        // definisikan skenario: kodename => [bolehSantri, statusSantri, bolehPelajar, statusPelajar, weight]
        $scenarios = [
            'active_both' => [true,  'aktif',  true,  'aktif', 40],
            'santri_only_active' => [true,  'aktif',  false, null,   10],
            'santri_only_alumni' => [true,  'alumni', false, null,    5],
            'pelajar_only_active' => [false, null,     true,  'aktif', 10],
            'pelajar_only_alumni' => [false, null,     true,  'alumni', 5],
            'santri_active_pendidikan_alumni' => [true,  'aktif',  true,  'alumni', 10],
            'santri_alumni_pendidikan_active' => [true,  'alumni', true,  'aktif', 10],
            'alumni_both' => [true,  'alumni', true,  'alumni', 10],
        ];

        // buat array weighted untuk pemilihan
        $weighted = [];
        foreach ($scenarios as $key => $cfg) {
            [$s, , , , $weight] = $cfg;
            for ($i = 0; $i < $weight; $i++) {
                $weighted[] = $key;
            }
        }

        foreach (array_slice($bioIds, 0, 200) as $bioId) {
            $kind = $faker->randomElement($weighted);
            [$useSantri, $santriStatus, $usePelajar, $pendidikanStatus] = $scenarios[$kind];

            // UUID baru untuk santri
            $newUuid = (string) Str::uuid();

            // jika termasuk santri, insert ke tabel santri + domisili
            if ($useSantri) {
                DB::table('santri')->insert([
                    'id' => $newUuid,
                    'biodata_id' => $bioId,
                    'nis' => $faker->unique()->numerify('###########'),
                    'tanggal_masuk' => $faker->date(),
                    'tanggal_keluar' => $santriStatus === 'alumni' ? $faker->date() : null,
                    'status' => $santriStatus,
                    'created_by' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('riwayat_domisili')->insert([
                    'santri_id' => $newUuid,
                    'wilayah_id' => $faker->randomElement($wilayahIds),
                    'blok_id' => $faker->randomElement($blokIds),
                    'kamar_id' => $faker->randomElement($kamarIds),
                    'tanggal_masuk' => $faker->dateTime(),
                    'tanggal_keluar' => $santriStatus === 'alumni' ? $faker->dateTime() : null,
                    'status' => $santriStatus,
                    'created_by' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // jika termasuk pelajar **dan** santri, insert riwayat pendidikan
            if ($usePelajar && $useSantri) {
                DB::table('riwayat_pendidikan')->insert([
                    'santri_id' => $newUuid,
                    'no_induk' => $faker->unique()->numerify('###########'),
                    'lembaga_id' => $faker->randomElement($lembagaIds),
                    'jurusan_id' => $faker->randomElement($jurusanIds),
                    'kelas_id' => $faker->randomElement($kelasIds),
                    'rombel_id' => $faker->randomElement($rombelIds),
                    'tanggal_masuk' => $faker->date(),
                    'tanggal_keluar' => $pendidikanStatus === 'alumni' ? $faker->date() : null,
                    'status' => $pendidikanStatus,
                    'created_by' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
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
