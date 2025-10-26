<?php

namespace App\Services\PesertaDidik\OrangTua;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PerizinanService
{
   public function perizinan($request) 
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

    $perPage = $request['per_page'] ?? 25; // 🔹 default 25 kalau tidak dikirim
    $page    = $request['page'] ?? 1;

    $perizinan = DB::table('perizinan as pr')
        ->join('santri as s', 's.id', 'pr.santri_id')
        ->join('biodata as b', 'b.id', 's.biodata_id')
        ->leftjoin('users as biktren', 'pr.biktren_id', '=', 'biktren.id')
        ->leftjoin('users as pengasuh', 'pr.pengasuh_id', '=', 'pengasuh.id')
        ->leftjoin('users as kamtib', 'pr.kamtib_id', '=', 'kamtib.id')
        ->leftjoin('users as pembuat', 'pr.created_by', '=', 'pembuat.id')
        ->where('pr.santri_id', $request['santri_id'])
        ->select(
            'pr.id',
            'b.nama as nama_santri',
            'b.jenis_kelamin',
            'pr.alasan_izin',
            'pr.alamat_tujuan',
            'pr.tanggal_mulai',
            'pr.tanggal_akhir',
            DB::raw("
                CASE
                WHEN DATE(pr.tanggal_mulai) = DATE(pr.tanggal_akhir) THEN 'sehari'
                ELSE 'bermalam'
                END AS bermalam
            "),
            DB::raw("
              CASE
                  WHEN TIMESTAMPDIFF(HOUR, pr.tanggal_mulai, pr.tanggal_akhir) < 24 THEN
                  CONCAT(TIMESTAMPDIFF(HOUR, pr.tanggal_mulai, pr.tanggal_akhir), ' jam')
                  WHEN TIMESTAMPDIFF(DAY, pr.tanggal_mulai, pr.tanggal_akhir) < 7 THEN
                  CONCAT(TIMESTAMPDIFF(DAY, pr.tanggal_mulai, pr.tanggal_akhir), ' hari')
                  WHEN TIMESTAMPDIFF(DAY, pr.tanggal_mulai, pr.tanggal_akhir) < 30 THEN
                  CONCAT(CEIL(TIMESTAMPDIFF(DAY, pr.tanggal_mulai, pr.tanggal_akhir) / 7), ' minggu')
                  ELSE
                  CONCAT(CEIL(TIMESTAMPDIFF(DAY, pr.tanggal_mulai, pr.tanggal_akhir) / 30), ' bulan')
              END
              AS lama_izin
              "),
            'pr.tanggal_kembali',
            'pr.jenis_izin',
            DB::raw("
                CASE
                    WHEN pr.status = 'telat' AND pr.tanggal_kembali IS NOT NULL THEN 'telat(sudah kembali)'
                    WHEN pr.tanggal_kembali IS NULL AND NOW() > pr.tanggal_akhir THEN 'telat(belum kembali)'
                    ELSE pr.status
                END AS status
            "),
            'pembuat.name as pembuat',
            'pengasuh.name as nama_pengasuh',
            'biktren.name as nama_biktren',
            'kamtib.name as nama_kamtib',
            'pr.approved_by_biktren',
            'pr.approved_by_kamtib',
            'pr.approved_by_pengasuh',
            'pr.keterangan',
            'pr.created_at',
            'pr.updated_at',
        )
        ->orderByDesc('pr.id')
        ->paginate($perPage, ['*'], 'page', $page);

    if ($perizinan->isEmpty()) {
        return [
            'success' => true,
            'message' => 'Santri belum memiliki data perizinan.',
            'data'    => [],
            'status'  => 200,
        ];
    }

    // 🔹 Bungkus response biar rapi
    return [
        'success' => true,
        'message' => 'Data perizinan berhasil diambil.',
        'status'  => 200,
        'meta'    => [
            'current_page' => $perizinan->currentPage(),
            'per_page'     => $perizinan->perPage(),
            'total'        => $perizinan->total(),
            'last_page'    => $perizinan->lastPage(),
        ],
        'data'     => $perizinan->items(),
    ];
}

}
