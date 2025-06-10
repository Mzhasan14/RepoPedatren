<?php

namespace Database\Seeders;

use DateTime;
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

        // Ambil data blok & kamar
        $wilayahList = DB::table('wilayah')->get();
        $blokList    = DB::table('blok')->get();           // kolom: id, wilayah_id
        $kamarList   = DB::table('kamar')->get();          // kolom: id, blok_id

        // Ambil data lembaga, jurusan, kelas, rombel
        $lembagaList = DB::table('lembaga')->get();        // kolom: id
        $jurusanList = DB::table('jurusan')->get();        // kolom: id, lembaga_id
        $kelasList   = DB::table('kelas')->get();          // kolom: id, jurusan_id
        $rombelList  = DB::table('rombel')->get();         // kolom: id, kelas_id

        // Ambil ID status hubungan keluarga (ayah kandung, ibu kandung)
        $hk         = DB::table('hubungan_keluarga')->get();
        $ayahStatus = $hk->firstWhere('nama_status', 'ayah kandung')->id;
        $ibuStatus  = $hk->firstWhere('nama_status', 'ibu kandung')->id;

        // Ambil daftar biodata_id pegawai
        $pegawaiBiodataIds = DB::table('pegawai')->pluck('biodata_id')->toArray();

        // Ambil daftar ID angkatan
        // Ambil daftar ID angkatan
        $angkatanSantriList  = DB::table('angkatan')->where('kategori', 'santri')->get();
        $angkatanPelajarList = DB::table('angkatan')->where('kategori', 'pelajar')->get();

        // Pastikan data angkatan ada
        if ($angkatanSantriList->isEmpty() || $angkatanPelajarList->isEmpty()) {
            throw new \Exception('Seeder gagal: tabel `angkatan` untuk kategori santri atau pelajar kosong.');
        }

        // Definisikan skenario
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
            'santri_no_domisili'              => [true,  'aktif',  false, null,      5],
        ];
        $weighted = [];
        foreach ($scenarios as $key => $cfg) {
            for ($j = 0; $j < $cfg[4]; $j++) {
                $weighted[] = $key;
            }
        }

        // Simpan data keluarga yang sudah dibuat
        $keluargaTersimpan = [];

        // Counter anak_pegawai
        $anakPegawaiCount    = 0;
        $requiredAnakPegawai = 50;

        for ($i = 1; $i <= 200; $i++) {
            $gunakanKeluargaLama = $faker->boolean(15);

            if ($gunakanKeluargaLama && count($keluargaTersimpan) > 0) {
                $keluarga      = $faker->randomElement($keluargaTersimpan);
                $currentNoKK   = $keluarga['no_kk'];
                $currentAyahId = $keluarga['ayah_id'];
                $currentIbuId  = $keluarga['ibu_id'];

                $ayahBiodata = DB::table('biodata')->where('id', $currentAyahId)->first();
                $negaraId    = $ayahBiodata->negara_id;
                $provinsiId  = $ayahBiodata->provinsi_id;
                $kabupatenId = $ayahBiodata->kabupaten_id;
                $kecamatanId = $ayahBiodata->kecamatan_id;
            } else {
                // PILIH NEGARA
                $negara   = $faker->randomElement($negaraList);
                $negaraId = $negara->id;

                // Filter provinsi
                $provinsiFiltered = $provinsiList->where('negara_id', $negaraId)->values();
                if ($provinsiFiltered->isEmpty()) {
                    $negara   = $negaraList->firstWhere('nama', 'Indonesia') ?? $negaraList->first();
                    $negaraId = $negara->id;
                    $provinsiFiltered = $provinsiList->where('negara_id', $negaraId)->values();
                }
                $provinsi    = $faker->randomElement($provinsiFiltered);
                $provinsiId  = $provinsi->id;

                // Filter kabupaten
                $kabupatenFiltered = $kabupatenList->where('provinsi_id', $provinsiId)->values();
                $kabupaten   = $faker->randomElement($kabupatenFiltered);
                $kabupatenId = $kabupaten->id;

                // Filter kecamatan
                $kecamatanFiltered = $kecamatanList->where('kabupaten_id', $kabupatenId)->values();
                $kecamatan   = $faker->randomElement($kecamatanFiltered);
                $kecamatanId = $kecamatan->id;

                $currentNoKK = $faker->numerify('################');
                // Buat ayah
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
                    'jenis_kelamin' => 'l',
                    'tanggal_lahir' => $faker->date(),
                    'tempat_lahir' => $faker->city,
                    'anak_keberapa' => rand(1, 5),
                    'dari_saudara' => rand(1, 5),
                    'nik' => $faker->numerify('################'),
                    'no_telepon' => $faker->phoneNumber,
                    'email' => $faker->unique()->email,
                    'status' => true,
                    'wafat' => false,
                    'created_by' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Buat ibu
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
                    'jenis_kelamin' => 'p',
                    'tanggal_lahir' => $faker->date(),
                    'tempat_lahir' => $faker->city,
                    'anak_keberapa' => rand(1, 5),
                    'dari_saudara' => rand(1, 5),
                    'nik' => $faker->numerify('################'),
                    'no_telepon' => $faker->phoneNumber,
                    'email' => $faker->unique()->email,
                    'status' => true,
                    'wafat' => false,
                    'created_by' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Insert orang tua (ayah sebagai wali)
                DB::table('orang_tua_wali')->insert([
                    [
                        'id_biodata' => $currentAyahId,
                        'id_hubungan_keluarga' => $ayahStatus,
                        'pekerjaan' => $faker->jobTitle(),
                        'penghasilan' => $faker->randomElement(['500000', '1000000', '2000000']),
                        'wali' => true,
                        'status' => true,
                        'created_by' => 2,
                    ],
                    [
                        'id_biodata' => $currentIbuId,
                        'id_hubungan_keluarga' => $ibuStatus,
                        'pekerjaan' => $faker->jobTitle(),
                        'penghasilan' => $faker->randomElement(['500000', '1000000', '2000000']),
                        'wali' => false,
                        'status' => true,
                        'created_by' => 2,
                    ],
                ]);

                // Simpan ke tabel keluarga
                foreach ([$currentAyahId, $currentIbuId] as $memberId) {
                    DB::table('keluarga')->insert([
                        'no_kk' => $currentNoKK,
                        'id_biodata' => $memberId,
                        'status' => true,
                        'created_by' => 1,
                    ]);
                }

                $keluargaTersimpan[] = [
                    'no_kk' => $currentNoKK,
                    'ayah_id' => $currentAyahId,
                    'ibu_id' => $currentIbuId,
                ];
            }

            // ------------------ MULAI MENYIMPAN DATA ANAK ------------------
            $childId = (string) Str::uuid();
            DB::table('biodata')->insert([
                'id'            => $childId,
                'negara_id'     => $negaraId,
                'provinsi_id'   => $provinsiId,
                'kabupaten_id'  => $kabupatenId,
                'kecamatan_id'  => $kecamatanId,
                'jalan'         => $faker->streetAddress,
                'kode_pos'      => $faker->postcode,
                'nama'          => $faker->name($faker->randomElement(['male', 'female'])),
                'no_passport'   => $faker->numerify('############'),
                'jenis_kelamin' => $faker->randomElement(['l', 'p']),
                'tanggal_lahir' => $faker->date(),
                'tempat_lahir'  => $faker->city,
                'anak_keberapa' => rand(1, 5),
                'dari_saudara'  => rand(1, 5),
                'nik'           => $faker->numerify('################'),
                'no_telepon'    => $faker->phoneNumber,
                'email'         => $faker->unique()->email,
                'jenjang_pendidikan_terakhir' => $faker->randomElement(['sd/mi', 'smp/mts', 'sma/smk/ma', 'd3', 'd4', 's1', 's2']),
                'status'        => true,
                'created_by'    => 1,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
            DB::table('keluarga')->insert([
                'no_kk'      => $currentNoKK,
                'id_biodata' => $childId,
                'status'     => true,
                'created_by' => 1,
            ]);

            // Pilih skenario
            $pick = $faker->randomElement($weighted);
            [$doSantri, $stSantri, $doPendidikan, $stPendidikan] = $scenarios[$pick];
            $noDomisili = $pick === 'santri_no_domisili';

            // ------------------ BAGIAN SANTRI ------------------  
            if ($doSantri) {
                $angkatan = $faker->randomElement($angkatanSantriList);
                $angkatanId = $angkatan->id;
                $tahunAjaran = DB::table('tahun_ajaran')->where('id', $angkatan->tahun_ajaran_id)->first();

                // Gantikan dateTimeBetween dengan logika sederhana
                $startDate = new DateTime($tahunAjaran->tanggal_mulai);
                $endDate = new DateTime($tahunAjaran->tanggal_selesai);
                if ($startDate > $endDate) {
                    $endDate = clone $startDate;
                }
                $tanggalMasukSantri = $startDate->format('Y-m-d');

                if ($stSantri === 'alumni') {
                    $tanggalKeluarSantri = null;
                    $masuk = new \DateTime($tanggalMasukSantri);
                    $maxEnd = (clone $masuk)->modify('+2 years');
                    $now = new \DateTime();

                    if ($masuk < $maxEnd) {
                        $end = $now > $maxEnd ? $maxEnd : $now;
                        $tanggalKeluarSantri = $masuk->format('Y-m-d'); // tetap valid, tidak acak
                    } else {
                        $tanggalKeluarSantri = $tanggalMasukSantri;
                    }
                } else {
                    $tanggalKeluarSantri = null;
                }

                $santriId = DB::table('santri')->insertGetId([
                    'biodata_id'    => $childId,
                    'angkatan_id'   => $angkatanId,
                    'nis'           => $faker->unique()->numerify('###########'),
                    'tanggal_masuk' => $tanggalMasukSantri,
                    'tanggal_keluar' => $tanggalKeluarSantri,
                    'status'        => $stSantri,
                    'created_by'    => 1,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);

                if (! $noDomisili) {
                    $wilayah = $faker->randomElement($wilayahList);
                    $blok = $faker->randomElement($blokList->where('wilayah_id', $wilayah->id)->values());
                    $kamar = $faker->randomElement($kamarList->where('blok_id', $blok->id)->values());

                    if ($stSantri === 'aktif') {
                        DB::table('domisili_santri')->insert([
                            'santri_id'     => $santriId,
                            'wilayah_id'    => $wilayah->id,
                            'blok_id'       => $blok->id,
                            'kamar_id'      => $kamar->id,
                            'tanggal_masuk' => $tanggalMasukSantri . ' 00:00:00',
                            'status'        => 'aktif',
                            'created_by'    => 1,
                            'created_at'    => now(),
                            'updated_at'    => now(),
                        ]);
                    } else {
                        DB::table('riwayat_domisili')->insert([
                            'santri_id'      => $santriId,
                            'wilayah_id'     => $wilayah->id,
                            'blok_id'        => $blok->id,
                            'kamar_id'       => $kamar->id,
                            'tanggal_masuk'  => $tanggalMasukSantri . ' 00:00:00',
                            'tanggal_keluar' => ($tanggalKeluarSantri ?: now()) . ' 00:00:00',
                            'status'         => 'keluar',
                            'created_by'     => 1,
                            'created_at'     => now(),
                            'updated_at'     => now(),
                        ]);
                        DB::table('domisili_santri')->where('santri_id', $santriId)->update([
                            'status' => 'keluar',
                            'updated_at' => now(),
                        ]);
                    }
                }
            }

            // ------------------ BAGIAN PENDIDIKAN ------------------
            if ($doPendidikan) {
                $angkatanPel = $faker->randomElement($angkatanPelajarList);
                $angkatanPelId = $angkatanPel->id;
                $tahunAjaranPel = DB::table('tahun_ajaran')->where('id', $angkatanPel->tahun_ajaran_id)->first();

                // Hindari dateTimeBetween
                $startDate = new DateTime($tahunAjaranPel->tanggal_mulai);
                $endDate = new DateTime($tahunAjaranPel->tanggal_selesai);
                if ($startDate > $endDate) {
                    $endDate = clone $startDate;
                }
                $tanggalMasukPendidikan = $startDate->format('Y-m-d');

                $lembaga = $faker->randomElement($lembagaList);
                $jurusan = $faker->randomElement($jurusanList->where('lembaga_id', $lembaga->id)->values());
                $kelas = $faker->randomElement($kelasList->where('jurusan_id', $jurusan->id)->values());
                $rombel = $faker->randomElement($rombelList->where('kelas_id', $kelas->id)->values());

                if ($stPendidikan === 'aktif') {
                    DB::table('pendidikan')->insert([
                        'biodata_id'     => $childId,
                        'angkatan_id'    => $angkatanPelId,
                        'no_induk'       => $faker->unique()->numerify('###########'),
                        'lembaga_id'     => $lembaga->id,
                        'jurusan_id'     => $jurusan->id,
                        'kelas_id'       => $kelas->id,
                        'rombel_id'      => $rombel->id,
                        'tanggal_masuk'  => $tanggalMasukPendidikan,
                        'status'         => 'aktif',
                        'created_by'     => 1,
                        'created_at'     => now(),
                        'updated_at'     => now(),
                    ]);
                } else {
                    $tanggalKeluarPendidikan = null;
                    $masuk = new \DateTime($tanggalMasukPendidikan);
                    $maxEnd = (clone $masuk)->modify('+2 years');
                    $now = new \DateTime();

                    if ($masuk < $maxEnd) {
                        $end = $now > $maxEnd ? $maxEnd : $now;
                        $tanggalKeluarPendidikan = $masuk->format('Y-m-d');
                    } else {
                        $tanggalKeluarPendidikan = $tanggalMasukPendidikan;
                    }

                    $existing = DB::table('pendidikan')->where('biodata_id', $childId)->where('status', 'aktif')->first();

                    if ($existing) {
                        DB::table('pendidikan')->where('id', $existing->id)->update([
                            'status'         => 'lulus',
                            'tanggal_keluar' => $tanggalKeluarPendidikan,
                            'updated_at'     => now(),
                        ]);

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
                            'status'         => 'lulus',
                            'created_by'     => 1,
                            'created_at'     => now(),
                            'updated_at'     => now(),
                        ]);
                    } else {
                        DB::table('pendidikan')->insert([
                            'biodata_id'     => $childId,
                            'angkatan_id'    => $angkatanPelId,
                            'no_induk'       => $faker->unique()->numerify('###########'),
                            'lembaga_id'     => $lembaga->id,
                            'jurusan_id'     => $jurusan->id,
                            'kelas_id'       => $kelas->id,
                            'rombel_id'      => $rombel->id,
                            'tanggal_masuk'  => $tanggalMasukPendidikan,
                            'tanggal_keluar' => $tanggalKeluarPendidikan,
                            'status'         => 'lulus',
                            'created_by'     => 1,
                            'created_at'     => now(),
                            'updated_at'     => now(),
                        ]);

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
                            'tanggal_masuk'  => $pendidikanBaru->tanggal_masuk,
                            'tanggal_keluar' => $tanggalKeluarPendidikan,
                            'status'         => 'lulus',
                            'created_by'     => 1,
                            'created_at'     => now(),
                            'updated_at'     => now(),
                        ]);
                    }
                }
            }


            // ------------------ BAGIAN ANAK PEGAWAI ------------------
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

                if ($faker->boolean(50)) {
                    // tambah santri
                    $angkatan      = $faker->randomElement($angkatanSantriList);
                    $angkatanId    = $angkatan->id;
                    $tahunAjaran   = DB::table('tahun_ajaran')->where('id', $angkatan->tahun_ajaran_id)->first();
                    $tanggalMasukSantri = $faker->dateTimeBetween($tahunAjaran->tanggal_mulai, $tahunAjaran->tanggal_selesai)->format('Y-m-d');
                    $santriId = DB::table('santri')->insertGetId([
                        'biodata_id'    => $childId,
                        'angkatan_id'   => $angkatanId,
                        'nis'           => $faker->unique()->numerify('###########'),
                        'tanggal_masuk' => $tanggalMasukSantri,
                        'status'        => 'aktif',
                        'created_by'    => 1,
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ]);
                    $wilayah      = $faker->randomElement($wilayahList);
                    $wilayahId    = $wilayah->id;
                    $blokFiltered = $blokList->where('wilayah_id', $wilayahId)->values();
                    $blok         = $faker->randomElement($blokFiltered);
                    $blokId       = $blok->id;
                    $kamarFiltered = $kamarList->where('blok_id', $blokId)->values();
                    $kamar         = $faker->randomElement($kamarFiltered);
                    $kamarId       = $kamar->id;
                    DB::table('domisili_santri')->insert([
                        'santri_id'     => $santriId,
                        'wilayah_id'    => $wilayahId,
                        'blok_id'       => $blokId,
                        'kamar_id'      => $kamarId,
                        'tanggal_masuk' => $tanggalMasukSantri . ' 00:00:00',
                        'status'        => 'aktif',
                        'created_by'    => 1,
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ]);
                } else {
                    // tambah pendidikan
                    $angkatanPel     = $faker->randomElement($angkatanPelajarList);
                    $angkatanPelId   = $angkatanPel->id;
                    $tahunAjaranPel  = DB::table('tahun_ajaran')->where('id', $angkatanPel->tahun_ajaran_id)->first();
                    $tanggalMasukPendidikan = $faker->dateTimeBetween($tahunAjaranPel->tanggal_mulai, $tahunAjaranPel->tanggal_selesai)->format('Y-m-d');
                    $lembaga    = $faker->randomElement($lembagaList);
                    $lembagaId  = $lembaga->id;
                    $jurusanF   = $jurusanList->where('lembaga_id', $lembagaId)->values();
                    $jurusan    = $faker->randomElement($jurusanF);
                    $jurusanId  = $jurusan->id;
                    $kelasF     = $kelasList->where('jurusan_id', $jurusanId)->values();
                    $kelas      = $faker->randomElement($kelasF);
                    $kelasId    = $kelas->id;
                    $rombelF    = $rombelList->where('kelas_id', $kelasId)->values();
                    $rombel     = $faker->randomElement($rombelF);
                    $rombelId   = $rombel->id;
                    DB::table('pendidikan')->insert([
                        'biodata_id'     => $childId,
                        'angkatan_id'    => $angkatanPelId,
                        'no_induk'       => $faker->unique()->numerify('###########'),
                        'lembaga_id'     => $lembagaId,
                        'jurusan_id'     => $jurusanId,
                        'kelas_id'       => $kelasId,
                        'rombel_id'      => $rombelId,
                        'tanggal_masuk'  => $tanggalMasukPendidikan,
                        'status'         => 'aktif',
                        'created_by'     => 1,
                        'created_at'     => now(),
                        'updated_at'     => now(),
                    ]);
                }
            }
        }

        // Tambah hingga minimal 50 anak_pegawai
        if ($anakPegawaiCount < $requiredAnakPegawai) {
            $pegawaiList   = DB::table('pegawai')->select('id', 'biodata_id')->get()->toArray();
            $need          = $requiredAnakPegawai - $anakPegawaiCount;
            $limitPegawai  = count($pegawaiList);
            if ($limitPegawai === 0) return;
            for ($j = 0; $j < $need; $j++) {
                $randomPegawai = $faker->randomElement($pegawaiList);
                $ayahId        = $randomPegawai->biodata_id;
                $pegawaiId     = $randomPegawai->id;
                $ayahBiodata   = DB::table('biodata')->where('id', $ayahId)->first();
                if (!$ayahBiodata) continue;
                $negaraId    = $ayahBiodata->negara_id;
                $provinsiId  = $ayahBiodata->provinsi_id;
                $kabupatenId = $ayahBiodata->kabupaten_id;
                $kecamatanId = $ayahBiodata->kecamatan_id;
                $newNoKK     = $faker->numerify('###############');
                if (!DB::table('orang_tua_wali')->where('id_biodata', $ayahId)->exists()) {
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
                    'id'            => $newIbuId,
                    'negara_id'     => $negaraId,
                    'provinsi_id'   => $provinsiId,
                    'kabupaten_id'  => $kabupatenId,
                    'kecamatan_id'  => $kecamatanId,
                    'jalan'         => $faker->streetAddress,
                    'kode_pos'      => $faker->postcode,
                    'nama'          => $faker->name('female'),
                    'no_passport'   => $faker->numerify('############'),
                    'jenis_kelamin' => 'p',
                    'tanggal_lahir' => $faker->date(),
                    'tempat_lahir'  => $faker->city,
                    'anak_keberapa' => rand(1, 5),
                    'dari_saudara'  => rand(1, 5),
                    'nik'           => $faker->numerify('################'),
                    'no_telepon'    => $faker->phoneNumber,
                    'email'         => $faker->unique()->email,
                    'jenjang_pendidikan_terakhir' => $faker->randomElement(['sd/mi', 'smp/mts', 'sma/smk/ma', 'd3', 'd4', 's1', 's2']),
                    'smartcard'     => $faker->numerify('############'),
                    'status'        => true,
                    'wafat'         => $ibuWafat,
                    'created_by'    => 1,
                    'created_at'    => now(),
                    'updated_at'    => now(),
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

                $childIdExtra = (string) Str::uuid();
                DB::table('biodata')->insert([
                    'id'            => $childIdExtra,
                    'negara_id'     => $negaraId,
                    'provinsi_id'   => $provinsiId,
                    'kabupaten_id'  => $kabupatenId,
                    'kecamatan_id'  => $kecamatanId,
                    'jalan'         => $faker->streetAddress,
                    'kode_pos'      => $faker->postcode,
                    'nama'          => $faker->name($faker->randomElement(['male', 'female'])),
                    'no_passport'   => $faker->numerify('############'),
                    'jenis_kelamin' => $faker->randomElement(['l', 'p']),
                    'tanggal_lahir' => $faker->date(),
                    'tempat_lahir'  => $faker->city,
                    'anak_keberapa' => rand(1, 5),
                    'dari_saudara'  => rand(1, 5),
                    'nik'           => $faker->numerify('################'),
                    'no_telepon'    => $faker->phoneNumber,
                    'email'         => $faker->unique()->email,
                    'jenjang_pendidikan_terakhir' => $faker->randomElement(['sd/mi', 'smp/mts', 'sma/smk/ma', 'd3', 'd4', 's1', 's2']),
                    'smartcard'     => $faker->numerify('############'),
                    'status'        => true,
                    'created_by'    => 1,
                    'created_at'    => now(),
                    'updated_at'    => now(),
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

                if ($faker->boolean(50)) {
                    $angkatanSantri     = $faker->randomElement($angkatanSantriList);
                    $angkatanIdSantri   = $angkatanSantri->id;
                    $tahunAjaranSantri  = DB::table('tahun_ajaran')->where('id', $angkatanSantri->tahun_ajaran_id)->first();
                    $tanggalMasukSantriExtra = $faker->dateTimeBetween($tahunAjaranSantri->tanggal_mulai, $tahunAjaranSantri->tanggal_selesai)->format('Y-m-d');
                    $santriIdExtra = DB::table('santri')->insertGetId([
                        'biodata_id'     => $childIdExtra,
                        'angkatan_id'    => $angkatanIdSantri,
                        'nis'            => $faker->unique()->numerify('###########'),
                        'tanggal_masuk'  => $tanggalMasukSantriExtra,
                        'status'         => 'aktif',
                        'created_by'     => 1,
                        'created_at'     => now(),
                        'updated_at'     => now(),
                    ]);
                    $wilayah      = $faker->randomElement($wilayahList);
                    $wilayahId    = $wilayah->id;
                    $blokFiltered = $blokList->where('wilayah_id', $wilayahId)->values();
                    $blok         = $faker->randomElement($blokFiltered);
                    $blokId       = $blok->id;
                    $kamarFiltered = $kamarList->where('blok_id', $blokId)->values();
                    $kamar         = $faker->randomElement($kamarFiltered);
                    $kamarId       = $kamar->id;
                    DB::table('domisili_santri')->insert([
                        'santri_id'     => $santriIdExtra,
                        'wilayah_id'    => $wilayahId,
                        'blok_id'       => $blokId,
                        'kamar_id'      => $kamarId,
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
                    $lembaga    = $faker->randomElement($lembagaList);
                    $lembagaId  = $lembaga->id;
                    $jurusanF   = $jurusanList->where('lembaga_id', $lembagaId)->values();
                    $jurusan    = $faker->randomElement($jurusanF);
                    $jurusanId  = $jurusan->id;
                    $kelasF     = $kelasList->where('jurusan_id', $jurusanId)->values();
                    $kelas      = $faker->randomElement($kelasF);
                    $kelasId    = $kelas->id;
                    $rombelF    = $rombelList->where('kelas_id', $kelasId)->values();
                    $rombel     = $faker->randomElement($rombelF);
                    $rombelId   = $rombel->id;
                    DB::table('pendidikan')->insert([
                        'biodata_id'     => $childIdExtra,
                        'angkatan_id'    => $angkatanPelIdExtra,
                        'no_induk'       => $faker->unique()->numerify('###########'),
                        'lembaga_id'     => $lembagaId,
                        'jurusan_id'     => $jurusanId,
                        'kelas_id'       => $kelasId,
                        'rombel_id'      => $rombelId,
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
