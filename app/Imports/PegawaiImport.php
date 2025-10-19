<?php

namespace App\Imports;

use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PegawaiImport implements ToCollection, WithHeadingRow
{
    protected $userId;

    public function headingRow(): int
    {
        return 2; // header ada di baris ke-2 (sama seperti SantriImport)
    }

    public function __construct($userId)
    {
        $this->userId = $userId;
    }

    public function collection(Collection $rows)
    {
        // Pastikan ada baris (HeadingRow akan otomatis dipakai oleh paket)
        if ($rows->isEmpty()) {
            return;
        }

        DB::beginTransaction();
        try {
            $processedNiks = [];
            foreach ($rows as $index => $rawRow) {
                $excelRow = $index + $this->headingRow() + 1;
                // Cek kalau semua kolom kosong (anggap 0 di kolom tanggal juga kosong)
                $isEmptyRow = collect($rawRow)->filter(function ($val) {
                    // Anggap kosong kalau null, '', whitespace, atau 0 yang berasal dari cell tanggal
                    if (is_numeric($val) && (int)$val === 0) {
                        return false;
                    }
                    return trim((string)$val) !== '';
                })->isEmpty();

                if ($isEmptyRow) {
                    continue; // lewati baris kosong
                }
                // Normalisasi keys header → jadi array biasa dengan key terstandard
                $row = $this->normalizeRow($rawRow->toArray());

                // =========================
                // Deteksi otomatis role berdasarkan keberadaan data kolom entitas
                // (TIDAK lagi memeriksa kolom status_karyawan/status_pengajar/...)
                // =========================
                $willInsertKaryawan = (
                    (isset($row['karyawan_golongan_jabatan']) && trim((string)$row['karyawan_golongan_jabatan']) !== '') ||
                    (isset($row['karyawan_lembaga']) && trim((string)$row['karyawan_lembaga']) !== '') ||
                    (isset($row['karyawan_jabatan']) && trim((string)$row['karyawan_jabatan']) !== '') ||
                    (isset($row['karyawan_keterangan_jabatan']) && trim((string)$row['karyawan_keterangan_jabatan']) !== '') ||
                    (isset($row['karyawan_tanggal_mulai']) && trim((string)$row['karyawan_tanggal_mulai']) !== '')
                );

                $willInsertPengajar = (
                    (isset($row['pengajar_kategori_golongan']) && trim((string)$row['pengajar_kategori_golongan']) !== '') ||
                    (isset($row['pengajar_golongan']) && trim((string)$row['pengajar_golongan']) !== '') ||
                    (isset($row['pengajar_lembaga']) && trim((string)$row['pengajar_lembaga']) !== '') ||
                    (isset($row['pengajar_keterangan_jabatan']) && trim((string)$row['pengajar_keterangan_jabatan']) !== '') ||
                    (isset($row['pengajar_jabatan']) && trim((string)$row['pengajar_jabatan']) !== '') ||
                    (isset($row['pengajar_tahun_masuk']) && trim((string)$row['pengajar_tahun_masuk']) !== '')
                );

                $willInsertPengurus = (
                    (isset($row['pengurus_golongan_jabatan']) && trim((string)$row['pengurus_golongan_jabatan']) !== '') ||
                    (isset($row['pengurus_satuan_kerja']) && trim((string)$row['pengurus_satuan_kerja']) !== '') ||
                    (isset($row['pengurus_jabatan']) && trim((string)$row['pengurus_jabatan']) !== '') ||
                    (isset($row['pengurus_keterangan_jabatan']) && trim((string)$row['pengurus_keterangan_jabatan']) !== '') ||
                    (isset($row['pengurus_tanggal_mulai']) && trim((string)$row['pengurus_tanggal_mulai']) !== '')
                );

                $willInsertWali = (
                    (isset($row['wali_lembaga']) && trim((string)$row['wali_lembaga']) !== '') ||
                    (isset($row['wali_jurusan']) && trim((string)$row['wali_jurusan']) !== '') ||
                    (isset($row['wali_kelas']) && trim((string)$row['wali_kelas']) !== '') ||
                    (isset($row['wali_rombel']) && trim((string)$row['wali_rombel']) !== '') ||
                    (isset($row['wali_periode_awal']) && trim((string)$row['wali_periode_awal']) !== '')
                );

                // Validasi: minimal satu role harus ada datanya (berdasarkan kolom entitas)
                if (!($willInsertKaryawan || $willInsertPengajar || $willInsertPengurus || $willInsertWali)) {
                    throw new \Exception("Minimal satu role (karyawan, pengajar, pengurus, atau wali kelas) harus memiliki data di baris {$excelRow}.");
                }

                // ====== NIK DUPLIKAT FILE CHECK ======
                $nikRaw = isset($row['nik']) ? trim((string)$row['nik']) : '';

                if ($nikRaw !== '') {
                    if (isset($processedNiks[$nikRaw])) {
                        $firstRow = $processedNiks[$nikRaw];
                        throw new \Exception("Duplikat NIK '{$nikRaw}' ditemukan di file Excel (baris {$excelRow} dan baris {$firstRow}).");
                    }
                    $processedNiks[$nikRaw] = $excelRow; // simpan baris pertama kali muncul
                }

                // ===== Insert Biodata =====
                $biodataId = Str::uuid()->toString();

                // Ambil nilai NIK & No Passport (bersih)
                $nikRaw = isset($row['nik']) ? trim((string)$row['nik']) : '';
                $noPassportRaw = isset($row['no_passport']) ? trim((string)$row['no_passport']) : '';

                // Tentukan kewarganegaraan (jika ada)
                $kewarganegaraan = strtoupper(trim((string)($row['kewarganegaraan'] ?? '')));

                // ==========================================================
                // --- TAMBAHAN VALIDASI 1: NEGARA UNTUK WNA ---
                // ==========================================================
                $negaraNama = trim((string)($row['negara'] ?? ''));
                if ($kewarganegaraan === 'WNA' && strtolower($negaraNama) === 'indonesia') {
                    throw new \Exception("Untuk WNA, negara tidak boleh 'Indonesia' di baris {$excelRow}.");
                }
                // ==========================================================
                // --- AKHIR TAMBAHAN 1 ---
                // ==========================================================

                // ===== VALIDASI BARU: NIK ↔ NO PASSPORT =====
                if ($nikRaw !== '' && $noPassportRaw !== '') {
                    throw new \Exception("Kolom 'nik' dan 'no_passport' tidak boleh diisi bersamaan di baris {$excelRow}.");
                }
                if ($kewarganegaraan === 'WNI' && $noPassportRaw !== '') {
                    throw new \Exception("Kewarganegaraan 'WNI' — kolom 'no_passport' harus dikosongkan di baris {$excelRow}.");
                }
                if ($kewarganegaraan === 'WNA' && $nikRaw !== '') {
                    throw new \Exception("Kewarganegaraan 'WNA' — kolom 'nik' harus dikosongkan di baris {$excelRow}.");
                }
                if ($nikRaw === '' && $noPassportRaw === '') {
                    throw new \Exception("Minimal kolom 'nik' atau 'no_passport' harus diisi di baris {$excelRow}.");
                }

                // ===== No KK berdasarkan kewarganegaraan =====
                $noKkRaw = null;
                if ($kewarganegaraan === 'WNA') {
                    $angka13Digit = (string) random_int(1000000000000, 9999999999999);
                    $noKkRaw = 'WNA' . $angka13Digit;
                } elseif ($kewarganegaraan === 'WNI') {
                    $noKkRaw = isset($row['no_kk']) ? trim((string)$row['no_kk']) : '';
                    if ($noKkRaw === '') {
                        throw new \Exception("Kolom 'no_kk' harus diisi untuk kewarganegaraan WNI di baris {$excelRow}.");
                    }
                } else {
                    $noKkRaw = isset($row['no_kk']) ? trim((string)$row['no_kk']) : null;
                }

                // Tetapkan nilai final untuk insert
                $nik = null;
                $noPassport = null;
                if ($kewarganegaraan === 'WNI') {
                    $nik = $nikRaw ?: null;
                } elseif ($kewarganegaraan === 'WNA') {
                    $noPassport = $noPassportRaw ?: null;
                } else {
                    if ($nikRaw !== '') $nik = $nikRaw;
                    if ($noPassportRaw !== '') $noPassport = $noPassportRaw;
                }

                // ======================================================================
                // --- TAMBAHAN VALIDASI 2: CEK STATUS AKTIF PEGAWAI & SANTRI DI DB ---
                // (Ini menggantikan blok "CEK DUPLIKASI NIK / NO PASSPORT DI DB" Anda)
                // ======================================================================
                $existingBiodata = null;
                if (!empty($nikRaw)) {
                    $existingBiodata = DB::table('biodata')->where('nik', $nikRaw)->first();
                } elseif (!empty($noPassportRaw)) {
                    $existingBiodata = DB::table('biodata')->where('no_passport', $noPassportRaw)->first();
                }

                if ($existingBiodata) {
                    $isPegawaiAktif = DB::table('pegawai')
                        ->where('biodata_id', $existingBiodata->id)
                        ->where('status_aktif', 'aktif')
                        ->exists();

                    if ($isPegawaiAktif) {
                        throw new \Exception("Identitas (NIK/Paspor) di baris {$excelRow} sudah terdaftar sebagai PEGAWAI AKTIF.");
                    }

                    $isSantriAktif = DB::table('santri')
                        ->where('biodata_id', $existingBiodata->id)
                        ->where('status', 'aktif')
                        ->exists();

                    if ($isSantriAktif) {
                        throw new \Exception("Identitas (NIK/Paspor) di baris {$excelRow} masih terdaftar sebagai SANTRI AKTIF.");
                    }

                    throw new \Exception("Identitas (NIK/Paspor) di baris {$excelRow} sudah ada di database. Proses import hanya untuk data baru.");
                }

                static $niupSeen = [];
                $niupRaw = isset($row['niup']) ? trim((string)$row['niup']) : '';

                if ($niupRaw !== '') {
                    // 1️⃣ Cek duplikasi di file Excel itu sendiri
                    if (isset($niupSeen[$niupRaw])) {
                        throw new \Exception("NIUP '{$niupRaw}' duplikat di file Excel: baris {$excelRow} sama dengan baris {$niupSeen[$niupRaw]}.");
                    }
                    $niupSeen[$niupRaw] = $excelRow;

                    // 2️⃣ Cek duplikasi di database
                    $existsNiup = DB::table('warga_pesantren')->where('niup', $niupRaw)->exists();
                    if ($existsNiup) {
                        throw new \Exception("NIUP '{$niupRaw}' sudah terdaftar di database (baris {$excelRow}).");
                    }
                }
                // ===== Konversi Jenis Kelamin (lebih toleran) =====
                $jkRaw = strtolower(trim((string)($row['jenis_kelamin'] ?? '')));
                $jkRaw = str_replace(['.', ',', '-', '_', ' '], '', $jkRaw); // hapus simbol

                if (in_array($jkRaw, ['l', 'lk', 'lelaki', 'laki', 'lakilaki', 'cowok'])) {
                    $jenisKelamin = 'l';
                } elseif (in_array($jkRaw, ['p', 'pr', 'perempuan', 'wanita', 'cewek'])) {
                    $jenisKelamin = 'p';
                } else {
                    throw new \Exception("Kolom 'jenis_kelamin' tidak dikenali di baris {$excelRow} (isi: '{$row['jenis_kelamin']}'). Harus diisi 'Laki-laki' atau 'Perempuan'.");
                }


                DB::table('biodata')->insert([
                    'id' => $biodataId,
                    'nama' => $row['nama_lengkap'] ?? null,
                    'nik' => $nik,
                    'no_passport' => $noPassport,
                    'jenis_kelamin' => $jenisKelamin,
                    'tempat_lahir' => $row['tempat_lahir'] ?? null,
                    'tanggal_lahir' => $this->transformDate($row['tanggal_lahir'] ?? null),
                    'jenjang_pendidikan_terakhir' => $row['jenjang_pendidikan_terakhir'] ?? null,
                    'nama_pendidikan_terakhir' => $row['nama_pendidikan_terakhir'] ?? null,
                    'email' => $row['email'] ?? null,
                    'no_telepon' => $row['no_telp'] ?? null,
                    'no_telepon_2' => $row['no_telp_2'] ?? null,
                    'negara_id' => $this->findId('negara', $row['negara'] ?? null, $excelRow, false),
                    'provinsi_id' => $this->findId('provinsi', $row['provinsi'] ?? null, $excelRow, false),
                    'kabupaten_id' => $this->findId('kabupaten', $row['kabupaten'] ?? null, $excelRow, false),
                    'kecamatan_id' => $this->findId('kecamatan', $row['kecamatan'] ?? null, $excelRow, false),
                    'jalan' => $row['jalan'] ?? null,
                    'kode_pos' => $row['kode_pos'] ?? null,
                    // 'smartcard' => $row['smartcard'] ?? null,
                    'anak_keberapa' => $row['anak_keberapa'] ?? null,
                    'dari_saudara' => $row['dari_saudara'] ?? null,
                    'tinggal_bersama' => $row['tinggal_bersama'] ?? null,
                    'status' => true,
                    'wafat' => isset($row['wafat']) && strtolower($row['wafat']) === 'ya',
                    'created_at' => now(),
                    'updated_at' => now(),
                    'created_by' => $this->userId ?? 1
                ]);

                DB::table('keluarga')->insert([
                    'id_biodata' => $biodataId,
                    'no_kk' => $noKkRaw,
                    'status' => 1,
                    'created_by' => $this->userId ?? 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // ===== Insert Warga Pesantren (opsional) jika ada =====
                $niupRaw = isset($row['niup']) ? trim((string)$row['niup']) : null;
                if ($niupRaw) {
                    DB::table('warga_pesantren')->insert([
                        'biodata_id' => $biodataId,
                        'niup' => $niupRaw,
                        'status' => 1,
                        'created_by' => $this->userId ?? 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                // ===== Insert Pegawai =====
                $pegawaiId = DB::table('pegawai')->insertGetId([
                    'biodata_id' => $biodataId,
                    'status_aktif' => 'aktif',
                    'created_at' => now(),
                    'updated_at' => now(),
                    'created_by' => $this->userId ?? 1
                ]);

                // ===== Role: KARYAWAN =====
                if ($willInsertKaryawan) {
                    DB::table('karyawan')->insert([
                        'pegawai_id' => $pegawaiId,
                        'golongan_jabatan_id' => $this->findId('golongan_jabatan', $row['karyawan_golongan_jabatan'] ?? null, $excelRow, false),
                        'lembaga_id' => $this->findId('lembaga', $row['karyawan_lembaga'] ?? null, $excelRow, false),
                        'jabatan' => $row['karyawan_jabatan'] ?? null,
                        'keterangan_jabatan' => $row['karyawan_keterangan_jabatan'] ?? null,
                        'tanggal_mulai' => $this->transformDate($row['karyawan_tanggal_mulai'] ?? null),
                        'status_aktif' => 'aktif',
                        'created_at' => now(),
                        'updated_at' => now(),
                        'created_by' => $this->userId ?? 1
                    ]);
                }

                // ===== Role: PENGAJAR =====
                if ($willInsertPengajar) {
                    if (!empty($row['pengajar_kode_mapel'])) {
                        $kodeMapel = $row['pengajar_kode_mapel'];
                        $mapelExists = DB::table('mata_pelajaran')
                            ->where('kode_mapel', $kodeMapel)
                            ->where('status', 1)
                            ->exists();
                        if ($mapelExists) {
                            throw new \Exception("Kode Mata Pelajaran '{$kodeMapel}' sudah digunakan oleh mapel lain yang aktif (baris {$excelRow}).");
                        }
                    }

                    $kategoriGolId = $this->findId('kategori_golongan', $row['pengajar_kategori_golongan'] ?? null, $excelRow, false);
                    $golonganId = $this->findGolonganId($row['pengajar_golongan'] ?? null, $kategoriGolId, $excelRow, false);

                    // Insert ke tabel pengajar & ambil ID-nya
                    $pengajarId = DB::table('pengajar')->insertGetId([
                        'pegawai_id' => $pegawaiId,
                        'lembaga_id' => $this->findId('lembaga', $row['pengajar_lembaga'] ?? null, $excelRow, false),
                        'golongan_id' => $golonganId,
                        'keterangan_jabatan' => $row['pengajar_keterangan_jabatan'] ?? null,
                        'jabatan' => $row['pengajar_jabatan'] ?? null,
                        'tahun_masuk' => $this->transformDate($row['pengajar_tahun_masuk'] ?? now()),
                        'status_aktif' => 'aktif',
                        'created_at' => now(),
                        'updated_at' => now(),
                        'created_by' => $this->userId ?? 1
                    ]);

                    if (!empty($row['pengajar_nama_mapel']) || !empty($row['nama_mapel_pengajar'])) {
                        $namaMapel = $row['pengajar_nama_mapel'] ?? $row['nama_mapel_pengajar'] ?? null;
                        $kodeMapel = $row['pengajar_kode_mapel'] ?? null;

                        DB::table('mata_pelajaran')->insert([
                            'lembaga_id' => $this->findId('lembaga', $row['pengajar_lembaga'] ?? null, $excelRow, false),
                            'kode_mapel' => $kodeMapel, 
                            'nama_mapel' => $namaMapel,
                            'pengajar_id' => $pengajarId,
                            'status' => 1,
                            'created_by' => $this->userId ?? 1,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }

                // ===== Role: PENGURUS =====
                if ($willInsertPengurus) {
                    DB::table('pengurus')->insert([
                        'pegawai_id' => $pegawaiId,
                        'golongan_jabatan_id' => $this->findId('golongan_jabatan', $row['pengurus_golongan_jabatan'] ?? null, $excelRow, false),
                        'satuan_kerja' => $row['pengurus_satuan_kerja'] ?? null,
                        'jabatan' => $row['pengurus_jabatan'] ?? null,
                        'keterangan_jabatan' => $row['pengurus_keterangan_jabatan'] ?? null,
                        'tanggal_mulai' => $this->transformDate($row['pengurus_tanggal_mulai'] ?? null),
                        'status_aktif' => 'aktif',
                        'created_at' => now(),
                        'updated_at' => now(),
                        'created_by' => $this->userId ?? 1
                    ]);
                }

                // ===== Role: WALI KELAS =====
                if ($willInsertWali) {
                    // Lembaga
                    $lembagaId = $this->findId('lembaga', $row['wali_lembaga'] ?? null, $excelRow, false);
                    // Jurusan sesuai lembaga
                    $jurusanId = $this->findId('jurusan', $row['wali_jurusan'] ?? null, $excelRow, false, [
                        'lembaga_id' => $lembagaId
                    ]);
                    // Kelas sesuai jurusan
                    $kelasId = $this->findId('kelas', $row['wali_kelas'] ?? null, $excelRow, false, [
                        'jurusan_id' => $jurusanId
                    ]);
                    // Rombel sesuai kelas
                    $rombelId = $this->findId('rombel', $row['wali_rombel'] ?? null, $excelRow, false, [
                        'kelas_id' => $kelasId
                    ]);

                    DB::table('wali_kelas')->insert([
                        'pegawai_id'      => $pegawaiId,
                        'lembaga_id'      => $lembagaId,
                        'jurusan_id'      => $jurusanId,
                        'kelas_id'        => $kelasId,
                        'rombel_id'       => $rombelId,
                        // 'jumlah_murid'    => $row['wali_jumlah_murid'] ?? null,
                        'periode_awal'    => $this->transformDate($row['wali_periode_awal'] ?? null),
                        'status_aktif'    => 'aktif',
                        'created_at'      => now(),
                        'updated_at'      => now(),
                        'created_by'      => $this->userId ?? 1
                    ]);
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            // Pastikan $excelRow terdefinisi untuk error message
            $line = $excelRow ?? 'unknown';
            throw new \Exception("Error di baris Excel: {$line} → " . $e->getMessage());
        }
    }


    /**
     * Normalize keys dari header Excel:
     * - hapus tanda '*', '.' dan ganti spasi → underscore
     * - lowercase
     * - bersihkan karakter non-alphanumeric/underscore
     *
     * contoh: "No Telp. Ayah*" -> "no_telp_ayah"
     */
    protected function normalizeRow(array $raw): array
    {
        $normalized = [];
        foreach ($raw as $k => $v) {
            $key = (string)$k;
            $key = trim($key);
            $key = mb_strtolower($key);
            $key = str_replace(['*', '.', ' ', '-'], ['', '_', '_'], $key);
            $key = preg_replace('/[^a-z0-9_]/u', '', $key);
            $key = preg_replace('/_+/', '_', $key);
            $key = trim($key, '_');

            $normalized[$key] = $v;
        }

        // Mapping manual header Excel → key yang dipakai di kode
        $mapping = [
            'nomor_kk'                    => 'no_kk',
            'nomor_telepon_1'              => 'no_telp',
            'nomor_telepon_2'              => 'no_telp_2',
            'lembaga_pengajar'             => 'pengajar_lembaga',
            'golongan_pengajar'            => 'pengajar_golongan',
            'keterangan_jabatan_pengajar'  => 'pengajar_keterangan_jabatan',
            'jenis_jabatan_pengajar'       => 'pengajar_jabatan',
            'tanggal_masuk_pengajar'       => 'pengajar_tahun_masuk',
            'kode_mapel_pengajar'          => 'pengajar_kode_mapel',
            'nama_mapel_pengajar'          => 'pengajar_nama_mapel',
            'golongan_jabatan_karyawan'    => 'karyawan_golongan_jabatan',
            'lembaga_karyawan'             => 'karyawan_lembaga',
            'keterangan_jabatan_karyawan'  => 'karyawan_keterangan_jabatan',
            'jabatanjenis_kontrak_karyawan' => 'karyawan_jabatan',
            'tanggal_mulai_karyawan'       => 'karyawan_tanggal_mulai',
            'golongan_jabatan_pengurus'    => 'pengurus_golongan_jabatan',
            'satuan_kerja_pengurus'        => 'pengurus_satuan_kerja',
            'keterangan_jabatan_pengurus'  => 'pengurus_keterangan_jabatan',
            'jabatanjenis_kontrak_pengurus' => 'pengurus_jabatan',
            'tanggal_mulai_pengurus'       => 'pengurus_tanggal_mulai',
            'lembaga_wali_kelas'           => 'wali_lembaga',
            'jurusan_wali_kelas'           => 'wali_jurusan',
            'kelas_wali_kelas'             => 'wali_kelas',
            'rombel_wali_kelas'            => 'wali_rombel',
            // 'jumlah_murid_wali_kelas'      => 'wali_jumlah_murid',
            'periode_awal_wali_kelas'      => 'wali_periode_awal'
        ];

        // Terapkan mapping
        foreach ($mapping as $from => $to) {
            if (array_key_exists($from, $normalized) && !array_key_exists($to, $normalized)) {
                $normalized[$to] = $normalized[$from];
            }
        }

        return $normalized;
    }

    /**
     * findId unchanged — tetap case-insensitive & safe
     */
    protected function findId(string $table, $value, int $excelRow, bool $required = true, array $filters = [])
    {
        if (! isset($value) || trim((string)$value) === '') {
            if ($required) {
                throw new \Exception("Referensi untuk tabel '{$table}' kosong di baris {$excelRow}.");
            }
            return null;
        }

        $columnMap = [
            'negara'            => 'nama_negara',
            'provinsi'          => 'nama_provinsi',
            'kabupaten'         => 'nama_kabupaten',
            'kecamatan'         => 'nama_kecamatan',
            'wilayah'           => 'nama_wilayah',
            'blok'              => 'nama_blok',
            'kamar'             => 'nama_kamar',
            'lembaga'           => 'nama_lembaga',
            'jurusan'           => 'nama_jurusan',
            'kelas'             => 'nama_kelas',
            'rombel'            => 'nama_rombel',
            'golongan_jabatan'  => 'nama_golongan_jabatan',
            'kategori_golongan' => 'nama_kategori_golongan',
            'golongan'          => 'nama_golongan',
        ];

        $column = $columnMap[$table] ?? 'nama';
        $search = trim((string)$value);
        $searchLower = mb_strtolower($search);

        // ID langsung
        if (is_numeric($search)) {
            $record = DB::table($table)
                ->where('id', $search);

            foreach ($filters as $key => $val) {
                $record->where($key, $val);
            }

            $found = $record->select('id')->first();
            if ($found) {
                return $found->id;
            }
        }

        // Query case-insensitive + filter relasi
        $query = DB::table($table)
            ->whereRaw("LOWER(`{$column}`) = ?", [$searchLower]);

        foreach ($filters as $key => $val) {
            $query->where($key, $val);
        }

        $record = $query->select('id')->first();

        if (! $record) {
            if ($required) {
                throw new \Exception("Referensi untuk '{$table}' dengan nilai '{$search}' tidak ditemukan di baris {$excelRow}.");
            }
            return null;
        }

        return $record->id;
    }

    /**
     * findGolonganId — cari golongan dengan optional kategori_golongan_id
     */
    protected function findGolonganId($value, $kategoriGolonganId = null, int $excelRow, bool $required = true)
    {
        if (!isset($value) || trim((string)$value) === '') {
            if ($required) {
                throw new \Exception("Golongan kosong di baris {$excelRow}.");
            }
            return null;
        }

        $search = trim((string)$value);
        $searchLower = mb_strtolower($search);

        // kalau numeric -> anggap ID langsung, tapi pastikan kategori cocok bila diberikan
        if (is_numeric($search)) {
            $query = DB::table('golongan')->where('id', $search);
            if ($kategoriGolonganId) {
                $query->where('kategori_golongan_id', $kategoriGolonganId);
            }
            $rec = $query->select('id')->first();
            if ($rec) return $rec->id;
        }

        // cari berdasarkan nama_golongan (case-insensitive), tambahkan filter kategori bila ada
        $query = DB::table('golongan')->whereRaw("LOWER(`nama_golongan`) = ?", [$searchLower]);
        if ($kategoriGolonganId) {
            $query->where('kategori_golongan_id', $kategoriGolonganId);
        }
        $rec = $query->select('id')->first();

        if (!$rec) {
            if ($required) {
                $kinfo = $kategoriGolonganId ? " dan kategori_id {$kategoriGolonganId}" : '';
                throw new \Exception("Golongan '{$search}'{$kinfo} tidak ditemukan di baris {$excelRow}.");
            }
            return null;
        }

        return $rec->id;
    }

    /**
     * transformDate — parse excel serial or date string to Y-m-d
     */
    protected function transformDate($value, $format = 'Y-m-d')
    {
        if (empty($value) && $value !== '0') {
            return null;
        }

        // Jika numeric (Excel date serial)
        if (is_numeric($value)) {
            try {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format($format);
            } catch (\Throwable $e) {
                // fallback ke parse string
            }
        }

        // Jika sudah format tanggal string
        try {
            return (new \DateTime($value))->format($format);
        } catch (\Throwable $e) {
            return null;
        }
    }
}
