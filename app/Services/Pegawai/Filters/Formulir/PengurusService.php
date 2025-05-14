<?php

namespace App\Services\Pegawai\Filters\Formulir;

use App\Models\Pegawai\Pegawai;
use App\Models\Pegawai\Pengurus;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PengurusService
{
    public function index(string $bioId): array
    {
        $pengurus = Pengurus::whereHas('pegawai', function ($query) use ($bioId) {
                $query->where('biodata_id', $bioId);
            })
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'jabatan_kontrak' => $item->jabatan,
                    'satuan_kerja' => $item->satuan_kerja,
                    'keterangan_jabatan' => $item->keterangan_jabatan,
                    'tanggal_masuk' => $item->tanggal_mulai,
                    'tanggal_keluar' => $item->tanggal_akhir,
                    'status' => $item->status_aktif,
                ];
            });

        if ($pengurus->isEmpty()) {
            return ['status' => false, 'message' => 'Data tidak ditemukan'];
        }

        return ['status' => true, 'data' => $pengurus];
    }


    public function edit($id): array
    {
        $pengurus = Pengurus::select(
                'id',
                'golongan_jabatan_id',
                'jabatan as jabatan_kontrak',
                'satuan_kerja',
                'keterangan_jabatan',
                'tanggal_mulai as tanggal_masuk',
                'tanggal_akhir as tanggal_keluar',
                'status_aktif as status',
            )
            ->find($id);

        if (!$pengurus) {
            return ['status' => false, 'message' => 'Data tidak ditemukan'];
        }

        return ['status' => true, 'data' => $pengurus];
    }

    public function update(array $data, string $id)
    {
        $pengurus = Pengurus::find($id);

        if (!$pengurus) {
            return ['status' => false, 'message' => 'Data tidak ditemukan'];
        }

        // Cegah update jika sudah memiliki tanggal keluar
        if (!is_null($pengurus->tanggal_akhir)) {
            return ['status' => false, 'message' => 'Data riwayat tidak boleh di rubah!'];
        }

        // Handle jika tanggal keluar diisi manual
        if (!empty($data['tanggal_akhir'])) {
            $tanggalMasuk = strtotime($pengurus->tanggal_mulai);
            $tanggalKeluar = strtotime($data['tanggal_akhir']);

            if ($tanggalKeluar < $tanggalMasuk) {
                return ['status' => false, 'message' => 'Tanggal keluar tidak boleh lebih awal dari tanggal masuk.'];
            }

            $pengurus->update([
                'tanggal_akhir' => $data['tanggal_akhir'],
                'status' => 'keluar',
                'updated_by' => Auth::id(),
                'updated_at' => now(),
            ]);

            return ['status' => true, 'data' => $pengurus->fresh()];
        }

        // Cek perubahan golongan jabatan
        $isGolonganJabatanChanged = $pengurus->golongan_jabatan_id !== $data['golongan_jabatan_id'];

        if ($isGolonganJabatanChanged) {
            // Nonaktifkan data lama
            $pengurus->update([
                'status_aktif' => 'tidak aktif',
                'tanggal_akhir' => now(),
                'updated_by' => Auth::id(),
                'updated_at' => now(),
            ]);

            // Buat entri baru
            $newPengurus = Pengurus::create([
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

            return ['status' => true, 'data' => $newPengurus];
        }

        return ['status' => false, 'message' => 'Tidak ada perubahan data'];
    }
    public function store(array $data, string $bioId)
    {
        // Cek apakah pegawai sudah memiliki pengurus aktif
        $exist = Pengurus::where('status_aktif', 'aktif')
            ->whereHas('pegawai', function ($query) use ($bioId) {
                $query->whereHas('biodata', function ($q) use ($bioId) {
                    $q->where('id', $bioId);
                });
            })
            ->first();

        if ($exist) {
            return ['status' => false, 'message' => 'Pegawai masih memiliki Pengurus aktif'];
        }

        // Ambil pegawai berdasarkan biodata_id (biodata harus relasi di model Pegawai)
        $pegawai = Pegawai::where('biodata_id', $bioId)->latest()->first();

        if (!$pegawai) {
            return ['status' => false, 'message' => 'Pegawai tidak ditemukan untuk biodata ini'];
        }

        // Simpan data pengurus baru
        $pengurus = Pengurus::create([
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

        return ['status' => true, 'data' => $pengurus];
    }

}