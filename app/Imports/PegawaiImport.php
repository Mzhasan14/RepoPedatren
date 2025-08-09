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
            foreach ($rows as $index => $rawRow) {
                $excelRow = $index + 2; // +2 karena heading row di baris 1

                // Normalisasi keys header → jadi array biasa dengan key terstandard
                $row = $this->normalizeRow($rawRow->toArray());

                // ===== Insert Biodata =====
                $biodataId = Str::uuid()->toString();

                // Ambil nilai NIK & No Passport (bersih)
                $nikRaw = isset($row['nik']) ? trim((string)$row['nik']) : '';
                $noPassportRaw = isset($row['no_passport']) ? trim((string)$row['no_passport']) : '';

                // Tentukan kewarganegaraan (jika ada)
                $kewarganegaraan = strtoupper(trim((string)($row['kewarganegaraan'] ?? '')));

                // ===== VALIDASI BARU: NIK ↔ NO PASSPORT =====
                // 1) Tidak boleh diisi bersamaan
                if ($nikRaw !== '' && $noPassportRaw !== '') {
                    throw new \Exception("Kolom 'nik' dan 'no_passport' tidak boleh diisi bersamaan di baris {$excelRow}.");
                }

                // 2) Konsistensi dengan kewarganegaraan (opsional tapi berguna)
                if ($kewarganegaraan === 'WNI' && $noPassportRaw !== '') {
                    throw new \Exception("Kewarganegaraan 'WNI' — kolom 'no_passport' harus dikosongkan di baris {$excelRow}.");
                }
                if ($kewarganegaraan === 'WNA' && $nikRaw !== '') {
                    throw new \Exception("Kewarganegaraan 'WNA' — kolom 'nik' harus dikosongkan di baris {$excelRow}.");
                }

                // Tetapkan nilai final untuk insert (mengikuti logic semula)
                $nik = null;
                $noPassport = null;
                if ($kewarganegaraan === 'WNI') {
                    $nik = $nikRaw ?: null;
                } elseif ($kewarganegaraan === 'WNA') {
                    $noPassport = $noPassportRaw ?: null;
                } else {
                    // Jika kewarganegaraan tidak diisi atau lain, gunakan apa yang ada (tetap jaga eksklusif)
                    if ($nikRaw !== '') $nik = $nikRaw;
                    if ($noPassportRaw !== '') $noPassport = $noPassportRaw;
                }

                DB::table('biodata')->insert([
                    'id' => $biodataId,
                    'nama' => $row['nama_lengkap'] ?? null,
                    'nik' => $nik,
                    'no_passport' => $noPassport,
                    'jenis_kelamin' => isset($row['jenis_kelamin']) ? strtolower($row['jenis_kelamin']) : null,
                    'tempat_lahir' => $row['tempat_lahir'] ?? null,
                    'tanggal_lahir' => $row['tanggal_lahir'] ?? null,
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
                    'smartcard' => $row['smartcard'] ?? null,
                    'status' => true,
                    'wafat' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                    'created_by' => $this->userId ?? 1
                ]);

                // ===== Insert Pegawai =====
                $pegawaiId = DB::table('pegawai')->insertGetId([
                    'biodata_id' => $biodataId,
                    'status_aktif' => 'aktif',
                    'created_at' => now(),
                    'updated_at' => now(),
                    'created_by' => $this->userId ?? 1
                ]);

                // ===== Role: KARYAWAN =====
                if (isset($row['status_karyawan']) && strtolower((string)$row['status_karyawan']) === 'iya') {
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
                if (isset($row['status_pengajar']) && strtolower((string)$row['status_pengajar']) === 'iya') {
                    $kategoriGolId = $this->findId('kategori_golongan', $row['pengajar_kategori_golongan'] ?? null, $excelRow, false);
                    $golonganId = $this->findGolonganId($row['pengajar_golongan'] ?? null, $kategoriGolId, $excelRow, false);

                    DB::table('pengajar')->insert([
                        'pegawai_id' => $pegawaiId,
                        'lembaga_id' => $this->findId('lembaga', $row['pengajar_lembaga'] ?? null, $excelRow, false),
                        'golongan_id' => $golonganId,
                        'jabatan' => $row['pengajar_jabatan'] ?? null,
                        'tahun_masuk' => $this->transformDate($row['pengajar_tahun_masuk'] ?? null),
                        'status_aktif' => 'aktif',
                        'created_at' => now(),
                        'updated_at' => now(),
                        'created_by' => $this->userId ?? 1
                    ]);
                }

                // ===== Role: PENGURUS =====
                if (isset($row['status_pengurus']) && strtolower((string)$row['status_pengurus']) === 'iya') {
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
                if (isset($row['status_wali_kelas']) && strtolower((string)$row['status_wali_kelas']) === 'iya') {
                    DB::table('wali_kelas')->insert([
                        'pegawai_id' => $pegawaiId,
                        'lembaga_id' => $this->findId('lembaga', $row['wali_lembaga'] ?? null, $excelRow, false),
                        'jurusan_id' => $this->findId('jurusan', $row['wali_jurusan'] ?? null, $excelRow, false),
                        'kelas_id' => $this->findId('kelas', $row['wali_kelas'] ?? null, $excelRow, false),
                        'rombel_id' => $this->findId('rombel', $row['wali_rombel'] ?? null, $excelRow, false),
                        'jumlah_murid' => $row['wali_jumlah_murid'] ?? null,
                        'periode_awal' => $this->transformDate($row['wali_periode_awal'] ?? null),
                        'status_aktif' => 'aktif',
                        'created_at' => now(),
                        'updated_at' => now(),
                        'created_by' => $this->userId ?? 1
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
