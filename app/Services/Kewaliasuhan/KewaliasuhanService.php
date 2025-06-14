<?php

namespace App\Services\Kewaliasuhan;

use App\Models\Kewaliasuhan\Kewaliasuhan;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KewaliasuhanService
{
    public function update(array $data)
    {
        $userId = Auth::id();
        $relasi = Kewaliasuhan::findOrFail($data['id']);

        DB::beginTransaction();
        try {
            $relasi->update([
                'tanggal_berakhir' => $data['tanggal_berakhir'] ?? $relasi->tanggal_berakhir,
                'status' => $data['status'] ?? $relasi->status,
                'updated_by' => $userId,
                'updated_at' => Carbon::now(),
            ]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Relasi anak asuh berhasil diperbarui.',
                'data' => $relasi,
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            return [
                'success' => false,
                'message' => 'Gagal memperbarui relasi anak asuh.',
                'error' => $e->getMessage(),
            ];
        }
    }

    public function delete($id)
    {
        $userId = Auth::id();
        $relasi = Kewaliasuhan::findOrFail($id);

        DB::beginTransaction();
        try {
            $relasi->update([
                'deleted_by' => $userId,
            ]);
            $relasi->delete();

            DB::commit();

            return [
                'success' => true,
                'message' => 'Relasi anak asuh berhasil dihapus.',
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            return [
                'success' => false,
                'message' => 'Gagal menghapus relasi anak asuh.',
                'error' => $e->getMessage(),
            ];
        }
    }
}
