<?php

namespace App\Services\PesertaDidik;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AnakPegawaiService
{
    public function getAllAnakPegawai(Request $request)
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

        return DB::table('santri AS s')
            ->join('biodata AS b', 's.biodata_id', '=', 'b.id')
            ->join('keluarga AS k', 'k.id_biodata', '=', 'b.id')
            ->join('keluarga AS parent_k', function ($j) {
                $j->on('parent_k.no_kk', '=', 'k.no_kk')
                    ->whereColumn('parent_k.id_biodata', '!=', 'k.id_biodata');
            })
            ->join('orang_tua_wali AS otw', 'otw.id_biodata', '=', 'parent_k.id_biodata')
            ->join('pegawai AS p', function ($j) {
                $j->on('p.biodata_id', '=', 'otw.id_biodata')
                    ->where('p.status', true);
            })
            ->join('biodata AS bp', 'bp.id', '=', 'p.biodata_id')
            ->leftJoin('riwayat_pendidikan AS rp', function ($j) {
                $j->on('s.id', '=', 'rp.santri_id')
                    ->where('rp.status', 'aktif');
            })
            ->leftJoin('lembaga AS l', 'rp.lembaga_id', '=', 'l.id')
            ->leftJoin('jurusan AS j', 'rp.jurusan_id', '=', 'j.id')
            ->leftJoin('kelas AS kls', 'rp.kelas_id', '=', 'kls.id')
            ->leftJoin('riwayat_domisili AS rd', function ($j) {
                $j->on('s.id', '=', 'rd.santri_id')
                    ->where('rd.status', 'aktif');
            })
            ->leftJoin('wilayah AS w', 'rd.wilayah_id', '=', 'w.id')
            ->leftJoin('blok AS bl', 'rd.blok_id', '=', 'bl.id')
            ->leftJoin('kamar AS km', 'rd.kamar_id', '=', 'km.id')
            ->leftJoinSub($fotoLast, 'fl', fn($j) => $j->on('b.id', '=', 'fl.biodata_id'))
            ->leftJoin('berkas AS br', 'br.id', '=', 'fl.last_id')
            ->leftJoinSub($wpLast, 'wl', fn($j) => $j->on('b.id', '=', 'wl.biodata_id'))
            ->leftJoin('warga_pesantren AS wp', 'wp.id', '=', 'wl.last_id')
            ->leftJoin('kabupaten AS kb', 'kb.id', '=', 'b.kabupaten_id')
            ->where(function ($q) {
                $q->where('s.status', 'aktif')
                    ->orWhere('rp.status', 'aktif');
            })
            ->select([
                's.id',
                DB::raw("COALESCE(b.nik, b.no_passport) AS identitas"),
                's.nis',
                'b.nama',
                'wp.niup',
                'l.nama_lembaga',
                'j.nama_jurusan',
                'kls.nama_kelas',
                'w.nama_wilayah',
                'km.nama_kamar',
                'bl.nama_blok',
                DB::raw("GROUP_CONCAT(DISTINCT bp.nama SEPARATOR '/ ') AS nama_ortu"),
                'kb.nama_kabupaten AS kota_asal',
                's.created_at',
                DB::raw("GREATEST(
                    s.updated_at,
                    COALESCE(rp.updated_at, s.updated_at),
                    COALESCE(rd.updated_at, s.updated_at)
                ) AS updated_at"),
                DB::raw("COALESCE(br.file_path, 'default.jpg') AS foto_profil"),
            ])
            ->groupBy([
                's.id',
                DB::raw("COALESCE(b.nik, b.no_passport)"),
                's.nis',
                'b.nama',
                'wp.niup',
                'l.nama_lembaga',
                'j.nama_jurusan',
                'kls.nama_kelas',
                'w.nama_wilayah',
                'km.nama_kamar',
                'bl.nama_blok',
                'kb.nama_kabupaten',
                's.created_at',
                DB::raw("GREATEST(s.updated_at, COALESCE(rp.updated_at, s.updated_at), COALESCE(rd.updated_at, s.updated_at))"),
                DB::raw("COALESCE(br.file_path, 'default.jpg')"),
            ])
            ->orderBy('s.id');
    }

    public function formatData($results)
    {
        return collect($results->items())->map(fn($item) => [
            'id'               => $item->id,
            'nik_or_passport'  => $item->identitas,
            'nis'               => $item->nis ?? '-',
            'nama'              => $item->nama,
            'niup'              => $item->niup ?? '-',
            'lembaga'           => $item->nama_lembaga ?? '-',
            'jurusan'           => $item->nama_jurusan ?? '-',
            'kelas'             => $item->nama_kelas ?? '-',
            'wilayah'           => $item->nama_wilayah ?? '-',
            'kamar'             => $item->nama_kamar ?? '-',
            'blok'              => $item->nama_blok ?? '-',
            'nama_ortu'         => $item->nama_ortu ?? '-',
            'kota_asal'        => $item->kota_asal,
            'tgl_update'       => Carbon::parse($item->updated_at)->translatedFormat('d F Y H:i:s') ?? '-',
            'tgl_input'        => Carbon::parse($item->created_at)->translatedFormat('d F Y H:i:s'),
            'foto_profil'      => url($item->foto_profil),
        ]);
    }
}
