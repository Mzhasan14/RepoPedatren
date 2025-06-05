<?php

namespace App\Services\PesertaDidik\Fitur;

use App\Models\Biodata;
use App\Models\RiwayatPendidikan;
use Illuminate\Support\Facades\Auth;

class ProsesLulusPendidikanService
{
    public function prosesLulus(array $data)
    {
        $now = now();
        $userId = Auth::id();
        $bioIds = $data['biodata_id'];

        // Ambil semua nama lengkap berdasarkan biodata_id
        $biodataList = Biodata::whereIn('id', $bioIds)->pluck('nama', 'id');

        // Ambil semua riwayat aktif untuk biodata_id yang diberikan
        $riwayatAktif = RiwayatPendidikan::whereIn('biodata_id', $bioIds)
            ->where('status', 'aktif')
            ->latest('id')
            ->get()
            ->keyBy('biodata_id');

        $dataGagal = [];
        $dataBerhasil = [];

        foreach ($bioIds as $bioId) {
            $rp = $riwayatAktif->get($bioId);
            $nama = $biodataList[$bioId] ?? 'Tidak diketahui';

            if (is_null($rp) || !is_null($rp->tanggal_keluar)) {
                $dataGagal[] = [
                    'nama' => $nama,
                    'message' => 'Riwayat pendidikan tidak ditemukan atau sudah keluar.',
                ];
                continue;
            }

            // Update status jadi lulus
            $rp->update([
                'status' => 'lulus',
                'tanggal_keluar' => $now,
                'updated_at' => $now,
                'updated_by' => $userId,
            ]);

            $dataBerhasil[] = [
                'nama' => $nama,
                'message' => 'Berhasil di-set lulus.',
            ];
        }

        return [
            'success' => true,
            'message' => 'Proses set lulus selesai.',
            'data_berhasil' => $dataBerhasil,
            'data_gagal' => $dataGagal,
        ];
    }
}
