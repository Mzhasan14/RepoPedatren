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

    public function __construct($userId)
    {
        $this->userId = $userId;
    }

    public function collection(Collection $rows)
    {
        DB::beginTransaction();
        try {
            foreach ($rows as $index => $row) {
                $excelRow = $index + 2;

                $biodataId = $this->insertOrUpdateBiodata($row, $excelRow);

                $pegawaiId = DB::table('pegawai')->insertGetId([
                    'biodata_id' => $biodataId,
                    'smartcard' => $row['smartcard'] ?? null,
                    'status' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                    'created_by' => $this->userId
                ]);

                $this->insertWargaPesantren($biodataId, $row, $excelRow);
                $this->insertKaryawan($pegawaiId, $row, $excelRow);
                $this->insertPengajar($pegawaiId, $row, $excelRow);
                $this->insertPengurus($pegawaiId, $row, $excelRow);
                $this->insertWaliKelas($pegawaiId, $row, $excelRow);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception("Error di baris Excel: {$excelRow} → " . $e->getMessage());
        }
    }

    private function insertOrUpdateBiodata($row, $excelRow)
    {
        $existing = DB::table('biodata')->where('nik', $row['nik'])->first();

        if ($existing) {
            return $existing->id;
        }

        $id = Str::uuid();

        DB::table('biodata')->insert([
            'id' => $id,
            'nik' => $row['nik'],
            'nama' => $row['nama_lengkap'],
            'jenis_kelamin' => strtolower($row['jenis_kelamin']),
            'tempat_lahir' => $row['tempat_lahir'],
            'tanggal_lahir' => $row['tanggal_lahir'],
            'anak_keberapa' => $row['anak_ke'] ?? null,
            'tinggal_bersama' => $row['tinggal_bersama'] ?? null,
            'jenjang_pendidikan_terakhir' => $row['jenjang_pendidikan_terakhir'] ?? null,
            'nama_pendidikan_terakhir' => $row['nama_pendidikan_terakhir'] ?? null,
            'no_telepon' => $row['nomor_telepon_1'] ?? null,
            'no_telepon_lain' => $row['nomor_telepon_2'] ?? null,
            'email' => $row['e_mail'] ?? null,
            'negara_id' => $this->findId('negara', $row['negara'], $excelRow),
            'provinsi_id' => $this->findId('provinsi', $row['provinsi'], $excelRow),
            'kabupaten_id' => $this->findId('kabupaten', $row['kabupaten'], $excelRow),
            'kecamatan_id' => $this->findId('kecamatan', $row['kecamatan'], $excelRow),
            'jalan' => $row['jalan'] ?? null,
            'kode_pos' => $row['kode_pos'] ?? null,
            'status' => true,
            'created_at' => now(),
            'updated_at' => now(),
            'created_by' => $this->userId
        ]);
        

        return $id;
    }

    private function insertWargaPesantren($biodataId, $row, $excelRow)
    {
        if (!isset($row['niup'])) return;
        DB::table('warga_pesantren')->insert([
            'biodata_id' => $biodataId,
            'niup' => $row['niup'],
            'status' => 1,
            'created_by' => $this->userId,
            'created_at' => now(),
            'updated_at' => now(),

        ]);
    }

    private function insertKaryawan($pegawaiId, $row, $excelRow)
    {
        if (!isset($row['golongan_jabatan_karyawan'])) return;

        DB::table('karyawan')->insert([
            'pegawai_id' => $pegawaiId,
            'lembaga_id' => $this->findId('lembaga', $row['lembaga_karyawan'], $excelRow),
            'jabatan_id' => $this->findId('jabatan', $row['jabatan_karyawan'], $excelRow),
            'keterangan_jabatan' => $row['keterangan_jabatan_karyawan'] ?? null,
            'golongan_id' => $this->findId('golongan', $row['golongan_jabatan_karyawan'], $excelRow),
            'tanggal_mulai' => $row['tanggal_mulai_karyawan'] ?? null,
            'status' => 'aktif',
            'created_at' => now(),
            'updated_at' => now(),
            'created_by' => $this->userId
        ]);
    }

    private function insertPengajar($pegawaiId, $row, $excelRow)
    {
        if (!isset($row['golongan_pengajar'])) return;

        DB::table('pengajar')->insert([
            'pegawai_id' => $pegawaiId,
            'lembaga_id' => $this->findId('lembaga', $row['lembaga_pengajar'], $excelRow),
            'jabatan_id' => $this->findId('jabatan', $row['jabatan_pengajar'], $excelRow),
            'golongan_id' => $this->findId('golongan', $row['golongan_pengajar'], $excelRow),
            'tanggal_mulai' => $row['tanggal_mulai_pengajar'] ?? null,
            'status' => 'aktif',
            'created_at' => now(),
            'updated_at' => now(),
            'created_by' => $this->userId
        ]);
    }

    private function insertPengurus($pegawaiId, $row, $excelRow)
    {
        if (!isset($row['golongan_jabatan_pengurus'])) return;

        DB::table('pengurus')->insert([
            'pegawai_id' => $pegawaiId,
            'satuan_kerja_id' => $this->findId('satuan_kerja', $row['satuan_kerja_pengurus'], $excelRow),
            'jabatan_id' => $this->findId('jabatan', $row['jabatan_pengurus'], $excelRow),
            'keterangan_jabatan' => $row['keterangan_jabatan_pengurus'] ?? null,
            'golongan_id' => $this->findId('golongan', $row['golongan_jabatan_pengurus'], $excelRow),
            'tanggal_mulai' => $row['tanggal_mulai_pengurus'] ?? null,
            'status' => 'aktif',
            'created_at' => now(),
            'updated_at' => now(),
            'created_by' => $this->userId
        ]);
    }

    private function insertWaliKelas($pegawaiId, $row, $excelRow)
    {
        if (!isset($row['kelas_wali_kelas'])) return;

        DB::table('wali_kelas')->insert([
            'pegawai_id' => $pegawaiId,
            'lembaga_id' => $this->findId('lembaga', $row['lembaga_wali_kelas'], $excelRow),
            'jurusan_id' => $this->findId('jurusan', $row['jurusan_wali_kelas'], $excelRow, false),
            'kelas_id' => $this->findId('kelas', $row['kelas_wali_kelas'], $excelRow),
            'rombel_id' => $this->findId('rombel', $row['rombel_wali_kelas'], $excelRow, false),
            'jumlah_murid' => $row['jumlah_murid_wali'] ?? null,
            'periode_awal' => $row['periode_awal_wali_kelas'] ?? null,
            'status' => 'aktif',
            'created_at' => now(),
            'updated_at' => now(),
            'created_by' => $this->userId
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

        $columnMap = [
            'negara'    => 'nama_negara',
            'provinsi'  => 'nama_provinsi',
            'kabupaten' => 'nama_kabupaten',
            'kecamatan' => 'nama_kecamatan',
            'lembaga'   => 'nama_lembaga',
            'jurusan'   => 'nama_jurusan',
            'kelas'     => 'nama_kelas',
            'rombel'    => 'nama_rombel',
            'jabatan'   => 'nama_jabatan',
            'golongan'  => 'nama_golongan',
            'satuan_kerja' => 'nama_satuan_kerja'
        ];

        $column = $columnMap[$table] ?? 'nama';
        $search = trim((string)$value);
        $searchLower = mb_strtolower($search);

        if (is_numeric($search)) {
            $record = DB::table($table)->where('id', $search)->select('id')->first();
            if ($record) return $record->id;
        }

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
}
