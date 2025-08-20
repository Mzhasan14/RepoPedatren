<?php

namespace App\Services\PesertaDidik\Pembayaran;

use App\Models\TagihanSantri;
use App\Models\Santri;
use App\Models\Tagihan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TagihanSantriService
{
    public function assignToSantri(array $data, int $userId): array
    {
        DB::beginTransaction();
        try {
            $tagihan = Tagihan::findOrFail($data['tagihan_id']);

            $santriQuery = Santri::query();
            if (!empty($data['angkatan_id'])) {
                $santriQuery->where('angkatan_id', $data['angkatan_id']);
            }
            if (!empty($data['santri_ids'])) {
                $santriQuery->whereIn('id', $data['santri_ids']);
            }

            $santriList = $santriQuery->pluck('id');

            foreach ($santriList as $santriId) {
                TagihanSantri::updateOrCreate(
                    ['tagihan_id' => $tagihan->id, 'santri_id' => $santriId],
                    [
                        'nominal' => $tagihan->nominal,
                        'sisa' => $tagihan->nominal,
                        'status' => 'pending',
                        'tanggal_jatuh_tempo' => $tagihan->jatuh_tempo,
                        'created_by' => $userId,
                    ]
                );
            }

            DB::commit();
            return ['success' => true, 'message' => 'Tagihan berhasil ditambahkan ke santri.'];
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Gagal assign tagihan santri", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return ['success' => false, 'message' => 'Terjadi kesalahan assign tagihan santri.'];
        }
    }
}
