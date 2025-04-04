<?php

namespace Database\Seeders;

use Faker\Factory as Faker;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DataKeluargaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        $negaraIds    = DB::table('negara')->pluck('id')->toArray();
        $provinsiIds  = DB::table('provinsi')->pluck('id')->toArray();
        $kabupatenIds = DB::table('kabupaten')->pluck('id')->toArray();
        $kecamatanIds = DB::table('kecamatan')->pluck('id')->toArray();
        
        $hubunganKeluarga = DB::table('hubungan_keluarga')->get();
        $ayahStatus = $hubunganKeluarga->where('nama_status', 'ayah')->first()->id;
        $ibuStatus  = $hubunganKeluarga->where('nama_status', 'ibu')->first()->id;
        $waliStatus = $hubunganKeluarga->where('nama_status', 'wali')->first()->id;
        
        // Variabel untuk sibling group
        $siblingGroup    = false;
        $currentNoKK     = null;
        $currentAyahId   = null;
        $currentIbuId    = null;
        
        for ($i = 1; $i <= 200; $i++) {
            // Jika tidak sedang dalam sibling group, buat data orang tua baru
            if (!$siblingGroup) {
                // Tentukan apakah akan memulai sibling group (misal: 30% kemungkinan)
                if ($faker->boolean(30)) {
                    $siblingGroup = true;
                }
                // Buat no_kk baru untuk peserta didik (dan orang tua) ini
                $currentNoKK = $faker->numerify('###############');
        
                // Tentukan status ayah (apakah wafat) 
                $ayahWafat = $faker->boolean(10);
                // Meskipun ada kemungkinan ibu wafat, namun karena ketentuannya setiap peserta didik wajib punya ayah dan ibu,
                // kita buat kedua data. Untuk kolom _wali_:
                // - Jika ayah hidup (tidak wafat), maka ayah yang jadi wali (wali = true)
                // - Jika ayah wafat, maka ibu yang jadi wali (wali = true)
                $ibuWafat = $faker->boolean(10);
        
                // Insert data biodata untuk ayah
                $currentAyahId = DB::table('biodata')->insertGetId([
                    'id_negara'               => $faker->randomElement($negaraIds),
                    'id_provinsi'             => $faker->randomElement($provinsiIds),
                    'id_kabupaten'            => $faker->randomElement($kabupatenIds),
                    'id_kecamatan'            => $faker->randomElement($kecamatanIds),
                    'jalan'                   => $faker->streetAddress,
                    'kode_pos'                => $faker->postcode,
                    'nama'                    => $faker->name('male'),
                    'no_passport'             => $faker->numerify('############'),
                    'jenis_kelamin'           => 'l',
                    'tanggal_lahir'           => $faker->date(),
                    'tempat_lahir'            => $faker->city,
                    'nik'                     => $faker->numerify('###############'),
                    'no_telepon'              => $faker->phoneNumber,
                    'no_telepon_2'            => $faker->phoneNumber,
                    'email'                   => $faker->unique()->email,
                    'jenjang_pendidikan_terakhir'=> $faker->randomElement(['sd/mi', 'smp/mts', 'sma/smk/ma', 'd3', 'd4', 's1', 's2']),
                    'nama_pendidikan_terakhir'=> $faker->company,
                    'anak_keberapa'           => $faker->numberBetween(1, 5),
                    'dari_saudara'            => $faker->numberBetween(1, 5),
                    'tinggal_bersama'         => $faker->randomElement(['orang tua', 'wali', 'asrama']),
                    'smartcard'               => $faker->numerify('############'),
                    'status'                  => true,
                    'wafat'                   => $ayahWafat,
                    'created_by'              => 1,
                    'updated_by'              => null,
                    'deleted_by'              => null,
                    'created_at'              => now(),
                    'updated_at'              => now(),
                ]);
                // Insert data biodata untuk ibu
                $currentIbuId = DB::table('biodata')->insertGetId([
                    'id_negara'               => $faker->randomElement($negaraIds),
                    'id_provinsi'             => $faker->randomElement($provinsiIds),
                    'id_kabupaten'            => $faker->randomElement($kabupatenIds),
                    'id_kecamatan'            => $faker->randomElement($kecamatanIds),
                    'jalan'                   => $faker->streetAddress,
                    'kode_pos'                => $faker->postcode,
                    'nama'                    => $faker->name('male'),
                    'no_passport'             => $faker->numerify('############'),
                    'jenis_kelamin'           => 'p',
                    'tanggal_lahir'           => $faker->date(),
                    'tempat_lahir'            => $faker->city,
                    'nik'                     => $faker->numerify('###############'),
                    'no_telepon'              => $faker->phoneNumber,
                    'no_telepon_2'            => $faker->phoneNumber,
                    'email'                   => $faker->unique()->email,
                    'jenjang_pendidikan_terakhir'=> $faker->randomElement(['sd/mi', 'smp/mts', 'sma/smk/ma', 'd3', 'd4', 's1', 's2']),
                    'nama_pendidikan_terakhir'=> $faker->company,
                    'anak_keberapa'           => $faker->numberBetween(1, 5),
                    'dari_saudara'            => $faker->numberBetween(1, 5),
                    'tinggal_bersama'         => $faker->randomElement(['orang tua', 'wali', 'asrama']),
                    'smartcard'               => $faker->numerify('############'),
                    'status'                  => true,
                    'wafat'                   => $ibuWafat,
                    'created_by'              => 1,
                    'updated_by'              => null,
                    'deleted_by'              => null,
                    'created_at'              => now(),
                    'updated_at'              => now(),
                ]);
                
                // Insert record orang_tua_wali untuk ayah dan ibu
                DB::table('orang_tua_wali')->insert([
                    [
                        'id_biodata'            => $currentAyahId,
                        'id_hubungan_keluarga'  => $ayahStatus,
                        'pekerjaan'             => $faker->jobTitle(),
                        'penghasilan'           => $faker->randomElement(['500000', '1000000', '2000000']),
                        'wali'                  => !$ayahWafat,  // jika ayah hidup, wali true untuk ayah
                        'status'                => true,
                        'created_by'            => 1
                    ],
                    [
                        'id_biodata'            => $currentIbuId,
                        'id_hubungan_keluarga'  => $ibuStatus,
                        'pekerjaan'             => $faker->jobTitle(),
                        'penghasilan'           => $faker->randomElement(['500000', '1000000', '2000000']),
                        'wali'                  => $ayahWafat,  // jika ayah wafat, maka ibu jadi wali
                        'status'                => true,
                        'created_by'            => 1
                    ]
                ]);
                
                // Masukkan data keluarga untuk orang tua (gunakan no_kk yang sama)
                DB::table('keluarga')->insert([
                    ['no_kk' => $currentNoKK, 'id_biodata' => $currentAyahId, 'status' => true, 'created_by' => 1],
                    ['no_kk' => $currentNoKK, 'id_biodata' => $currentIbuId, 'status' => true, 'created_by' => 1],
                ]);
            } 
            // Jika sedang dalam sibling group, maka gunakan data orang tua dan no_kk yang sudah ada
            else {
                // Dengan probabilitas 70% sibling group masih berlanjut; jika tidak, akhiri group.
                if (!$faker->boolean(70)) {
                    $siblingGroup = false;
                }
                // $currentNoKK, $currentAyahId, dan $currentIbuId sudah tersedia dari awal group
            }
            
            // ---------------------------
            // Insert biodata peserta didik (anak)
            $childBiodataId = DB::table('biodata')->insertGetId([
                'id_negara'               => $faker->randomElement($negaraIds),
                'id_provinsi'             => $faker->randomElement($provinsiIds),
                'id_kabupaten'            => $faker->randomElement($kabupatenIds),
                'id_kecamatan'            => $faker->randomElement($kecamatanIds),
                'jalan'                   => $faker->streetAddress,
                'kode_pos'                => $faker->postcode,
                'nama'                    => $faker->name('male'),
                'no_passport'             => $faker->numerify('############'),
                'jenis_kelamin'           => 'p',
                'tanggal_lahir'           => $faker->date(),
                'tempat_lahir'            => $faker->city,
                'nik'                     => $faker->numerify('###############'),
                'no_telepon'             => $faker->phoneNumber,
                'no_telepon_2'           => $faker->phoneNumber,
                'email'                   => $faker->unique()->email,
                'jenjang_pendidikan_terakhir'=> $faker->randomElement(['sd/mi', 'smp/mts', 'sma/smk/ma', 'd3', 'd4', 's1', 's2']),
                'nama_pendidikan_terakhir'=> $faker->company,
                'anak_keberapa'           => $faker->numberBetween(1, 5),
                'dari_saudara'            => $faker->numberBetween(1, 5),
                'tinggal_bersama'         => $faker->randomElement(['orang tua', 'wali', 'asrama']),
                'smartcard'               => $faker->numerify('############'),
                'status'                  => true,
                'created_by'              => 1,
                'updated_by'              => null,
                'deleted_by'              => null,
                'created_at'              => now(),
                'updated_at'              => now(),
            ]);
            
            // Insert peserta didik (anak)
            DB::table('peserta_didik')->insertGetId([
                'id'          => (string) Str::uuid(),
                'id_biodata'  => $childBiodataId,
                'status'      => true,
                'created_by'  => 1,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
            
            // Masukkan data keluarga untuk anak (gunakan no_kk yang sama dengan orang tua)
            DB::table('keluarga')->insert([
                ['no_kk' => $currentNoKK, 'id_biodata' => $childBiodataId, 'status' => true, 'created_by' => 1],
            ]);
        }
        
    }        

    // {
    //     $faker = Faker::create('id_ID');

    //     // Ambil ID acak dari tabel terkait
    //     $negaraIds = DB::table('negara')->pluck('id')->toArray();
    //     $provinsiIds = DB::table('provinsi')->pluck('id')->toArray();
    //     $kabupatenIds = DB::table('kabupaten')->pluck('id')->toArray();
    //     $kecamatanIds = DB::table('kecamatan')->pluck('id')->toArray();

    //     // Ambil data hubungan_keluarga
    //     $hubunganKeluarga = DB::table('hubungan_keluarga')->get();

    //     // Loop untuk membuat 200 peserta_didik
    //     for ($i = 1; $i <= 200; $i++) {
    //         // Generate nomor KK
    //         $noKK = $faker->unique()->numerify('###############');

    //         // Buat biodata untuk peserta_didik
    //         $biodataId = DB::table('biodata')->insertGetId([
    //             'id_negara' => $faker->randomElement($negaraIds),
    //             'id_provinsi' => $faker->randomElement($provinsiIds),
    //             'id_kabupaten' => $faker->randomElement($kabupatenIds),
    //             'id_kecamatan' => $faker->randomElement($kecamatanIds),
    //             'jalan' => $faker->streetAddress,
    //             'kode_pos' => $faker->postcode,
    //             'nama' => $faker->name('male'),
    // 'niup' => $faker->unique()->numerify('###########'),
    //             'no_passport' => $faker->unique()->numerify('############'),
    //             'jenis_kelamin' => 'p',
    //             'tanggal_lahir' => $faker->date(),
    //             'tempat_lahir' => $faker->city,
    //             'nik' => $faker->unique()->numerify('###############'),
    //             'no_telepon' => $faker->phoneNumber,
    //             'no_telepon_2' => $faker->phoneNumber,
    //             'email' => $faker->unique()->email,
    //             'jenjang_pendidikan_terakhir' => $faker->randomElement(['sd/mi', 'smp/mts', 'sma/smk/ma', 'd3', 'd4', 's1', 's2']),
    //             'nama_pendidikan_terakhir' => $faker->company,
    //             'anak_keberapa' => $faker->numberBetween(1, 5),
    //             'dari_saudara' => $faker->numberBetween(1, 5),
    //             'tinggal_bersama' => $faker->randomElement(['orang tua', 'wali', 'asrama']),
    //             'smartcard' => $faker->unique()->numerify('############'),
    //             'status' => true,
    //             'created_by' => 1,
    //             'updated_by' => null,
    //             'deleted_by' => null,
    //             'created_at' => now(),
    //             'updated_at' => now(),
    //         ]);

    //         // Buat peserta_didik
    //         $pesertaDidikId = DB::table('peserta_didik')->insertGetId([
    //             'id' => (string) Str::uuid(),
    //             'id_biodata' => $biodataId,
    //             'status' => true,
    //             'created_by' => 1,
    //             'created_at' => now(),
    //             'updated_at' => now(),
    //         ]);

    //         // Status hidup/wafat orang tua
    //         $ayahWafat = $faker->boolean(10);
    //         $ibuWafat = $faker->boolean(10);

    //         // Buat orang tua (ayah dan ibu) dengan nomor KK yang sama
    //         $ayahId = DB::table('biodata')->insertGetId([
    //             'id_negara' => $faker->randomElement($negaraIds),
    //             'id_provinsi' => $faker->randomElement($provinsiIds),
    //             'id_kabupaten' => $faker->randomElement($kabupatenIds),
    //             'id_kecamatan' => $faker->randomElement($kecamatanIds),
    //             'jalan' => $faker->streetAddress,
    //             'kode_pos' => $faker->postcode,
    //             'nama' => $faker->name('male'),
    //             'niup' => $faker->unique()->numerify('###########'),
    //             'no_passport' => $faker->unique()->numerify('############'),
    //             'jenis_kelamin' => 'l',
    //             'tanggal_lahir' => $faker->date(),
    //             'tempat_lahir' => $faker->city,
    //             'nik' => $faker->unique()->numerify('###############'),
    //             'no_telepon' => $faker->phoneNumber,
    //             'no_telepon_2' => $faker->phoneNumber,
    //             'email' => $faker->unique()->email,
    //             'jenjang_pendidikan_terakhir' => $faker->randomElement(['sd/mi', 'smp/mts', 'sma/smk/ma', 'd3', 'd4', 's1', 's2']),
    //             'nama_pendidikan_terakhir' => $faker->company,
    //             'anak_keberapa' => $faker->numberBetween(1, 5),
    //             'dari_saudara' => $faker->numberBetween(1, 5),
    //             'tinggal_bersama' => $faker->randomElement(['orang tua', 'wali', 'asrama']),
    //             'smartcard' => $faker->unique()->numerify('############'),
    //             'status' => true,
    //             'created_by' => 1,
    //             'updated_by' => null,
    //             'deleted_by' => null,
    //             'created_at' => now(),
    //             'updated_at' => now(),
    //         ]);

    //         $ibuId = DB::table('biodata')->insertGetId([
    //             'id_negara' => $faker->randomElement($negaraIds),
    //             'id_provinsi' => $faker->randomElement($provinsiIds),
    //             'id_kabupaten' => $faker->randomElement($kabupatenIds),
    //             'id_kecamatan' => $faker->randomElement($kecamatanIds),
    //             'jalan' => $faker->streetAddress,
    //             'kode_pos' => $faker->postcode,
    //             'nama' => $faker->name('female'),
    //             'niup' => $faker->unique()->numerify('###########'),
    //             'no_passport' => $faker->unique()->numerify('############'),
    //             'jenis_kelamin' => 'p',
    //             'tanggal_lahir' => $faker->date(),
    //             'tempat_lahir' => $faker->city,
    //             'nik' => $faker->unique()->numerify('###############'),
    //             'no_telepon' => $faker->phoneNumber,
    //             'no_telepon_2' => $faker->phoneNumber,
    //             'email' => $faker->unique()->email,
    //             'jenjang_pendidikan_terakhir' => $faker->randomElement(['sd/mi', 'smp/mts', 'sma/smk/ma', 'd3', 'd4', 's1', 's2']),
    //             'nama_pendidikan_terakhir' => $faker->company,
    //             'anak_keberapa' => $faker->numberBetween(1, 5),
    //             'dari_saudara' => $faker->numberBetween(1, 5),
    //             'tinggal_bersama' => $faker->randomElement(['orang tua', 'wali', 'asrama']),
    //             'smartcard' => $faker->unique()->numerify('############'),
    //             'status' => true,
    //             'created_by' => 1,
    //             'updated_by' => null,
    //             'deleted_by' => null,
    //             'created_at' => now(),
    //             'updated_at' => now(),
    //         ]);

    //         DB::table('orang_tua_wali')->insert([
    //             ['id_biodata' => $ayahId, 'id_hubungan_keluarga' => $hubunganKeluarga->where('nama_status', 'ayah')->first()->id, 'wali' => !$ayahWafat, 'wafat' => $ayahWafat, 'status' => true, 'created_by' => 1],
    //             ['id_biodata' => $ibuId, 'id_hubungan_keluarga' => $hubunganKeluarga->where('nama_status', 'ibu')->first()->id, 'wali' => $ayahWafat && !$ibuWafat, 'wafat' => $ibuWafat, 'status' => true,  'created_by' => 1]
    //         ]);

    //         // Jika kedua orang tua wafat, buat wali
    //         if ($ayahWafat && $ibuWafat) {
    //             $waliId = DB::table('biodata')->insertGetId([
    //                 'id_negara' => $faker->randomElement($negaraIds),
    //                 'id_provinsi' => $faker->randomElement($provinsiIds),
    //                 'id_kabupaten' => $faker->randomElement($kabupatenIds),
    //                 'id_kecamatan' => $faker->randomElement($kecamatanIds),
    //                 'jalan' => $faker->streetAddress,
    //                 'kode_pos' => $faker->postcode,
    //                 'nama' => $faker->name('male'),
    //                 'niup' => $faker->unique()->numerify('###########'),
    //                 'no_passport' => $faker->unique()->numerify('############'),
    //                 'jenis_kelamin' => 'p',
    //                 'tanggal_lahir' => $faker->date(),
    //                 'tempat_lahir' => $faker->city,
    //                 'nik' => $faker->unique()->numerify('###############'),
    //                 'no_telepon' => $faker->phoneNumber,
    //                 'no_telepon_2' => $faker->phoneNumber,
    //                 'email' => $faker->unique()->email,
    //                 'jenjang_pendidikan_terakhir' => $faker->randomElement(['sd/mi', 'smp/mts', 'sma/smk/ma', 'd3', 'd4', 's1', 's2']),
    //                 'nama_pendidikan_terakhir' => $faker->company,
    //                 'anak_keberapa' => $faker->numberBetween(1, 5),
    //                 'dari_saudara' => $faker->numberBetween(1, 5),
    //                 'tinggal_bersama' => $faker->randomElement(['orang tua', 'wali', 'asrama']),
    //                 'smartcard' => $faker->unique()->numerify('############'),
    //                 'status' => true,
    //                 'created_by' => 1,
    //                 'updated_by' => null,
    //                 'deleted_by' => null,
    //                 'created_at' => now(),
    //                 'updated_at' => now(),
    //             ]);

    //             DB::table('orang_tua_wali')->insert([
    //                 'id_biodata' => $waliId,
    //                 'id_hubungan_keluarga' => $hubunganKeluarga->where('nama_status', 'wali')->first()->id,
    //                 'wali' => true,
    //                 'wafat' => false,
    //                 'status' => true,
    //                 'created_by' => 1,
    //             ]);
    //         }

    //         // Tambahkan keluarga dengan nomor KK yang sama
    //         DB::table('keluarga')->insert([
    //             ['no_kk' => $noKK, 'id_biodata' => $biodataId, 'status' => true, 'created_by' => 1,],
    //             ['no_kk' => $noKK, 'id_biodata' => $ayahId, 'status' => true, 'created_by' => 1,],
    //             ['no_kk' => $noKK, 'id_biodata' => $ibuId, 'status' => true, 'created_by' => 1,]
    //         ]);

    //         if (isset($waliId)) {
    //             DB::table('keluarga')->insert([
    //                 'no_kk' => $noKK,
    //                 'id_biodata' => $waliId,
    //                 'status' => true,
    //                 'created_by' => 1,
    //             ]);
    //         }
    //     }
    // }
}
