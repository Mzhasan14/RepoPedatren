<?php

namespace App\Services\Pegawai\Filters\Formulir;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PengurusService
{
    public function index(string $bioId): array
    {
        $pengurus = DB::table('pengurus as k')
            ->join('pegawai as p', 'k.pegawai_id', 'p.id')
            ->join('biodata as b', 'p.biodata_id', 'b.id')
            ->where('b.id', $bioId)
            ->select(
                'k.id',
                'k.jabatan as jabatan_kontrak',
                'k.satuan_kerja',
                'k.keterangan_jabatan',
                'k.tanggal_mulai as tanggal_masuk',
                'k.tanggal_akhir as tanggal_keluar',
                'k.status_aktif as status',
            )
            ->get();

        return ['status' => true, 'data' => $pengurus];
    }

    public function edit($id): array
    {
        $pengurus = DB::table('pengurus as k')
            ->where('k.id', $id)
            ->select(
                'k.id',
                'k.golongan_jabatan_id',
                'k.jabatan as jabatan_kontrak',
                'k.satuan_kerja',
                'k.keterangan_jabatan',
                'k.tanggal_mulai as tanggal_masuk',
                'k.tanggal_akhir as tanggal_keluar',
                'k.status_aktif as status',
            )
            ->first();
            if (!$pengurus) {
                return ['status' => false, 'message' => 'Data tidak ditemukan'];
            }
        return ['status' => true, 'data' => $pengurus];
    }
    public function store(array $data, string $bioId)
    {
        // Cek apakah pegawai sudah memiliki pengurus aktif
        $exist = DB::table('pengurus as k')
            ->join('pegawai as p', 'k.pegawai_id', 'p.id')
            ->join('biodata as b', 'p.biodata_id', 'b.id')
            ->where('b.id', $bioId)
            ->where('k.status_aktif', 'aktif')
            ->first();

        if ($exist) {
            return ['status' => false, 'message' => 'Pegawai masih memiliki Pengurus aktif'];
        }

        // Cari pegawai berdasarkan biodata_id (bioId)
        $pegawai = DB::table('pegawai')
            ->where('biodata_id', $bioId)
            ->latest()
            ->first();
        if (!$pegawai) {
            return ['status' => false, 'message' => 'pegawai tidak ditemukan untuk biodata ini'];
        }

        // Insert data baru
        $id = DB::table('pengurus')->insertGetId([
            'pegawai_id' => $pegawai->id,
            'golongan_jabatan_id' => $data['golongan_jabatan_id'],
            'jabatan' => $data['jabatan'],
            'satuan_kerja' => $data['satuan_kerja'],
            'keterangan_jabatan' => $data['keterangan_jabatan'],
            'tanggal_mulai' => $data['tanggal_mulai'] ?? now(),
            'status_aktif' => 'aktif',
            'created_by' => Auth::id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $new = DB::table('pengurus')->where('id', $id)->first();

        return ['status' => true, 'data' => $new];
    }
    public function update(array $data, string $id)
    {
        $pengurus = DB::table('pengurus')->where('id', $id)->first();

        if (!$pengurus) {
            return ['status' => false, 'message' => 'Data tidak ditemukan'];
        }

        // Cegah update jika tanggal_keluar sudah terisi sebelumnya
        if (!is_null($pengurus->tanggal_akhir)) {
            return ['status' => false, 'message' => 'Data riwayat tidak boleh di rubah!'];
        }

        // Jika tanggal_keluar diisi manual, pastikan tanggal_keluar tidak lebih awal dari tanggal_masuk
        if (!empty($data['tanggal_akhir'])) {
            $tanggalMasuk = strtotime($pengurus->tanggal_mulai);
            $tanggalKeluar = strtotime($data['tanggal_akhir']);

            if ($tanggalKeluar < $tanggalMasuk) {
                return ['status' => false, 'message' => 'Tanggal keluar tidak boleh lebih awal dari tanggal masuk.'];
            }

            DB::table('pengurus')
                ->where('id', $id)
                ->update([
                    'tanggal_akhir' => $data['tanggal_akhir'],
                    'status' => 'keluar',
                    'updated_by' => Auth::id(),
                    'updated_at' => now(),
                ]);

            $updated = DB::table('pengurus')->where('id', $id)->first();
            return ['status' => true, 'data' => $updated];
        }
        // Cek perubahan lokasi
        $isGolonganJabatanChanged = $pengurus->golongan_jabatan_id !== $data['golongan_jabatan_id'];

        if ($isGolonganJabatanChanged) {

            DB::table('pengurus')
                ->where('id', $id)
                ->update([
                    'status_aktif' => 'tidak aktif',
                    'tanggal_akhir' => now(),
                    'updated_by' => Auth::id(),
                    'updated_at' => now(),
                ]);

            $new = DB::table('pengurus')->insertGetId([
                'pegawai_id' => $pengurus->pegawai_id,
                'golongan_jabatan_id' => $data['golongan_jabatan_id'],
                'satuan_kerja' => $data['satuan_kerja'] ?? $pengurus->satuan_kerja,
                'jabatan' => $data['jabatan'] ?? $pengurus->jabatan,
                'keterangan_jabatan' => $data['keterangan_jabatan'] ?? $pengurus->keterangan_jabatan,
                'tanggal_mulai' => now(),
                'status_aktif' => 'aktif',
                'created_by' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $newData = DB::table('pengurus')->where('id', $new)->first();
            return ['status' => true, 'data' => $newData];
        }

        return ['status' => false, 'message' => 'Tidak ada perubahan data'];
    }
}