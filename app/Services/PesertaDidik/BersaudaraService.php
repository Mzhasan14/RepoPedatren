<?php

namespace App\Services\PesertaDidik;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class BersaudaraService
{
    public function getAllBersaudara(Request $request)
    {
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

        // 4) Derived table: nama ibu & ayah per no_kk
        $parents = DB::table('orang_tua_wali AS otw')
            ->join('keluarga AS k2', 'k2.id_biodata', '=', 'otw.id_biodata')
            ->join('biodata AS b2', 'b2.id', '=', 'otw.id_biodata')
            ->join('hubungan_keluarga AS hk', 'hk.id', '=', 'otw.id_hubungan_keluarga')
            ->select([
                'k2.no_kk',
                DB::raw("MAX(CASE WHEN hk.nama_status='ibu' THEN b2.nama END) AS nama_ibu"),
                DB::raw("MAX(CASE WHEN hk.nama_status='ayah' THEN b2.nama END) AS nama_ayah"),
            ])
            ->groupBy('k2.no_kk');

        // 5) Derived table: keluarga dengan >1 anak aktif
        $siblings = DB::table('keluarga AS k2')
            ->leftjoin('santri AS s2', 's2.biodata_id', '=', 'k2.id_biodata')
            ->leftjoin('riwayat_pendidikan AS rp2', 's2.id', '=', 'rp2.santri_id')
            ->whereNotIn('k2.id_biodata', function ($q) {
                $q->select('id_biodata')->from('orang_tua_wali');
            })
            ->where(fn($q) => $q->where('s2.status', 'aktif')
                ->orWhere('rp2.status', '=', 'aktif'))
            ->select('k2.no_kk', DB::raw('COUNT(*) AS cnt'))
            ->groupBy('k2.no_kk')
            ->having('cnt', '>', 1);

        // Query utama: data peserta didik bersaudara all
        return DB::table('santri AS s')
            ->join('biodata AS b', 's.biodata_id', '=', 'b.id')
            ->join('keluarga AS k', 'k.id_biodata', '=', 'b.id')
            ->joinSub($parents, 'parents', fn($j) => $j->on('k.no_kk', '=', 'parents.no_kk'))
            ->joinSub($siblings, 'sib', fn($join) => $join->on('k.no_kk', '=', 'sib.no_kk'))
            ->leftjoin('riwayat_pendidikan AS rp', fn($j) => $j->on('s.id', '=', 'rp.santri_id')->where('rp.status', 'aktif'))
            ->leftJoin('lembaga AS l', 'rp.lembaga_id', '=', 'l.id')
            ->leftjoin('riwayat_domisili AS rd', fn($join) => $join->on('s.id', '=', 'rd.santri_id')->where('rd.status', 'aktif'))
            ->leftJoin('wilayah AS w', 'rd.wilayah_id', '=', 'w.id')
            ->leftJoinSub($fotoLast, 'fl', fn($j) => $j->on('b.id', '=', 'fl.biodata_id'))
            ->leftJoin('berkas AS br', 'br.id', '=', 'fl.last_id')
            ->leftJoinSub($wpLast, 'wl', fn($j) => $j->on('b.id', '=', 'wl.biodata_id'))
            ->leftJoin('warga_pesantren AS wp', 'wp.id', '=', 'wl.last_id')
            ->leftJoin('kabupaten AS kb', 'kb.id', '=', 'b.kabupaten_id')
            ->where(fn($q) => $q->where('s.status', 'aktif')
                ->orWhere('rp.status', '=', 'aktif'))
            ->where(fn($q) => $q->whereNull('b.deleted_at')
                ->whereNull('s.deleted_at'))
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
                DB::raw("
                 GREATEST(
                     s.updated_at,
                     COALESCE(rp.updated_at, s.updated_at),
                     COALESCE(rd.updated_at, s.updated_at)
                 ) AS updated_at
             "),
                DB::raw("COALESCE(parents.nama_ibu, 'Tidak Diketahui') AS nama_ibu"),
                DB::raw("COALESCE(parents.nama_ayah, 'Tidak Diketahui') AS nama_ayah"),
            ])
            ->orderBy('k.no_kk');
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
