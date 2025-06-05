<?php

namespace App\Services\PesertaDidik\Fitur;

use App\Models\Biodata;
use App\Models\RiwayatPendidikan;
use Illuminate\Support\Facades\Auth;

class PindahNaikJenjangService
{
    public function pindah(array $data)
    {
        $now = now();
        $userId = Auth::id();
        $bioIds = $data['biodata_id'];

        // Ambil nama lengkap
        $biodataList = Biodata::whereIn('id', $bioIds)->pluck('nama', 'id');

        // Ambil riwayat aktif
        $riwayatAktif = RiwayatPendidikan::whereIn('biodata_id', $bioIds)
            ->where('status', 'aktif')
            ->latest('id')
            ->get()
            ->keyBy('biodata_id');

        $dataBaru = [];
        $dataBaruNama = [];
        $dataGagal = [];

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

            $rp->update([
                'status' => 'pindah',
                'tanggal_keluar' => $now,
                'updated_at' => $now,
                'updated_by' => $userId,
            ]);

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

            $dataBaruNama[] = [
                'nama' => $nama,
                'message' => 'Berhasil dipindahkan.',
            ];
        }

        if (!empty($dataBaru)) {
            RiwayatPendidikan::insert($dataBaru);
        }

        return [
            'success' => true,
            'message' => 'Peserta didik berhasil dipindahkan ke jenjang baru.',
            'data_baru' => $dataBaruNama,
            'data_gagal' => $dataGagal,
        ];
    }

    public function naik(array $data)
    {
        $now = now();
        $userId = Auth::id();
        $bioIds = $data['biodata_id'];

        $biodataList = Biodata::whereIn('id', $bioIds)->pluck('nama', 'id');

        $riwayatAktif = RiwayatPendidikan::whereIn('biodata_id', $bioIds)
            ->where('status', 'aktif')
            ->latest('id')
            ->get()
            ->keyBy('biodata_id');

        $dataBaru = [];
        $dataBaruNama = [];
        $dataGagal = [];

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

            $rp->update([
                'status' => 'naik_kelas',
                'tanggal_keluar' => $now,
                'updated_at' => $now,
                'updated_by' => $userId,
            ]);

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

            $dataBaruNama[] = [
                'nama' => $nama,
                'message' => 'Berhasil naik kelas.',
            ];
        }

        if (!empty($dataBaru)) {
            RiwayatPendidikan::insert($dataBaru);
        }

        return [
            'success' => true,
            'message' => 'Peserta didik berhasil naik ke jenjang baru.',
            'data_baru' => $dataBaruNama,
            'data_gagal' => $dataGagal,
        ];
    }
}
