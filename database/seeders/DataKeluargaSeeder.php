<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory;
use Illuminate\Support\Str;

class DataKeluargaSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Factory::create('id_ID');

        // Ambil semua data wilayah dalam collection keyed by id dan indexed by parent key untuk filter mudah
        $negaraList = DB::table('negara')->get();
        $provinsiList = DB::table('provinsi')->get();
        $kabupatenList = DB::table('kabupaten')->get();
        $kecamatanList = DB::table('kecamatan')->get();

        $hk = DB::table('hubungan_keluarga')->get();
        $ayahStatus = $hk->firstWhere('nama_status', 'ayah kandung')->id;
        $ibuStatus = $hk->firstWhere('nama_status', 'ibu kandung')->id;

        $pegawaiBiodataIds = DB::table('pegawai')->pluck('biodata_id')->toArray();

        $lembagaIds = DB::table('lembaga')->pluck('id')->toArray();
        $jurusanIds = DB::table('jurusan')->pluck('id')->toArray();
        $angkatanList = DB::table('angkatan')->get(); // ambil seluruh angkatan
        $kelasIds = DB::table('kelas')->pluck('id')->toArray();
        $rombelIds = DB::table('rombel')->pluck('id')->toArray();
        $wilayahIds = DB::table('wilayah')->pluck('id')->toArray();
        $blokIds = DB::table('blok')->pluck('id')->toArray();
        $kamarIds = DB::table('kamar')->pluck('id')->toArray();

        $scenarios = [
            'active_both'                     => [true,  'aktif',  true,  'aktif', 40],
            'santri_only_active'              => [true,  'aktif',  false, null,   10],
            'santri_only_alumni'              => [true,  'alumni', false, null,    5],
            'pelajar_only_active'             => [false, null,     true,  'aktif', 10],
            'pelajar_only_alumni'             => [false, null,     true,  'alumni', 5],
            'santri_active_pendidikan_alumni' => [true,  'aktif',  true,  'alumni', 10],
            'santri_alumni_pendidikan_active' => [true,  'alumni', true,  'aktif', 10],
            'alumni_both'                     => [true,  'alumni', true,  'alumni', 10],
        ];

        $weighted = [];
        foreach ($scenarios as $key => $cfg) {
            for ($j = 0; $j < $cfg[4]; $j++) {
                $weighted[] = $key;
            }
        }

        $keluargaTersimpan = [];

        for ($i = 1; $i <= 200; $i++) {
            $gunakanKeluargaLama = $faker->boolean(15);

            if ($gunakanKeluargaLama && count($keluargaTersimpan) > 0) {
                // Ambil keluarga lama
                $keluarga = $faker->randomElement($keluargaTersimpan);
                $currentNoKK = $keluarga['no_kk'];
                $currentAyahId = $keluarga['ayah_id'];
                $currentIbuId = $keluarga['ibu_id'];

                // Ambil wilayah dari ayah (atau ibu) yang sudah tersimpan
                $ayahBiodata = DB::table('biodata')->where('id', $currentAyahId)->first();
                $negaraId = $ayahBiodata->negara_id;
                $provinsiId = $ayahBiodata->provinsi_id;
                $kabupatenId = $ayahBiodata->kabupaten_id;
                $kecamatanId = $ayahBiodata->kecamatan_id;

                $provinsi = $provinsiList->firstWhere('id', $provinsiId);
                $kabupaten = $kabupatenList->firstWhere('id', $kabupatenId);
                $kecamatan = $kecamatanList->firstWhere('id', $kecamatanId);
            } else {
                // PILIH NEGARA dulu
                $negara = $faker->randomElement($negaraList);
                $negaraId = $negara->id;

                // Filter provinsi yang negara_id-nya sama dengan negara terpilih
                $provinsiFiltered = $provinsiList->where('negara_id', $negaraId)->values();
                if ($provinsiFiltered->isEmpty()) {
                    // Jika negara terpilih tidak punya provinsi (misal Vietnam), skip ke Indonesia saja
                    $negara = $negaraList->firstWhere('nama', 'Indonesia') ?? $negaraList->first();
                    $negaraId = $negara->id;
                    $provinsiFiltered = $provinsiList->where('negara_id', $negaraId)->values();
                }
                $provinsi = $faker->randomElement($provinsiFiltered);
                $provinsiId = $provinsi->id;

                // Filter kabupaten sesuai provinsi
                $kabupatenFiltered = $kabupatenList->where('provinsi_id', $provinsiId)->values();
                $kabupaten = $faker->randomElement($kabupatenFiltered);
                $kabupatenId = $kabupaten->id;

                // Filter kecamatan sesuai kabupaten
                $kecamatanFiltered = $kecamatanList->where('kabupaten_id', $kabupatenId)->values();
                $kecamatan = $faker->randomElement($kecamatanFiltered);
                $kecamatanId = $kecamatan->id;

                $currentNoKK = $faker->numerify('###############');

                // Buat Ayah
                $ayahWafat = $faker->boolean(10);
                $currentAyahId = (string) Str::uuid();
                DB::table('biodata')->insert([
                    'id' => $currentAyahId,
                    'negara_id' => $negaraId,
                    'provinsi_id' => $provinsiId,
                    'kabupaten_id' => $kabupatenId,
                    'kecamatan_id' => $kecamatanId,
                    'jalan' => $faker->streetAddress,
                    'kode_pos' => $faker->postcode,
                    'nama' => $faker->name('male'),
                    'no_passport' => $faker->numerify('############'),
                    'jenis_kelamin' => 'l',
                    'tanggal_lahir' => $faker->date(),
                    'tempat_lahir' => $faker->city,
                    'nik' => $faker->numerify('################'),
                    'anak_keberapa' => rand(1, 5),
                    'dari_saudara' => rand(1, 5),
                    'no_telepon' => $faker->phoneNumber,
                    'email' => $faker->unique()->email,
                    'jenjang_pendidikan_terakhir' => $faker->randomElement(['sd/mi', 'smp/mts', 'sma/smk/ma', 'd3', 'd4', 's1', 's2']),
                    'smartcard' => $faker->numerify('############'),
                    'status' => true,
                    'wafat' => $ayahWafat,
                    'created_by' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Buat Ibu
                $ibuWafat = $faker->boolean(10);
                $currentIbuId = (string) Str::uuid();
                DB::table('biodata')->insert([
                    'id' => $currentIbuId,
                    'negara_id' => $negaraId,
                    'provinsi_id' => $provinsiId,
                    'kabupaten_id' => $kabupatenId,
                    'kecamatan_id' => $kecamatanId,
                    'jalan' => $faker->streetAddress,
                    'kode_pos' => $faker->postcode,
                    'nama' => $faker->name('female'),
                    'no_passport' => $faker->numerify('############'),
                    'jenis_kelamin' => 'p',
                    'tanggal_lahir' => $faker->date(),
                    'tempat_lahir' => $faker->city,
                    'nik' => $faker->numerify('################'),
                    'anak_keberapa' => rand(1, 5),
                    'dari_saudara' => rand(1, 5),
                    'no_telepon' => $faker->phoneNumber,
                    'email' => $faker->unique()->email,
                    'jenjang_pendidikan_terakhir' => $faker->randomElement(['sd/mi', 'smp/mts', 'sma/smk/ma', 'd3', 'd4', 's1', 's2']),
                    'smartcard' => $faker->numerify('############'),
                    'status' => true,
                    'wafat' => $ibuWafat,
                    'created_by' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $keluargaTersimpan[] = [
                    'no_kk' => $currentNoKK,
                    'ayah_id' => $currentAyahId,
                    'ibu_id' => $currentIbuId,
                ];

                DB::table('orang_tua_wali')->insert([
                    [
                        'id_biodata' => $currentAyahId,
                        'id_hubungan_keluarga' => $ayahStatus,
                        'pekerjaan' => $faker->jobTitle(),
                        'penghasilan' => $faker->randomElement(['500000', '1000000', '2000000']),
                        'wali' => !$ayahWafat,
                        'status' => true,
                        'created_by' => 1,
                    ],
                    [
                        'id_biodata' => $currentIbuId,
                        'id_hubungan_keluarga' => $ibuStatus,
                        'pekerjaan' => $faker->jobTitle(),
                        'penghasilan' => $faker->randomElement(['500000', '1000000', '2000000']),
                        'wali' => !$ibuWafat,
                        'status' => true,
                        'created_by' => 1,
                    ],
                ]);

                DB::table('keluarga')->insert([
                    ['no_kk' => $currentNoKK, 'id_biodata' => $currentAyahId, 'status' => true, 'created_by' => 1],
                    ['no_kk' => $currentNoKK, 'id_biodata' => $currentIbuId, 'status' => true, 'created_by' => 1],
                ]);
            }

            // Anak (biodata), ikut wilayah orang tua
            $childId = (string) Str::uuid();
            DB::table('biodata')->insert([
                'id' => $childId,
                'negara_id' => $negaraId,
                'provinsi_id' => $provinsiId,
                'kabupaten_id' => $kabupatenId,
                'kecamatan_id' => $kecamatanId,
                'jalan' => $faker->streetAddress,
                'kode_pos' => $faker->postcode,
                'nama' => $faker->name($faker->randomElement(['male', 'female'])),
                'no_passport' => $faker->numerify('############'),
                'jenis_kelamin' => $faker->randomElement(['l', 'p']),
                'tanggal_lahir' => $faker->date(),
                'tempat_lahir' => $faker->city,
                'anak_keberapa' => rand(1, 5),
                'dari_saudara' => rand(1, 5),
                'nik' => $faker->numerify('################'),
                'no_telepon' => $faker->phoneNumber,
                'email' => $faker->unique()->email,
                'jenjang_pendidikan_terakhir' => $faker->randomElement(['sd/mi', 'smp/mts', 'sma/smk/ma', 'd3', 'd4', 's1', 's2']),
                'smartcard' => $faker->numerify('############'),
                'status' => true,
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('keluarga')->insert([
                ['no_kk' => $currentNoKK, 'id_biodata' => $childId, 'status' => true, 'created_by' => 1],
            ]);

            $pick = $faker->randomElement($weighted);
            [$doSantri, $stSantri, $doPendidikan, $stPendidikan] = $scenarios[$pick];

            if ($doSantri) {
                $angkatan = $faker->randomElement($angkatanList);
                $angkatanId = $angkatan->id;

                // Tanggal masuk sesuai awal tahun ajaran angkatan
                $tahunAjaran = DB::table('tahun_ajaran')->where('id', $angkatan->tahun_ajaran_id)->first();
                $tanggalMasuk = $faker->dateTimeBetween($tahunAjaran->tanggal_mulai, $tahunAjaran->tanggal_selesai)->format('Y-m-d');
                $tanggalKeluar = $stSantri === 'alumni' ? $faker->dateTimeBetween($tanggalMasuk, '+2 years')->format('Y-m-d') : null;

                $santriId = DB::table('santri')->insertGetId([
                    'biodata_id' => $childId,
                    'angkatan_id' => $angkatanId,
                    'nis' => $faker->unique()->numerify('###########'),
                    'tanggal_masuk' => $tanggalMasuk,
                    'tanggal_keluar' => $tanggalKeluar,
                    'status' => $stSantri,
                    'created_by' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                if ($faker->boolean(85)) {
                    DB::table('riwayat_domisili')->insert([
                        'santri_id' => $santriId,
                        'wilayah_id' => $faker->randomElement($wilayahIds),
                        'blok_id' => $faker->randomElement($blokIds),
                        'kamar_id' => $faker->randomElement($kamarIds),
                        'tanggal_masuk' => $tanggalMasuk,
                        'tanggal_keluar' => $tanggalKeluar,
                        'status' => $stSantri === 'alumni' ? 'keluar' : $stSantri,
                        'created_by' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                if ($doPendidikan) {
                    $tanggalMasukPendidikan = $faker->dateTimeBetween($tahunAjaran->tanggal_mulai, $tahunAjaran->tanggal_selesai)->format('Y-m-d');
                    $tanggalKeluarPendidikan = $stPendidikan === 'alumni' ? $faker->dateTimeBetween($tanggalMasukPendidikan, '+2 years')->format('Y-m-d') : null;

                    DB::table('riwayat_pendidikan')->insert([
                        'santri_id' => $santriId,
                        'angkatan_id' => $angkatanId,
                        'no_induk' => $faker->unique()->numerify('###########'),
                        'lembaga_id' => $faker->randomElement($lembagaIds),
                        'jurusan_id' => $faker->randomElement($jurusanIds),
                        'kelas_id' => $faker->randomElement($kelasIds),
                        'rombel_id' => $faker->randomElement($rombelIds),
                        'tanggal_masuk' => $tanggalMasukPendidikan,
                        'tanggal_keluar' => $tanggalKeluarPendidikan,
                        'status' => $stPendidikan,
                        'created_by' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }



            // Jika ayah adalah pegawai, simpan ke anak_pegawai
            if (in_array($currentAyahId, $pegawaiBiodataIds)) {
                DB::table('anak_pegawai')->insert([
                    'id_biodata' => $childId,
                    'id_pegawai' => DB::table('pegawai')->where('biodata_id', $currentAyahId)->value('id'),
                    'status' => true,
                    'created_by' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}





// {
//     public function run(): void
//     {
//         $faker        = Factory::create('id_ID');

//         // === 1. Pluck untuk biodata & lokasi orang tua/anak ===
//         $negaraIds    = DB::table('negara')->pluck('id')->toArray();
//         $provinsiIds  = DB::table('provinsi')->pluck('id')->toArray();
//         $kabupatenIds = DB::table('kabupaten')->pluck('id')->toArray();
//         $kecamatanIds = DB::table('kecamatan')->pluck('id')->toArray();

//         // Ambil status hubungan keluarga
//         $hk           = DB::table('hubungan_keluarga')->get();
//         $ayahStatus   = $hk->firstWhere('nama_status', 'ayah')->id;
//         $ibuStatus    = $hk->firstWhere('nama_status', 'ibu')->id;

//         // === 2. Pluck untuk skenario santri/pelajar ===
//         $lembagaIds  = DB::table('lembaga')->pluck('id')->toArray();
//         $jurusanIds  = DB::table('jurusan')->pluck('id')->toArray();
//         $kelasIds    = DB::table('kelas')->pluck('id')->toArray();
//         $rombelIds   = DB::table('rombel')->pluck('id')->toArray();
//         $wilayahIds  = DB::table('wilayah')->pluck('id')->toArray();
//         $blokIds     = DB::table('blok')->pluck('id')->toArray();
//         $kamarIds    = DB::table('kamar')->pluck('id')->toArray();

//         // === 3. Definisikan skenario & bobot ===
//         $scenarios = [
//             'active_both'                     => [true,  'aktif',  true,  'aktif', 40],
//             'santri_only_active'              => [true,  'aktif',  false, null,   10],
//             'santri_only_alumni'              => [true,  'alumni', false, null,    5],
//             'pelajar_only_active'             => [false, null,     true,  'aktif', 10],
//             'pelajar_only_alumni'             => [false, null,     true,  'alumni', 5],
//             'santri_active_pendidikan_alumni' => [true,  'aktif',  true,  'alumni',10],
//             'santri_alumni_pendidikan_active' => [true,  'alumni', true,  'aktif', 10],
//             'alumni_both'                     => [true,  'alumni', true,  'alumni',10],
//         ];
//         $weighted = [];
//         foreach ($scenarios as $key => $cfg) {
//             for ($j = 0; $j < $cfg[4]; $j++) {
//                 $weighted[] = $key;
//             }
//         }

//         // === 4. Seeder loop: buat 200 keluarga + anak + skenario santri/pelajar ===
//         $siblingGroup  = false;
//         $currentNoKK   = null;
//         $currentAyahId = null;
//         $currentIbuId  = null;

//         for ($i = 1; $i <= 200; $i++) {
//             // -- Orang tua & KK --
//             if (! $siblingGroup) {
//                 // 30% mulai sibling group
//                 if ($faker->boolean(30)) {
//                     $siblingGroup = true;
//                 }
//                 $currentNoKK = $faker->numerify('###############');

//                 // status wafat
//                 $ayahWafat = $faker->boolean(10);
//                 $ibuWafat  = $faker->boolean(10);

//                 // biodata ayah
//                 $currentAyahId = DB::table('biodata')->insertGetId([
//                     'negara_id'       => $faker->randomElement($negaraIds),
//                     'provinsi_id'     => $faker->randomElement($provinsiIds),
//                     'kabupaten_id'    => $faker->randomElement($kabupatenIds),
//                     'kecamatan_id'    => $faker->randomElement($kecamatanIds),
//                     'jalan'           => $faker->streetAddress,
//                     'kode_pos'        => $faker->postcode,
//                     'nama'            => $faker->name('male'),
//                     'no_passport'     => $faker->numerify('############'),
//                     'jenis_kelamin'   => 'l',
//                     'tanggal_lahir'   => $faker->date(),
//                     'tempat_lahir'    => $faker->city,
//                     'nik'             => $faker->numerify('###############'),
//                     'anak_keberapa'   => rand(1, 5),
//                     'dari_saudara'    => rand(1, 5),
//                     'no_telepon'      => $faker->phoneNumber,
//                     'email'           => $faker->unique()->email,
//                     'jenjang_pendidikan_terakhir' => $faker->randomElement(['sd/mi','smp/mts','sma/smk/ma','d3','d4','s1','s2']),
//                     'smartcard'       => $faker->numerify('############'),
//                     'status'          => true,
//                     'wafat'           => $ayahWafat,
//                     'created_by'      => 1,
//                     'created_at'      => now(),
//                     'updated_at'      => now(),
//                 ]);
//                 // biodata ibu
//                 $currentIbuId = DB::table('biodata')->insertGetId([
//                     'negara_id'       => $faker->randomElement($negaraIds),
//                     'provinsi_id'     => $faker->randomElement($provinsiIds),
//                     'kabupaten_id'    => $faker->randomElement($kabupatenIds),
//                     'kecamatan_id'    => $faker->randomElement($kecamatanIds),
//                     'jalan'           => $faker->streetAddress,
//                     'kode_pos'        => $faker->postcode,
//                     'nama'            => $faker->name('female'),
//                     'no_passport'     => $faker->numerify('############'),
//                     'jenis_kelamin'   => 'p',
//                     'tanggal_lahir'   => $faker->date(),
//                     'tempat_lahir'    => $faker->city,
//                     'nik'             => $faker->numerify('###############'),
//                     'anak_keberapa'   => rand(1, 5),
//                     'dari_saudara'    => rand(1, 5),
//                     'no_telepon'      => $faker->phoneNumber,
//                     'email'           => $faker->unique()->email,
//                     'jenjang_pendidikan_terakhir' => $faker->randomElement(['sd/mi','smp/mts','sma/smk/ma','d3','d4','s1','s2']),
//                     'smartcard'       => $faker->numerify('############'),
//                     'status'          => true,
//                     'wafat'           => $ibuWafat,
//                     'created_by'      => 1,
//                     'created_at'      => now(),
//                     'updated_at'      => now(),
//                 ]);

//                 // pastikan ayah dan ibu berhasil dibuat
//                 if (! $currentAyahId || ! $currentIbuId) {
//                     dd("Error: Orang tua (ayah atau ibu) gagal dibuat pada iterasi ke $i");
//                 }

//                 // orang_tua_wali & keluarga
//                 DB::table('orang_tua_wali')->insert([
//                     [
//                         'id_biodata'           => $currentAyahId,
//                         'id_hubungan_keluarga' => $ayahStatus,
//                         'pekerjaan'            => $faker->jobTitle(),
//                         'penghasilan'          => $faker->randomElement(['500000','1000000','2000000']),
//                         'wali'                 => ! $ayahWafat,
//                         'status'               => true,
//                         'created_by'           => 1,
//                     ],
//                     [
//                         'id_biodata'           => $currentIbuId,
//                         'id_hubungan_keluarga' => $ibuStatus,
//                         'pekerjaan'            => $faker->jobTitle(),
//                         'penghasilan'          => $faker->randomElement(['500000','1000000','2000000']),
//                         'wali'                 => $ayahWafat,
//                         'status'               => true,
//                         'created_by'           => 1,
//                     ],
//                 ]);
//                 DB::table('keluarga')->insert([
//                     ['no_kk' => $currentNoKK, 'id_biodata' => $currentAyahId, 'status'=>true,'created_by'=>1],
//                     ['no_kk' => $currentNoKK, 'id_biodata' => $currentIbuId, 'status'=>true,'created_by'=>1],
//                 ]);
//             } else {
//                 if (! $faker->boolean(70)) {
//                     $siblingGroup = false;
//                 }
//             }

//             // -- Biodata anak --
//             $childId = DB::table('biodata')->insertGetId([
//                 'negara_id'       => $faker->randomElement($negaraIds),
//                 'provinsi_id'     => $faker->randomElement($provinsiIds),
//                 'kabupaten_id'    => $faker->randomElement($kabupatenIds),
//                 'kecamatan_id'    => $faker->randomElement($kecamatanIds),
//                 'jalan'           => $faker->streetAddress,
//                 'kode_pos'        => $faker->postcode,
//                 'nama'            => $faker->name($faker->randomElement(['male','female'])),
//                 'no_passport'     => $faker->numerify('############'),
//                 'jenis_kelamin'   => $faker->randomElement(['l','p']),
//                 'tanggal_lahir'   => $faker->date(),
//                 'tempat_lahir'    => $faker->city,
//                 'anak_keberapa'   => rand(1, 5),
//                 'dari_saudara'    => rand(1, 5),
//                 'nik'             => $faker->numerify('###############'),
//                 'no_telepon'      => $faker->phoneNumber,
//                 'email'           => $faker->unique()->email,
//                 'jenjang_pendidikan_terakhir' => $faker->randomElement(['sd/mi','smp/mts','sma/smk/ma','d3','d4','s1','s2']),
//                 'smartcard'       => $faker->numerify('############'),
//                 'status'          => true,
//                 'created_by'      => 1,
//                 'created_at'      => now(),
//                 'updated_at'      => now(),
//             ]);
//             // Pastikan setiap anak punya orang tua
//             if (! $currentAyahId || ! $currentIbuId) {
//                 dd("Error: Anak tanpa orang tua pada iterasi ke $i");
//             }

//             DB::table('keluarga')->insert([
//                 ['no_kk' => $currentNoKK, 'id_biodata' => $childId, 'status'=>true,'created_by'=>1],
//             ]);

//             // -- 5. Tentukan skenario anak => santri + domisili + pendidikan --
//             $pick   = $faker->randomElement($weighted);
//             [$doSantri, $stSantri, $doPendidikan, $stPendidikan] = $scenarios[$pick];

//             if ($doSantri) {
//                 $uuid = (string) Str::uuid();
//                 DB::table('santri')->updateOrInsert([
//                     'biodata_id'    => $childId
//                 ],[
//                     'id'            => $uuid,
//                     'nis'           => $faker->unique()->numerify('###########'),
//                     'tanggal_masuk' => $faker->date(),
//                     'tanggal_keluar'=> $stSantri === 'alumni' ? $faker->date() : null,
//                     'status'        => $stSantri,
//                     'created_by'    => 1,
//                     'created_at'    => now(),
//                     'updated_at'    => now(),
//                 ]);
//                 // ... riwayat domisili & pendidikan tetap sama ...
//                 DB::table('riwayat_domisili')->insert([
//                     'santri_id'     => $uuid,
//                     'wilayah_id'    => $faker->randomElement($wilayahIds),
//                     'blok_id'       => $faker->randomElement($blokIds),
//                     'kamar_id'      => $faker->randomElement($kamarIds),
//                     'tanggal_masuk' => $faker->dateTime(),
//                     'tanggal_keluar'=> $stSantri === 'alumni' ? $faker->dateTime() : null,
//                     'status'        => $stSantri,
//                     'created_by'    => 1,
//                     'created_at'    => now(),
//                     'updated_at'    => now(),
//                 ]);
//                 if ($doPendidikan) {
//                     DB::table('riwayat_pendidikan')->insert([
//                         'santri_id'     => $uuid,
//                         'no_induk'      => $faker->unique()->numerify('###########'),
//                         'lembaga_id'    => $faker->randomElement($lembagaIds),
//                         'jurusan_id'    => $faker->randomElement($jurusanIds),
//                         'kelas_id'      => $faker->randomElement($kelasIds),
//                         'rombel_id'     => $faker->randomElement($rombelIds),
//                         'tanggal_masuk' => $faker->date(),
//                         'tanggal_keluar'=> $stPendidikan==='alumni' ? $faker->date() : null,
//                         'status'        => $stPendidikan,
//                         'created_by'    => 1,
//                         'created_at'    => now(),
//                         'updated_at'    => now(),
//                     ]);
//                 }
//             }
//         }
//     }
// }

// {
//     public function run(): void
//     {
//         $faker        = Factory::create('id_ID');

//         // === 1. Pluck untuk biodata & lokasi orang tua/anak ===
//         $negaraIds    = DB::table('negara')->pluck('id')->toArray();
//         $provinsiIds  = DB::table('provinsi')->pluck('id')->toArray();
//         $kabupatenIds = DB::table('kabupaten')->pluck('id')->toArray();
//         $kecamatanIds = DB::table('kecamatan')->pluck('id')->toArray();

//         // Ambil status hubungan keluarga
//         $hk           = DB::table('hubungan_keluarga')->get();
//         $ayahStatus   = $hk->firstWhere('nama_status', 'ayah')->id;
//         $ibuStatus    = $hk->firstWhere('nama_status', 'ibu')->id;

//         // === 2. Pluck untuk skenario santri/pelajar ===
//         $lembagaIds  = DB::table('lembaga')->pluck('id')->toArray();
//         $jurusanIds  = DB::table('jurusan')->pluck('id')->toArray();
//         $kelasIds    = DB::table('kelas')->pluck('id')->toArray();
//         $rombelIds   = DB::table('rombel')->pluck('id')->toArray();
//         $wilayahIds  = DB::table('wilayah')->pluck('id')->toArray();
//         $blokIds     = DB::table('blok')->pluck('id')->toArray();
//         $kamarIds    = DB::table('kamar')->pluck('id')->toArray();

//         // === 3. Definisikan skenario & bobot ===
//         $scenarios = [
//             'active_both'                     => [true,  'aktif',  true,  'aktif', 40],
//             'santri_only_active'              => [true,  'aktif',  false, null,   10],
//             'santri_only_alumni'              => [true,  'alumni', false, null,    5],
//             'pelajar_only_active'             => [false, null,     true,  'aktif', 10],
//             'pelajar_only_alumni'             => [false, null,     true,  'alumni', 5],
//             'santri_active_pendidikan_alumni' => [true,  'aktif',  true,  'alumni',10],
//             'santri_alumni_pendidikan_active' => [true,  'alumni', true,  'aktif', 10],
//             'alumni_both'                     => [true,  'alumni', true,  'alumni',10],
//         ];
//         // bangun array weighted untuk pemilihan
//         $weighted = [];
//         foreach ($scenarios as $key => $cfg) {
//             $weight = $cfg[4];
//             for ($i = 0; $i < $weight; $i++) {
//                 $weighted[] = $key;
//             }
//         }

//         // === 4. Seeder loop: buat 200 keluarga + anak + skenario santri/pelajar ===
//         $siblingGroup  = false;
//         $currentNoKK   = null;
//         $currentAyahId = null;
//         $currentIbuId  = null;

//         for ($i = 1; $i <= 200; $i++) {
//             // -- Orang tua & KK --
//             if (! $siblingGroup) {
//                 // 30% mulai sibling group
//                 if ($faker->boolean(30)) {
//                     $siblingGroup = true;
//                 }
//                 $currentNoKK = $faker->numerify('###############');

//                 // status wafat
//                 $ayahWafat = $faker->boolean(10);
//                 $ibuWafat  = $faker->boolean(10);

//                 // biodata ayah
//                 $currentAyahId = DB::table('biodata')->insertGetId([
//                     'negara_id'       => $faker->randomElement($negaraIds),
//                     'provinsi_id'     => $faker->randomElement($provinsiIds),
//                     'kabupaten_id'    => $faker->randomElement($kabupatenIds),
//                     'kecamatan_id'    => $faker->randomElement($kecamatanIds),
//                     'jalan'           => $faker->streetAddress,
//                     'kode_pos'        => $faker->postcode,
//                     'nama'            => $faker->name('male'),
//                     'no_passport'     => $faker->numerify('############'),
//                     'jenis_kelamin'   => 'l',
//                     'tanggal_lahir'   => $faker->date(),
//                     'tempat_lahir'    => $faker->city,
//                     'nik'             => $faker->numerify('###############'),
//                     'anak_keberapa' => rand(1, 5),
//                     'dari_saudara' => rand(1, 5),
//                     'no_telepon'      => $faker->phoneNumber,
//                     'email'           => $faker->unique()->email,
//                     'jenjang_pendidikan_terakhir' => $faker->randomElement(['sd/mi','smp/mts','sma/smk/ma','d3','d4','s1','s2']),
//                     'smartcard'       => $faker->numerify('############'),
//                     'status'          => true,
//                     'wafat'           => $ayahWafat,
//                     'created_by'      => 1,
//                     'created_at'      => now(),
//                     'updated_at'      => now(),
//                 ]);
//                 // biodata ibu
//                 $currentIbuId = DB::table('biodata')->insertGetId([
//                     'negara_id'       => $faker->randomElement($negaraIds),
//                     'provinsi_id'     => $faker->randomElement($provinsiIds),
//                     'kabupaten_id'    => $faker->randomElement($kabupatenIds),
//                     'kecamatan_id'    => $faker->randomElement($kecamatanIds),
//                     'jalan'           => $faker->streetAddress,
//                     'kode_pos'        => $faker->postcode,
//                     'nama'            => $faker->name('female'),
//                     'no_passport'     => $faker->numerify('############'),
//                     'jenis_kelamin'   => 'p',
//                     'tanggal_lahir'   => $faker->date(),
//                     'tempat_lahir'    => $faker->city,
//                     'nik'             => $faker->numerify('###############'),
//                     'no_telepon'      => $faker->phoneNumber,
//                     'email'           => $faker->unique()->email,
//                     'anak_keberapa' => rand(1, 5),
//                     'dari_saudara' => rand(1, 5),
//                     'jenjang_pendidikan_terakhir' => $faker->randomElement(['sd/mi','smp/mts','sma/smk/ma','d3','d4','s1','s2']),
//                     'smartcard'       => $faker->numerify('############'),
//                     'status'          => true,
//                     'wafat'           => $ibuWafat,
//                     'created_by'      => 1,
//                     'created_at'      => now(),
//                     'updated_at'      => now(),
//                 ]);

//                 // orang_tua_wali & keluarga
//                 DB::table('orang_tua_wali')->insert([
//                     [
//                         'id_biodata'           => $currentAyahId,
//                         'id_hubungan_keluarga' => $ayahStatus,
//                         'pekerjaan'            => $faker->jobTitle(),
//                         'penghasilan'          => $faker->randomElement(['500000','1000000','2000000']),
//                         'wali'                 => ! $ayahWafat,
//                         'status'               => true,
//                         'created_by'           => 1,
//                     ],
//                     [
//                         'id_biodata'           => $currentIbuId,
//                         'id_hubungan_keluarga' => $ibuStatus,
//                         'pekerjaan'            => $faker->jobTitle(),
//                         'penghasilan'          => $faker->randomElement(['500000','1000000','2000000']),
//                         'wali'                 => $ayahWafat,
//                         'status'               => true,
//                         'created_by'           => 1,
//                     ],
//                 ]);
//                 DB::table('keluarga')->insert([
//                     ['no_kk' => $currentNoKK, 'id_biodata' => $currentAyahId, 'status'=>true,'created_by'=>1],
//                     ['no_kk' => $currentNoKK, 'id_biodata' => $currentIbuId, 'status'=>true,'created_by'=>1],
//                 ]);
//             } else {
//                 if (! $faker->boolean(70)) {
//                     $siblingGroup = false;
//                 }
//             }

//             // -- Biodata anak --
//             $childId = DB::table('biodata')->insertGetId([
//                 'negara_id'       => $faker->randomElement($negaraIds),
//                 'provinsi_id'     => $faker->randomElement($provinsiIds),
//                 'kabupaten_id'    => $faker->randomElement($kabupatenIds),
//                 'kecamatan_id'    => $faker->randomElement($kecamatanIds),
//                 'jalan'           => $faker->streetAddress,
//                 'kode_pos'        => $faker->postcode,
//                 'nama'            => $faker->name($faker->randomElement(['male','female'])),
//                 'no_passport'     => $faker->numerify('############'),
//                 'jenis_kelamin'   => $faker->randomElement(['l','p']),
//                 'tanggal_lahir'   => $faker->date(),
//                 'tempat_lahir'    => $faker->city,
//                 'anak_keberapa' => rand(1, 5),
//                 'dari_saudara' => rand(1, 5),
//                 'nik'             => $faker->numerify('###############'),
//                 'no_telepon'      => $faker->phoneNumber,
//                 'email'           => $faker->unique()->email,
//                 'jenjang_pendidikan_terakhir' => $faker->randomElement(['sd/mi','smp/mts','sma/smk/ma','d3','d4','s1','s2']),
//                 'smartcard'       => $faker->numerify('############'),
//                 'status'          => true,
//                 'created_by'      => 1,
//                 'created_at'      => now(),
//                 'updated_at'      => now(),
//             ]);
//             DB::table('keluarga')->insert([
//                 ['no_kk' => $currentNoKK, 'id_biodata' => $childId, 'status'=>true,'created_by'=>1],
//             ]);

//             // -- 5. Tentukan skenario anak => santri + domisili + pendidikan --
//             $pick   = $faker->randomElement($weighted);
//             [$doSantri, $stSantri, $doPendidikan, $stPendidikan] = $scenarios[$pick];

//             if ($doSantri) {
//                 $uuid = (string) Str::uuid();
            
//                 // gunakan updateOrInsert agar per biodata_id hanya 1 record
//                 DB::table('santri')->updateOrInsert(
//                     ['biodata_id'    => $childId],
//                     [
//                         'id'            => $uuid,
//                         'nis'           => $faker->unique()->numerify('###########'),
//                         'tanggal_masuk' => $faker->date(),
//                         'tanggal_keluar'=> $stSantri === 'alumni' ? $faker->date() : null,
//                         'status'        => $stSantri,
//                         'created_by'    => 1,
//                         'created_at'    => now(),
//                         'updated_at'    => now(),
//                     ]
//                 );
            

//                 // riwayat domisili
//                 DB::table('riwayat_domisili')->insert([
//                     'santri_id'     => $uuid,
//                     'wilayah_id'    => $faker->randomElement($wilayahIds),
//                     'blok_id'       => $faker->randomElement($blokIds),
//                     'kamar_id'      => $faker->randomElement($kamarIds),
//                     'tanggal_masuk' => $faker->dateTime(),
//                     'tanggal_keluar'=> $stSantri==='alumni' ? $faker->dateTime() : null,
//                     'status'        => $stSantri,
//                     'created_by'    => 1,
//                     'created_at'    => now(),
//                     'updated_at'    => now(),
//                 ]);

//                 // riwayat pendidikan (hanya jika kedua‐duanya true)
//                 if ($doPendidikan) {
//                     DB::table('riwayat_pendidikan')->insert([
//                         'santri_id'     => $uuid,
//                         'no_induk'      => $faker->unique()->numerify('###########'),
//                         'lembaga_id'    => $faker->randomElement($lembagaIds),
//                         'jurusan_id'    => $faker->randomElement($jurusanIds),
//                         'kelas_id'      => $faker->randomElement($kelasIds),
//                         'rombel_id'     => $faker->randomElement($rombelIds),
//                         'tanggal_masuk' => $faker->date(),
//                         'tanggal_keluar'=> $stPendidikan==='alumni' ? $faker->date() : null,
//                         'status'        => $stPendidikan,
//                         'created_by'    => 1,
//                         'created_at'    => now(),
//                         'updated_at'    => now(),
//                     ]);
//                 }
//             }
//         }
//     }
// }
