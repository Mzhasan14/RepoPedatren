<?php

namespace App\Services\Pegawai;

use App\Models\Pegawai\Golongan;
use Illuminate\Support\Facades\Auth;

class GolonganService
{
    public function index()
    {
        $golongan = Golongan::select([
            'id',
            'nama_golongan',
            'kategori_golongan_id',
            'status',
        ])
            ->where('status', 1) // Hanya tampilkan yang aktif
            ->get(); // Tambahkan get() untuk eksekusi query

        if ($golongan->isEmpty()) { // Perbaikan pengecekan data kosong
            return ['status' => false, 'message' => 'Data tidak ditemukan'];
        }

        return ['status' => true, 'data' => $golongan];
    }

    public function store(array $data)
    {
        $golongan = Golongan::create([
            'nama_golongan' => $data['nama_golongan'],
            'kategori_golongan_id' => $data['kategori_golongan_id'],
            'created_by' => Auth::id(),
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(), // Tambahkan updated_at
        ]);

        return ['status' => true, 'data' => $golongan];
    }

    public function edit($id): array
    {
        $golongan = Golongan::select([
            'id',
            'nama_golongan',
            'kategori_golongan_id',
            'status',
        ])->find($id); // find() sudah tepat untuk mencari by ID

        if (! $golongan) {
            return ['status' => false, 'message' => 'Data tidak ditemukan'];
        }

        return ['status' => true, 'data' => $golongan];
    }

    public function update(array $data, $id)
    {
        $golongan = Golongan::find($id); // Hapus ->first() karena find() sudah return single object

        if (! $golongan) {
            return ['status' => false, 'message' => 'Data tidak ditemukan'];
        }

        $golongan->update([
            'nama_golongan' => $data['nama_golongan'],
            'kategori_golongan_id' => $data['kategori_golongan_id'],
            'updated_by' => Auth::id(),
            'updated_at' => now(),
            // Status tidak diupdate karena mungkin ingin mempertahankan status sebelumnya
        ]);

        return ['status' => true, 'data' => $golongan];
    }

    public function destroy($id)
    {
        $golongan = Golongan::find($id); // Hapus ->first()

        if (! $golongan) {
            return ['status' => false, 'message' => 'Data tidak ditemukan'];
        }

        $golongan->update([
            'status' => 0,
            'updated_by' => Auth::id(),
            'updated_at' => now(),
        ]);

        return ['status' => true, 'data' => $golongan, 'message' => 'Data berhasil dinonaktifkan'];
    }
}
