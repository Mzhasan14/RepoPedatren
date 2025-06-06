<?php

namespace App\Services\PesertaDidik\Fitur;

use App\Models\Santri;
use App\Models\DomisiliSantri;
use App\Models\RiwayatDomisili;
use App\Models\RiwayatPendidikan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PindahKamarService
{
    public function pindah(array $data)
    {
        $now = now();
        $userId = Auth::id();
        $santriIds = $data['santri_id'];

        $namaSantriList = Santri::whereIn('id', $santriIds)
            ->with('biodata:id,nama')
            ->get()
            ->pluck('biodata.nama', 'id');

        $domisiliAktif = DomisiliSantri::whereIn('santri_id', $santriIds)
            ->get()
            ->keyBy('santri_id');

        $dataBaruNama = [];
        $dataGagal = [];

        DB::beginTransaction();
        try {
            foreach ($santriIds as $santriId) {
                $domisili = $domisiliAktif->get($santriId);
                $nama = $namaSantriList[$santriId] ?? 'Tidak diketahui';

                if (is_null($domisili)) {
                    $dataGagal[] = [
                        'nama' => $nama,
                        'message' => 'Data domisili aktif tidak ditemukan.',
                    ];
                    continue;
                }

                // Simpan riwayat lama
                RiwayatDomisili::create([
                    'santri_id' => $domisili->santri_id,
                    'wilayah_id' => $domisili->wilayah_id,
                    'blok_id' => $domisili->blok_id,
                    'kamar_id' => $domisili->kamar_id,
                    'status' => 'pindah',
                    'tanggal_masuk' => $domisili->tanggal_masuk,
                    'tanggal_keluar' => $now,
                    'created_by' => $domisili->created_by,
                    'created_at' => $domisili->created_at,
                    'updated_by' => $userId,
                    'updated_at' => $now,
                ]);

                // Update domisili aktif
                $domisili->update([
                    'wilayah_id' => $data['wilayah_id'],
                    'blok_id' => $data['blok_id'],
                    'kamar_id' => $data['kamar_id'],
                    'tanggal_masuk' => $now,
                    'updated_by' => $userId,
                    'updated_at' => $now,
                ]);

                $dataBaruNama[] = [
                    'nama' => $nama,
                    'message' => 'Berhasil dipindahkan.',
                ];
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan saat memindahkan domisili.',
                'error' => $e->getMessage(),
            ];
        }

        return [
            'success' => true,
            'message' => 'Santri berhasil dipindahkan ke domisili baru.',
            'data_baru' => $dataBaruNama,
            'data_gagal' => $dataGagal,
        ];
    }
}
