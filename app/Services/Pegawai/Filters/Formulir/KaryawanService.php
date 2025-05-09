<?php

namespace App\Services\Pegawai\Filters\Formulir;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KaryawanService
{
    public function index(string $bioId): array
    {
        $karyawan = DB::table('karyawan as k')
            ->join('pegawai as p', 'k.pegawai_id', 'p.id')
            ->join('biodata as b', 'p.biodata_id', 'b.id')
            ->where('b.id', $bioId)
            ->select(
                'k.id',
                'k.jabatan as jabatan_kontrak',
                'k.keterangan_jabatan',
                'k.tanggal_mulai as tanggal_masuk',
                'k.tanggal_selesai as tanggal_keluar',
                'k.status_aktif as status',
            )
            ->get();

        return ['status' => true, 'data' => $karyawan];
    }

    public function edit($id): array
    {
        $karyawan = DB::table('karyawan as k')
            // ->join('pegawai as p', 'k.pegawai_id', 'p.id')
            // ->join('biodata as b', 'p.biodata_id', 'b.id')
            ->where('k.id', $id)
            ->select(
                'k.id',
                'k.golongan_jabatan_id',
                'k.lembaga_id',
                'k.jabatan as jabatan_kontrak',
                'k.keterangan_jabatan',
                'k.tanggal_mulai as tanggal_masuk',
                'k.tanggal_selesai as tanggal_keluar',
                'k.status_aktif as status',
            )
            ->first();
            if (!$karyawan) {
                return ['status' => false, 'message' => 'Data tidak ditemukan'];
            }
        return ['status' => true, 'data' => $karyawan];
    }
    public function store(array $data, string $bioId)
    {
        // Cek apakah pegawai sudah memiliki karyawan aktif
        $exist = DB::table('karyawan as k')
            ->join('pegawai as p', 'k.pegawai_id', 'p.id')
            ->join('biodata as b', 'p.biodata_id', 'b.id')
            ->where('b.id', $bioId)
            ->where('k.status_aktif', 'aktif')
            ->first();

        if ($exist) {
            return ['status' => false, 'message' => 'Pegawai masih memiliki Karyawan aktif'];
        }

        // Cari pegawai berdasarkan biodata_id (bioId)
        $karyawan = DB::table('pegawai')
            ->where('biodata_id', $bioId)
            ->latest()
            ->first();
        if (!$karyawan) {
            return ['status' => false, 'message' => 'pegawai tidak ditemukan untuk biodata ini'];
        }

        // Insert data baru
        $id = DB::table('karyawan')->insertGetId([
            'pegawai_id' => $karyawan->id,
            'golongan_jabatan_id' => $data['golongan_jabatan_id'],
            'lembaga_id' => $data['lembaga_id'],
            'jabatan' => $data['jabatan'],
            'keterangan_jabatan' => $data['keterangan_jabatan'],
            'tanggal_mulai' => $data['tanggal_mulai'] ?? now(),
            'status_aktif' => 'aktif',
            'created_by' => Auth::id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $new = DB::table('karyawan')->where('id', $id)->first();

        return ['status' => true, 'data' => $new];
    }
    public function update(array $data, string $id)
    {
        $karyawan = DB::table('karyawan')->where('id', $id)->first();

        if (!$karyawan) {
            return ['status' => false, 'message' => 'Data tidak ditemukan'];
        }

        // Cegah update jika tanggal_keluar sudah terisi sebelumnya
        if (!is_null($karyawan->tanggal_selesai)) {
            return ['status' => false, 'message' => 'Data riwayat tidak boleh di rubah!'];
        }

        // Jika tanggal_keluar diisi manual, pastikan tanggal_keluar tidak lebih awal dari tanggal_masuk
        if (!empty($data['tanggal_selesai'])) {
            $tanggalMasuk = strtotime($karyawan->tanggal_mulai);
            $tanggalKeluar = strtotime($data['tanggal_selesai']);

            if ($tanggalKeluar < $tanggalMasuk) {
                return ['status' => false, 'message' => 'Tanggal keluar tidak boleh lebih awal dari tanggal masuk.'];
            }

            DB::table('karyawan')
                ->where('id', $id)
                ->update([
                    'tanggal_selesai' => $data['tanggal_selesai'],
                    'status' => 'keluar',
                    'updated_by' => Auth::id(),
                    'updated_at' => now(),
                ]);

            $updated = DB::table('karyawan')->where('id', $id)->first();
            return ['status' => true, 'data' => $updated];
        }
        // Cek perubahan lokasi
        $isGolonganJabatanChanged = $karyawan->golongan_jabatan_id !== $data['golongan_jabatan_id'];
        $isLembagaChanged = $karyawan->lembaga_id !== $data['lembaga_id'];

        if ($isGolonganJabatanChanged || $isLembagaChanged) {

            DB::table('karyawan')
                ->where('id', $id)
                ->update([
                    'status_aktif' => 'tidak aktif',
                    'tanggal_selesai' => now(),
                    'updated_by' => Auth::id(),
                    'updated_at' => now(),
                ]);

            $new = DB::table('karyawan')->insertGetId([
                'pegawai_id' => $karyawan->pegawai_id,
                'golongan_jabatan_id' => $data['golongan_jabatan_id'],
                'lembaga_id' => $data['lembaga_id'],
                'jabatan' => $data['jabatan'] ?? $karyawan->jabatan,
                'keterangan_jabatan' => $data['keterangan_jabatan'] ?? $karyawan->keterangan_jabatan,
                'tanggal_mulai' => now(),
                'status_aktif' => 'aktif',
                'created_by' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $newData = DB::table('karyawan')->where('id', $new)->first();
            return ['status' => true, 'data' => $newData];
        }

        return ['status' => false, 'message' => 'Tidak ada perubahan data'];
    }
}