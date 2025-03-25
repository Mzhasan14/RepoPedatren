<?php

namespace Database\Seeders;

use Faker\Factory as Faker;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class BioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        // Ambil ID acak dari tabel terkait
        $negaraIds = DB::table('negara')->pluck('id')->toArray();
        $provinsiIds = DB::table('provinsi')->pluck('id')->toArray();
        $kabupatenIds = DB::table('kabupaten')->pluck('id')->toArray();
        $kecamatanIds = DB::table('kecamatan')->pluck('id')->toArray();

        // Ambil data hubungan_keluarga
        $hubunganKeluarga = DB::table('hubungan_keluarga')->get();

        // Loop untuk membuat 200 peserta_didik
        for ($i = 1; $i <= 200; $i++) {
            // Generate nomor KK
            $noKK = $faker->unique()->numerify('###############');

            // Buat biodata untuk peserta_didik
            $biodataId = DB::table('biodata')->insertGetId([
                'id_negara' => $faker->randomElement($negaraIds),
                'id_provinsi' => $faker->randomElement($provinsiIds),
                'id_kabupaten' => $faker->randomElement($kabupatenIds),
                'id_kecamatan' => $faker->randomElement($kecamatanIds),
                'jalan' => $faker->streetAddress,
                'kode_pos' => $faker->postcode,
                'nama' => $faker->name('male'),
                'niup' => $faker->unique()->numerify('###########'),
                'no_passport' => $faker->unique()->numerify('############'),
                'jenis_kelamin' => 'p',
                'tanggal_lahir' => $faker->date(),
                'tempat_lahir' => $faker->city,
                'nik' => $faker->unique()->numerify('###############'),
                'no_telepon' => $faker->phoneNumber,
                'no_telepon_2' => $faker->phoneNumber,
                'email' => $faker->unique()->email,
                'jenjang_pendidikan_terakhir' => $faker->randomElement(['sd/mi', 'smp/mts', 'sma/smk/ma', 'd3', 'd4', 's1', 's2']),
                'nama_pendidikan_terakhir' => $faker->company,
                'anak_keberapa' => $faker->numberBetween(1, 5),
                'dari_saudara' => $faker->numberBetween(1, 5),
                'tinggal_bersama' => $faker->randomElement(['orang tua', 'wali', 'asrama']),
                'smartcard' => $faker->unique()->numerify('############'),
                'status' => true,
                'created_by' => 1,
                'updated_by' => null,
                'deleted_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Buat peserta_didik
            $pesertaDidikId = DB::table('peserta_didik')->insertGetId([
                'id' => (string) Str::uuid(),
                'id_biodata' => $biodataId,
                'status' => true,
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Status hidup/wafat orang tua
            $ayahWafat = $faker->boolean(10);
            $ibuWafat = $faker->boolean(10);

            // Buat orang tua (ayah dan ibu) dengan nomor KK yang sama
            $ayahId = DB::table('biodata')->insertGetId([
                'id_negara' => $faker->randomElement($negaraIds),
                'id_provinsi' => $faker->randomElement($provinsiIds),
                'id_kabupaten' => $faker->randomElement($kabupatenIds),
                'id_kecamatan' => $faker->randomElement($kecamatanIds),
                'jalan' => $faker->streetAddress,
                'kode_pos' => $faker->postcode,
                'nama' => $faker->name('male'),
                'niup' => $faker->unique()->numerify('###########'),
                'no_passport' => $faker->unique()->numerify('############'),
                'jenis_kelamin' => 'l',
                'tanggal_lahir' => $faker->date(),
                'tempat_lahir' => $faker->city,
                'nik' => $faker->unique()->numerify('###############'),
                'no_telepon' => $faker->phoneNumber,
                'no_telepon_2' => $faker->phoneNumber,
                'email' => $faker->unique()->email,
                'jenjang_pendidikan_terakhir' => $faker->randomElement(['sd/mi', 'smp/mts', 'sma/smk/ma', 'd3', 'd4', 's1', 's2']),
                'nama_pendidikan_terakhir' => $faker->company,
                'anak_keberapa' => $faker->numberBetween(1, 5),
                'dari_saudara' => $faker->numberBetween(1, 5),
                'tinggal_bersama' => $faker->randomElement(['orang tua', 'wali', 'asrama']),
                'smartcard' => $faker->unique()->numerify('############'),
                'status' => true,
                'created_by' => 1,
                'updated_by' => null,
                'deleted_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $ibuId = DB::table('biodata')->insertGetId([
                'id_negara' => $faker->randomElement($negaraIds),
                'id_provinsi' => $faker->randomElement($provinsiIds),
                'id_kabupaten' => $faker->randomElement($kabupatenIds),
                'id_kecamatan' => $faker->randomElement($kecamatanIds),
                'jalan' => $faker->streetAddress,
                'kode_pos' => $faker->postcode,
                'nama' => $faker->name('female'),
                'niup' => $faker->unique()->numerify('###########'),
                'no_passport' => $faker->unique()->numerify('############'),
                'jenis_kelamin' => 'p',
                'tanggal_lahir' => $faker->date(),
                'tempat_lahir' => $faker->city,
                'nik' => $faker->unique()->numerify('###############'),
                'no_telepon' => $faker->phoneNumber,
                'no_telepon_2' => $faker->phoneNumber,
                'email' => $faker->unique()->email,
                'jenjang_pendidikan_terakhir' => $faker->randomElement(['sd/mi', 'smp/mts', 'sma/smk/ma', 'd3', 'd4', 's1', 's2']),
                'nama_pendidikan_terakhir' => $faker->company,
                'anak_keberapa' => $faker->numberBetween(1, 5),
                'dari_saudara' => $faker->numberBetween(1, 5),
                'tinggal_bersama' => $faker->randomElement(['orang tua', 'wali', 'asrama']),
                'smartcard' => $faker->unique()->numerify('############'),
                'status' => true,
                'created_by' => 1,
                'updated_by' => null,
                'deleted_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('orang_tua_wali')->insert([
                ['id_biodata' => $ayahId, 'id_hubungan_keluarga' => $hubunganKeluarga->where('nama_status', 'ayah')->first()->id, 'wali' => !$ayahWafat, 'wafat' => $ayahWafat, 'status' => true, 'created_by' => 1],
                ['id_biodata' => $ibuId, 'id_hubungan_keluarga' => $hubunganKeluarga->where('nama_status', 'ibu')->first()->id, 'wali' => $ayahWafat && !$ibuWafat, 'wafat' => $ibuWafat, 'status' => true,  'created_by' => 1]
            ]);

            // Jika kedua orang tua wafat, buat wali
            if ($ayahWafat && $ibuWafat) {
                $waliId = DB::table('biodata')->insertGetId([
                    'id_negara' => $faker->randomElement($negaraIds),
                    'id_provinsi' => $faker->randomElement($provinsiIds),
                    'id_kabupaten' => $faker->randomElement($kabupatenIds),
                    'id_kecamatan' => $faker->randomElement($kecamatanIds),
                    'jalan' => $faker->streetAddress,
                    'kode_pos' => $faker->postcode,
                    'nama' => $faker->name('male'),
                    'niup' => $faker->unique()->numerify('###########'),
                    'no_passport' => $faker->unique()->numerify('############'),
                    'jenis_kelamin' => 'p',
                    'tanggal_lahir' => $faker->date(),
                    'tempat_lahir' => $faker->city,
                    'nik' => $faker->unique()->numerify('###############'),
                    'no_telepon' => $faker->phoneNumber,
                    'no_telepon_2' => $faker->phoneNumber,
                    'email' => $faker->unique()->email,
                    'jenjang_pendidikan_terakhir' => $faker->randomElement(['sd/mi', 'smp/mts', 'sma/smk/ma', 'd3', 'd4', 's1', 's2']),
                    'nama_pendidikan_terakhir' => $faker->company,
                    'anak_keberapa' => $faker->numberBetween(1, 5),
                    'dari_saudara' => $faker->numberBetween(1, 5),
                    'tinggal_bersama' => $faker->randomElement(['orang tua', 'wali', 'asrama']),
                    'smartcard' => $faker->unique()->numerify('############'),
                    'status' => true,
                    'created_by' => 1,
                    'updated_by' => null,
                    'deleted_by' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('orang_tua_wali')->insert([
                    'id_biodata' => $waliId,
                    'id_hubungan_keluarga' => $hubunganKeluarga->where('nama_status', 'wali')->first()->id,
                    'wali' => true,
                    'wafat' => false,
                    'status' => true,
                    'created_by' => 1,
                ]);
            }

            // Tambahkan keluarga dengan nomor KK yang sama
            DB::table('keluarga')->insert([
                ['no_kk' => $noKK, 'id_biodata' => $biodataId, 'status' => true, 'created_by' => 1,],
                ['no_kk' => $noKK, 'id_biodata' => $ayahId, 'status' => true, 'created_by' => 1,],
                ['no_kk' => $noKK, 'id_biodata' => $ibuId, 'status' => true, 'created_by' => 1,]
            ]);

            if (isset($waliId)) {
                DB::table('keluarga')->insert([
                    'no_kk' => $noKK,
                    'id_biodata' => $waliId,
                    'status' => true,
                    'created_by' => 1,
                ]);
            }
        }
    }
}
