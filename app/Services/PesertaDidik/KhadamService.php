<?php

namespace App\Services\PesertaDidik;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class KhadamService
{
    public function getAllKhadam(Request $request)
    {
        // 1) Ambil ID jenis berkas 'Pas foto'
        $pasFotoId = DB::table('jenis_berkas')
            ->where('nama_jenis_berkas', 'Pas foto')
            ->value('id');

        // Subqueries: ID terakhir berkas pas foto
        $fotoLast = DB::table('berkas')
            ->select('biodata_id', DB::raw('MAX(id) AS last_id'))
            ->where('jenis_berkas_id', $pasFotoId)
            ->groupBy('biodata_id');

        // Subqueries: ID terakhir warga pesantren yang aktif
        $wpLast = DB::table('warga_pesantren')
            ->select('biodata_id', DB::raw('MAX(id) AS last_id'))
            ->where('status', true)
            ->groupBy('biodata_id');

        return DB::table('khadam as kh')
            ->join('biodata as b', 'kh.biodata_id', '=', 'b.id')
            ->leftjoin('santri as s', 's.biodata_id', '=', 'b.id')
            ->leftJoinSub($fotoLast, 'fl', fn($j) => $j->on('b.id', '=', 'fl.biodata_id'))
            ->leftJoin('berkas AS br', 'br.id', '=', 'fl.last_id')
            ->leftJoinSub($wpLast, 'wl', fn($j) => $j->on('b.id', '=', 'wl.biodata_id'))
            ->leftJoin('warga_pesantren AS wp', 'wp.id', '=', 'wl.last_id')
            ->where('kh.status', true)
            ->select(
                'kh.id',
                'wp.niup',
                DB::raw("COALESCE(b.nik, b.no_passport) as identitas"),
                'b.nama',
                'kh.keterangan',
                'kh.created_at',
                'kh.updated_at',
                DB::raw("COALESCE(br.file_path, 'default.jpg') as foto_profil")
            );
    }

    public function formatData($results)
    {
        return collect($results->items())->map(fn($item) => [
            "id_khadam" => $item->id,
            "niup" => $item->niup ?? '-',
            "nik" => $item->identitas ?? '-',
            "nama" => $item->nama,
            "keterangan" => $item->keterangan,
            "tgl_update" => Carbon::parse($item->updated_at)->translatedFormat('d F Y H:i:s') ?? '-',
            "tgl_input" =>  Carbon::parse($item->created_at)->translatedFormat('d F Y H:i:s'),
            "foto_profil" => url($item->foto_profil)
        ]);
    }
}
