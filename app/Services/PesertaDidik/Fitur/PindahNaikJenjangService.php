<?php

namespace App\Services\PesertaDidik\Fitur;

use App\Models\RiwayatPendidikan;
use Illuminate\Support\Facades\Auth;

class PindahNaikJenjangService
{
    public function pindah(array $data)
    {
        $now = now();
        $userId = Auth::id();
        $santriIds = $data['santri_id'];

        // Ambil semua riwayat aktif sekaligus untuk menghindari query berulang
        $riwayatAktif = RiwayatPendidikan::whereIn('santri_id', $santriIds)
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
                    'message' => 'Riwayat pendidikan tidak ditemukan atau sudah keluar.',
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
                'lembaga_id' => $data['lembaga_id'],
                'jurusan_id' => $data['jurusan_id'],
                'kelas_id' => $data['kelas_id'],
                'rombel_id' => $data['rombel_id'],
                'status' => 'aktif',
                'tanggal_masuk' => $now,
                'created_by' => $userId,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // Insert sekaligus untuk efisiensi
        if (!empty($dataBaru)) {
            RiwayatPendidikan::insert($dataBaru);
        }

        return [
            'success' => true,
            'message' => 'Santri berhasil dipindahkan ke jenjang baru.',
            'data_baru' => count($dataBaru),
            'data_gagal' => $dataGagal ?? 0,
        ];
    }
}
