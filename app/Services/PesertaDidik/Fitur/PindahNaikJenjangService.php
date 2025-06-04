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
        $bioIds = $data['biodata_id'];

        // Ambil semua riwayat aktif sekaligus untuk menghindari query berulang
        $riwayatAktif = RiwayatPendidikan::whereIn('biodata_id', $bioIds)
            ->where('status', 'aktif')
            ->latest('id')
            ->get()
            ->keyBy('biodata_id');

        $dataBaru = [];
        $dataGagal = [];

        foreach ($bioIds as $bioId) {
            $rp = $riwayatAktif->get($bioId);

            if (is_null($rp) || !is_null($rp->tanggal_keluar)) {
                $dataGagal[] = [
                    'biodata_id' => $bioId,
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
                'biodata_id' => $bioId,
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
            'message' => 'Peserta didik berhasil dipindahkan ke jenjang baru.',
            'data_baru' => count($dataBaru),
            'data_gagal' => $dataGagal ?? 0,
        ];
    }

    public function naik(array $data)
    {
        $now = now();
        $userId = Auth::id();
        $bioIds = $data['biodata_id'];

        // Ambil semua riwayat aktif sekaligus untuk menghindari query berulang
        $riwayatAktif = RiwayatPendidikan::whereIn('biodata_id', $bioIds)
            ->where('status', 'aktif')
            ->latest('id')
            ->get()
            ->keyBy('biodata_id');

        $dataBaru = [];
        $dataGagal = [];

        foreach ($bioIds as $bioId) {
            $rp = $riwayatAktif->get($bioId);

            if (is_null($rp) || !is_null($rp->tanggal_keluar)) {
                $dataGagal[] = [
                    'santri_id' => $bioId,
                    'message' => 'Riwayat pendidikan tidak ditemukan atau sudah keluar.',
                ];
                
                continue;
            }

            // Update riwayat lama
            $rp->update([
                'status' => 'naik_kelas',
                'tanggal_keluar' => $now,
                'updated_at' => $now,
                'updated_by' => $userId,
            ]);

            // Siapkan riwayat baru
            $dataBaru[] = [
                'biodata_id' => $bioId,
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
            'message' => 'Peserta didik berhasil naik ke jenjang baru.',
            'data_baru' => count($dataBaru),
            'data_gagal' => $dataGagal ?? 0,
        ];
    }
}
