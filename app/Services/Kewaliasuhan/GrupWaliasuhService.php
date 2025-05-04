<?php

namespace App\Services\Kewaliasuhan;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class GrupWaliasuhService
{
    public function getAllGrupWaliasuh(Request $request)
    {
        return DB::table('grup_wali_asuh AS gs')
            ->join('wali_asuh as ws', 'gs.id', '=', 'ws.id_grup_wali_asuh')
            ->join('kewaliasuhan as ks', 'ks.id_wali_asuh', '=', 'ws.id')
            ->join('anak_asuh AS aa', 'ks.id_anak_asuh', '=', 'aa.id')
            ->join('santri AS s', 'ws.id_santri', '=', 's.id')
            ->join('biodata AS b', 's.biodata_id', '=', 'b.id')
            ->leftJoin('wilayah AS w', 'gs.id_wilayah', '=', 'w.id')
            ->where('gs.status', true)
            ->select([
                'gs.id',
                'gs.nama_grup as group',
                's.nis',
                'b.nama',
                'w.nama_wilayah',
                DB::raw("COUNT(aa.id) as jumlah_anak_asuh"),
                'gs.updated_at',
                'gs.created_at',
            ])
            ->groupBy(
                'gs.id',
                'gs.nama_grup',
                's.nis',
                'b.nama',
                'w.nama_wilayah',
                'gs.updated_at',
                'gs.created_at'
            )
            ->orderBy('gs.id');
        }

    public function formatData($results)
    {
        return collect($results->items())->map(fn($item) => [
            "id" => $item->id,
            "group" => $item->group,
            "nis_wali_asuh" => $item->nis,
            "nama_wali_asuh" => $item->nama,
            "wilayah" => $item->nama_wilayah,
            "jumlah_anak_asuh" => $item->jumlah_anak_asuh,
            "tgl_update" => Carbon::parse($item->updated_at)->translatedFormat('d F Y H:i:s') ?? '-',
            "tgl_input" =>  Carbon::parse($item->created_at)->translatedFormat('d F Y H:i:s'),
        ]);
    }
}
