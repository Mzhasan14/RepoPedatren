<?php

namespace App\Services\PesertaDidik\Pembayaran;

use App\Models\TagihanSantri;
use App\Models\Pembayaran;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PembayaranService
{
    public function bayar(array $data, int $userId): array
    {
        DB::beginTransaction();
        try {
            $tagihanSantri = TagihanSantri::lockForUpdate()->findOrFail($data['tagihan_santri_id']);

            if ($tagihanSantri->sisa <= 0) {
                return ['success' => false, 'message' => 'Tagihan sudah lunas.'];
            }

            $bayar = min($data['jumlah_bayar'], $tagihanSantri->sisa);

            // Insert pembayaran
            $pembayaran = Pembayaran::create([
                'tagihan_santri_id' => $tagihanSantri->id,
                'metode' => $data['metode'],
                'jumlah_bayar' => $bayar,
                'status' => 'berhasil',
                'keterangan' => $data['keterangan'] ?? null,
                'created_by' => $userId,
            ]);

            // Update tagihan_santri
            $tagihanSantri->sisa -= $bayar;
            if ($tagihanSantri->sisa == 0) {
                $tagihanSantri->status = 'lunas';
            } elseif ($tagihanSantri->sisa < $tagihanSantri->nominal) {
                $tagihanSantri->status = 'sebagian';
            }
            $tagihanSantri->tanggal_bayar = now();
            $tagihanSantri->updated_by = $userId;
            $tagihanSantri->save();

            DB::commit();
            return ['success' => true, 'message' => 'Pembayaran berhasil.', 'data' => $pembayaran];
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Gagal pembayaran tagihan santri", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return ['success' => false, 'message' => 'Terjadi kesalahan saat pembayaran.'];
        }
    }
}
