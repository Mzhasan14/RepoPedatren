<?php

namespace App\Imports;

use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SantriImport implements ToCollection, WithHeadingRow
{
    protected $userId;

    public function headingRow(): int
    {
        return 2; // header ada di baris 2
    }

    public function __construct($userId)
    {
        $this->userId = $userId;
    }

    public function collection(Collection $rows)
    {
        if ($rows->isEmpty()) {
            return;
        }

        // Filter hanya row yang punya minimal 1 kolom terisi
        $rows = $rows->filter(function ($row) {
            // Cek apakah ada kolom yang tidak kosong
            foreach ($row as $value) {
                if (trim((string) $value) !== '') {
                    return true; // ada data
                }
            }
            return false; // semua kolom kosong
        });

        if ($rows->isEmpty()) {
            return; // Tidak ada data valid
        }

        DB::beginTransaction();

        try {
            foreach ($rows as $index => $rawRow) {
                $excelRow = $index + 3; // data mulai dari baris 3 (baris 1 kosong, baris 2 header)
                $row = $this->normalizeRow($rawRow->toArray());

                // --- CEK NIK SANTRI PALING AWAL ---
                $kewarganegaraan = strtoupper(trim((string)($row['kewarganegaraan'] ?? '')));
                if ($kewarganegaraan === 'WNI') {
                    $nik = preg_replace('/\D/', '', (string)($row['nik'] ?? ''));
                    if (empty($nik)) {
                        throw new \Exception("Kolom 'NIK' wajib diisi untuk WNI di baris {$excelRow}, kolom 'nik'.");
                    }

                    $existsNik = DB::table('biodata')
                        ->where('nik', $nik)
                        ->exists();

                    if ($existsNik) {
                        throw new \Exception("NIK '{$nik}' sudah terdaftar di baris {$excelRow}, kolom 'nik' pada file Excel.");
                    }
                }
                // --- AKHIR CEK NIK SANTRI ---

                if (empty($row['nama_lengkap'])) {
                    throw new \Exception("Kolom 'Nama Lengkap' wajib diisi di baris {$excelRow}");
                }
                if (empty($row['kewarganegaraan'])) {
                    throw new \Exception("Kolom 'Kewarganegaraan' wajib diisi di baris {$excelRow}");
                }

                $biodataId = Str::uuid()->toString();

                $kewarganegaraan = strtoupper(trim((string)($row['kewarganegaraan'] ?? '')));
                $nik = null;
                $noPassport = null;

                if ($kewarganegaraan === 'WNI') {
                    $nik = $row['nik'] ?? null;
                } elseif ($kewarganegaraan === 'WNA') {
                    $noPassport = $row['no_passport'] ?? null;
                }

                DB::table('biodata')->insert([
                    'id' => $biodataId,
                    'nama' => $row['nama_lengkap'] ?? null,
                    'nik' => $nik,
                    'no_passport' => $noPassport,
                    'jenis_kelamin' => isset($row['jenis_kelamin']) ? strtolower($row['jenis_kelamin']) : null,
                    'tempat_lahir' => $row['tempat_lahir'] ?? null,
                    'tanggal_lahir' => $row['tanggal_lahir'] ?? null,
                    'anak_keberapa' => $row['anak_keberapa'] ?? null,
                    'dari_saudara' => $row['dari_berapa_saudara'] ?? null,
                    'tinggal_bersama' => $row['tinggal_bersama'] ?? null,
                    'jenjang_pendidikan_terakhir' => $row['jenjang_pendidikan_terakhir'] ?? null,
                    'nama_pendidikan_terakhir' => $row['nama_pendidikan_terakhir'] ?? null,
                    'email' => $row['email'] ?? null,
                    'no_telepon' => $row['no_telp_1'] ?? null,
                    'no_telepon_2' => $row['no_telp_2'] ?? null,
                    'negara_id' => $this->findId('negara', $row['negara'] ?? null, $excelRow, true),
                    'provinsi_id' => $this->findId('provinsi', $row['provinsi'] ?? null, $excelRow, true),
                    'kabupaten_id' => $this->findId('kabupaten', $row['kabupaten'] ?? null, $excelRow, true),
                    'kecamatan_id' => $this->findId('kecamatan', $row['kecamatan'] ?? null, $excelRow, true),
                    'jalan' => $row['jalan'] ?? null,
                    'kode_pos' => $row['kode_pos'] ?? null,
                    // 'smartcard' => $row['smartcard'] ?? null,
                    'status' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                    'created_by' => $this->userId ?? 1
                ]);
                // ... bagian atas class tetap sama

                // --- di dalam foreach($rows as $index => $rawRow) setelah insert ayah & ibu ---
                $ayahBiodataId = $this->upsertOrangTuaBiodata($row, 'ayah', $excelRow);
                $ibuBiodataId  = $this->upsertOrangTuaBiodata($row, 'ibu', $excelRow);

                $this->insertOrangTuaRelation($biodataId, $ayahBiodataId, 'ayah', $row, $excelRow, false);
                $this->insertOrangTuaRelation($biodataId, $ibuBiodataId, 'ibu', $row, $excelRow, false);

                // ==== LOGIKA WALI ====
                // ==== LOGIKA WALI ====
                $waliBiodataId = null;
                $namaAyah  = trim(strtolower($row['nama_ayah'] ?? ''));
                $namaIbu   = trim(strtolower($row['nama_ibu'] ?? ''));
                $namaWali  = trim(strtolower($row['nama_wali'] ?? ''));

                $nikAyah   = preg_replace('/\D/', '', (string)($row['nik_ayah'] ?? ''));
                $nikIbu    = preg_replace('/\D/', '', (string)($row['nik_ibu'] ?? ''));
                $nikWali   = preg_replace('/\D/', '', (string)($row['nik_wali'] ?? ''));

                // Cek kesamaan berdasarkan NIK atau nama
                $samaDenganAyah = ($nikWali && $nikAyah && $nikWali === $nikAyah) ||
                    (!$nikWali && !$nikAyah && $namaWali && $namaWali === $namaAyah);

                $samaDenganIbu  = ($nikWali && $nikIbu && $nikWali === $nikIbu) ||
                    (!$nikWali && !$nikIbu && $namaWali && $namaWali === $namaIbu);

                if ($samaDenganAyah) {
                    $waliBiodataId = $ayahBiodataId;
                    // update kolom wali = true di orang_tua_wali ayah
                    DB::table('orang_tua_wali')
                        ->where('id_biodata', $ayahBiodataId)
                        ->update(['wali' => true]);
                } elseif ($samaDenganIbu) {
                    $waliBiodataId = $ibuBiodataId;
                    // update kolom wali = true di orang_tua_wali ibu
                    DB::table('orang_tua_wali')
                        ->where('id_biodata', $ibuBiodataId)
                        ->update(['wali' => true]);
                } else {
                    // Kalau wali berbeda dari ayah & ibu, cek apakah sudah ada
                    if (!empty($nikWali)) {
                        $existing = DB::table('biodata')->where('nik', $nikWali)->select('id')->first();
                        if ($existing) {
                            $waliBiodataId = $existing->id;
                        }
                    }
                    if (!$waliBiodataId && !empty($namaWali)) {
                        $existing = DB::table('biodata')
                            ->whereRaw("LOWER(nama) = ?", [mb_strtolower($namaWali)])
                            ->select('id')
                            ->first();
                        if ($existing) {
                            $waliBiodataId = $existing->id;
                        }
                    }
                    // Jika belum ada, buat baru
                    if (!$waliBiodataId && (!empty($nikWali) || !empty($namaWali))) {
                        $waliBiodataId = $this->createBiodataForParent($row, 'wali');
                    }

                    // Insert relasi wali
                    if ($waliBiodataId) {
                        $this->insertOrangTuaRelation($biodataId, $waliBiodataId, 'wali', $row, $excelRow, true);
                    }
                }


                // --- Insert keluarga ---
                if ($kewarganegaraan === 'WNA') {
                    $angka13Digit = (string) random_int(1000000000000, 9999999999999);
                    $generatedNoKK = 'WNA' . $angka13Digit;
                } else {
                    $generatedNoKK = $row['no_kk'] ?? null;
                }

                // Santri
                if (empty($generatedNoKK)) {
                    throw new \Exception("Kolom 'Nomor KK' wajib diisi untuk WNI di baris {$excelRow}");
                }

                // $existsKeluarga = DB::table('keluarga')
                //     ->where('no_kk', $generatedNoKK)
                //     ->where('id_biodata', $biodataId)
                //     ->exists();

                // if ($existsKeluarga) {
                // }

                DB::table('keluarga')->insert([
                    'no_kk' => $generatedNoKK,
                    'id_biodata' => $biodataId,
                    'status' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                    'created_by' => $this->userId ?? 1
                ]);

                // Ayah
                // Ayah
                if (!empty($row['nik_ayah'])) {
                    // Cari berdasarkan NIK
                    $ayahBiodataId = DB::table('biodata')->where('nik', $row['nik_ayah'])->value('id');
                } elseif (!empty($row['nama_ayah'])) {
                    // Cari berdasarkan nama
                    $ayahBiodataId = DB::table('biodata')
                        ->whereRaw('LOWER(nama) = ?', [mb_strtolower($row['nama_ayah'])])
                        ->value('id');
                }

                if ($ayahBiodataId) {
                    $existsAyah = DB::table('keluarga as k')
                        ->join('biodata as b', 'b.id', '=', 'k.id_biodata')
                        ->where('k.no_kk', $generatedNoKK)
                        ->where(function ($q) use ($row) {
                            if (!empty($row['nik_ayah'])) {
                                $q->where('b.nik', $row['nik_ayah']);
                            } else {
                                $q->whereRaw('LOWER(b.nama) = ?', [mb_strtolower($row['nama_ayah'])]);
                            }
                        })
                        ->exists();

                    if (!$existsAyah) {
                        DB::table('keluarga')->insertOrIgnore([
                            'no_kk' => $generatedNoKK,
                            'id_biodata' => $ayahBiodataId,
                            'status' => true,
                            'created_at' => now(),
                            'updated_at' => now(),
                            'created_by' => $this->userId ?? 1
                        ]);
                    }
                }

                // Ibu
                if (!empty($row['nik_ibu'])) {
                    $ibuBiodataId = DB::table('biodata')->where('nik', $row['nik_ibu'])->value('id');
                } elseif (!empty($row['nama_ibu'])) {
                    $ibuBiodataId = DB::table('biodata')
                        ->whereRaw('LOWER(nama) = ?', [mb_strtolower($row['nama_ibu'])])
                        ->value('id');
                }

                if ($ibuBiodataId) {
                    $existsIbu = DB::table('keluarga as k')
                        ->join('biodata as b', 'b.id', '=', 'k.id_biodata')
                        ->where('k.no_kk', $generatedNoKK)
                        ->where(function ($q) use ($row) {
                            if (!empty($row['nik_ibu'])) {
                                $q->where('b.nik', $row['nik_ibu']);
                            } else {
                                $q->whereRaw('LOWER(b.nama) = ?', [mb_strtolower($row['nama_ibu'])]);
                            }
                        })
                        ->exists();

                    if (!$existsIbu) {
                        DB::table('keluarga')->insertOrIgnore([
                            'no_kk' => $generatedNoKK,
                            'id_biodata' => $ibuBiodataId,
                            'status' => true,
                            'created_at' => now(),
                            'updated_at' => now(),
                            'created_by' => $this->userId ?? 1
                        ]);
                    }
                }

                // Wali
                if (!empty($row['nik_wali'])) {
                    $waliBiodataId = DB::table('biodata')->where('nik', $row['nik_wali'])->value('id');
                } elseif (!empty($row['nama_wali'])) {
                    $waliBiodataId = DB::table('biodata')
                        ->whereRaw('LOWER(nama) = ?', [mb_strtolower($row['nama_wali'])])
                        ->value('id');
                }

                if ($waliBiodataId && $waliBiodataId !== ($ayahBiodataId ?? null) && $waliBiodataId !== ($ibuBiodataId ?? null)) {
                    $existsWali = DB::table('keluarga as k')
                        ->join('biodata as b', 'b.id', '=', 'k.id_biodata')
                        ->where('k.no_kk', $generatedNoKK)
                        ->where(function ($q) use ($row) {
                            if (!empty($row['nik_wali'])) {
                                $q->where('b.nik', $row['nik_wali']);
                            } else {
                                $q->whereRaw('LOWER(b.nama) = ?', [mb_strtolower($row['nama_wali'])]);
                            }
                        })
                        ->exists();

                    if (!$existsWali) {
                        DB::table('keluarga')->insertOrIgnore([
                            'no_kk' => $generatedNoKK,
                            'id_biodata' => $waliBiodataId,
                            'status' => true,
                            'created_at' => now(),
                            'updated_at' => now(),
                            'created_by' => $this->userId ?? 1
                        ]);
                    }
                }

                $mondokVal = strtolower((string)($row['status_mondok'] ?? ''));

                if ($mondokVal === '' || $mondokVal === 'iya') {
                    $angkatanId   = $this->findAngkatanId($row['angkatan_santri'] ?? null, 'santri', $excelRow);

                    // cari tahun dari tabel angkatan
                    $tahunAngkatan = null;
                    if ($angkatanId) {
                        $tahunAngkatan = DB::table('angkatan')->where('id', $angkatanId)->value('angkatan');
                    }

                    // Tentukan tanggal masuk santri
                    if (!empty($row['tanggal_masuk_santri'])) {
                        $tanggalMasukSantri = $row['tanggal_masuk_santri'];
                    } elseif ($tahunAngkatan) {
                        // default tanggal masuk pesantren → awal tahun ajaran
                        $tanggalMasukSantri = $tahunAngkatan . '-07-01';
                    } else {
                        // fallback terakhir kalau angkatan tidak ditemukan
                        $tanggalMasukSantri = null;
                    }

                    // ambil 2 digit terakhir tahun
                    $tahunMasuk2Digit = $tahunAngkatan ? substr($tahunAngkatan, -2) : date('y');

                    // generate nomor urut terakhir untuk angkatan ini
                    $lastUrut = DB::table('santri')
                        ->where('angkatan_id', $angkatanId)
                        ->select(DB::raw("MAX(RIGHT(nis,3)) as last_urut"))
                        ->value('last_urut');

                    $nextUrut = str_pad(((int) $lastUrut) + 1, 3, '0', STR_PAD_LEFT);

                    // generate nis unik
                    do {
                        $random = str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);
                        $nis = $tahunMasuk2Digit . '11' . $random . $nextUrut;
                    } while (
                        DB::table('santri')->where('nis', $nis)->exists()
                    );

                    // insert santri
                    $santriId = DB::table('santri')->insertGetId([
                        'biodata_id'    => $biodataId,
                        'nis'           => $nis,
                        'angkatan_id'   => $angkatanId,
                        'tanggal_masuk' => $tanggalMasukSantri,
                        'status'        => 'aktif',
                        'created_at'    => now(),
                        'updated_at'    => now(),
                        'created_by'    => $this->userId ?? 1
                    ]);

                    if (!empty(trim($row['wilayah'] ?? ''))) {
                        DB::table('domisili_santri')->insert([
                            'santri_id' => $santriId,
                            'wilayah_id' => $this->findId('wilayah', $row['wilayah'], $excelRow, false),
                            'blok_id' => $this->findId('blok', $row['blok'] ?? null, $excelRow, false),
                            'kamar_id' => $this->findId('kamar', $row['kamar'] ?? null, $excelRow, false),
                            'tanggal_masuk' => $row['tanggal_masuk_domisili'] ?? null,
                            'status' => 'aktif',
                            'created_at' => now(),
                            'updated_at' => now(),
                            'created_by' => $this->userId ?? 1
                        ]);
                    }
                }

                if (!empty(trim($row['lembaga'] ?? ''))) {
                    DB::table('pendidikan')->insert([
                        'biodata_id' => $biodataId,
                        'no_induk' => $row['no_induk_pendidikan'] ?? null,
                        'lembaga_id' => $this->findId('lembaga', $row['lembaga'], $excelRow),
                        'jurusan_id' => $this->findId('jurusan', $row['jurusan'] ?? null, $excelRow, false),
                        'kelas_id' => $this->findId('kelas', $row['kelas'] ?? null, $excelRow, false),
                        'rombel_id' => $this->findId('rombel', $row['rombel'] ?? null, $excelRow, false),
                        'angkatan_id' => $this->findAngkatanId($row['angkatan_pelajar'] ?? null, 'pelajar', $excelRow, false),
                        'tanggal_masuk' => $row['tanggal_masuk_pendidikan'] ?? null,
                        'status' => 'aktif',
                        'created_at' => now(),
                        'updated_at' => now(),
                        'created_by' => $this->userId ?? 1
                    ]);
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            $line = $excelRow ?? 'unknown';
            $msg = $e->getMessage();

            if (strpos($msg, 'Integrity constraint violation') !== false && strpos($msg, 'Duplicate entry') !== false) {
                preg_match("/Duplicate entry '([^']+)' for key '([^']+)'/", $msg, $matches);
                $duplicateValue = $matches[1] ?? 'data';
                $keyName = $matches[2] ?? 'unique key';

                if (stripos($keyName, 'santri_nis_unique') !== false) {
                    $friendlyMsg = "NIS '{$duplicateValue}' sudah terdaftar. Silakan periksa data pada baris {$line}.";
                } elseif (stripos($keyName, 'biodata_nik_unique') !== false) {
                    $friendlyMsg = "NIK '{$duplicateValue}' sudah terdaftar. Silakan periksa data pada baris {$line}.";
                } else {
                    $friendlyMsg = "Data dengan nilai '{$duplicateValue}' pada kolom unik '{$keyName}' sudah ada. Silakan cek baris {$line}.";
                }

                throw new \Exception($friendlyMsg);
            }

            if (strpos($msg, 'Cannot add or update a child row') !== false) {
                throw new \Exception("Referensi data terkait tidak ditemukan atau tidak valid di baris {$line}. Pastikan semua data referensi sudah benar.");
            }

            if (strpos($msg, 'Column') !== false && strpos($msg, 'cannot be null') !== false) {
                preg_match("/Column '([^']+)' cannot be null/", $msg, $matches);
                $col = $matches[1] ?? 'kolom penting';
                throw new \Exception("Kolom '{$col}' tidak boleh kosong. Silakan periksa data pada baris {$line}.");
            }

            throw new \Exception("Error di baris Excel: {$line} → " . $msg);
        }
    }

    protected function normalizeRow(array $raw): array
    {
        $normalized = [];
        foreach ($raw as $k => $v) {
            $key = (string)$k;
            $key = trim($key);
            $key = mb_strtolower($key);
            $key = str_replace(['*', '.', ' '], ['', '_', '_'], $key);
            $key = preg_replace('/[^a-z0-9_]/u', '', $key);
            $key = preg_replace('/_+/', '_', $key);
            $key = trim($key, '_');

            $normalized[$key] = $v;
        }
        return $normalized;
    }

    protected function upsertOrangTuaBiodata(array $row, string $tipe, int $excelRow)
    {
        $tipeLower = strtolower($tipe);
        $nikKey = "nik_{$tipeLower}";
        $namaKey = "nama_{$tipeLower}";

        $nik = !empty($row[$nikKey]) ? trim($row[$nikKey]) : null;

        if (!empty($nik)) {
            $existing = DB::table('biodata')->where('nik', $nik)->select('id')->first();
            if ($existing) {
                return $existing->id;
            }
        }

        $nama = $row[$namaKey] ?? null;
        if (empty($nama)) {
            return null;
        }

        return $this->createBiodataForParent($row, $tipeLower);
    }

    protected function createBiodataForParent(array $row, string $tipeLower)
    {
        $nikKey = "nik_{$tipeLower}";
        $namaKey = "nama_{$tipeLower}";
        $tempatKey = "tempat_lahir_{$tipeLower}";
        $tanggalKey = "tanggal_lahir_{$tipeLower}";
        $noTelpKey = "no_telp_{$tipeLower}";
        $noTelp2Key = "no_telp_2_{$tipeLower}";
        $pendidikanKey = "jenjang_pendidikan_terakhir_{$tipeLower}";

        $parentId = Str::uuid()->toString();

        DB::table('biodata')->insert([
            'id' => $parentId,
            'nama' => $row[$namaKey] ?? null,
            'nik' => $row[$nikKey] ?? null,
            'tempat_lahir' => $row[$tempatKey] ?? null,
            'tanggal_lahir' => $row[$tanggalKey] ?? null,
            'no_telepon' => $row[$noTelpKey] ?? null,
            'no_telepon_2' => $row[$noTelp2Key] ?? null,
            'jenjang_pendidikan_terakhir' => $row[$pendidikanKey] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
            'created_by' => $this->userId ?? 1
        ]);

        return $parentId;
    }

    protected function insertKeluargaIfNotExists($noKK, $biodataId)
    {
        if (!$noKK || !$biodataId) {
            return;
        }

        $exists = DB::table('keluarga')
            ->where('no_kk', $noKK)
            ->where('id_biodata', $biodataId)
            ->exists();

        if (!$exists) {
            DB::table('keluarga')->insert([
                'no_kk' => $noKK,
                'id_biodata' => $biodataId,
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
                'created_by' => $this->userId ?? 1
            ]);
        }
    }


    protected function insertOrangTuaRelation(string $childBiodataId, ?string $parentBiodataId, string $tipe, array $row, int $excelRow, bool $isWali = false)
    {
        if (!$parentBiodataId) {
            return;
        }

        $namaStatus = ucfirst($tipe);
        if ($tipe !== 'wali') {
            $namaStatus .= ' kandung';
        }

        $idHubungan = DB::table('hubungan_keluarga')
            ->where('nama_status', $namaStatus)
            ->value('id');

        if (!$idHubungan) {
            throw new \Exception("Hubungan keluarga '{$tipe}' tidak ditemukan di baris {$excelRow}");
        }

        $pekerjaanKey = "pekerjaan_{$tipe}";
        $penghasilanKey = "penghasilan_{$tipe}";
        $statusWafatKey = "status_wafat_{$tipe}";

        // ✅ Cek apakah sudah ada data orang_tua_wali dengan id_biodata + id_hubungan_keluarga yang sama
        $exists = DB::table('orang_tua_wali')
            ->where('id_biodata', $parentBiodataId)
            ->where('id_hubungan_keluarga', $idHubungan)
            ->exists();

        if ($exists) {
            // Kalau sudah ada, tapi ini wali, pastikan kolom wali = true
            if ($isWali) {
                DB::table('orang_tua_wali')
                    ->where('id_biodata', $parentBiodataId)
                    ->where('id_hubungan_keluarga', $idHubungan)
                    ->update(['wali' => true, 'updated_at' => now()]);
            }
            return; // ❌ Tidak insert ulang
        }

        // ✅ Insert baru kalau belum ada
        DB::table('orang_tua_wali')->insert([
            'id_biodata' => $parentBiodataId,
            'id_hubungan_keluarga' => $idHubungan,
            'wali' => $isWali ? 1 : 0,
            'pekerjaan' => $row[$pekerjaanKey] ?? null,
            'penghasilan' => $row[$penghasilanKey] ?? null,
            'status' => isset($row[$statusWafatKey])
                ? !(in_array(strtolower((string)$row[$statusWafatKey]), ['iya', 'true', 'wafat']))
                : true,
            'created_at' => now(),
            'updated_at' => now(),
            'created_by' => $this->userId ?? 1
        ]);
    }


    protected function findId(string $table, $value, int $excelRow, bool $required = true, string $columnName = null)
    {
        if (!isset($value) || trim((string)$value) === '') {
            if ($required) {
                throw new \Exception("Referensi untuk tabel '{$table}' kosong di baris {$excelRow}.");
            }
            return null;
        }

        $columnMap = [
            'negara'    => 'nama_negara',
            'provinsi'  => 'nama_provinsi',
            'kabupaten' => 'nama_kabupaten',
            'kecamatan' => 'nama_kecamatan',
            'angkatan'  => 'angkatan',
            'wilayah'   => 'nama_wilayah',
            'blok'      => 'nama_blok',
            'kamar'     => 'nama_kamar',
            'lembaga'   => 'nama_lembaga',
            'jurusan'   => 'nama_jurusan',
            'kelas'     => 'nama_kelas',
            'rombel'    => 'nama_rombel',
        ];

        $column = $columnName ?: ($columnMap[$table] ?? 'nama');

        $search = trim((string)$value);
        $searchLower = mb_strtolower($search);

        if (is_numeric($search)) {
            $record = DB::table($table)->where('id', $search)->select('id')->first();
            if ($record) {
                return $record->id;
            }
        }

        $record = DB::table($table)
            ->whereRaw("LOWER(`{$column}`) = ?", [$searchLower])
            ->select('id')
            ->first();

        if (!$record) {
            if ($required) {
                throw new \Exception("Referensi untuk '{$table}' dengan nilai '{$search}' tidak ditemukan (kolom '{$column}') di baris {$excelRow}.");
            }
            return null;
        }

        return $record->id;
    }

    protected function findAngkatanId($value, $kategori, int $excelRow, bool $required = true)
    {
        if (!isset($value) || trim((string)$value) === '') {
            // if ($required) {
            //     throw new \Exception("Angkatan untuk kategori '{$kategori}' kosong di baris {$excelRow}.");
            // }
            return null;
        }

        $search = trim((string)$value);
        $record = DB::table('angkatan')
            ->whereRaw("LOWER(angkatan) = ?", [mb_strtolower($search)])
            ->where('kategori', $kategori)
            ->select('id')
            ->first();

        if (!$record) {
            // if ($required) {
            //     throw new \Exception("Angkatan '{$search}' untuk kategori '{$kategori}' tidak ditemukan di baris {$excelRow}.");
            // }
            return null;
        }

        return $record->id;
    }
}
