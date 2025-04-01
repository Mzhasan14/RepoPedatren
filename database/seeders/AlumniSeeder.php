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
    public function run(): void
    {
        $faker = Faker::create();

        // Ambil data referensi dari database
        $negaraIds    = DB::table('negara')->pluck('id')->toArray();
        $provinsiIds  = DB::table('provinsi')->pluck('id')->toArray();
        $kabupatenIds = DB::table('kabupaten')->pluck('id')->toArray();
        $kecamatanIds = DB::table('kecamatan')->pluck('id')->toArray();

        $lembagaIds = DB::table('lembaga')->pluck('id')->toArray();
        $jurusanIds = DB::table('jurusan')->pluck('id')->toArray();
        $kelasIds   = DB::table('kelas')->pluck('id')->toArray();
        $rombelIds  = DB::table('rombel')->pluck('id')->toArray();
        $wilayahIds = DB::table('wilayah')->pluck('id')->toArray();
        $blokIds    = DB::table('blok')->pluck('id')->toArray();
        $kamarIds   = DB::table('kamar')->pluck('id')->toArray();

        for ($i = 1; $i <= 100; $i++) {
            // Membuat data biodata dengan Faker dan mengambil data referensi secara acak
            $biodataId = DB::table('biodata')->insertGetId([
                'id_negara'                   => $faker->randomElement($negaraIds),
                'id_provinsi'                 => $faker->randomElement($provinsiIds),
                'id_kabupaten'                => $faker->randomElement($kabupatenIds),
                'id_kecamatan'                => $faker->randomElement($kecamatanIds),
                'jalan'                       => $faker->streetAddress,
                'kode_pos'                    => $faker->postcode,
                'nama'                        => $faker->name,
                'no_passport'                 => null,
                'jenis_kelamin'               => $faker->randomElement(['l', 'p']),
                'tanggal_lahir'               => $faker->date('Y-m-d', '2000-01-01'),
                'tempat_lahir'                => $faker->city,
                'nik'                         => $faker->numerify('################'),
                'no_telepon'                  => $faker->phoneNumber,
                'no_telepon_2'                => $faker->phoneNumber,
                'email'                       => $faker->unique()->safeEmail,
                'jenjang_pendidikan_terakhir' => $faker->randomElement(['paud', 'sd/mi', 'smp/mts', 'sma/smk/ma', 'd3', 'd4', 's1', 's2']),
                'nama_pendidikan_terakhir'    => $faker->company,
                'anak_keberapa'               => $faker->numberBetween(1, 5),
                'dari_saudara'                => $faker->numberBetween(1, 5),
                'tinggal_bersama'             => $faker->randomElement(['Orang Tua', 'Wali', 'Sendiri']),
                'smartcard'                   => null,
                'status'                      => 1,
                'wafat'                       => false,
                'created_by'                  => 1,
                'updated_by'                  => null,
                'deleted_by'                  => null,
                'created_at'                  => Carbon::now(),
                'updated_at'                  => Carbon::now(),
            ]);

            // Membuat data peserta didik dan relasikan ke biodata
            $pesertaDidikId = (string) Str::uuid();
            DB::table('peserta_didik')->insert([
                'id'          => $pesertaDidikId,
                'id_biodata'  => $biodataId,
                'status'      => 1,
                'created_by'  => 1,
                'updated_by'  => null,
                'deleted_by'  => null,
                'created_at'  => Carbon::now(),
                'updated_at'  => Carbon::now(),
            ]);

            // Membuat data alumni pelajar
            $idPelajar = (string) Str::uuid();
            DB::table('alumni_pelajar')->insert([
                'id_pelajar'             => $idPelajar,
                'id_peserta_didik'       => $pesertaDidikId,
                'no_induk'               => 'P' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'angkatan_pelajar'       => $faker->year,
                'tanggal_masuk_pelajar'  => $faker->date('Y-m-d', '2010-01-01'),
                'tanggal_keluar_pelajar' => $faker->date('Y-m-d', '2015-12-31'),
                'created_by'             => 1,
                'updated_by'             => null,
                'deleted_by'             => null,
                'created_at'             => Carbon::now(),
                'updated_at'             => Carbon::now(),
            ]);

            // Membuat data alumni santri
            $idSantri = (string) Str::uuid();
            DB::table('alumni_santri')->insert([
                'id_santri'            => $idSantri,
                'id_peserta_didik'     => $pesertaDidikId,
                'nis'                  => 'S' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'angkatan_santri'      => $faker->year,
                'tanggal_masuk_santri' => $faker->date('Y-m-d', '2005-01-01'),
                'tanggal_keluar_santri'=> $faker->date('Y-m-d', '2010-12-31'),
                'created_by'           => 1,
                'updated_by'           => null,
                'deleted_by'           => null,
                'created_at'           => Carbon::now(),
                'updated_at'           => Carbon::now(),
            ]);

            // Membuat data pendidikan alumni untuk alumni pelajar
            DB::table('pendidikan_alumni')->insert([
                'id_pelajar'    => $idPelajar,
                'id_lembaga'    => $faker->randomElement($lembagaIds),
                'id_jurusan'    => $faker->randomElement($jurusanIds),
                'id_kelas'      => $faker->randomElement($kelasIds),
                'id_rombel'     => $faker->randomElement($rombelIds),
                'status'        => 'keluar',
                'tanggal_masuk' => $faker->dateTimeBetween('-10 years', '-5 years'),
                'tanggal_keluar'=> $faker->dateTimeBetween('-4 years', 'now'),
                'created_by'    => 1,
                'updated_by'    => null,
                'deleted_by'    => null,
                'created_at'    => Carbon::now(),
                'updated_at'    => Carbon::now(),
            ]);

            // Membuat data domisili alumni untuk alumni santri
            DB::table('domisili_alumni')->insert([
                'id_santri'     => $idSantri,
                'id_wilayah'    => $faker->randomElement($wilayahIds),
                'id_blok'       => $faker->randomElement($blokIds),
                'id_kamar'      => $faker->randomElement($kamarIds),
                'tanggal_masuk' => $faker->dateTimeBetween('-5 years', '-2 years'),
                'tanggal_keluar'=> $faker->dateTimeBetween('-1 years', 'now'),
                'status'        => 'keluar',
                'created_by'    => 1,
                'updated_by'    => null,
                'deleted_by'    => null,
                'created_at'    => Carbon::now(),
                'updated_at'    => Carbon::now(),
            ]);
        }
    }
}
