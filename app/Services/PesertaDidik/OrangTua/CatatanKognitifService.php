<?php

namespace App\Services\PesertaDidik\OrangTua;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CatatanKognitifService
{

    public function catatanKognitif($request)
    {
        $user = Auth::user();
        $noKk = $user->no_kk;

        // 🔹 Ambil semua anak dari KK yang sama
        $anak = DB::table('keluarga as k')
            ->join('biodata as b', 'k.id_biodata', '=', 'b.id')
            ->join('santri as s', 'b.id', '=', 's.biodata_id')
            ->select('s.id as santri_id')
            ->where('k.no_kk', $noKk)
            ->get();

        if ($anak->isEmpty()) {
            return [
                'success' => false,
                'message' => 'Tidak ada data anak yang ditemukan.',
                'data'    => null,
                'status'  => 404,
            ];
        }

        // 🔹 Cek apakah santri_id request valid
        $dataAnak = $anak->firstWhere('santri_id', $request['santri_id'] ?? null);

        if (!$dataAnak) {
            return [
                'success' => false,
                'message' => 'Santri tidak valid untuk user ini.',
                'data'    => null,
                'status'  => 403,
            ];
        }

        $catatan = DB::table('catatan_kognitif as ck')
            ->join('santri as s', 's.id', '=', 'ck.id_santri')
            ->join('biodata as b', 'b.id', '=', 's.biodata_id')
            ->where('ck.id_santri', $request['santri_id'])
            ->select(
                'ck.id',
                'b.nama as nama_santri',
                'b.jenis_kelamin',
                'ck.kebahasaan_nilai',
                'ck.kebahasaan_tindak_lanjut',
                'ck.baca_kitab_kuning_nilai',
                'ck.baca_kitab_kuning_tindak_lanjut',
                'ck.hafalan_tahfidz_nilai',
                'ck.hafalan_tahfidz_tindak_lanjut',
                'ck.furudul_ainiyah_nilai',
                'ck.furudul_ainiyah_tindak_lanjut',
                'ck.tulis_alquran_nilai',
                'ck.tulis_alquran_tindak_lanjut',
                'ck.baca_alquran_nilai',
                'ck.baca_alquran_tindak_lanjut',
                'ck.tanggal_buat'
            )
            ->orderByDesc('ck.id')
            ->first();

        if (!$catatan) {
            return [
                'success' => true,
                'message' => 'Santri belum memiliki catatan kognitif.',
                'data'    => null,
                'status'  => 200,
            ];
        }

        return [
            'success' => true,
            'message' => 'Data catatan kognitif terbaru berhasil diambil.',
            'status'  => 200,
            'data'    => $catatan,
        ];
    }
}
