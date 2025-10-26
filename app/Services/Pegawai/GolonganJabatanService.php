<?php

namespace App\Services\Pegawai;

use App\Models\Pegawai\GolonganJabatan;
use Illuminate\Support\Facades\Auth;

class GolonganJabatanService
{
    public function index()
    {
        $kategori = GolonganJabatan::select([
            'id',
            'nama_golongan_jabatan',
            'status',
        ])
            ->where('status', 1) // Hanya tampilkan yang aktif
            ->get();

        if ($kategori->isEmpty()) {
            return ['status' => false, 'message' => 'Data tidak ditemukan'];
        }

        return ['status' => true, 'data' => $kategori];
    }

    public function store(array $data)
    {
        $kategori = GolonganJabatan::create([
            'nama_golongan_jabatan' => $data['nama_golongan_jabatan'],
            'created_by' => Auth::id(),
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return ['status' => true, 'data' => $kategori];
    }

    public function edit($id): array
    {
        $kategori = GolonganJabatan::select([
            'id',
            'nama_golongan_jabatan',
            'status',
        ])->find($id);

        if (! $kategori) {
            return ['status' => false, 'message' => 'Data tidak ditemukan'];
        }

        return ['status' => true, 'data' => $kategori];
    }

    public function update(array $data, $id)
    {
        $kategori = GolonganJabatan::find($id);

        if (! $kategori) {
            return ['status' => false, 'message' => 'Data tidak ditemukan'];
        }

        $kategori->update([
            'nama_golongan_jabatan' => $data['nama_golongan_jabatan'],
            'updated_by' => Auth::id(),
            'updated_at' => now(),
        ]);

        return ['status' => true, 'data' => $kategori];
    }

    public function destroy($id)
    {
        $kategori = GolonganJabatan::find($id);

        if (! $kategori) {
            return ['status' => false, 'message' => 'Data tidak ditemukan'];
        }

        $kategori->update([
            'status' => 0,
            'updated_by' => Auth::id(),
            'updated_at' => now(),
        ]);

        return ['status' => true, 'data' => $kategori, 'message' => 'Kategori berhasil dinonaktifkan'];
    }
}
