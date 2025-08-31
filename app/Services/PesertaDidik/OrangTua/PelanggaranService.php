<?php

namespace App\Services\PesertaDidik\OrangTua;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PelanggaranService
{
    public function pelanggaran($request)
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

        $perPage = $request['per_page'] ?? 25;
        $page    = $request['page'] ?? 1;

        $pelanggaran = DB::table('pelanggaran as pl')
            ->join('santri as s', 's.id', 'pl.santri_id')
            ->join('biodata as b', 'b.id', 's.biodata_id')
            ->leftJoin('users as pencatat', 'pl.created_by', '=', 'pencatat.id')
            ->where('pl.santri_id', $request['santri_id'])
            ->select(
                'pl.status_pelanggaran',
                'pl.jenis_pelanggaran',
                'pl.jenis_putusan',
                'pl.diproses_mahkamah',
                'pl.keterangan',
                'pl.created_at',
                DB::raw("COALESCE(pencatat.name, '(AutoSystem)') as pencatat"),
            )
            ->orderByDesc('pl.id')
            ->paginate($perPage, ['*'], 'page', $page);

        if ($pelanggaran->isEmpty()) {
            return [
                'success' => true,
                'message' => 'Santri belum memiliki data pelanggaran.',
                'data'    => [],
                'status'  => 200,
            ];
        }

        return [
            'success' => true,
            'message' => 'Data pelanggaran berhasil diambil.',
            'status'  => 200,
            'meta'    => [
                'current_page' => $pelanggaran->currentPage(),
                'per_page'     => $pelanggaran->perPage(),
                'total'        => $pelanggaran->total(),
                'last_page'    => $pelanggaran->lastPage(),
            ],
            'data'     => $pelanggaran->items(),
        ];
    }
}
