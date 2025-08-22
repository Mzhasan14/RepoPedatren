<?php

namespace App\Services\PesertaDidik\Fitur;

use Illuminate\Support\Facades\DB;

class ViewOrangTuaService
{
    public function getAnak(string $biodataOrtu)
    {
        $noKk = DB::table('keluarga as k')
            ->where('k.id_biodata', $biodataOrtu)
            ->value('no_kk');

        if (!$noKk) {
            return response()->json([
                'message' => 'Data keluarga tidak ditemukan.',
            ], 404);
        }

        $anak = DB::table('keluarga as k')
            ->join('biodata as b', 'k.id_biodata', '=', 'b.id')
            ->join('santri as s', 'b.id', '=', 's.biodata_id')
            ->leftjoin('orang_tua_wali as otw', 'b.id', '=', 'otw.id_biodata')
            ->select('b.id as biodata_id', 's.id as santri_id', 'b.nama')
            ->whereNull('otw.id_biodata')
            ->where('k.no_kk', $noKk)
            ->where('k.id_biodata', '!=', $biodataOrtu)->get();

        if ($anak->isEmpty()) {
            return response()->json([
                'message' => 'Tidak ada data anak yang ditemukan.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $anak->map(function ($item) {
                return [
                    'biodata_id' => $item->biodata_id,
                    'santri_id' => $item->santri_id,
                    'nama' => $item->nama,
                ];
            })
        ]);
    }

    
}
