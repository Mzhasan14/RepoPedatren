<?php

namespace App\Services\PesertaDidik;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class BersaudaraService
{
    public function getAllBersaudara(Request $request)
    {
        $pasFotoId = DB::table('jenis_berkas')
            ->where('nama_jenis_berkas', 'Pas foto')
            ->value('id');

        // Foto terakhir
        $fotoLast = DB::table('berkas')
            ->select('biodata_id', DB::raw('MAX(id) AS last_id'))
            ->where('jenis_berkas_id', $pasFotoId)
            ->groupBy('biodata_id');

        // Warga pesantren aktif terakhir
        $wpLast = DB::table('warga_pesantren')
            ->select('biodata_id', DB::raw('MAX(id) AS last_id'))
            ->where('status', true)
            ->groupBy('biodata_id');

        // Ambil semua no_kk yang memiliki lebih dari 1 santri aktif
        $noKkBersaudara = DB::table('biodata as b')
            ->leftjoin('santri as s', 's.biodata_id', '=', 'b.id')
            ->join('keluarga AS k', 'k.id_biodata', '=', 's.biodata_id')
            ->leftJoin('pendidikan AS pd', 'b.id', '=', 'pd.biodata_id')
            ->where(fn($q) => $q->where('s.status', 'aktif')->orWhere('pd.status', 'aktif'))
            ->whereNull('s.deleted_at')
            ->groupBy('k.no_kk')
            ->havingRaw('COUNT(k.id_biodata) > 1')
            ->pluck('k.no_kk');

        // Orang tua per KK
        $parents = DB::table('orang_tua_wali AS otw')
            ->join('keluarga AS k2', 'k2.id_biodata', '=', 'otw.id_biodata')
            ->join('biodata AS b2', 'b2.id', '=', 'otw.id_biodata')
            ->join('hubungan_keluarga AS hk', 'hk.id', '=', 'otw.id_hubungan_keluarga')
            ->select([
                'k2.no_kk',
                DB::raw("MAX(CASE WHEN hk.nama_status IN ('ibu kandung', 'ibu sambung') THEN b2.nama END) AS nama_ibu"),
                DB::raw("MAX(CASE WHEN hk.nama_status IN ('ayah kandung', 'ayah sambung') THEN b2.nama END) AS nama_ayah"),
            ])
            ->groupBy('k2.no_kk');

        return DB::table('biodata as b')
            ->leftjoin('santri as s', 's.biodata_id', '=', 'b.id')
            ->join('keluarga AS k', 'k.id_biodata', '=', 'b.id')
            ->whereIn('k.no_kk', $noKkBersaudara)
            ->leftJoinSub($parents, 'parents', fn($j) => $j->on('k.no_kk', '=', 'parents.no_kk'))
            ->leftJoin('pendidikan AS pd', fn($j) => $j->on('b.id', '=', 'pd.biodata_id')->where('pd.status', 'aktif'))
            ->leftJoin('lembaga AS l', 'pd.lembaga_id', '=', 'l.id')
            ->leftJoin('domisili_santri AS ds', fn($join) => $join->on('s.id', '=', 'ds.santri_id')->where('ds.status', 'aktif'))
            ->leftJoin('wilayah AS w', 'ds.wilayah_id', '=', 'w.id')
            ->leftJoinSub($fotoLast, 'fl', fn($j) => $j->on('b.id', '=', 'fl.biodata_id'))
            ->leftJoin('berkas AS br', 'br.id', '=', 'fl.last_id')
            ->leftJoinSub($wpLast, 'wl', fn($j) => $j->on('b.id', '=', 'wl.biodata_id'))
            ->leftJoin('warga_pesantren AS wp', 'wp.id', '=', 'wl.last_id')
            ->leftJoin('kabupaten AS kb', 'kb.id', '=', 'b.kabupaten_id')
            ->where(fn($q) => $q->where('s.status', 'aktif')
                ->orWhere('pd.status', 'aktif'))
            ->whereNull('b.deleted_at')
            ->whereNull('s.deleted_at')
            ->select([
                'b.id as biodata_id',
                's.id',
                DB::raw("COALESCE(b.nik, b.no_passport) AS identitas"),
                'k.no_kk',
                'b.nama',
                'wp.niup',
                'l.nama_lembaga',
                'w.nama_wilayah',
                'br.file_path AS foto_profil',
                'kb.nama_kabupaten AS kota_asal',
                's.created_at',
                DB::raw("GREATEST(
                s.updated_at,
                COALESCE(pd.updated_at, s.updated_at),
                COALESCE(ds.updated_at, s.updated_at)
            ) AS updated_at"),
                DB::raw("COALESCE(parents.nama_ibu, 'Tidak Diketahui') AS nama_ibu"),
                DB::raw("COALESCE(parents.nama_ayah, 'Tidak Diketahui') AS nama_ayah"),
            ])
        ;
    }


    public function formatData($results)
    {
        return collect($results->items())->map(fn($item) => [
            "biodata_id" => $item->biodata_id,
            "id" => $item->id,
            "nik_nopassport"   => $item->identitas,
            "no_kk"             => $item->no_kk,
            "nama"             => $item->nama,
            "niup"             => $item->niup ?? '-',
            "lembaga"          => $item->nama_lembaga ?? '-',
            "wilayah"          => $item->nama_wilayah ?? '-',
            "kota_asal"        => $item->kota_asal,
            "ibu_kandung"      => $item->nama_ibu,
            "ayah_kandung"     => $item->nama_ayah,
            "tgl_update"       => Carbon::parse($item->updated_at)->translatedFormat('d F Y H:i:s') ?? '-',
            "tgl_input"        => Carbon::parse($item->created_at)->translatedFormat('d F Y H:i:s'),
            "foto_profil"      => url($item->foto_profil),
        ]);
    }
}
