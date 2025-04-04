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
                    'no_induk' => $faker->unique()->numerify('###########'),
                    'angkatan_pelajar' => $faker->year,
                    'tanggal_masuk_pelajar' => $faker->date(),
                    // Set tanggal_keluar_pelajar hanya jika status alumni
                    'tanggal_keluar_pelajar' => $statusPelajar === 'alumni' ? $faker->date() : null,
                    'status_pelajar' => $statusPelajar,
                    'created_by' => 1,
                    'updated_by' => null,
                    'deleted_by' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
    
                // Buat satu data pendidikan_pelajar dengan id_pelajar
                DB::table('pendidikan_pelajar')->insert([
                    'id_pelajar' => $pelajarUuid,
                    'id_lembaga' => $faker->randomElement($lembagaIds),
                    'id_jurusan' => $faker->randomElement($jurusanIds),
                    'id_kelas' => $faker->randomElement($kelasIds),
                    'id_rombel' => $faker->randomElement($rombelIds),
                    'status' => true,
                    'tanggal_masuk' => $faker->dateTime(),
                    // Set tanggal_keluar hanya jika status pelajar alumni
                    'tanggal_keluar' => $statusPelajar === 'alumni' ? $faker->dateTime() : null,
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
                    'angkatan_santri' => $faker->year,
                    'tanggal_masuk_santri' => $faker->date(),
                    // Set tanggal_keluar_santri hanya jika status alumni
                    'tanggal_keluar_santri' => $statusSantri === 'alumni' ? $faker->date() : null,
                    'status_santri' =>  $statusSantri,
                    'created_by' => 1,
                    'updated_by' => null,
                    'deleted_by' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
    
                // Buat satu data domisili_santri dengan id_santri
                DB::table('domisili_santri')->insert([
                    'id_santri' => $santriUuid,
                    'id_wilayah' => $faker->randomElement($wilayahIds),
                    'id_blok' => $faker->randomElement($blokIds),
                    'id_kamar' => $faker->randomElement($kamarIds),
                    'tanggal_masuk' => $faker->dateTime(),
                    // Set tanggal_keluar hanya jika status santri alumni
                    'tanggal_keluar' => $statusSantri === 'alumni' ? $faker->dateTime() : null,
                    'status' => true,
                    'created_by' => 1,
                    'updated_by' => null,
                    'deleted_by' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
    
}
