<?php

namespace App\Imports;

use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SantriImport implements ToCollection, WithHeadingRow
{
    protected $userId;

    public function headingRow(): int
    {
        return 2; // ambil header dari baris kedua
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
            foreach ($rows as $index => $rawRow) {
                $excelRow = $index + 2; // +2 karena heading row di baris 1

                // Normalisasi keys header → jadi array biasa dengan key terstandard
                $row = $this->normalizeRow($rawRow->toArray());

                // ===== Insert Biodata Peserta Didik =====
                $biodataId = Str::uuid()->toString();

                // Tentukan NIK atau No Passport berdasarkan kewarganegaraan
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
                    // 'kewarganegaraan' => $kewarganegaraan,
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
                    'negara_id' => $this->findId('negara', $row['negara'] ?? null, $excelRow),
                    'provinsi_id' => $this->findId('provinsi', $row['provinsi'] ?? null, $excelRow),
                    'kabupaten_id' => $this->findId('kabupaten', $row['kabupaten'] ?? null, $excelRow),
                    'kecamatan_id' => $this->findId('kecamatan', $row['kecamatan'] ?? null, $excelRow),
                    'jalan' => $row['jalan'] ?? null,
                    'kode_pos' => $row['kode_pos'] ?? null,
                    'smartcard' => $row['smartcard'] ?? null,
                    'status' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                    'created_by' => $this->userId ?? 1
                ]);

                // ===== Insert Orang Tua (ayah & ibu) — buat biodata jika belum ada =====
                // Insert ayah; method mengembalikan biodata_id ayah (jika dibuat/ada)
                $ayahBiodataId = $this->upsertOrangTuaBiodata(
                    $row,
                    'ayah',
                    $excelRow
                );

                // Insert ibu
                $ibuBiodataId = $this->upsertOrangTuaBiodata(
                    $row,
                    'ibu',
                    $excelRow
                );

                // Simpan relasi orang_tua_wali untuk ayah & ibu
                $this->insertOrangTuaRelation($biodataId, $ayahBiodataId, 'ayah', $row, $excelRow, false);
                $this->insertOrangTuaRelation($biodataId, $ibuBiodataId, 'ibu', $row, $excelRow, false);

                // ===== Wali: cek apakah NIK wali sama dengan ayah/ibu; jika sama -> reuse, jika beda -> upsert biodata baru =====
                $waliBiodataId = null;
                // Ambil nik wali dari row
                $nikWali = $row['nik_wali'] ?? null;
                $namaWali = $row['nama_wali'] ?? null;

                // Jika nik wali sama dengan ayah atau ibu NIK, reuse
                if (!empty($nikWali)) {
                    if (!empty($row['nik_ayah']) && $nikWali === $row['nik_ayah']) {
                        $waliBiodataId = $ayahBiodataId;
                    } elseif (!empty($row['nik_ibu']) && $nikWali === $row['nik_ibu']) {
                        $waliBiodataId = $ibuBiodataId;
                    }
                }

                // Jika belum matched, try find existing biodata by nik
                if (!$waliBiodataId && !empty($nikWali)) {
                    $existing = DB::table('biodata')->where('nik', $nikWali)->select('id')->first();
                    if ($existing) {
                        $waliBiodataId = $existing->id;
                    }
                }

                // Jika masih belum ada dan ada data wali minimal nama, create new biodata
                if (!$waliBiodataId && (!empty($nikWali) || !empty($namaWali))) {
                    $waliBiodataId = $this->createBiodataForParent($row, 'wali');
                }

                // Insert relation wali (jika ada biodata wali)
                if ($waliBiodataId) {
                    $this->insertOrangTuaRelation($biodataId, $waliBiodataId, 'wali', $row, $excelRow, true);
                }

                // ===== Insert Keluarga =====
                DB::table('keluarga')->insert([
                    'no_kk' => $row['no_kk'] ?? null,
                    'id_biodata' => $biodataId,
                    'status' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                    'created_by' => $this->userId ?? 1
                ]);

                // ===== Insert Santri (Mondok) =====
                $mondokVal = strtolower((string)($row['status_mondok'] ?? $row['status_mondok'] ?? $row['status_mondok'] ?? $row['status_mondok'] ?? ''));
                // Karena ada kemungkinan header 'Status Mondok' atau 'Status Mondok*' → kita gunakan nama normalized 'status_mondok'
                if (isset($row['status_mondok']) && strtolower($row['status_mondok']) === 'iya') {
                    $santriId = DB::table('santri')->insertGetId([
                        'biodata_id' => $biodataId,
                        'nis' => $row['no_induk_santri'] ?? null,
                        'angkatan_id' => $this->findAngkatanId($row['angkatan_santri'] ?? null, 'santri', $excelRow),
                        'tanggal_masuk' => $row['tanggal_masuk_santri'] ?? null,
                        'status' => 'aktif',
                        'created_at' => now(),
                        'updated_at' => now(),
                        'created_by' => $this->userId ?? 1
                    ]);

                    // ===== Insert Domisili =====
                    DB::table('domisili_santri')->insert([
                        'santri_id' => $santriId,
                        'wilayah_id' => $this->findId('wilayah', $row['wilayah'] ?? null, $excelRow),
                        'blok_id' => $this->findId('blok', $row['blok'] ?? null, $excelRow, false),
                        'kamar_id' => $this->findId('kamar', $row['kamar'] ?? null, $excelRow, false),
                        'tanggal_masuk' => $row['tanggal_masuk_domisili'] ?? null,
                        'status' => 'aktif',
                        'created_at' => now(),
                        'updated_at' => now(),
                        'created_by' => $this->userId ?? 1
                    ]);
                }

                // ===== Insert Pendidikan =====
                DB::table('pendidikan')->insert([
                    'biodata_id' => $biodataId,
                    'no_induk' => $row['no_induk_pendidikan'] ?? null,
                    'lembaga_id' => $this->findId('lembaga', $row['lembaga'] ?? null, $excelRow),
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
            // replace dot and asterisk and multiple spaces with underscore
            $key = str_replace(['*', '.', ' '], ['', '_', '_'], $key);
            // remove anything not a-z0-9_
            $key = preg_replace('/[^a-z0-9_]/u', '', $key);
            // collapse multiple underscores
            $key = preg_replace('/_+/', '_', $key);
            $key = trim($key, '_');

            $normalized[$key] = $v;
        }

        return $normalized;
    }

    /**
     * Upsert biodata untuk ayah/ibu (cari berdasarkan NIK; jika tidak ada, buat baru)
     * Mengembalikan biodata_id dari ayah/ibu yang ada/baru.
     */
    protected function upsertOrangTuaBiodata(array $row, string $tipe, int $excelRow)
    {
        $tipeLower = strtolower($tipe); // 'ayah' atau 'ibu'
        $nikKey = "nik_{$tipeLower}";
        $namaKey = "nama_{$tipeLower}";
        $tempatKey = "tempat_lahir_{$tipeLower}";
        $tanggalKey = "tanggal_lahir_{$tipeLower}";
        $noTelpKey = "no_telp_{$tipeLower}";
        $noTelp2Key = "no_telp_2_{$tipeLower}";
        $pendidikanKey = "jenjang_pendidikan_terakhir_{$tipeLower}";
        $smartcardKey = "smartcard_{$tipeLower}"; // kemungkinan tidak ada

        $nik = !empty($row[$nikKey]) ? trim($row[$nikKey]) : null;

        // Cek jika biodata sudah ada berdasarkan NIK
        if (!empty($nik)) {
            $existing = DB::table('biodata')->where('nik', $nik)->select('id')->first();
            if ($existing) {
                return $existing->id;
            }
        }

        // Jika tidak ada NIK atau tidak ditemukan, buat biodata baru (minimal nama)
        $nama = $row[$namaKey] ?? null;
        if (empty($nama)) {
            // jika tidak ada nama, biarkan caller yang memutuskan; untuk keamanan, return null
            return null;
        }

        return $this->createBiodataForParent($row, $tipeLower);
    }

    /**
     * Buat biodata baru untuk parent berdasarkan tipe (ayah/ibu/wali)
     * Mengembalikan id biodata baru
     */
    protected function createBiodataForParent(array $row, string $tipeLower)
    {
        $nikKey = "nik_{$tipeLower}";
        $namaKey = "nama_{$tipeLower}";
        $tempatKey = "tempat_lahir_{$tipeLower}";
        $tanggalKey = "tanggal_lahir_{$tipeLower}";
        $noTelpKey = "no_telp_{$tipeLower}";
        $noTelp2Key = "no_telp_2_{$tipeLower}";
        $pendidikanKey = "jenjang_pendidikan_terakhir_{$tipeLower}";
        $pekerjaanKey = "pekerjaan_{$tipeLower}";
        $penghasilanKey = "penghasilan_{$tipeLower}";
        $statusWafatKey = "status_wafat_{$tipeLower}";

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

    /**
     * Masukkan baris relasi ke tabel orang_tua_wali
     * Menyimpan id_biodata (santri) dan id_biodata_orang_tua (orang tua)
     */
    protected function insertOrangTuaRelation(string $childBiodataId, ?string $parentBiodataId, string $tipe, array $row, int $excelRow, bool $isWali = false)
    {
        if (!$parentBiodataId) {
            // jika tidak ada biodata parent, skip insert relation
            return;
        }

        $idHubungan = DB::table('hubungan_keluarga')
            ->where('nama_status', ucfirst($tipe))
            ->value('id');

        // Ambil fields tambahan untuk relasi
        $pekerjaanKey = "pekerjaan_{$tipe}";
        $penghasilanKey = "penghasilan_{$tipe}";
        $statusWafatKey = "status_wafat_{$tipe}";

        DB::table('orang_tua_wali')->insert([
            'id_biodata' => $parentBiodataId,
            'id_hubungan_keluarga' => $idHubungan,
            'wali' => $isWali ? 1 : 0,
            'pekerjaan' => $row[$pekerjaanKey] ?? null,
            'penghasilan' => $row[$penghasilanKey] ?? null,
            'status' => isset($row[$statusWafatKey]) ? (strtolower((string)$row[$statusWafatKey]) !== 'iya' && strtolower((string)$row[$statusWafatKey]) !== 'true' && strtolower((string)$row[$statusWafatKey]) !== 'wafat') : true,
            'created_at' => now(),
            'updated_at' => now(),
            'created_by' => $this->userId ?? 1
        ]);
    }

    /**
     * findId unchanged — tetap case-insensitive & safe
     */
    protected function findId(string $table, $value, int $excelRow, bool $required = true)
    {
        if (! isset($value) || trim((string)$value) === '') {
            if ($required) {
                throw new \Exception("Referensi untuk tabel '{$table}' kosong di baris {$excelRow}.");
            }
            return null;
        }

        // Mapping kolom khusus per tabel
        $columnMap = [
            'negara'    => 'nama_negara',
            'provinsi'  => 'nama_provinsi',
            'kabupaten' => 'nama_kabupaten',
            'kecamatan' => 'nama_kecamatan',
            'angkatan'  => 'angkatan', // khusus tabel angkatan
            'wilayah'   => 'nama_wilayah',
            'blok'      => 'nama_blok',
            'kamar'     => 'nama_kamar',
            'lembaga'   => 'nama_lembaga',
            'jurusan'   => 'nama_jurusan',
            'kelas'     => 'nama_kelas',
            'rombel'    => 'nama_rombel',
        ];

        $column = $columnMap[$table] ?? 'nama'; // fallback ke 'nama'

        $search = trim((string)$value);
        $searchLower = mb_strtolower($search);

        // Jika diisi angka dan kemungkinan itu ID langsung
        if (is_numeric($search)) {
            $record = DB::table($table)->where('id', $search)->select('id')->first();
            if ($record) {
                return $record->id;
            }
        }

        // Query case-insensitive
        $record = DB::table($table)
            ->whereRaw("LOWER(`{$column}`) = ?", [$searchLower])
            ->select('id')
            ->first();

        if (! $record) {
            if ($required) {
                throw new \Exception("Referensi untuk '{$table}' dengan nilai '{$search}' tidak ditemukan (kolom '{$column}') di baris {$excelRow}.");
            }
            return null;
        }

        return $record->id;
    }

    /**
     * findAngkatanId tetap sama (case-insensitive)
     */
    protected function findAngkatanId($value, $kategori, int $excelRow, bool $required = true)
    {
        if (!isset($value) || trim((string)$value) === '') {
            if ($required) {
                throw new \Exception("Angkatan {$kategori} kosong di baris {$excelRow}.");
            }
            return null;
        }

        $search = trim((string)$value);
        $searchLower = mb_strtolower($search);

        // Jika pakai ID langsung
        if (is_numeric($search)) {
            $record = DB::table('angkatan')
                ->where('id', $search)
                ->where('kategori', strtolower($kategori))
                ->select('id')
                ->first();
            if ($record) {
                return $record->id;
            }
        }

        // Cek berdasarkan nama angkatan + kategori
        $record = DB::table('angkatan')
            ->whereRaw("LOWER(`angkatan`) = ?", [$searchLower])
            ->where('kategori', strtolower($kategori))
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
