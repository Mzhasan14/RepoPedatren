<?php

namespace App\Services\Administrasi;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

class DetailPerizinanService
{
  public function getDetailPerizinan(string $perizinanId): array
  {
    $biodata = DB::table('santri as s')
      ->join('perizinan as pr', 's.id', '=', 'pr.santri_id')
      ->join('biodata as b', 's.biodata_id', '=', 'b.id')
      ->leftjoin('riwayat_pendidikan AS rp', fn($j) => $j->on('s.id', '=', 'rp.santri_id')->where('rp.status', 'aktif'))
      ->leftJoin('lembaga AS l', 'rp.lembaga_id', '=', 'l.id')
      ->leftjoin('jurusan as j', 'rp.jurusan_id', '=', 'j.id')
      ->leftjoin('kelas as kls', 'rp.kelas_id', '=', 'kls.id')
      ->leftjoin('rombel as r', 'rp.rombel_id', '=', 'r.id')
      ->leftjoin('riwayat_domisili AS rd', fn($join) => $join->on('s.id', '=', 'rd.santri_id')->where('rd.status', 'aktif'))
      ->leftJoin('wilayah AS w', 'rd.wilayah_id', '=', 'w.id')
      ->leftJoin('blok AS bl', 'rd.blok_id', '=', 'bl.id')
      ->leftJoin('kamar AS km', 'rd.kamar_id', '=', 'km.id')
      ->leftJoin('kecamatan as kc', 'b.kecamatan_id', '=', 'kc.id')
      ->leftJoin('kabupaten as kb', 'b.kabupaten_id', '=', 'kb.id')
      ->leftJoin('provinsi as pv', 'b.provinsi_id', '=', 'pv.id')
      ->leftJoin('negara as ng', 'b.negara_id', '=', 'ng.id')
      ->leftjoin('users as biktren', 'pr.biktren_id', '=', 'biktren.id')
      ->leftjoin('users as pengasuh',  'pr.pengasuh_id',  '=', 'pengasuh.id')
      ->leftjoin('users as kamtib',  'pr.kamtib_id',  '=', 'kamtib.id')
      ->join('users as creator', 'pr.created_by', '=', 'creator.id')
      ->leftJoin('berkas as br', function ($j) {
        $j->on('b.id', 'br.biodata_id')
          ->where('br.jenis_berkas_id', function ($q) {
            $q->select('id')
              ->from('jenis_berkas')
              ->where('nama_jenis_berkas', 'Pas foto')
              ->limit(1);
          })
          ->whereRaw('br.id = (
                    select max(id)
                    from berkas
                    where biodata_id = b.id
                      and jenis_berkas_id = br.jenis_berkas_id
                )');
      })
      ->where('pr.id', $perizinanId)
      ->select([
        'b.nama as nama_santri',
        'b.jenis_kelamin',
        'w.nama_wilayah',
        'bl.nama_blok',
        'km.nama_kamar',
        'l.nama_lembaga',
        'pv.nama_provinsi',
        'kb.nama_kabupaten',
        'kc.nama_kecamatan',
        'pr.alasan_izin',
        'pr.alamat_tujuan',
        'pr.tanggal_mulai',
        'pr.tanggal_akhir',
        DB::raw("
          CASE
            WHEN DATE(pr.tanggal_mulai) = DATE(pr.tanggal_akhir)
              THEN 'sehari'
            ELSE 'bermalam'
          END AS bermalam
        "),
        DB::raw("
          CASE
            WHEN TIMESTAMPDIFF(HOUR, pr.tanggal_mulai, pr.tanggal_akhir) < 24 THEN
              CONCAT(TIMESTAMPDIFF(HOUR, pr.tanggal_mulai, pr.tanggal_akhir), ' jam')
            WHEN TIMESTAMPDIFF(DAY, pr.tanggal_mulai, pr.tanggal_akhir) < 7 THEN
              CONCAT(TIMESTAMPDIFF(DAY, pr.tanggal_mulai, pr.tanggal_akhir), ' hari')
            WHEN TIMESTAMPDIFF(DAY, pr.tanggal_mulai, pr.tanggal_akhir) < 30 THEN
              CONCAT(CEIL(TIMESTAMPDIFF(DAY, pr.tanggal_mulai, pr.tanggal_akhir) / 7), ' minggu')
            ELSE
              CONCAT(CEIL(TIMESTAMPDIFF(DAY, pr.tanggal_mulai, pr.tanggal_akhir) / 30), ' bulan')
          END AS lama_izin
        "),
        'pr.tanggal_kembali',
        'pr.jenis_izin',
        'pr.status_izin',
        'creator.name as pembuat',
        'pengasuh.name as nama_pengasuh',
        'biktren.name as nama_biktren',
        'kamtib.name as nama_kamtib',
        'pr.keterangan',
        DB::raw("COALESCE(br.file_path,'default.jpg') as foto"),
      ])
      ->first();

    $data['Pemohon Izin'] = [
      'nama'               => $biodata->nama_santri ?? '-',
      'jenis_kelamin'      => $biodata->jenis_kelamin ?? '-',
      'wilayah'            => $biodata->nama_wilayah ?? '-',
      'blok'               => $biodata->nama_blok ?? '-',
      'kamar'              => $biodata->nama_kamar ?? '-',
      'lembaga'            => $biodata->nama_lembaga ?? '-',
      'kecamatan'          => $biodata->nama_kecamatan ?? '-',
      'kabupaten'          => $biodata->nama_kabupaten ?? '-',
      'provinsi'           => $biodata->nama_provinsi ?? '-',
      'alasan_izin'        => $biodata->alasan_izin ?? '-',
      'alamat_tujuan'      => $biodata->alamat_tujuan ?? '-',
      'tanggal_mulai'      => $biodata->tanggal_mulai ?? '-',
      'tanggal_akhir'      => $biodata->tanggal_akhir ?? '-',
      'bermalam'           => $biodata->bermalam ?? '-',
      'lama_izin'          => $biodata->lama_izin ?? '-',
      'tanggal_kembali'    => $biodata->tanggal_kembali ?? '-',
      'jenis_izin'         => $biodata->jenis_izin ?? '-',
      'status_izin'        => $biodata->status_izin ?? '-',
      'pembuat'            => $biodata->pembuat ?? '-',
      'pengasuh'           => $biodata->nama_pengasuh ?? '-',
      'biktren'            => $biodata->nama_biktren ?? '-',
      'kamtib'             => $biodata->nama_kamtib ?? '-',
      'keterangan'         => $biodata->keterangan ?? '-',
      'foto_profil'        => URL::to($biodata->foto),
    ];

    // Data Pengantar / Penjemput
    $pengantar = DB::table('perizinan as pr')
      ->join('santri as s', 'pr.santri_id', '=', 's.id')
      ->join('biodata as b', 's.biodata_id', '=', 'b.id')
      ->leftjoin('orang_tua_wali as otw', 'pr.pengantar_id', '=', 'otw.id')
      ->leftjoin('biodata as b_ortu', 'otw.id_biodata', '=', 'b_ortu.id')
      ->leftJoin('hubungan_keluarga as hk', 'otw.id_hubungan_keluarga', '=', 'hk.id')
      ->leftjoin('users as biktren', 'pr.biktren_id', '=', 'biktren.id')
      ->leftjoin('users as pengasuh',  'pr.pengasuh_id',  '=', 'pengasuh.id')
      ->leftjoin('users as kamtib',  'pr.kamtib_id',  '=', 'kamtib.id')
      ->join('users as creator', 'pr.created_by', '=', 'creator.id')
      ->select([
        'b_ortu.nama',
        DB::raw("TIMESTAMPDIFF(YEAR, b_ortu.tanggal_lahir, CURDATE()) as usia"),
        DB::raw("COALESCE(hk.nama_status, '-') as status"),
        'creator.name as pembuat',
        'pengasuh.name as nama_pengasuh',
        'biktren.name as nama_biktren',
        'kamtib.name as nama_kamtib',
      ])
      ->where('pr.id', $perizinanId)
      ->first();

    if ($pengantar) {
      $data['Pengantar'] = [
        'nama'   => $pengantar->nama ?? '-',
        'usia'   => $pengantar->usia ?? '-',
        'status' => $pengantar->status ?? '-',
        'pembuat'            => $biodata->pembuat ?? '-',
        'pengasuh'           => $biodata->nama_pengasuh ?? '-',
        'biktren'            => $biodata->nama_biktren ?? '-',
        'kamtib'             => $biodata->nama_kamtib ?? '-',
      ];
    }

    // Berkas Perizinan
    $berkas = DB::table('perizinan as pr')
      ->join('santri as s', 's.id', 'pr.santri_id')
      ->join('biodata as b', 's.biodata_id', 'b.id')
      ->join('berkas_perizinan as bp', 'pr.id', 'bp.perizinan_id')
      ->where('pr.id', $perizinanId)
      ->whereColumn('bp.created_at', '>=', 'pr.created_at')
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
