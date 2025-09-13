<?php

namespace App\Services\PesertaDidik\OrangTua;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CatatanAfektifService
{
    public function catatanAfektif($request)
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

        $catatan = DB::table('catatan_afektif as ca')
            ->join('santri as s', 's.id', '=', 'ca.id_santri')
            ->join('biodata as b', 'b.id', '=', 's.biodata_id')
            ->where('ca.id_santri', $request['santri_id'])
            ->select(
                'ca.id',
                'b.nama as nama_santri',
                'b.jenis_kelamin',
                'ca.kepedulian_nilai',
                'ca.kepedulian_tindak_lanjut',
                'ca.kebersihan_nilai',
                'ca.kebersihan_tindak_lanjut',
                'ca.akhlak_nilai',
                'ca.akhlak_tindak_lanjut',
                'ca.tanggal_buat'
            )
            ->orderByDesc('ca.id')
            ->first();

        if (!$catatan) {
            return [
                'success' => true,
                'message' => 'Santri belum memiliki catatan afektif.',
                'data'    => null,
                'status'  => 200,
            ];
        }

        return [
            'success' => true,
            'message' => 'Data catatan afektif terbaru berhasil diambil.',
            'status'  => 200,
            'data'    => $catatan,
        ];
    }
}
