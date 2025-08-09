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

        DB::beginTransaction();

        try {
            foreach ($rows as $index => $rawRow) {
                $excelRow = $index + 3; // data mulai dari baris 3 (baris 1 kosong, baris 2 header)
                $row = $this->normalizeRow($rawRow->toArray());

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
                    'smartcard' => $row['smartcard'] ?? null,
                    'status' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                    'created_by' => $this->userId ?? 1
                ]);

                $ayahBiodataId = $this->upsertOrangTuaBiodata($row, 'ayah', $excelRow);
                $ibuBiodataId = $this->upsertOrangTuaBiodata($row, 'ibu', $excelRow);

                $this->insertOrangTuaRelation($biodataId, $ayahBiodataId, 'ayah', $row, $excelRow, false);
                $this->insertOrangTuaRelation($biodataId, $ibuBiodataId, 'ibu', $row, $excelRow, false);

                $waliBiodataId = null;
                $nikWali = $row['nik_wali'] ?? null;
                $namaWali = $row['nama_wali'] ?? null;

                if (!empty($nikWali)) {
                    if (!empty($row['nik_ayah']) && $nikWali === $row['nik_ayah']) {
                        $waliBiodataId = $ayahBiodataId;
                    } elseif (!empty($row['nik_ibu']) && $nikWali === $row['nik_ibu']) {
                        $waliBiodataId = $ibuBiodataId;
                    }
                }

                if (!$waliBiodataId && !empty($nikWali)) {
                    $existing = DB::table('biodata')->where('nik', $nikWali)->select('id')->first();
                    if ($existing) {
                        $waliBiodataId = $existing->id;
                    }
                }

                if (!$waliBiodataId && (!empty($nikWali) || !empty($namaWali))) {
                    $waliBiodataId = $this->createBiodataForParent($row, 'wali');
                }

                if ($waliBiodataId) {
                    $this->insertOrangTuaRelation($biodataId, $waliBiodataId, 'wali', $row, $excelRow, true);
                }

                // Insert keluarga untuk santri
                if ($kewarganegaraan === 'WNA') {
                    $angka13Digit = (string) random_int(1000000000000, 9999999999999);
                    $generatedNoKK = 'WNA' . $angka13Digit;
                } else {
                    $generatedNoKK = $row['no_kk'] ?? null;
                }

                // Insert keluarga untuk santri
                DB::table('keluarga')->insert([
                    'no_kk' => $generatedNoKK,
                    'id_biodata' => $biodataId,
                    'status' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                    'created_by' => $this->userId ?? 1
                ]);


                // Insert keluarga untuk ayah jika ada
                // Insert keluarga untuk ayah jika ada
                if ($ayahBiodataId) {
                    DB::table('keluarga')->insertOrIgnore([
                        'no_kk' => $generatedNoKK,
                        'id_biodata' => $ayahBiodataId,
                        'status' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                        'created_by' => $this->userId ?? 1
                    ]);
                }

                // Insert keluarga untuk ibu jika ada
                if ($ibuBiodataId) {
                    DB::table('keluarga')->insertOrIgnore([
                        'no_kk' => $generatedNoKK,
                        'id_biodata' => $ibuBiodataId,
                        'status' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                        'created_by' => $this->userId ?? 1
                    ]);
                }

                // Insert keluarga untuk wali jika ada dan beda dengan ayah & ibu
                if ($waliBiodataId && $waliBiodataId !== $ayahBiodataId && $waliBiodataId !== $ibuBiodataId) {
                    DB::table('keluarga')->insertOrIgnore([
                        'no_kk' => $generatedNoKK,
                        'id_biodata' => $waliBiodataId,
                        'status' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                        'created_by' => $this->userId ?? 1
                    ]);
                }


                // Insert keluarga untuk wali jika ada dan beda dengan ayah & ibu
                if ($waliBiodataId && $waliBiodataId !== $ayahBiodataId && $waliBiodataId !== $ibuBiodataId) {
                    DB::table('keluarga')->insertOrIgnore([
                        'no_kk' => $row['no_kk'] ?? null,
                        'id_biodata' => $waliBiodataId,
                        'status' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                        'created_by' => $this->userId ?? 1
                    ]);
                }


                $mondokVal = strtolower((string)($row['status_mondok'] ?? ''));

                if ($mondokVal === 'iya') {
                    $santriId = DB::table('santri')->insertGetId([
                        'biodata_id' => $biodataId,
                        'nis' => $row['no_induk_santri'] ?? null,
                        'angkatan_id' => $this->findAngkatanId($row['angkatan_santri'] ?? null, 'santri', $excelRow),
                        'tanggal_masuk' => $row['tanggal_masuk_santri'] ?? now(),
                        'status' => 'aktif',
                        'created_at' => now(),
                        'updated_at' => now(),
                        'created_by' => $this->userId ?? 1
                    ]);

                    if (!empty(trim($row['wilayah'] ?? ''))) {
                        DB::table('domisili_santri')->insert([
                            'santri_id' => $santriId,
                            'wilayah_id' => $this->findId('wilayah', $row['wilayah'], $excelRow, false),
                            'blok_id' => $this->findId('blok', $row['blok'] ?? null, $excelRow, false),
                            'kamar_id' => $this->findId('kamar', $row['kamar'] ?? null, $excelRow, false),
                            'tanggal_masuk' => $row['tanggal_masuk_domisili'] ?? now(),
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
                        'tanggal_masuk' => $row['tanggal_masuk_pendidikan'] ?? now(),
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

        Log::info("Inserting hubungan keluarga '{$tipe}' untuk biodata anak {$childBiodataId} dengan parent {$parentBiodataId} pada baris {$excelRow}");

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

        Log::info("Insert hubungan keluarga '{$tipe}' selesai pada baris {$excelRow}");
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
            if ($required) {
                throw new \Exception("Angkatan untuk kategori '{$kategori}' kosong di baris {$excelRow}.");
            }
            return null;
        }

        $search = trim((string)$value);
        $record = DB::table('angkatan')
            ->whereRaw("LOWER(angkatan) = ?", [mb_strtolower($search)])
            ->where('kategori', $kategori)
            ->select('id')
            ->first();

        if (!$record) {
            if ($required) {
                throw new \Exception("Angkatan '{$search}' untuk kategori '{$kategori}' tidak ditemukan di baris {$excelRow}.");
            }
            return null;
        }

        return $record->id;
    }
}
