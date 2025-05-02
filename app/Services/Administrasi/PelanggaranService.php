<?php

namespace App\Services\Administrasi;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PelanggaranService
{
    public function getAllPelanggaran(Request $request)
    {
        $pasFotoId = DB::table('jenis_berkas')
            ->where('nama_jenis_berkas', 'Pas foto')
            ->value('id');

        $fotoLast = DB::table('berkas')
            ->select('biodata_id', DB::raw('MAX(id) AS last_id'))
            ->where('jenis_berkas_id', $pasFotoId)
            ->groupBy('biodata_id');

        $pelanggaranLast = DB::table('pelanggaran')
            ->select('santri_id', DB::raw('MAX(id) AS last_pl_id'))
            ->groupBy('santri_id');

        return DB::table('pelanggaran as pl')
            ->joinSub($pelanggaranLast, 'plt', function ($join) {
                $join->on('pl.santri_id', '=', 'plt.santri_id')
                    ->on('pl.id', '=', 'plt.last_pl_id');
            })
            ->join('santri as s', 'pl.santri_id', '=', 's.id')
            ->leftjoin('biodata as b', 's.biodata_id', '=', 'b.id')
            ->leftjoin('provinsi as pv', 'b.provinsi_id', '=', 'pv.id')
            ->leftjoin('kabupaten as kb', 'b.kabupaten_id', '=', 'kb.id')
            ->leftjoin('kecamatan as kc', 'b.kecamatan_id', '=', 'kc.id')
            ->leftjoin('riwayat_domisili as rd', fn($j) => $j->on('s.id', '=', 'rd.santri_id')->where('rd.status', 'aktif'))
            ->leftjoin('wilayah as w', 'rd.wilayah_id', '=', 'w.id')
            ->leftjoin('blok as bl', 'rd.blok_id', '=', 'bl.id')
            ->leftjoin('kamar as km', 'rd.kamar_id', '=', 'km.id')
            ->leftjoin('riwayat_pendidikan AS rp', fn($j) => $j->on('s.id', '=', 'rp.santri_id')->where('rp.status', 'aktif'))
            ->leftJoin('lembaga as l', 'rp.lembaga_id', '=', 'l.id')
            ->leftJoin('users as pencatat', 'pl.created_by', '=', 'pencatat.id')
            ->leftJoinSub($fotoLast, 'fl', fn($j) => $j->on('b.id', '=', 'fl.biodata_id'))
            ->leftJoin('berkas AS br', 'br.id', '=', 'fl.last_id')
            ->select([
                'pl.id',
                'b.nama',
                'pv.nama_provinsi',
                'kb.nama_kabupaten',
                'kc.nama_kecamatan',
                'w.nama_wilayah',
                'bl.nama_blok',
                'km.nama_kamar',
                'l.nama_lembaga',
                'pl.status_pelanggaran',
                'pl.jenis_pelanggaran',
                'pl.jenis_putusan',
                'pl.diproses_mahkamah',
                'pl.keterangan',
                'pl.created_at',
                DB::raw("COALESCE(pencatat.name, '(AutoSystem)') as pencatat"),
                DB::raw("COALESCE(br.file_path, 'default.jpg') AS foto_profil"),
            ])
            ->orderBy('pl.id', 'desc');
    }

    public function formatData($results)
    {
        return collect($results->items())->map(function ($item) {
            return [
                'id'                   => $item->id,
                'nama_santri'          => $item->nama,                     
                'provinsi'             => $item->nama_provinsi ?? '-',
                'kabupaten'            => $item->nama_kabupaten ?? '-',
                'kecamatan'            => $item->nama_kecamatan ?? '-',
                'wilayah'              => $item->nama_wilayah ?? '-',
                'blok'                 => $item->nama_blok     ?? '-',
                'kamar'                => $item->nama_kamar    ?? '-',
                'lembaga'              => $item->nama_lembaga  ?? '-',
                'status_pelanggaran'   => $item->status_pelanggaran,
                'jenis_pelanggaran'    => $item->jenis_pelanggaran,
                'jenis_putusan'        => $item->jenis_putusan,
                'diproses_mahkamah'    => (bool) $item->diproses_mahkamah,
                'keterangan'           => $item->keterangan    ?? '-',
                'pencatat'             => $item->pencatat,
                'foto_profil'          => url($item->foto_profil),
                'tgl_input'            => Carbon::parse($item->created_at)
                    ->translatedFormat('d F Y H:i:s'),
            ];
        });
    }
}
