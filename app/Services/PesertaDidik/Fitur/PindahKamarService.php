<?php

namespace App\Services\PesertaDidik\Fitur;

use App\Models\RiwayatDomisili;
use App\Models\RiwayatPendidikan;
use Illuminate\Support\Facades\Auth;

class PindahKamarService
{
    public function pindah(array $data)
    {
        $now = now();
        $userId = Auth::id();
        $santriIds = $data['santri_id'];

        // Ambil semua riwayat aktif sekaligus untuk menghindari query berulang
        $riwayatAktif = RiwayatDomisili::whereIn('santri_id', $santriIds)
            ->where('status', 'aktif')
            ->latest('id')
            ->get()
            ->keyBy('santri_id');

        $dataBaru = [];
        $dataGagal = [];

        foreach ($santriIds as $santriId) {
            $rp = $riwayatAktif->get($santriId);

            if (is_null($rp) || !is_null($rp->tanggal_keluar)) {
                $dataGagal[] = [
                    'santri_id' => $santriId,
                    'message' => 'Riwayat domisili tidak ditemukan atau sudah keluar.',
                ];

                continue;
            }

            // Update riwayat lama
            $rp->update([
                'status' => 'pindah',
                'tanggal_keluar' => $now,
                'updated_at' => $now,
                'updated_by' => $userId,
            ]);

            // Siapkan riwayat baru
            $dataBaru[] = [
                'santri_id' => $santriId,
                'wilayah_id' => $data['wilayah_id'],
                'blok_id' => $data['blok_id'],
                'kamar_id' => $data['kamar_id'],
                'status' => 'aktif',
                'tanggal_masuk' => $now,
                'created_by' => $userId,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // Insert sekaligus untuk efisiensi
        if (!empty($dataBaru)) {
            RiwayatDomisili::insert($dataBaru);
        }

        return [
            'success' => true,
            'message' => 'Santri berhasil dipindahkan ke domisili baru.',
            'data_baru' => count($dataBaru),
            'data_gagal' => $dataGagal ?? 0,
        ];
    }
}
