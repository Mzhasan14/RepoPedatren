<?php

namespace Database\Seeders;

use App\Models\Santri;
use App\Models\Pelajar;
use App\Models\Peserta_didik;
use App\Models\DomisiliSantri;
use Illuminate\Database\Seeder;
use App\Models\Kewilayahan\Blok;
use App\Models\Pendidikan\Kelas;
use App\Models\Kewilayahan\Kamar;
use App\Models\Pendidikan\Rombel;
use App\Models\PendidikanPelajar;
use App\Models\Pendidikan\Jurusan;
use App\Models\Pendidikan\Lembaga;
use Illuminate\Support\Facades\DB;
use App\Models\Kewilayahan\Wilayah;
use App\Models\Kewilayahan\Domisili;
use Database\Factories\Kewilayahan\BlokFactory;
use Database\Factories\Pendidikan\KelasFactory;
use Database\Factories\Kewilayahan\KamarFactory;
use Database\Factories\Pendidikan\RombelFactory;
use Database\Factories\Pendidikan\JurusanFactory;
use Database\Factories\Pendidikan\LembagaFactory;
use Database\Factories\Kewilayahan\WilayahFactory;
use Database\Factories\Kewilayahan\DomisiliFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Faker\Factory as Faker;
class PelajarSantriSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        // Ambil ID acak dari tabel terkait
        $pesertaDidikIds = DB::table('peserta_didik')->pluck('id')->toArray();
        $lembagaIds = DB::table('lembaga')->pluck('id')->toArray();
        $jurusanIds = DB::table('jurusan')->pluck('id')->toArray();
        $kelasIds = DB::table('kelas')->pluck('id')->toArray();
        $rombelIds = DB::table('rombel')->pluck('id')->toArray();
        $wilayahIds = DB::table('wilayah')->pluck('id')->toArray();
        $blokIds = DB::table('blok')->pluck('id')->toArray();
        $kamarIds = DB::table('kamar')->pluck('id')->toArray();

        foreach ($pesertaDidikIds as $pesertaDidikId) {
            // Tentukan apakah peserta_didik akan menjadi pelajar atau santri, tetapi tidak keduanya
            $isPelajar = $faker->boolean(50); // 50% kemungkinan menjadi pelajar

            if ($isPelajar) {
                // Buat data pelajar
                DB::table('pelajar')->insert([
                    'id_peserta_didik' => $pesertaDidikId,
                    'no_induk' => $faker->unique()->numerify('###########'),
                    'angkatan_pelajar' => $faker->year,
                    'tanggal_masuk_pelajar' => $faker->date(),
                    'tanggal_keluar_pelajar' => $faker->optional()->date(),
                    'status_pelajar' => $faker->randomElement(['aktif', 'alumni']),
                    'created_by' => 1,
                    'updated_by' => null,
                    'deleted_by' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Buat satu data pendidikan_pelajar
                DB::table('pendidikan_pelajar')->insert([
                    'id_peserta_didik' => $pesertaDidikId,
                    'id_lembaga' => $faker->randomElement($lembagaIds),
                    'id_jurusan' => $faker->randomElement($jurusanIds),
                    'id_kelas' => $faker->randomElement($kelasIds),
                    'id_rombel' => $faker->randomElement($rombelIds),
                    'status' => $faker->randomElement(['aktif']),
                    'tanggal_masuk' => $faker->dateTime(),
                    'tanggal_keluar' => $faker->optional()->dateTime(),
                    'created_by' => 1,
                    'updated_by' => null,
                    'deleted_by' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                // Buat data santri
                DB::table('santri')->insert([
                    'id_peserta_didik' => $pesertaDidikId,
                    'nis' => $faker->unique()->numerify('###########'),
                    'angkatan_santri' => $faker->year,
                    'tanggal_masuk_santri' => $faker->date(),
                    'tanggal_keluar_santri' => $faker->optional()->date(),
                    'status_santri' => $faker->randomElement(['aktif', 'alumni']),
                    'created_by' => 1,
                    'updated_by' => null,
                    'deleted_by' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Buat satu data domisili_santri
                DB::table('domisili_santri')->insert([
                    'id_peserta_didik' => $pesertaDidikId,
                    'id_wilayah' => $faker->randomElement($wilayahIds),
                    'id_blok' => $faker->randomElement($blokIds),
                    'id_kamar' => $faker->randomElement($kamarIds),
                    'tanggal_masuk' => $faker->dateTime(),
                    'tanggal_keluar' => $faker->optional()->dateTime(),
                    'status' => $faker->randomElement(['aktif']),
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
