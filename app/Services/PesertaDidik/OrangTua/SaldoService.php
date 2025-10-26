<?php

namespace App\Services\PesertaDidik\OrangTua;

use App\Models\Saldo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class SaldoService
{
    public function saldo($request)
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

        // 🔹 Cek apakah santri_id valid
        $dataAnak = $anak->firstWhere('santri_id', $request['santri_id']);

        if (!$dataAnak) {
            return [
                'success' => false,
                'message' => 'Santri tidak valid untuk user ini.',
                'data'    => null,
                'status'  => 403,
            ];
        }

        $saldo = Saldo::where('santri_id', $request['santri_id'])
            ->where('status', true)
            ->first();

        if (!$saldo) {
            return [
                'success' => false,
                'data' => null,
                'message' => 'Data saldo tidak di temukan'
            ];
        }

        return [
            'success' => true,
            'data' => $saldo,
            'message' => 'Saldo berhasil ditampilkan'
        ];
    }
}
