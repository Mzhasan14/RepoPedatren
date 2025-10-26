<?php

namespace App\Services\PesertaDidik\Fitur;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KitabService
{
    public function getAllKitabs()
    {
        return DB::table('kitab')
            ->select(
                'id',
                'nama_kitab',
                'total_bait',
                'status'
            )
            ->orderByDesc('status')
            ->get();
    }

    public function show(int $id)
    {
        $kitab = DB::table('kitab')
            ->select(
                'id',
                'nama_kitab',
                'total_bait'
            )
            ->where('id', $id)
            ->first();

        return $kitab;
    }

    public function createKitab(array $data): array
    {
        $id = DB::table('kitab')->insertGetId([
            'nama_kitab' => $data['nama_kitab'],
            'total_bait' => $data['total_bait'] ?? 0,
            'status' => true,
            'created_by' => Auth::id(),
            'updated_by' => null,
            'deleted_by' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($id) {
            return ['success' => true, 'message' => 'Kitab berhasil dibuat', 'id' => $id];
        }

        return ['success' => false, 'message' => 'Gagal membuat kitab'];
    }


    public function updateKitab(int $id, array $data): array
    {
        // Cek dulu apakah data ada dan status aktif
        $kitab = DB::table('kitab')
            ->select('status')
            ->where('id', $id)
            ->first();

        if (!$kitab) {
            return ['success' => false, 'message' => 'Kitab tidak ditemukan'];
        }

        if (!$kitab->status) {
            return ['success' => false, 'message' => 'Kitab tidak aktif sehingga tidak bisa diupdate'];
        }

        // Lanjut update
        $dataUpdate = [
            'updated_by' => Auth::id(),
            'updated_at' => now(),
        ];

        if (isset($data['nama_kitab'])) {
            $dataUpdate['nama_kitab'] = $data['nama_kitab'];
        }
        if (isset($data['total_bait'])) {
            $dataUpdate['total_bait'] = $data['total_bait'];
        }

        $updatedRows = DB::table('kitab')
            ->where('id', $id)
            ->update($dataUpdate);

        if ($updatedRows > 0) {
            return ['success' => true, 'message' => 'Kitab berhasil diupdate'];
        }

        return ['success' => false, 'message' => 'Tidak ada perubahan data'];
    }

    public function deactivateKitab(int $id, ?int $userId): array
    {
        $exists = DB::table('kitab')->where('id', $id)->exists();

        if (!$exists) {
            return ['success' => false, 'message' => 'Kitab tidak ditemukan'];
        }

        $updatedRows = DB::table('kitab')
            ->where('id', $id)
            ->where('status', true) // hanya nonaktifkan kalau masih aktif
            ->update([
                'status' => false,
                'deleted_by' => $userId,
                'updated_at' => now(),
            ]);

        if ($updatedRows > 0) {
            return ['success' => true, 'message' => 'Kitab berhasil dinonaktifkan'];
        }

        return ['success' => false, 'message' => 'Kitab sudah nonaktif atau gagal dinonaktifkan'];
    }

    public function activateKitab(int $id): array
    {
        $exists = DB::table('kitab')->where('id', $id)->exists();

        if (!$exists) {
            return ['success' => false, 'message' => 'Kitab tidak ditemukan'];
        }

        $updatedRows = DB::table('kitab')
            ->where('id', $id)
            ->where('status', false) // hanya aktifkan kalau saat ini nonaktif
            ->update([
                'status' => true,
                'deleted_by' => null,
                'updated_at' => now(),
            ]);

        if ($updatedRows > 0) {
            return ['success' => true, 'message' => 'Kitab berhasil diaktifkan kembali'];
        }

        return ['success' => false, 'message' => 'Kitab sudah aktif atau gagal diaktifkan'];
    }

}