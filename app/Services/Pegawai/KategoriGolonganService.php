<?php

namespace App\Services\Pegawai;

use App\Models\Pegawai\KategoriGolongan;
use Illuminate\Support\Facades\Auth;

class KategoriGolonganService
{
    public function index()
    {
        $kategori = KategoriGolongan::select([
            'id',
            'nama_kategori_golongan',
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
        $kategori = KategoriGolongan::create([
            'nama_kategori_golongan' => $data['nama_kategori_golongan'],
            'created_by' => Auth::id(),
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return ['status' => true, 'data' => $kategori];
    }

    public function edit($id): array
    {
        $kategori = KategoriGolongan::select([
            'id',
            'nama_kategori_golongan',
            'status',
        ])->find($id);

        if (!$kategori) {
            return ['status' => false, 'message' => 'Data tidak ditemukan'];
        }

        return ['status' => true, 'data' => $kategori];
    }

    public function update(array $data, $id)
    {
        $kategori = KategoriGolongan::find($id);

        if (!$kategori) {
            return ['status' => false, 'message' => 'Data tidak ditemukan'];
        }

        $kategori->update([
            'nama_kategori_golongan' => $data['nama_kategori_golongan'],
            'updated_by' => Auth::id(),
            'updated_at' => now()
        ]);

        return ['status' => true, 'data' => $kategori];
    }

    public function destroy($id)
    {
        $kategori = KategoriGolongan::find($id);

        if (!$kategori) {
            return ['status' => false, 'message' => 'Data tidak ditemukan'];
        }

        $kategori->update([
            'status' => 0,
            'updated_by' => Auth::id(),
            'updated_at' => now()
        ]);

        return ['status' => true, 'data' => $kategori,'message' => 'Kategori berhasil dinonaktifkan'];
    }
}