<?php

namespace Database\Seeders;

use Faker\Factory;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Helpers\StatusPesertaDidikHelper;

class DataKeluargaSeeder extends Seeder
{
  public function run(): void
{
    $faker = Factory::create('id_ID');

    // Ambil data wilayah (negara, provinsi, kabupaten, kecamatan)
    $negaraList    = DB::table('negara')->get();
    $provinsiList  = DB::table('provinsi')->get();
    $kabupatenList = DB::table('kabupaten')->get();
    $kecamatanList = DB::table('kecamatan')->get();

    // Ambil ID status hubungan keluarga (ayah kandung, ibu kandung)
    $hk         = DB::table('hubungan_keluarga')->get();
    $ayahStatus = $hk->firstWhere('nama_status', 'ayah kandung')->id;
    $ibuStatus  = $hk->firstWhere('nama_status', 'ibu kandung')->id;

    // Ambil daftar biodata_id pegawai
    $pegawaiBiodataIds = DB::table('pegawai')->pluck('biodata_id')->toArray();

    // Ambil daftar ID lembaga, jurusan, angkatan, kelas, rombel, wilayah, blok, kamar
    $lembagaIds    = DB::table('lembaga')->pluck('id')->toArray();
    $jurusanIds    = DB::table('jurusan')->pluck('id')->toArray();
    $angkatanList  = DB::table('angkatan')->get();
    $angkatanSantriList  = $angkatanList->where('kategori', 'santri')->values();
    $angkatanPelajarList = $angkatanList->where('kategori', 'pelajar')->values();
    $kelasIds      = DB::table('kelas')->pluck('id')->toArray();
    $rombelIds     = DB::table('rombel')->pluck('id')->toArray();
    $wilayahIds    = DB::table('wilayah')->pluck('id')->toArray();
    $blokIds       = DB::table('blok')->pluck('id')->toArray();
    $kamarIds      = DB::table('kamar')->pluck('id')->toArray();

    // Definisikan skenario (menggabungkan status santri & pendidikan)
    $scenarios = [
        // [doSantri, stSantri, doPendidikan, stPendidikan, weight]
        'active_both'                     => [true,  'aktif',  true,  'aktif',   40],
        'santri_only_active'              => [true,  'aktif',  false, null,     10],
        'santri_only_alumni'              => [true,  'alumni', false, null,      5],
        'pelajar_only_active'             => [false, null,     true,  'aktif',   10],
        'pelajar_only_lulus'              => [false, null,     true,  'lulus',    5],
        'santri_active_pendidikan_lulus'  => [true,  'aktif',  true,  'lulus',   10],
        'santri_alumni_pendidikan_active' => [true,  'alumni', true,  'aktif',   10],
        'lulus_both'                      => [true,  'alumni', true,  'lulus',   10],
    ];

    // Buat array weighted berdasarkan skenario
    $weighted = [];
    foreach ($scenarios as $key => $cfg) {
        for ($j = 0; $j < $cfg[4]; $j++) {
            $weighted[] = $key;
        }
    }

    // Simpan data keluarga yang sudah dibuat (no_kk, ayah_id, ibu_id)
    $keluargaTersimpan = [];

    // Variabel penghitung record anak_pegawai
    $anakPegawaiCount    = 0;
    $requiredAnakPegawai = 50;

    for ($i = 1; $i <= 200; $i++) {
        $gunakanKeluargaLama = $faker->boolean(15);

        if ($gunakanKeluargaLama && count($keluargaTersimpan) > 0) {
            // Ambil salah satu keluarga yang sudah tersimpan
            $keluarga      = $faker->randomElement($keluargaTersimpan);
            $currentNoKK   = $keluarga['no_kk'];
            $currentAyahId = $keluarga['ayah_id'];
            $currentIbuId  = $keluarga['ibu_id'];

            // Ambil data biodata ayah (untuk wilayah yang sama)
            $ayahBiodata = DB::table('biodata')->where('id', $currentAyahId)->first();
            $negaraId    = $ayahBiodata->negara_id;
            $provinsiId  = $ayahBiodata->provinsi_id;
            $kabupatenId = $ayahBiodata->kabupaten_id;
            $kecamatanId = $ayahBiodata->kecamatan_id;
        } else {
            // PILIH NEGARA dulu
            $negara   = $faker->randomElement($negaraList);
            $negaraId = $negara->id;

            // Filter provinsi yang sesuai negara
            $provinsiFiltered = $provinsiList->where('negara_id', $negaraId)->values();
            if ($provinsiFiltered->isEmpty()) {
                // Jika negara tanpa provinsi, pakai Indonesia
                $negara   = $negaraList->firstWhere('nama', 'Indonesia') ?? $negaraList->first();
                $negaraId = $negara->id;
                $provinsiFiltered = $provinsiList->where('negara_id', $negaraId)->values();
            }
            $provinsi    = $faker->randomElement($provinsiFiltered);
            $provinsiId  = $provinsi->id;

            // Filter kabupaten sesuai provinsi
            $kabupatenFiltered = $kabupatenList->where('provinsi_id', $provinsiId)->values();
            $kabupaten   = $faker->randomElement($kabupatenFiltered);
            $kabupatenId = $kabupaten->id;

            // Filter kecamatan sesuai kabupaten
            $kecamatanFiltered = $kecamatanList->where('kabupaten_id', $kabupatenId)->values();
            $kecamatan   = $faker->randomElement($kecamatanFiltered);
            $kecamatanId = $kecamatan->id;

            $currentNoKK = $faker->numerify('###############');

            // Buat Ayah
            $ayahWafat     = $faker->boolean(10);
            $currentAyahId = (string) Str::uuid();
            DB::table('biodata')->insert([
                'id'                          => $currentAyahId,
                'negara_id'                   => $negaraId,
                'provinsi_id'                 => $provinsiId,
                'kabupaten_id'                => $kabupatenId,
                'kecamatan_id'                => $kecamatanId,
                'jalan'                       => $faker->streetAddress,
                'kode_pos'                    => $faker->postcode,
                'nama'                        => $faker->name('male'),
                'no_passport'                 => $faker->numerify('############'),
                'jenis_kelamin'               => 'l',
                'tanggal_lahir'               => $faker->date(),
                'tempat_lahir'                => $faker->city,
                'anak_keberapa'               => rand(1, 5),
                'dari_saudara'                => rand(1, 5),
                'nik'                         => $faker->numerify('################'),
                'no_telepon'                  => $faker->phoneNumber,
                'email'                       => $faker->unique()->email,
                'jenjang_pendidikan_terakhir' => $faker->randomElement(['sd/mi', 'smp/mts', 'sma/smk/ma', 'd3', 'd4', 's1', 's2']),
                'smartcard'                   => $faker->numerify('############'),
                'status'                      => true,
                'wafat'                       => $ayahWafat,
                'created_by'                  => 1,
                'created_at'                  => now(),
                'updated_at'                  => now(),
            ]);

            // Buat Ibu
            $ibuWafat     = $faker->boolean(10);
            $currentIbuId = (string) Str::uuid();
            DB::table('biodata')->insert([
                'id'                          => $currentIbuId,
                'negara_id'                   => $negaraId,
                'provinsi_id'                 => $provinsiId,
                'kabupaten_id'                => $kabupatenId,
                'kecamatan_id'                => $kecamatanId,
                'jalan'                       => $faker->streetAddress,
                'kode_pos'                    => $faker->postcode,
                'nama'                        => $faker->name('female'),
                'no_passport'                 => $faker->numerify('############'),
                'jenis_kelamin'               => 'p',
                'tanggal_lahir'               => $faker->date(),
                'tempat_lahir'                => $faker->city,
                'anak_keberapa'               => rand(1, 5),
                'dari_saudara'                => rand(1, 5),
                'nik'                         => $faker->numerify('################'),
                'no_telepon'                  => $faker->phoneNumber,
                'email'                       => $faker->unique()->email,
                'jenjang_pendidikan_terakhir' => $faker->randomElement(['sd/mi', 'smp/mts', 'sma/smk/ma', 'd3', 'd4', 's1', 's2']),
                'smartcard'                   => $faker->numerify('############'),
                'status'                      => true,
                'wafat'                       => $ibuWafat,
                'created_by'                  => 1,
                'created_at'                  => now(),
                'updated_at'                  => now(),
            ]);

            // Simpan info keluarga (no_kk, ayah_id, ibu_id)
            $keluargaTersimpan[] = [
                'no_kk'   => $currentNoKK,
                'ayah_id' => $currentAyahId,
                'ibu_id'  => $currentIbuId,
            ];

            // Insert ke tabel orang_tua_wali
            DB::table('orang_tua_wali')->insert([
                [
                    'id_biodata'           => $currentAyahId,
                    'id_hubungan_keluarga' => $ayahStatus,
                    'pekerjaan'            => $faker->jobTitle(),
                    'penghasilan'          => $faker->randomElement(['500000', '1000000', '2000000']),
                    'wali'                 => !$ayahWafat,
                    'status'               => true,
                    'created_by'           => 1,
                ],
                [
                    'id_biodata'           => $currentIbuId,
                    'id_hubungan_keluarga' => $ibuStatus,
                    'pekerjaan'            => $faker->jobTitle(),
                    'penghasilan'          => $faker->randomElement(['500000', '1000000', '2000000']),
                    'wali'                 => !$ibuWafat,
                    'status'               => true,
                    'created_by'           => 1,
                ],
            ]);

            // Insert ke tabel keluarga
            DB::table('keluarga')->insert([
                ['no_kk' => $currentNoKK, 'id_biodata' => $currentAyahId, 'status' => true, 'created_by' => 1],
                ['no_kk' => $currentNoKK, 'id_biodata' => $currentIbuId, 'status' => true, 'created_by' => 1],
            ]);
        }

        // --------------- MULAI MENYIMPAN DATA ANAK ---------------
        $childId = (string) Str::uuid();
        DB::table('biodata')->insert([
            'id'                          => $childId,
            'negara_id'                   => $negaraId,
            'provinsi_id'                 => $provinsiId,
            'kabupaten_id'                => $kabupatenId,
            'kecamatan_id'                => $kecamatanId,
            'jalan'                       => $faker->streetAddress,
            'kode_pos'                    => $faker->postcode,
            'nama'                        => $faker->name($faker->randomElement(['male', 'female'])),
            'no_passport'                 => $faker->numerify('############'),
            'jenis_kelamin'               => $faker->randomElement(['l', 'p']),
            'tanggal_lahir'               => $faker->date(),
            'tempat_lahir'                => $faker->city,
            'anak_keberapa'               => rand(1, 5),
            'dari_saudara'                => rand(1, 5),
            'nik'                         => $faker->numerify('################'),
            'no_telepon'                  => $faker->phoneNumber,
            'email'                       => $faker->unique()->email,
            'jenjang_pendidikan_terakhir' => $faker->randomElement(['sd/mi', 'smp/mts', 'sma/smk/ma', 'd3', 'd4', 's1', 's2']),
            'smartcard'                   => $faker->numerify('############'),
            'status'                      => true,
            'created_by'                  => 1,
            'created_at'                  => now(),
            'updated_at'                  => now(),
        ]);

        // Tambahkan anak ke dalam tabel keluarga (pakai no_kk yang sama)
        DB::table('keluarga')->insert([
            ['no_kk' => $currentNoKK, 'id_biodata' => $childId, 'status' => true, 'created_by' => 1],
        ]);

        // Ambil satu skenario secara acak
        $pick                   = $faker->randomElement($weighted);
        [$doSantri, $stSantri, $doPendidikan, $stPendidikan] = $scenarios[$pick];

        // Jika masuk ke tabel santri
        if ($doSantri) {
            // Pilih angkatan yang kategori = 'santri'
            $angkatan   = $faker->randomElement($angkatanSantriList);
            $angkatanId = $angkatan->id;

            // Tanggal masuk santri (acak antara tanggal awal dan akhir tahun ajaran)
            $tahunAjaran        = DB::table('tahun_ajaran')->where('id', $angkatan->tahun_ajaran_id)->first();
            $tanggalMasukSantri = $faker->dateTimeBetween($tahunAjaran->tanggal_mulai, $tahunAjaran->tanggal_selesai)->format('Y-m-d');
            $tanggalKeluarSantri = $stSantri === 'alumni'
                ? $faker->dateTimeBetween($tanggalMasukSantri, '+2 years')->format('Y-m-d')
                : null;

            // Insert ke tabel santri
            $santriId = DB::table('santri')->insertGetId([
                'biodata_id'     => $childId,
                'angkatan_id'    => $angkatanId,
                'nis'            => $faker->unique()->numerify('###########'),
                'tanggal_masuk'  => $tanggalMasukSantri,
                'tanggal_keluar' => $tanggalKeluarSantri,
                'status'         => $stSantri, // enum: ['aktif','alumni','do','berhenti','nonaktif']
                'created_by'     => 1,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);

            // =========================
            // ==== KELOLA DOMISILI ====
            // =========================

            if ($faker->boolean(85)) {
                if ($stSantri === 'aktif') {
                    // Hanya simpan ke tabel domisili_santri (active). Tidak masukkan ke riwayat_domisili.
                    DB::table('domisili_santri')->insert([
                        'santri_id'     => $santriId,
                        'wilayah_id'    => $faker->randomElement($wilayahIds),
                        'blok_id'       => $faker->randomElement($blokIds),
                        'kamar_id'      => $faker->randomElement($kamarIds),
                        'tanggal_masuk' => $tanggalMasukSantri . ' 00:00:00',
                        'status'        => 'aktif', // enum di table: ['aktif','pindah','keluar']
                        'created_by'    => 1,
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ]);
                } else {
                    // Santri sudah alumni → masukkan ke riwayat_domisili dengan status 'keluar'
                    DB::table('riwayat_domisili')->insert([
                        'santri_id'      => $santriId,
                        'wilayah_id'     => $faker->randomElement($wilayahIds),
                        'blok_id'        => $faker->randomElement($blokIds),
                        'kamar_id'       => $faker->randomElement($kamarIds),
                        'tanggal_masuk'  => $tanggalMasukSantri . ' 00:00:00',
                        'tanggal_keluar' => $tanggalKeluarSantri ? ($tanggalKeluarSantri . ' 00:00:00') : now(),
                        'status'         => 'keluar', // enum di table: ['aktif','pindah','keluar']
                        'created_by'     => 1,
                        'created_at'     => now(),
                        'updated_at'     => now(),
                    ]);

                    // Update record di domisili_santri menjadi 'keluar' (jika sebelumnya ada)
                    DB::table('domisili_santri')
                        ->where('santri_id', $santriId)
                        ->update([
                            'status'     => 'keluar',
                            'updated_at' => now(),
                        ]);
                }
            }

            // ================================
            // ==== KELOLA RIWAYAT PENDIDIKAN ==
            // ================================

            if ($doPendidikan) {
                // Pilih angkatan yang kategori = 'pelajar'
                $angkatanPel    = $faker->randomElement($angkatanPelajarList);
                $angkatanPelId  = $angkatanPel->id;
                $tahunAjaranPel = DB::table('tahun_ajaran')->where('id', $angkatanPel->tahun_ajaran_id)->first();

                $tanggalMasukPendidikan = $faker->dateTimeBetween($tahunAjaranPel->tanggal_mulai, $tahunAjaranPel->tanggal_selesai)->format('Y-m-d');

                if ($stPendidikan === 'aktif') {
                    // Hanya simpan ke tabel pendidikan (active). Tidak masukkan ke riwayat_pendidikan.
                    DB::table('pendidikan')->insert([
                        'biodata_id'     => $childId,
                        'angkatan_id'    => $angkatanPelId,
                        'no_induk'       => $faker->unique()->numerify('###########'),
                        'lembaga_id'     => $faker->randomElement($lembagaIds),
                        'jurusan_id'     => $faker->randomElement($jurusanIds),
                        'kelas_id'       => $faker->randomElement($kelasIds),
                        'rombel_id'      => $faker->randomElement($rombelIds),
                        'tanggal_masuk'  => $tanggalMasukPendidikan,
                        'status'         => 'aktif', // enum: ['aktif','lulus','do','berhenti','nonaktif']
                        'created_by'     => 1,
                        'created_at'     => now(),
                        'updated_at'     => now(),
                    ]);
                } else {
                    // stPendidikan bukan 'aktif' → dianggap lulus
                    $tanggalKeluarPendidikan = $faker->dateTimeBetween($tanggalMasukPendidikan, '+2 years')->format('Y-m-d');

                    // Cek apakah sudah ada record aktif di tabel pendidikan untuk biodata_id ini
                    $existing = DB::table('pendidikan')
                        ->where('biodata_id', $childId)
                        ->where('status', 'aktif')
                        ->first();

                    if ($existing) {
                        // Jika ada, **update** status‐nya menjadi 'lulus' dan isi tanggal_keluar
                        DB::table('pendidikan')
                            ->where('id', $existing->id)
                            ->update([
                                'status'         => 'lulus',
                                'tanggal_keluar' => $tanggalKeluarPendidikan,
                                'updated_at'     => now(),
                            ]);

                        // Masukkan ke riwayat_pendidikan dengan status 'lulus'
                        DB::table('riwayat_pendidikan')->insert([
                            'biodata_id'     => $childId,
                            'angkatan_id'    => $existing->angkatan_id,
                            'no_induk'       => $existing->no_induk,
                            'lembaga_id'     => $existing->lembaga_id,
                            'jurusan_id'     => $existing->jurusan_id,
                            'kelas_id'       => $existing->kelas_id,
                            'rombel_id'      => $existing->rombel_id,
                            'tanggal_masuk'  => $existing->tanggal_masuk,
                            'tanggal_keluar' => $tanggalKeluarPendidikan,
                            'status'         => 'lulus', // diganti menjadi 'lulus'
                            'created_by'     => 1,
                            'created_at'     => now(),
                            'updated_at'     => now(),
                        ]);

                    } else {
                        // Jika belum ada record aktif di pendidikan, langsung insert sebagai 'lulus'
                        DB::table('pendidikan')->insert([
                            'biodata_id'     => $childId,
                            'angkatan_id'    => $angkatanPelId,
                            'no_induk'       => $faker->unique()->numerify('###########'),
                            'lembaga_id'     => $faker->randomElement($lembagaIds),
                            'jurusan_id'     => $faker->randomElement($jurusanIds),
                            'kelas_id'       => $faker->randomElement($kelasIds),
                            'rombel_id'      => $faker->randomElement($rombelIds),
                            'tanggal_masuk'  => $tanggalMasukPendidikan,
                            'tanggal_keluar' => $tanggalKeluarPendidikan, // ditambahkan
                            'status'         => 'lulus', // langsung lulus
                            'created_by'     => 1,
                            'created_at'     => now(),
                            'updated_at'     => now(),
                        ]);

                        // Ambil ID record pendidikan yang baru saja diinsert untuk referensi riwayat
                        $pendidikanBaru = DB::table('pendidikan')
                            ->where('biodata_id', $childId)
                            ->where('status', 'lulus')
                            ->orderBy('created_at', 'desc')
                            ->first();

                        DB::table('riwayat_pendidikan')->insert([
                            'biodata_id'     => $childId,
                            'angkatan_id'    => $angkatanPelId,
                            'no_induk'       => $pendidikanBaru->no_induk,
                            'lembaga_id'     => $pendidikanBaru->lembaga_id,
                            'jurusan_id'     => $pendidikanBaru->jurusan_id,
                            'kelas_id'       => $pendidikanBaru->kelas_id,
                            'rombel_id'      => $pendidikanBaru->rombel_id,
                            'tanggal_masuk'  => $tanggalMasukPendidikan,
                            'tanggal_keluar' => $tanggalKeluarPendidikan,
                            'status'         => 'lulus', // diganti menjadi 'lulus'
                            'created_by'     => 1,
                            'created_at'     => now(),
                            'updated_at'     => now(),
                        ]);
                    }
                }
            }
        }

        // Jika ayah adalah pegawai, simpan ke tabel anak_pegawai dan tambahkan relasi ke santri/pendidikan
        if (in_array($currentAyahId, $pegawaiBiodataIds)) {
            DB::table('anak_pegawai')->insert([
                'biodata_id'  => $childId,
                'pegawai_id'  => DB::table('pegawai')->where('biodata_id', $currentAyahId)->value('id'),
                'status'      => true,
                'created_by'  => 1,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
            $anakPegawaiCount++;

            // Tambahkan relasi: random antara santri+domisili atau langsung pendidikan
            if ($faker->boolean(50)) {
                // --- Buat sebagai santri (untuk anak pegawai) ---
                $angkatan      = $faker->randomElement($angkatanSantriList);
                $angkatanId    = $angkatan->id;
                $tahunAjaran   = DB::table('tahun_ajaran')->where('id', $angkatan->tahun_ajaran_id)->first();

                $tanggalMasukSantri = $faker->dateTimeBetween($tahunAjaran->tanggal_mulai, $tahunAjaran->tanggal_selesai)->format('Y-m-d');
                $tanggalKeluarSantri = null; // langsung aktif

                $santriId = DB::table('santri')->insertGetId([
                    'biodata_id'     => $childId,
                    'angkatan_id'    => $angkatanId,
                    'nis'            => $faker->unique()->numerify('###########'),
                    'tanggal_masuk'  => $tanggalMasukSantri,
                    'tanggal_keluar' => $tanggalKeluarSantri,
                    'status'         => 'aktif',
                    'created_by'     => 1,
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);

                // Simpan ke tabel domisili_santri (active)
                DB::table('domisili_santri')->insert([
                    'santri_id'     => $santriId,
                    'wilayah_id'    => $faker->randomElement($wilayahIds),
                    'blok_id'       => $faker->randomElement($blokIds),
                    'kamar_id'      => $faker->randomElement($kamarIds),
                    'tanggal_masuk' => $tanggalMasukSantri . ' 00:00:00',
                    'status'        => 'aktif',
                    'created_by'    => 1,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            } else {
                // --- Buat pendidikan langsung (untuk anak pegawai) ---
                $angkatanPel     = $faker->randomElement($angkatanPelajarList);
                $angkatanPelId   = $angkatanPel->id;
                $tahunAjaranPel  = DB::table('tahun_ajaran')->where('id', $angkatanPel->tahun_ajaran_id)->first();
                $tanggalMasukPendidikan = $faker->dateTimeBetween($tahunAjaranPel->tanggal_mulai, $tahunAjaranPel->tanggal_selesai)->format('Y-m-d');

                // Simpan ke tabel pendidikan (aktif)
                DB::table('pendidikan')->insert([
                    'biodata_id'     => $childId,
                    'angkatan_id'    => $angkatanPelId,
                    'no_induk'       => $faker->unique()->numerify('###########'),
                    'lembaga_id'     => $faker->randomElement($lembagaIds),
                    'jurusan_id'     => $faker->randomElement($jurusanIds),
                    'kelas_id'       => $faker->randomElement($kelasIds),
                    'rombel_id'      => $faker->randomElement($rombelIds),
                    'tanggal_masuk'  => $tanggalMasukPendidikan,
                    'status'         => 'aktif',
                    'created_by'     => 1,
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);
            }
        }
    }

    // Setelah loop utama, jika jumlah anak_pegawai < 50, tambahkan hingga mencapai 50
    if ($anakPegawaiCount < $requiredAnakPegawai) {
        $pegawaiList   = DB::table('pegawai')->select('id', 'biodata_id')->get()->toArray();
        $need          = $requiredAnakPegawai - $anakPegawaiCount;
        $limitPegawai  = count($pegawaiList);

        if ($limitPegawai === 0) {
            return;
        }

        for ($j = 0; $j < $need; $j++) {
            $randomPegawai = $faker->randomElement($pegawaiList);
            $ayahId        = $randomPegawai->biodata_id;
            $pegawaiId     = $randomPegawai->id;

            $ayahBiodata = DB::table('biodata')->where('id', $ayahId)->first();
            if (!$ayahBiodata) {
                continue;
            }

            $negaraId    = $ayahBiodata->negara_id;
            $provinsiId  = $ayahBiodata->provinsi_id;
            $kabupatenId = $ayahBiodata->kabupaten_id;
            $kecamatanId = $ayahBiodata->kecamatan_id;

            $newNoKK = $faker->numerify('###############');

            $existsAyahWali = DB::table('orang_tua_wali')->where('id_biodata', $ayahId)->exists();
            if (!$existsAyahWali) {
                DB::table('orang_tua_wali')->insert([
                    'id_biodata'           => $ayahId,
                    'id_hubungan_keluarga' => $ayahStatus,
                    'pekerjaan'            => $faker->jobTitle(),
                    'penghasilan'          => $faker->randomElement(['500000', '1000000', '2000000']),
                    'wali'                 => true,
                    'status'               => true,
                    'created_by'           => 1,
                ]);
            }
            DB::table('keluarga')->insert([
                'no_kk'      => $newNoKK,
                'id_biodata' => $ayahId,
                'status'     => true,
                'created_by' => 1,
            ]);

            $ibuWafat = $faker->boolean(10);
            $newIbuId = (string) Str::uuid();
            DB::table('biodata')->insert([
                'id'                          => $newIbuId,
                'negara_id'                   => $negaraId,
                'provinsi_id'                 => $provinsiId,
                'kabupaten_id'                => $kabupatenId,
                'kecamatan_id'                => $kecamatanId,
                'jalan'                       => $faker->streetAddress,
                'kode_pos'                    => $faker->postcode,
                'nama'                        => $faker->name('female'),
                'no_passport'                 => $faker->numerify('############'),
                'jenis_kelamin'               => 'p',
                'tanggal_lahir'               => $faker->date(),
                'tempat_lahir'                => $faker->city,
                'anak_keberapa'               => rand(1, 5),
                'dari_saudara'                => rand(1, 5),
                'nik'                         => $faker->numerify('################'),
                'no_telepon'                  => $faker->phoneNumber,
                'email'                       => $faker->unique()->email,
                'jenjang_pendidikan_terakhir' => $faker->randomElement(['sd/mi', 'smp/mts', 'sma/smk/ma', 'd3', 'd4', 's1', 's2']),
                'smartcard'                   => $faker->numerify('############'),
                'status'                      => true,
                'wafat'                       => $ibuWafat,
                'created_by'                  => 1,
                'created_at'                  => now(),
                'updated_at'                  => now(),
            ]);
            DB::table('orang_tua_wali')->insert([
                'id_biodata'           => $newIbuId,
                'id_hubungan_keluarga' => $ibuStatus,
                'pekerjaan'            => $faker->jobTitle(),
                'penghasilan'          => $faker->randomElement(['500000', '1000000', '2000000']),
                'wali'                 => !$ibuWafat,
                'status'               => true,
                'created_by'           => 1,
            ]);
            DB::table('keluarga')->insert([
                'no_kk'      => $newNoKK,
                'id_biodata' => $newIbuId,
                'status'     => true,
                'created_by' => 1,
            ]);

            $keluargaTersimpan[] = [
                'no_kk'   => $newNoKK,
                'ayah_id' => $ayahId,
                'ibu_id'  => $newIbuId,
            ];

            $childIdExtra = (string) Str::uuid();
            DB::table('biodata')->insert([
                'id'                          => $childIdExtra,
                'negara_id'                   => $negaraId,
                'provinsi_id'                 => $provinsiId,
                'kabupaten_id'                => $kabupatenId,
                'kecamatan_id'                => $kecamatanId,
                'jalan'                       => $faker->streetAddress,
                'kode_pos'                    => $faker->postcode,
                'nama'                        => $faker->name($faker->randomElement(['male', 'female'])),
                'no_passport'                 => $faker->numerify('############'),
                'jenis_kelamin'               => $faker->randomElement(['l', 'p']),
                'tanggal_lahir'               => $faker->date(),
                'tempat_lahir'                => $faker->city,
                'anak_keberapa'               => rand(1, 5),
                'dari_saudara'                => rand(1, 5),
                'nik'                         => $faker->numerify('################'),
                'no_telepon'                  => $faker->phoneNumber,
                'email'                       => $faker->unique()->email,
                'jenjang_pendidikan_terakhir' => $faker->randomElement(['sd/mi', 'smp/mts', 'sma/smk/ma', 'd3', 'd4', 's1', 's2']),
                'smartcard'                   => $faker->numerify('############'),
                'status'                      => true,
                'created_by'                  => 1,
                'created_at'                  => now(),
                'updated_at'                  => now(),
            ]);

            DB::table('keluarga')->insert([
                'no_kk'      => $newNoKK,
                'id_biodata' => $childIdExtra,
                'status'     => true,
                'created_by' => 1,
            ]);

            DB::table('anak_pegawai')->insert([
                'biodata_id'  => $childIdExtra,
                'pegawai_id'  => $pegawaiId,
                'status'      => true,
                'created_by'  => 1,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
            $anakPegawaiCount++;

            // Relasi ke santri atau pendidikan
            if ($faker->boolean(50)) {
                $angkatanSantri     = $faker->randomElement($angkatanSantriList);
                $angkatanIdSantri   = $angkatanSantri->id;
                $tahunAjaranSantri  = DB::table('tahun_ajaran')->where('id', $angkatanSantri->tahun_ajaran_id)->first();

                $tanggalMasukSantriExtra = $faker->dateTimeBetween($tahunAjaranSantri->tanggal_mulai, $tahunAjaranSantri->tanggal_selesai)->format('Y-m-d');
                $tanggalKeluarSantriExtra = null; // langsung aktif

                $santriIdExtra = DB::table('santri')->insertGetId([
                    'biodata_id'     => $childIdExtra,
                    'angkatan_id'    => $angkatanIdSantri,
                    'nis'            => $faker->unique()->numerify('###########'),
                    'tanggal_masuk'  => $tanggalMasukSantriExtra,
                    'tanggal_keluar' => $tanggalKeluarSantriExtra,
                    'status'         => 'aktif',
                    'created_by'     => 1,
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);

                DB::table('domisili_santri')->insert([
                    'santri_id'     => $santriIdExtra,
                    'wilayah_id'    => $faker->randomElement($wilayahIds),
                    'blok_id'       => $faker->randomElement($blokIds),
                    'kamar_id'      => $faker->randomElement($kamarIds),
                    'tanggal_masuk' => $tanggalMasukSantriExtra . ' 00:00:00',
                    'status'        => 'aktif',
                    'created_by'    => 1,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            } else {
                $angkatanPelExtra     = $faker->randomElement($angkatanPelajarList);
                $angkatanPelIdExtra   = $angkatanPelExtra->id;
                $tahunAjaranPelExtra  = DB::table('tahun_ajaran')->where('id', $angkatanPelExtra->tahun_ajaran_id)->first();
                $tanggalMasukPendidikanExtra = $faker->dateTimeBetween($tahunAjaranPelExtra->tanggal_mulai, $tahunAjaranPelExtra->tanggal_selesai)->format('Y-m-d');

                DB::table('pendidikan')->insert([
                    'biodata_id'     => $childIdExtra,
                    'angkatan_id'    => $angkatanPelIdExtra,
                    'no_induk'       => $faker->unique()->numerify('###########'),
                    'lembaga_id'     => $faker->randomElement($lembagaIds),
                    'jurusan_id'     => $faker->randomElement($jurusanIds),
                    'kelas_id'       => $faker->randomElement($kelasIds),
                    'rombel_id'      => $faker->randomElement($rombelIds),
                    'tanggal_masuk'  => $tanggalMasukPendidikanExtra,
                    'status'         => 'aktif',
                    'created_by'     => 1,
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);
            }
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
