<?php

namespace App\Services\Kewaliasuhan;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class WaliasuhService
{
    public function getAllWaliasuh(Request $request) {
        // 1) Ambil ID untuk jenis berkas "Pas foto"
        $pasFotoId = DB::table('jenis_berkas')
            ->where('nama_jenis_berkas', 'Pas foto')
            ->value('id');

        // 2) Subquery: foto terakhir per biodata
        $fotoLast = DB::table('berkas')
            ->select('biodata_id', DB::raw('MAX(id) AS last_id'))
            ->where('jenis_berkas_id', $pasFotoId)
            ->groupBy('biodata_id');

        // 3) Subquery: warga_pesantren terakhir per biodata (status = true)
        $wpLast = DB::table('warga_pesantren')
            ->select('biodata_id', DB::raw('MAX(id) AS last_id'))
            ->where('status', true)
            ->groupBy('biodata_id');

        return DB::table('wali_asuh AS ws')
            ->join('santri AS s', 'ws.id_santri','=','s.id')
            ->join('biodata AS b', 's.biodata_id', '=', 'b.id')
            ->leftjoin('riwayat_domisili AS rd', fn($join) => $join->on('s.id', '=', 'rd.santri_id')->where('rd.status', 'aktif'))
            ->leftjoin('wilayah AS w', 'rd.wilayah_id', '=', 'w.id')
            ->leftjoin('blok AS bl', 'rd.blok_id', '=', 'bl.id')
            ->leftjoin('kamar AS km', 'rd.kamar_id', '=', 'km.id')
            ->leftJoinSub($fotoLast, 'fl', fn($j) => $j->on('b.id', '=', 'fl.biodata_id'))
            ->leftJoin('berkas AS br', 'br.id', '=', 'fl.last_id')
            ->leftJoinSub($wpLast, 'wl', fn($j) => $j->on('b.id', '=', 'wl.biodata_id'))
            ->leftJoin('warga_pesantren AS wp', 'wp.id', '=', 'wl.last_id')
            ->leftJoin('kabupaten AS kb', 'kb.id', '=', 'b.kabupaten_id')
            ->where('ws.status', true)
            ->select([
                'ws.id',
                's.nis',
                'b.nama',
                'km.nama_kamar',
                'bl.nama_blok',
                'w.nama_wilayah',
                DB::raw('YEAR(s.tanggal_masuk) as angkatan'),
                'kb.nama_kabupaten AS kota_asal',
                's.created_at',
                // ambil updated_at terbaru antar s, rp, rd
                DB::raw("
                   GREATEST(
                       s.updated_at,
                       COALESCE(ws.updated_at, s.updated_at),
                       COALESCE(ws.updated_at, s.updated_at)
                   ) AS updated_at
               "),
                DB::raw("COALESCE(br.file_path, 'default.jpg') AS foto_profil"),
            ])
            ->orderBy('ws.id');
    }

    public function formatData($results)
    {
        return collect($results->items())->map(fn($item) => [
            "id" => $item->id,
            "nis" => $item->nis,
            "nama" => $item->nama,
            "kamar" => $item->nama_kamar ?? '-',
            "blok" => $item->nama_blok ?? '-',
            "wilayah" => $item->nama_wilayah ?? '-',
            "kota_asal" => $item->kota_asal,
            "angkatan" => $item->angkatan,
            "tgl_update" => Carbon::parse($item->updated_at)->translatedFormat('d F Y H:i:s') ?? '-',
            "tgl_input" =>  Carbon::parse($item->created_at)->translatedFormat('d F Y H:i:s'),
            "foto_profil" => url($item->foto_profil)
        ]);
    }
}
