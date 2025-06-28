<?php

namespace Database\Seeders;

use DateTime;
use Faker\Factory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DataKeluargaSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Factory::create('id_ID');

        $pekerjaanList = [
            'Belum/Tidak Bekerja', 'Mengurus Rumah Tangga', 'Pelajar/Mahasiswa', 'Pensiunan',
            'Pegawai Negeri Sipil (PNS)', 'Tentara Nasional Indonesia (TNI)', 'Kepolisian RI (POLRI)',
            'Perdagangan', 'Petani/Pekebun', 'Peternak', 'Nelayan/Perikanan', 'Industri', 'Konstruksi',
            'Transportasi', 'Karyawan Swasta', 'Karyawan BUMN', 'Karyawan BUMD', 'Karyawan Honorer',
            'Buruh Harian Lepas', 'Buruh Tani/Perkebunan', 'Buruh Nelayan/Perikanan', 'Buruh Peternakan',
            'Pembantu Rumah Tangga', 'Tukang Cukur', 'Tukang Listrik', 'Tukang Batu', 'Tukang Kayu',
            'Tukang Sol Sepatu', 'Tukang Las/Pandai Besi', 'Tukang Jahit', 'Tukang Gigi', 'Penata Rias',
            'Penata Busana', 'Penata Rambut', 'Mekanik', 'Seniman', 'Tabib', 'Paraji', 'Perancang Busana',
            'Penterjemah', 'Imam Masjid', 'Pendeta', 'Pastor', 'Wartawan', 'Ustadz/Mubaligh', 'Juru Masak',
            'Promotor Acara', 'Anggota DPR-RI', 'Anggota DPD', 'Anggota BPK', 'Presiden', 'Wakil Presiden',
            'Anggota Mahkamah Konstitusi', 'Anggota Kabinet/Kementerian', 'Duta Besar', 'Gubernur', 'Wakil Gubernur',
            'Bupati', 'Wakil Bupati', 'Walikota', 'Wakil Walikota', 'Anggota DPRD Propinsi',
            'Anggota DPRD Kabupaten/Kota', 'Dosen', 'Guru', 'Pilot', 'Pengacara', 'Notaris', 'Arsitek',
            'Akuntan', 'Konsultan', 'Dokter', 'Bidan', 'Perawat', 'Apoteker', 'Psikiater/Psikolog',
            'Penyiar Televisi', 'Penyiar Radio', 'Pelaut', 'Peneliti', 'Sopir', 'Pialang', 'Paranormal',
            'Pedagang', 'Perangkat Desa', 'Kepala Desa', 'Biarawati', 'Wiraswasta'
        ];

        // ==== HELPER FUNCTION ====
        $usedNames = [
            'male' => [],
            'female' => [],
        ];
        $generateUniqueName = function ($faker, $gender, &$usedNames) {
            do {
                $name = $faker->unique()->name($gender);
            } while (in_array($name, $usedNames[$gender]));
            $usedNames[$gender][] = $name;
            return $name;
        };
        $generateNIK = function ($kodeWilayah, $tanggalLahir) {
            $tgl = date('dmy', strtotime($tanggalLahir));
            return $kodeWilayah . $tgl . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
        };
        $generatePhone = function () {
            $prefix = ['0811', '0812', '0813', '0821', '0822', '0823', '0852', '0853', '0851'];
            $num = $prefix[array_rand($prefix)];
            $num .= str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);
            $num .= str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);
            return $num;
        };
        $generateEmail = function ($name, $faker) {
            $clear = strtolower(preg_replace('/[^a-z]/', '.', $name));
            $clear = preg_replace('/\.+/', '.', $clear);
            return $clear . '@' . $faker->freeEmailDomain;
        };
        // ==== END HELPER FUNCTION ====

        // Ambil data referensi
        $negaraList = DB::table('negara')->get();
        $provinsiList = DB::table('provinsi')->get();
        $kabupatenList = DB::table('kabupaten')->get();
        $kecamatanList = DB::table('kecamatan')->get();
        $wilayahList = DB::table('wilayah')->get();
        $blokList = DB::table('blok')->get();
        $kamarList = DB::table('kamar')->get();
        $lembagaList = DB::table('lembaga')->get();
        $jurusanList = DB::table('jurusan')->get();
        $kelasList = DB::table('kelas')->get();
        $rombelList = DB::table('rombel')->get();

        $hk = DB::table('hubungan_keluarga')->get();
        $ayahStatus = $hk->firstWhere('nama_status', 'ayah kandung')->id;
        $ibuStatus = $hk->firstWhere('nama_status', 'ibu kandung')->id;

        $pegawaiBiodataIds = DB::table('pegawai')->pluck('biodata_id')->toArray();
        $angkatanSantriList = DB::table('angkatan')->where('kategori', 'santri')->get();
        $angkatanPelajarList = DB::table('angkatan')->where('kategori', 'pelajar')->get();

        if ($angkatanSantriList->isEmpty() || $angkatanPelajarList->isEmpty()) {
            throw new \Exception('Seeder gagal: tabel `angkatan` untuk kategori santri atau pelajar kosong.');
        }

        // Skema santri/pendidikan
        $scenarios = [
            'active_both' => [true,  'aktif',  true,  'aktif',   40],
            'santri_only_active' => [true,  'aktif',  false, null,     10],
            'santri_only_alumni' => [true,  'alumni', false, null,      5],
            'pelajar_only_active' => [false, null,     true,  'aktif',   10],
            'pelajar_only_lulus' => [false, null,     true,  'lulus',    5],
            'santri_active_pendidikan_lulus' => [true,  'aktif',  true,  'lulus',   10],
            'santri_alumni_pendidikan_active' => [true,  'alumni', true,  'aktif',   10],
            'lulus_both' => [true,  'alumni', true,  'lulus',   10],
            'santri_no_domisili' => [true,  'aktif',  false, null,      5],
        ];
        $weighted = [];
        foreach ($scenarios as $key => $cfg) {
            for ($j = 0; $j < $cfg[4]; $j++) {
                $weighted[] = $key;
            }
        }

        $keluargaTersimpan = [];
        $anakPegawaiCount = 0;
        $requiredAnakPegawai = 50;
        $now = new \DateTime();

        // --------- GENERATE KELUARGA (ayah-ibu-anak) ----------
        for ($i = 1; $i <= 200; $i++) {
            $gunakanKeluargaLama = $faker->boolean(15) && count($keluargaTersimpan) > 0;
            if ($gunakanKeluargaLama) {
                do {
                    $keluarga = $faker->randomElement($keluargaTersimpan);
                    $ayahBiodata = DB::table('biodata')->where('id', $keluarga['ayah_id'])->first();
                    $ibuBiodata = DB::table('biodata')->where('id', $keluarga['ibu_id'])->first();
                } while (!$ayahBiodata || !$ibuBiodata);
                $currentNoKK = $keluarga['no_kk'];
                $currentAyahId = $keluarga['ayah_id'];
                $currentIbuId = $keluarga['ibu_id'];
                $negaraId = $ayahBiodata->negara_id;
                $provinsiId = $ayahBiodata->provinsi_id;
                $kabupatenId = $ayahBiodata->kabupaten_id;
                $kecamatanId = $ayahBiodata->kecamatan_id;
                $kodeWilayah = str_pad(
                    (is_numeric($provinsiId) ? $provinsiId : '11') .
                    (is_numeric($kabupatenId) ? $kabupatenId : '01') .
                    (is_numeric($kecamatanId) ? $kecamatanId : '01'),
                    6, '0', STR_PAD_RIGHT
                );
            } else {
                $negara = $faker->randomElement($negaraList);
                $negaraId = $negara->id;
                $provinsiFiltered = $provinsiList->where('negara_id', $negaraId)->values();
                if ($provinsiFiltered->isEmpty()) {
                    $negara = $negaraList->firstWhere('nama', 'Indonesia') ?? $negaraList->first();
                    $negaraId = $negara->id;
                    $provinsiFiltered = $provinsiList->where('negara_id', $negaraId)->values();
                }
                $provinsi = $faker->randomElement($provinsiFiltered);
                $provinsiId = $provinsi->id;
                $kabupatenFiltered = $kabupatenList->where('provinsi_id', $provinsiId)->values();
                $kabupaten = $faker->randomElement($kabupatenFiltered);
                $kabupatenId = $kabupaten->id;
                $kecamatanFiltered = $kecamatanList->where('kabupaten_id', $kabupatenId)->values();
                $kecamatan = $faker->randomElement($kecamatanFiltered);
                $kecamatanId = $kecamatan->id;

                $kodeWilayah = str_pad(
                    (is_numeric($provinsiId) ? $provinsiId : '11') .
                    (is_numeric($kabupatenId) ? $kabupatenId : '01') .
                    (is_numeric($kecamatanId) ? $kecamatanId : '01'),
                    6, '0', STR_PAD_RIGHT
                );

                $currentNoKK = $faker->numerify('################');

                // Ayah
                $currentAyahId = (string) Str::uuid();
                $ayahTglLahir = $faker->date('Y-m-d', (new DateTime())->modify('-35 years')->format('Y-m-d'));
                $ayahNama = $generateUniqueName($faker, 'male', $usedNames);
                $ayahNIK = $generateNIK($kodeWilayah, $ayahTglLahir);
                $ayahPhone = $generatePhone();
                $ayahEmail = $generateEmail($ayahNama, $faker);

                DB::table('biodata')->insert([
                    'id' => $currentAyahId,
                    'negara_id' => $negaraId,
                    'provinsi_id' => $provinsiId,
                    'kabupaten_id' => $kabupatenId,
                    'kecamatan_id' => $kecamatanId,
                    'jalan' => $faker->streetAddress,
                    'kode_pos' => $faker->postcode,
                    'nama' => $ayahNama,
                    'jenis_kelamin' => 'l',
                    'tanggal_lahir' => $ayahTglLahir,
                    'tempat_lahir' => $faker->city,
                    'anak_keberapa' => rand(1, 5),
                    'dari_saudara' => rand(1, 5),
                    'nik' => $ayahNIK,
                    'no_telepon' => $ayahPhone,
                    'email' => $ayahEmail,
                    'status' => true,
                    'wafat' => false,
                    'created_by' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                // Ibu
                $currentIbuId = (string) Str::uuid();
                $ibuTglLahir = $faker->date('Y-m-d', (new DateTime())->modify('-33 years')->format('Y-m-d'));
                $ibuNama = $generateUniqueName($faker, 'female', $usedNames);
                $ibuNIK = $generateNIK($kodeWilayah, $ibuTglLahir);
                $ibuPhone = $generatePhone();
                $ibuEmail = $generateEmail($ibuNama, $faker);

                DB::table('biodata')->insert([
                    'id' => $currentIbuId,
                    'negara_id' => $negaraId,
                    'provinsi_id' => $provinsiId,
                    'kabupaten_id' => $kabupatenId,
                    'kecamatan_id' => $kecamatanId,
                    'jalan' => $faker->streetAddress,
                    'kode_pos' => $faker->postcode,
                    'nama' => $ibuNama,
                    'jenis_kelamin' => 'p',
                    'tanggal_lahir' => $ibuTglLahir,
                    'tempat_lahir' => $faker->city,
                    'anak_keberapa' => rand(1, 5),
                    'dari_saudara' => rand(1, 5),
                    'nik' => $ibuNIK,
                    'no_telepon' => $ibuPhone,
                    'email' => $ibuEmail,
                    'status' => true,
                    'wafat' => false,
                    'created_by' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                DB::table('orang_tua_wali')->insert([
                    [
                        'id_biodata' => $currentAyahId,
                        'id_hubungan_keluarga' => $ayahStatus,
                        'pekerjaan' => $faker->randomElement($pekerjaanList),
                        'penghasilan' => $faker->randomElement(['500000', '1000000', '2000000']),
                        'wali' => true,
                        'status' => true,
                        'created_by' => 2,
                    ],
                    [
                        'id_biodata' => $currentIbuId,
                        'id_hubungan_keluarga' => $ibuStatus,
                        'pekerjaan' => $faker->randomElement($pekerjaanList),
                        'penghasilan' => $faker->randomElement(['500000', '1000000', '2000000']),
                        'wali' => false,
                        'status' => true,
                        'created_by' => 2,
                    ],
                ]);
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

            // BUAT ANAK
            $childId = (string) Str::uuid();
            $jenisKelaminAnak = $faker->randomElement(['l', 'p']);
            $genderAnak = $jenisKelaminAnak === 'l' ? 'male' : 'female';
            $childBirthDate = (new DateTime())->modify('-' . rand(7, 18) . ' years')->format('Y-m-d');
            $namaAnak = $generateUniqueName($faker, $genderAnak, $usedNames);
            $nikAnak = $generateNIK($kodeWilayah, $childBirthDate);
            $phoneAnak = $generatePhone();
            $emailAnak = $generateEmail($namaAnak, $faker);

            DB::table('biodata')->insert([
                'id' => $childId,
                'negara_id' => $negaraId,
                'provinsi_id' => $provinsiId,
                'kabupaten_id' => $kabupatenId,
                'kecamatan_id' => $kecamatanId,
                'jalan' => $faker->streetAddress,
                'kode_pos' => $faker->postcode,
                'nama' => $namaAnak,
                'no_passport' => $faker->numerify('############'),
                'jenis_kelamin' => $jenisKelaminAnak,
                'tanggal_lahir' => $childBirthDate,
                'tempat_lahir' => $faker->city,
                'anak_keberapa' => rand(1, 5),
                'dari_saudara' => rand(1, 5),
                'nik' => $nikAnak,
                'no_telepon' => $phoneAnak,
                'email' => $emailAnak,
                'jenjang_pendidikan_terakhir' => $faker->randomElement(['sd/mi', 'smp/mts', 'sma/smk/ma', 'd3', 'd4', 's1', 's2']),
                'status' => true,
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('keluarga')->insert([
                'no_kk' => $currentNoKK,
                'id_biodata' => $childId,
                'status' => true,
                'created_by' => 1,
            ]);

            $pick = $faker->randomElement($weighted);
            [$doSantri, $stSantri, $doPendidikan, $stPendidikan] = $scenarios[$pick];
            $noDomisili = $pick === 'santri_no_domisili';

            // SANTRI
            if ($doSantri) {
                $angkatan = $faker->randomElement($angkatanSantriList);
                $angkatanId = $angkatan->id;
                $tahunAjaran = DB::table('tahun_ajaran')->where('id', $angkatan->tahun_ajaran_id)->first();
                $startDate = new DateTime($tahunAjaran->tanggal_mulai);
                $endDate = new DateTime($tahunAjaran->tanggal_selesai);
                $nowDate = new DateTime();
                $maxMasuk = $nowDate < $endDate ? $nowDate : $endDate;
                $tanggalMasukSantri = $startDate > $maxMasuk ? $startDate->format('Y-m-d') : $faker->dateTimeBetween($startDate->format('Y-m-d'), $maxMasuk->format('Y-m-d'))->format('Y-m-d');
                if ($tanggalMasukSantri > date('Y-m-d')) $tanggalMasukSantri = date('Y-m-d');
                if ($stSantri === 'alumni') {
                    $keluarDate = (new DateTime($tanggalMasukSantri))->modify('+3 years');
                    $tanggalKeluarSantri = $keluarDate > $nowDate ? $nowDate->format('Y-m-d') : $keluarDate->format('Y-m-d');
                } else {
                    $tanggalKeluarSantri = null;
                }
                $santriId = DB::table('santri')->insertGetId([
                    'biodata_id' => $childId,
                    'angkatan_id' => $angkatanId,
                    'nis' => $faker->unique()->numerify('###########'),
                    'tanggal_masuk' => $tanggalMasukSantri,
                    'tanggal_keluar' => $tanggalKeluarSantri,
                    'status' => $stSantri,
                    'created_by' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                if (!$noDomisili) {
                    // FILTER wilayah sesuai jenis kelamin anak
                    if ($jenisKelaminAnak === 'l') {
                        $wilayahFiltered = $wilayahList->where('kategori', 'putra')->values();
                    } elseif ($jenisKelaminAnak === 'p') {
                        $wilayahFiltered = $wilayahList->where('kategori', 'putri')->values();
                    } else {
                        $wilayahFiltered = $wilayahList;
                    }
                    $wilayah = $wilayahFiltered->isEmpty() ? $faker->randomElement($wilayahList) : $faker->randomElement($wilayahFiltered);

                    $blokFiltered = $blokList->where('wilayah_id', $wilayah->id)->values();
                    $blok = $blokFiltered->isEmpty() ? $faker->randomElement($blokList) : $faker->randomElement($blokFiltered);

                    $kamarFiltered = $kamarList->where('blok_id', $blok->id)->values();
                    $kamar = $kamarFiltered->isEmpty() ? $faker->randomElement($kamarList) : $faker->randomElement($kamarFiltered);

                    if ($stSantri === 'aktif') {
                        DB::table('domisili_santri')->insert([
                            'santri_id' => $santriId,
                            'wilayah_id' => $wilayah->id,
                            'blok_id' => $blok->id,
                            'kamar_id' => $kamar->id,
                            'tanggal_masuk' => $tanggalMasukSantri . ' 00:00:00',
                            'status' => 'aktif',
                            'created_by' => 1,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    } else {
                        DB::table('riwayat_domisili')->insert([
                            'santri_id' => $santriId,
                            'wilayah_id' => $wilayah->id,
                            'blok_id' => $blok->id,
                            'kamar_id' => $kamar->id,
                            'tanggal_masuk' => $tanggalMasukSantri . ' 00:00:00',
                            'tanggal_keluar' => ($tanggalKeluarSantri ?: now()) . ' 00:00:00',
                            'status' => 'keluar',
                            'created_by' => 1,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                        DB::table('domisili_santri')->where('santri_id', $santriId)->update([
                            'status' => 'keluar',
                            'updated_at' => now(),
                        ]);
                    }
                }
            }

            // PENDIDIKAN
            if ($doPendidikan) {
                $angkatanPel = $faker->randomElement($angkatanPelajarList);
                $angkatanPelId = $angkatanPel->id;
                $tahunAjaranPel = DB::table('tahun_ajaran')->where('id', $angkatanPel->tahun_ajaran_id)->first();
                $startDate = new DateTime($tahunAjaranPel->tanggal_mulai);
                $endDate = new DateTime($tahunAjaranPel->tanggal_selesai);
                $nowDate = new DateTime();
                $maxMasuk = $nowDate < $endDate ? $nowDate : $endDate;
                $tanggalMasukPendidikan = $startDate > $maxMasuk ? $startDate->format('Y-m-d') : $faker->dateTimeBetween($startDate->format('Y-m-d'), $maxMasuk->format('Y-m-d'))->format('Y-m-d');
                if ($tanggalMasukPendidikan > date('Y-m-d')) $tanggalMasukPendidikan = date('Y-m-d');

                $lembaga = $faker->randomElement($lembagaList);
                $jurusan = $faker->randomElement($jurusanList->where('lembaga_id', $lembaga->id)->values());
                $kelas = $faker->randomElement($kelasList->where('jurusan_id', $jurusan->id)->values());

                // FILTER rombel sesuai jenis kelamin anak
                $rombelFiltered = $rombelList
                    ->where('kelas_id', $kelas->id)
                    ->where('gender_rombel', $jenisKelaminAnak === 'l' ? 'putra' : 'putri')
                    ->values();
                $rombel = $rombelFiltered->isEmpty()
                    ? $faker->randomElement($rombelList->where('kelas_id', $kelas->id)->values())
                    : $faker->randomElement($rombelFiltered);

                if ($stPendidikan === 'aktif') {
                    DB::table('pendidikan')->insert([
                        'biodata_id' => $childId,
                        'angkatan_id' => $angkatanPelId,
                        'no_induk' => $faker->unique()->numerify('###########'),
                        'lembaga_id' => $lembaga->id,
                        'jurusan_id' => $jurusan->id,
                        'kelas_id' => $kelas->id,
                        'rombel_id' => $rombel->id,
                        'tanggal_masuk' => $tanggalMasukPendidikan,
                        'status' => 'aktif',
                        'created_by' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    $keluarDate = (new DateTime($tanggalMasukPendidikan))->modify('+3 years');
                    $tanggalKeluarPendidikan = $keluarDate > $nowDate ? $nowDate->format('Y-m-d') : $keluarDate->format('Y-m-d');
                    $existing = DB::table('pendidikan')->where('biodata_id', $childId)->where('status', 'aktif')->first();
                    if ($existing) {
                        DB::table('pendidikan')->where('id', $existing->id)->update([
                            'status' => 'lulus',
                            'tanggal_keluar' => $tanggalKeluarPendidikan,
                            'updated_at' => now(),
                        ]);
                        DB::table('riwayat_pendidikan')->insert([
                            'biodata_id' => $childId,
                            'angkatan_id' => $existing->angkatan_id,
                            'no_induk' => $existing->no_induk,
                            'lembaga_id' => $existing->lembaga_id,
                            'jurusan_id' => $existing->jurusan_id,
                            'kelas_id' => $existing->kelas_id,
                            'rombel_id' => $existing->rombel_id,
                            'tanggal_masuk' => $existing->tanggal_masuk,
                            'tanggal_keluar' => $tanggalKeluarPendidikan,
                            'status' => 'lulus',
                            'created_by' => 1,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    } else {
                        DB::table('pendidikan')->insert([
                            'biodata_id' => $childId,
                            'angkatan_id' => $angkatanPelId,
                            'no_induk' => $faker->unique()->numerify('###########'),
                            'lembaga_id' => $lembaga->id,
                            'jurusan_id' => $jurusan->id,
                            'kelas_id' => $kelas->id,
                            'rombel_id' => $rombel->id,
                            'tanggal_masuk' => $tanggalMasukPendidikan,
                            'tanggal_keluar' => $tanggalKeluarPendidikan,
                            'status' => 'lulus',
                            'created_by' => 1,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                        $pendidikanBaru = DB::table('pendidikan')
                            ->where('biodata_id', $childId)
                            ->where('status', 'lulus')
                            ->orderBy('created_at', 'desc')
                            ->first();
                        DB::table('riwayat_pendidikan')->insert([
                            'biodata_id' => $childId,
                            'angkatan_id' => $angkatanPelId,
                            'no_induk' => $pendidikanBaru->no_induk,
                            'lembaga_id' => $pendidikanBaru->lembaga_id,
                            'jurusan_id' => $pendidikanBaru->jurusan_id,
                            'kelas_id' => $pendidikanBaru->kelas_id,
                            'rombel_id' => $pendidikanBaru->rombel_id,
                            'tanggal_masuk' => $pendidikanBaru->tanggal_masuk,
                            'tanggal_keluar' => $tanggalKeluarPendidikan,
                            'status' => 'lulus',
                            'created_by' => 1,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }

            // --- ANAK PEGAWAI (di loop utama)
            if (in_array($currentAyahId, $pegawaiBiodataIds)) {
                $pegawaiId = DB::table('pegawai')->where('biodata_id', $currentAyahId)->value('id');
                if ($faker->boolean(60)) {
                    // Pendidikan aktif
                    $angkatanPel = $faker->randomElement($angkatanPelajarList);
                    $angkatanPelId = $angkatanPel->id;
                    $tahunAjaranPel = DB::table('tahun_ajaran')->where('id', $angkatanPel->tahun_ajaran_id)->first();
                    $startDate = new DateTime($tahunAjaranPel->tanggal_mulai);
                    $endDate = new DateTime($tahunAjaranPel->tanggal_selesai);
                    $nowDate = new DateTime();
                    $maxMasuk = $nowDate < $endDate ? $nowDate : $endDate;
                    $tanggalMasuk = $startDate > $maxMasuk ? $startDate->format('Y-m-d') : $faker->dateTimeBetween($startDate->format('Y-m-d'), $maxMasuk->format('Y-m-d'))->format('Y-m-d');

                    $lembaga = $faker->randomElement($lembagaList);
                    $jurusan = $faker->randomElement($jurusanList->where('lembaga_id', $lembaga->id)->values());
                    $kelas = $faker->randomElement($kelasList->where('jurusan_id', $jurusan->id)->values());

                    $rombelFiltered = $rombelList
                        ->where('kelas_id', $kelas->id)
                        ->where('gender_rombel', $jenisKelaminAnak === 'l' ? 'putra' : 'putri')
                        ->values();
                    $rombel = $rombelFiltered->isEmpty()
                        ? $faker->randomElement($rombelList->where('kelas_id', $kelas->id)->values())
                        : $faker->randomElement($rombelFiltered);

                    DB::table('pendidikan')->insert([
                        'biodata_id' => $childId,
                        'angkatan_id' => $angkatanPelId,
                        'no_induk' => $faker->unique()->numerify('###########'),
                        'lembaga_id' => $lembaga->id,
                        'jurusan_id' => $jurusan->id,
                        'kelas_id' => $kelas->id,
                        'rombel_id' => $rombel->id,
                        'tanggal_masuk' => $tanggalMasuk,
                        'status' => 'aktif',
                        'created_by' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    // Santri aktif
                    $angkatan = $faker->randomElement($angkatanSantriList);
                    $angkatanId = $angkatan->id;
                    $tahunAjaran = DB::table('tahun_ajaran')->where('id', $angkatan->tahun_ajaran_id)->first();
                    $startDate = new DateTime($tahunAjaran->tanggal_mulai);
                    $endDate = new DateTime($tahunAjaran->tanggal_selesai);
                    $nowDate = new DateTime();
                    $maxMasuk = $nowDate < $endDate ? $nowDate : $endDate;
                    $tanggalMasuk = $startDate > $maxMasuk ? $startDate->format('Y-m-d') : $faker->dateTimeBetween($startDate->format('Y-m-d'), $maxMasuk->format('Y-m-d'))->format('Y-m-d');
                    $santriId = DB::table('santri')->insertGetId([
                        'biodata_id' => $childId,
                        'angkatan_id' => $angkatanId,
                        'nis' => $faker->unique()->numerify('###########'),
                        'tanggal_masuk' => $tanggalMasuk,
                        'tanggal_keluar' => null,
                        'status' => 'aktif',
                        'created_by' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    // DOMISILI SANTRI
                    if ($jenisKelaminAnak === 'l') {
                        $wilayahFiltered = $wilayahList->where('kategori', 'putra')->values();
                    } elseif ($jenisKelaminAnak === 'p') {
                        $wilayahFiltered = $wilayahList->where('kategori', 'putri')->values();
                    } else {
                        $wilayahFiltered = $wilayahList;
                    }
                    $wilayah = $wilayahFiltered->isEmpty() ? $faker->randomElement($wilayahList) : $faker->randomElement($wilayahFiltered);

                    $blokFiltered = $blokList->where('wilayah_id', $wilayah->id)->values();
                    $blok = $blokFiltered->isEmpty() ? $faker->randomElement($blokList) : $faker->randomElement($blokFiltered);

                    $kamarFiltered = $kamarList->where('blok_id', $blok->id)->values();
                    $kamar = $kamarFiltered->isEmpty() ? $faker->randomElement($kamarList) : $faker->randomElement($kamarFiltered);

                    DB::table('domisili_santri')->insert([
                        'santri_id' => $santriId,
                        'wilayah_id' => $wilayah->id,
                        'blok_id' => $blok->id,
                        'kamar_id' => $kamar->id,
                        'tanggal_masuk' => $tanggalMasuk . ' 00:00:00',
                        'status' => 'aktif',
                        'created_by' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
                DB::table('anak_pegawai')->insert([
                    'biodata_id' => $childId,
                    'pegawai_id' => $pegawaiId,
                    'status' => true,
                    'created_by' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $anakPegawaiCount++;
            }
        }

        // PATCH KHUSUS: Menambah anak pegawai jika belum ada sama sekali
        $pegawaiList = DB::table('pegawai')->select('id', 'biodata_id')->get();
        if ($pegawaiList->isEmpty()) {
            throw new \Exception('Seeder gagal: Tidak ada data pegawai pada tabel pegawai.');
        }
        foreach ($pegawaiList as $pegawai) {
            $ayahId = $pegawai->biodata_id;
            $pegawaiId = $pegawai->id;
            $sudahAda = DB::table('anak_pegawai')->where('pegawai_id', $pegawaiId)->exists();
            if ($sudahAda) continue;
            $ayahBiodata = DB::table('biodata')->where('id', $ayahId)->first();
            if (!$ayahBiodata) continue;
            $negaraId = $ayahBiodata->negara_id;
            $provinsiId = $ayahBiodata->provinsi_id;
            $kabupatenId = $ayahBiodata->kabupaten_id;
            $kecamatanId = $ayahBiodata->kecamatan_id;
            $kodeWilayah = str_pad(
                (is_numeric($provinsiId) ? $provinsiId : '11') .
                (is_numeric($kabupatenId) ? $kabupatenId : '01') .
                (is_numeric($kecamatanId) ? $kecamatanId : '01'),
                6, '0', STR_PAD_RIGHT
            );
            $newNoKK = $faker->numerify('##############');
            // Ibu
            $newIbuId = (string) Str::uuid();
            $ibuTglLahir = $faker->date('Y-m-d', (new DateTime())->modify('-33 years')->format('Y-m-d'));
            $ibuNama = $generateUniqueName($faker, 'female', $usedNames);
            $ibuNIK = $generateNIK($kodeWilayah, $ibuTglLahir);
            $ibuPhone = $generatePhone();
            $ibuEmail = $generateEmail($ibuNama, $faker);

            DB::table('biodata')->insert([
                'id' => $newIbuId,
                'negara_id' => $negaraId,
                'provinsi_id' => $provinsiId,
                'kabupaten_id' => $kabupatenId,
                'kecamatan_id' => $kecamatanId,
                'jalan' => $faker->streetAddress,
                'kode_pos' => $faker->postcode,
                'nama' => $ibuNama,
                'jenis_kelamin' => 'p',
                'tanggal_lahir' => $ibuTglLahir,
                'tempat_lahir' => $faker->city,
                'anak_keberapa' => rand(1, 5),
                'dari_saudara' => rand(1, 5),
                'nik' => $ibuNIK,
                'no_telepon' => $ibuPhone,
                'email' => $ibuEmail,
                'status' => true,
                'wafat' => false,
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            foreach ([$ayahId, $newIbuId] as $parentId) {
                DB::table('keluarga')->insert([
                    'no_kk' => $newNoKK,
                    'id_biodata' => $parentId,
                    'status' => true,
                    'created_by' => 1,
                ]);
            }
            DB::table('orang_tua_wali')->insert([
                [
                    'id_biodata' => $ayahId,
                    'id_hubungan_keluarga' => $ayahStatus,
                    'pekerjaan' => 'Pegawai Negeri Sipil (PNS)',
                    'penghasilan' => $faker->randomElement(['500000', '1000000', '2000000']),
                    'wali' => true,
                    'status' => true,
                    'created_by' => 2,
                ],
                [
                    'id_biodata' => $newIbuId,
                    'id_hubungan_keluarga' => $ibuStatus,
                    'pekerjaan' => 'Pegawai Negeri Sipil (PNS)',
                    'penghasilan' => $faker->randomElement(['500000', '1000000', '2000000']),
                    'wali' => false,
                    'status' => true,
                    'created_by' => 2,
                ]
            ]);
            // Anak
            $childId = (string) Str::uuid();
            $jenisKelamin = $faker->randomElement(['l', 'p']);
            $gender = $jenisKelamin === 'l' ? 'male' : 'female';
            $childBirthDate = (new DateTime())->modify('-' . rand(7, 18) . ' years')->format('Y-m-d');
            $namaAnak = $generateUniqueName($faker, $gender, $usedNames);
            $nikAnak = $generateNIK($kodeWilayah, $childBirthDate);
            $phoneAnak = $generatePhone();
            $emailAnak = $generateEmail($namaAnak, $faker);

            DB::table('biodata')->insert([
                'id' => $childId,
                'negara_id' => $negaraId,
                'provinsi_id' => $provinsiId,
                'kabupaten_id' => $kabupatenId,
                'kecamatan_id' => $kecamatanId,
                'jalan' => $faker->streetAddress,
                'kode_pos' => $faker->postcode,
                'nama' => $namaAnak,
                'jenis_kelamin' => $jenisKelamin,
                'tanggal_lahir' => $childBirthDate,
                'tempat_lahir' => $faker->city,
                'anak_keberapa' => rand(1, 5),
                'dari_saudara' => rand(1, 5),
                'nik' => $nikAnak,
                'no_telepon' => $phoneAnak,
                'email' => $emailAnak,
                'status' => true,
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('keluarga')->insert([
                'no_kk' => $newNoKK,
                'id_biodata' => $childId,
                'status' => true,
                'created_by' => 1,
            ]);
            if ($faker->boolean(60)) {
                // Pendidikan aktif
                $angkatanPel = $faker->randomElement($angkatanPelajarList);
                $angkatanPelId = $angkatanPel->id;
                $tahunAjaranPel = DB::table('tahun_ajaran')->where('id', $angkatanPel->tahun_ajaran_id)->first();
                $startDate = new DateTime($tahunAjaranPel->tanggal_mulai);
                $endDate = new DateTime($tahunAjaranPel->tanggal_selesai);
                $nowDate = new DateTime();
                $maxMasuk = $nowDate < $endDate ? $nowDate : $endDate;
                $tanggalMasuk = $startDate > $maxMasuk ? $startDate->format('Y-m-d') : $faker->dateTimeBetween($startDate->format('Y-m-d'), $maxMasuk->format('Y-m-d'))->format('Y-m-d');

                $lembaga = $faker->randomElement($lembagaList);
                $jurusan = $faker->randomElement($jurusanList->where('lembaga_id', $lembaga->id)->values());
                $kelas = $faker->randomElement($kelasList->where('jurusan_id', $jurusan->id)->values());
                $rombelFiltered = $rombelList
                    ->where('kelas_id', $kelas->id)
                    ->where('gender_rombel', $jenisKelamin === 'l' ? 'putra' : 'putri')
                    ->values();
                $rombel = $rombelFiltered->isEmpty()
                    ? $faker->randomElement($rombelList->where('kelas_id', $kelas->id)->values())
                    : $faker->randomElement($rombelFiltered);

                DB::table('pendidikan')->insert([
                    'biodata_id' => $childId,
                    'angkatan_id' => $angkatanPelId,
                    'no_induk' => $faker->unique()->numerify('###########'),
                    'lembaga_id' => $lembaga->id,
                    'jurusan_id' => $jurusan->id,
                    'kelas_id' => $kelas->id,
                    'rombel_id' => $rombel->id,
                    'tanggal_masuk' => $tanggalMasuk,
                    'status' => 'aktif',
                    'created_by' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                // Santri aktif
                $angkatan = $faker->randomElement($angkatanSantriList);
                $angkatanId = $angkatan->id;
                $tahunAjaran = DB::table('tahun_ajaran')->where('id', $angkatan->tahun_ajaran_id)->first();
                $startDate = new DateTime($tahunAjaran->tanggal_mulai);
                $endDate = new DateTime($tahunAjaran->tanggal_selesai);
                $nowDate = new DateTime();
                $maxMasuk = $nowDate < $endDate ? $nowDate : $endDate;
                $tanggalMasuk = $startDate > $maxMasuk ? $startDate->format('Y-m-d') : $faker->dateTimeBetween($startDate->format('Y-m-d'), $maxMasuk->format('Y-m-d'))->format('Y-m-d');
                $santriId = DB::table('santri')->insertGetId([
                    'biodata_id' => $childId,
                    'angkatan_id' => $angkatanId,
                    'nis' => $faker->unique()->numerify('###########'),
                    'tanggal_masuk' => $tanggalMasuk,
                    'tanggal_keluar' => null,
                    'status' => 'aktif',
                    'created_by' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                // DOMISILI SANTRI
                if ($jenisKelamin === 'l') {
                    $wilayahFiltered = $wilayahList->where('kategori', 'putra')->values();
                } elseif ($jenisKelamin === 'p') {
                    $wilayahFiltered = $wilayahList->where('kategori', 'putri')->values();
                } else {
                    $wilayahFiltered = $wilayahList;
                }
                $wilayah = $wilayahFiltered->isEmpty() ? $faker->randomElement($wilayahList) : $faker->randomElement($wilayahFiltered);

                $blokFiltered = $blokList->where('wilayah_id', $wilayah->id)->values();
                $blok = $blokFiltered->isEmpty() ? $faker->randomElement($blokList) : $faker->randomElement($blokFiltered);

                $kamarFiltered = $kamarList->where('blok_id', $blok->id)->values();
                $kamar = $kamarFiltered->isEmpty() ? $faker->randomElement($kamarList) : $faker->randomElement($kamarFiltered);

                DB::table('domisili_santri')->insert([
                    'santri_id' => $santriId,
                    'wilayah_id' => $wilayah->id,
                    'blok_id' => $blok->id,
                    'kamar_id' => $kamar->id,
                    'tanggal_masuk' => $tanggalMasuk . ' 00:00:00',
                    'status' => 'aktif',
                    'created_by' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            DB::table('anak_pegawai')->insert([
                'biodata_id' => $childId,
                'pegawai_id' => $pegawaiId,
                'status' => true,
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
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
