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

    public function __construct($userId)
    {
        $this->userId = $userId;
    }

    public function collection(Collection $rows)
    {
        DB::beginTransaction();
        try {
            foreach ($rows as $index => $row) {
                $excelRow = $index + 2; // +2 karena heading row di baris 1

                // ===== Insert Biodata =====
                $biodataId = Str::uuid()->toString();
                DB::table('biodata')->insert([
                    'id' => $biodataId,
                    'nama' => $row['nama'],
                    'nik' => $row['nik'],
                    'jenis_kelamin' => strtolower($row['jenis_kelamin']),
                    'tempat_lahir' => $row['tempat_lahir'],
                    'tanggal_lahir' => $row['tanggal_lahir'],
                    'anak_keberapa' => $row['anak_keberapa'] ?? null,
                    'dari_saudara' => $row['dari_saudara'] ?? null,
                    'tinggal_bersama' => $row['tinggal_bersama'] ?? null,
                    'jenjang_pendidikan_terakhir' => $row['jenjang_pendidikan_terakhir'] ?? null,
                    'nama_pendidikan_terakhir' => $row['nama_pendidikan_terakhir'] ?? null,
                    'email' => $row['email'] ?? null,
                    'no_telepon' => $row['no_telepon'] ?? null,
                    'negara_id' => $this->findId('negara', $row['negara'], $excelRow),
                    'provinsi_id' => $this->findId('provinsi', $row['provinsi'], $excelRow),
                    'kabupaten_id' => $this->findId('kabupaten', $row['kabupaten'], $excelRow),
                    'kecamatan_id' => $this->findId('kecamatan', $row['kecamatan'], $excelRow),
                    'jalan' => $row['jalan'] ?? null,
                    'kode_pos' => $row['kode_pos'] ?? null,
                    'status' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                    'created_by' => 1
                ]);

                // ===== Insert Orang Tua =====
                $this->insertOrangTua($biodataId, $row, 'ayah', $excelRow);
                $this->insertOrangTua($biodataId, $row, 'ibu', $excelRow);
                $this->insertOrangTua($biodataId, $row, 'wali', $excelRow, true);

                // ===== Insert Keluarga =====
                DB::table('keluarga')->insert([
                    'no_kk' => $row['no_kk'],
                    'id_biodata' => $biodataId,
                    'status' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                    'created_by' => 1
                ]);

                // ===== Insert Santri =====
                if (strtolower($row['mondok']) === 'iya') {
                    $santriId = DB::table('santri')->insertGetId([
                        'biodata_id' => $biodataId,
                        'nis' => $row['nis'] ?? null,
                        'angkatan_id' => $this->findAngkatanId($row['angkatan_santri'], 'santri', $excelRow),
                        'tanggal_masuk' => $row['tanggal_masuk_santri'],
                        'status' => 'aktif',
                        'created_at' => now(),
                        'updated_at' => now(),
                        'created_by' => 1
                    ]);

                    // ===== Insert Domisili =====
                    DB::table('domisili_santri')->insert([
                        'santri_id' => $santriId,
                        'wilayah_id' => $this->findId('wilayah', $row['wilayah'], $excelRow),
                        'blok_id' => $this->findId('blok', $row['blok'], $excelRow, false),
                        'kamar_id' => $this->findId('kamar', $row['kamar'], $excelRow, false),
                        'tanggal_masuk' => $row['tanggal_masuk_domisili'],
                        'status' => 'aktif',
                        'created_at' => now(),
                        'updated_at' => now(),
                        'created_by' => 1
                    ]);
                }

                // ===== Insert Pendidikan =====
                DB::table('pendidikan')->insert([
                    'biodata_id' => $biodataId,
                    'no_induk' => $row['no_induk'] ?? null,
                    'lembaga_id' => $this->findId('lembaga', $row['lembaga'], $excelRow),
                    'jurusan_id' => $this->findId('jurusan', $row['jurusan'], $excelRow, false),
                    'kelas_id' => $this->findId('kelas', $row['kelas'], $excelRow, false),
                    'rombel_id' => $this->findId('rombel', $row['rombel'], $excelRow, false),
                    'angkatan_id' => $this->findAngkatanId($row['angkatan_pelajar'], 'pelajar', $excelRow, false),
                    'tanggal_masuk' => $row['tanggal_masuk_pendidikan'],
                    'status' => 'aktif',
                    'created_at' => now(),
                    'updated_at' => now(),
                    'created_by' => 1
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception("Error di baris Excel: {$excelRow} → " . $e->getMessage());
        }
    }

    private function insertOrangTua($biodataId, $row, $tipe, $excelRow, $wali = false)
    {
        $idHubungan = DB::table('hubungan_keluarga')->where('nama_status', ucfirst($tipe))->value('id');

        DB::table('orang_tua_wali')->insert([
            'id_biodata' => $biodataId,
            'id_hubungan_keluarga' => $idHubungan,
            'wali' => $wali,
            'pekerjaan' => $row["pekerjaan_{$tipe}"] ?? null,
            'penghasilan' => $row["penghasilan_{$tipe}"] ?? null,
            'status' => strtolower($row["wafat_{$tipe}"]) === 'tidak',
            'created_at' => now(),
            'updated_at' => now(),
            'created_by' => 1
        ]);
    }

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
            'wilayah'  => 'nama_wilayah',
            'blok'     => 'nama_blok',
            'kamar'    => 'nama_kamar',
            'lembaga'  => 'nama_lembaga',
            'jurusan'  => 'nama_jurusan',
            'kelas'    => 'nama_kelas',
            'rombel'   => 'nama_rombel',
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
