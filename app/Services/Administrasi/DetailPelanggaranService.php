<?php

namespace App\Services\Administrasi;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

class DetailPelanggaranService
{
    public function getDetailPelanggaran(string $pelanggaranId)
    {
        $pasFotoId = DB::table('jenis_berkas')
            ->where('nama_jenis_berkas', 'Pas foto')
            ->value('id');

        $fotoLast = DB::table('berkas')
            ->select('biodata_id', DB::raw('MAX(id) AS last_id'))
            ->where('jenis_berkas_id', $pasFotoId)
            ->groupBy('biodata_id');

        $pelanggaran = DB::table('pelanggaran as pl')
            ->join('santri as s', 'pl.santri_id', '=', 's.id')
            ->leftjoin('biodata as b', 's.biodata_id', '=', 'b.id')
            ->leftjoin('provinsi as pv', 'b.provinsi_id', '=', 'pv.id')
            ->leftjoin('kabupaten as kb', 'b.kabupaten_id', '=', 'kb.id')
            ->leftjoin('kecamatan as kc', 'b.kecamatan_id', '=', 'kc.id')
            ->leftjoin('riwayat_domisili as rd', fn ($j) => $j->on('s.id', '=', 'rd.santri_id')->where('rd.status', 'aktif'))
            ->leftjoin('wilayah as w', 'rd.wilayah_id', '=', 'w.id')
            ->leftjoin('blok as bl', 'rd.blok_id', '=', 'bl.id')
            ->leftjoin('kamar as km', 'rd.kamar_id', '=', 'km.id')
            ->leftjoin('riwayat_pendidikan AS rp', fn ($j) => $j->on('b.id', '=', 'rp.biodata_id')->where('rp.status', 'aktif'))
            ->leftJoin('lembaga as l', 'rp.lembaga_id', '=', 'l.id')
            ->leftJoin('users as pencatat', 'pl.created_by', '=', 'pencatat.id')
            ->leftJoinSub($fotoLast, 'fl', fn ($j) => $j->on('b.id', '=', 'fl.biodata_id'))
            ->leftJoin('berkas AS br', 'br.id', '=', 'fl.last_id')
            ->where('pl.id', $pelanggaranId)
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
            ])->first();

        if ($pelanggaran) {
            $data['pelanggaran'] = [
                'id' => $pelanggaran->id,
                'nama_santri' => $pelanggaran->nama,
                'provinsi' => $pelanggaran->nama_provinsi ?? '-',
                'kabupaten' => $pelanggaran->nama_kabupaten ?? '-',
                'kecamatan' => $pelanggaran->nama_kecamatan ?? '-',
                'wilayah' => $pelanggaran->nama_wilayah ?? '-',
                'blok' => $pelanggaran->nama_blok ?? '-',
                'kamar' => $pelanggaran->nama_kamar ?? '-',
                'lembaga' => $pelanggaran->nama_lembaga ?? '-',
                'status_pelanggaran' => $pelanggaran->status_pelanggaran,
                'jenis_pelanggaran' => $pelanggaran->jenis_pelanggaran,
                'jenis_putusan' => $pelanggaran->jenis_putusan,
                'diproses_mahkamah' => (bool) $pelanggaran->diproses_mahkamah,
                'keterangan' => $pelanggaran->keterangan ?? '-',
                'pencatat' => $pelanggaran->pencatat,
                'foto_profil' => url($pelanggaran->foto_profil),
                'tgl_input' => Carbon::parse($pelanggaran->created_at)
                    ->translatedFormat('d F Y H:i:s'),
            ];
        }

        // Berkas Pelanggaran
        $berkas = DB::table('pelanggaran as pl')
            ->join('santri as s', 's.id', 'pl.santri_id')
            ->join('biodata as b', 's.biodata_id', 'b.id')
            ->join('berkas_pelanggaran as bp', 'pl.id', 'bp.pelanggaran_id')
            ->where('pl.id', $pelanggaranId)
            ->whereColumn('bp.created_at', '>=', 'pl.created_at')
            ->orderBy('bp.created_at', 'desc')
            ->limit(4)
            ->selectRaw("COALESCE(bp.file_path, 'default.jpg') as file_path")
            ->get();

        $data['Berkas'] = $berkas->map(function ($r) {
            return URL::to($r->file_path);
        })->toArray();

        return $data;
    }
}
