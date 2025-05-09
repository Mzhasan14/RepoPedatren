<?php

namespace App\Services\Pegawai;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

class GetDetailKepegawaianService
{
  public function getAllKepegawaian($Id): array
  {
// --- Ambil basic pegawai + biodata + keluarga ---
        $base = DB::table('pegawai')
            ->join('biodata as b', 'pegawai.biodata_id', '=', 'b.id')
            ->leftJoin('keluarga as k', 'b.id', '=', 'k.id_biodata')
            ->where('b.id', $Id)
            ->select([
                'b.id as biodata_id',
                'k.no_kk',
            ])
            ->first();

        if (! $base) {
            return ['error' => 'Pegawai tersebut tidak ditemukan'];
        }
        $bioId     = $base->biodata_id;
        $noKk      = $base->no_kk;


        // --- Biodata detail ---
      $biodata = DB::table('biodata as b')
                ->leftJoin('warga_pesantren as wp', function ($j) {
                    $j->on('b.id', 'wp.biodata_id')
                      ->where('wp.status', true)
                      ->whereRaw('wp.id = (
                          select max(id)
                             from warga_pesantren
                            where biodata_id = b.id and status = true
                        )');
                    })
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
                    ->leftJoin('kecamatan as kc', 'b.kecamatan_id', '=', 'kc.id')
                    ->leftJoin('kabupaten as kb', 'b.kabupaten_id', '=', 'kb.id')
                    ->leftJoin('provinsi as pv', 'b.provinsi_id', '=', 'pv.id')
                    ->leftJoin('negara as ng', 'b.negara_id', '=', 'ng.id')
                    ->where('b.id', $bioId)
                    ->selectRaw(implode(', ', [
                        'COALESCE(b.nik, b.no_passport) as identitas',
                        'wp.niup',
                        'b.nama',
                        'b.jenis_kelamin',
                        "CONCAT(b.tempat_lahir, ', ', DATE_FORMAT(b.tanggal_lahir, '%e %M %Y')) as ttl",
                        "CONCAT(b.anak_keberapa, ' dari ', b.dari_saudara, ' bersaudara') as anak_ke",
                        "CONCAT(TIMESTAMPDIFF(YEAR, b.tanggal_lahir, CURDATE()), ' tahun') as umur",
                        'kc.nama_kecamatan',
                        'kb.nama_kabupaten',
                        'pv.nama_provinsi',
                        'ng.nama_negara',
                        "COALESCE(br.file_path,'default.jpg') as foto"
                    ]))
                    ->first();
        
                $data['Biodata'] = [
                    'nokk'               => $noKk ?? '-',
                    'nik_nopassport'     => $biodata->identitas,
                    'niup'               => $biodata->niup ?? '-',
                    'nama'               => $biodata->nama,
                    'jenis_kelamin'      => $biodata->jenis_kelamin,
                    'tempat_tanggal_lahir' => $biodata->ttl,
                    'anak_ke'            => $biodata->anak_ke,
                    'umur'               => $biodata->umur,
                    'kecamatan'          => $biodata->nama_kecamatan ?? '-',
                    'kabupaten'          => $biodata->nama_kabupaten ?? '-',
                    'provinsi'           => $biodata->nama_provinsi ?? '-',
                    'warganegara'        => $biodata->nama_negara ?? '-',
                    'foto_profil' => isset($biodata->foto) ? URL::to($biodata->foto) : URL::to('default.jpg'),

                ];

// -- Orang Tua / Wali --
$ortu = DB::table('keluarga as k')
    ->where('k.no_kk', $noKk)
    ->join('orang_tua_wali as ow', 'k.id_biodata', '=', 'ow.id_biodata')
    ->join('biodata as bo', 'ow.id_biodata', '=', 'bo.id')
    ->join('hubungan_keluarga as hk', 'ow.id_hubungan_keluarga', '=', 'hk.id')
    ->select([
        'bo.nama',
        'bo.nik',
        DB::raw("hk.nama_status as status"),
        'ow.wali'
    ])
    ->get();

// Ambil id biodata yang termasuk orang tua/wali
$excluded = DB::table('orang_tua_wali')->pluck('id_biodata')->toArray();

// Ambil id anak aktif
$anakAktifIds = DB::table('keluarga as k')
    ->where('k.no_kk', $noKk)
    ->where('k.id_biodata', '!=', $bioId)
    ->join('santri as s', 'k.id_biodata', '=', 's.biodata_id')
    ->join('riwayat_pendidikan as rp', 's.id', '=', 'rp.santri_id')
    ->where('rp.status', 'aktif')
    ->pluck('k.id_biodata')
    ->toArray();

// Ambil Anak Aktif
$anakAktif = DB::table('keluarga as k')
    ->whereIn('k.id_biodata', $anakAktifIds)
    ->join('biodata as b', 'k.id_biodata', '=', 'b.id')
    ->select([
        'b.nama',
        'b.nik',
        DB::raw("'Anak Aktif' as status"),
        DB::raw("NULL as wali")
    ])
    ->get();

// Ambil Saudara Kandung yang bukan ortu dan bukan anak aktif
$saudara = DB::table('keluarga as k')
    ->where('k.no_kk', $noKk)
    ->whereNotIn('k.id_biodata', $excluded)
    ->whereNotIn('k.id_biodata', $anakAktifIds)
    ->where('k.id_biodata', '!=', $bioId)
    ->join('biodata as bs', 'k.id_biodata', '=', 'bs.id')
    ->select([
        'bs.nama',
        'bs.nik',
        DB::raw("'Saudara Kandung' as status"),
        DB::raw("NULL as wali")
    ])
    ->get();

// Gabungkan dengan urutan: ortu -> anak aktif -> saudara
$keluarga = collect()->merge($ortu)->merge($anakAktif)->merge($saudara);

// Mapping hasil akhir
$data['Keluarga'] = $keluarga->isNotEmpty()
    ? $keluarga->map(fn($i) => [
        'nama'   => $i->nama,
        'nik'    => $i->nik,
        'status' => $i->status,
        'wali'   => $i->wali ?? '-',
    ])->toArray()
    : [];

    


        // ---  Informasi Pegawai yang juga Santri ---
        $santriInfo = DB::table('santri as s')
            ->where('biodata_id', $bioId)
            ->select('s.nis', 's.tanggal_masuk', 's.tanggal_keluar')
            ->first();

            $data['Status_Santri']['Santri'] = $santriInfo
            ? [[
                'NIS'           => $santriInfo->nis,
                'Tanggal_Mulai' => $santriInfo->tanggal_masuk,
                'Tanggal_Akhir' => $santriInfo->tanggal_keluar ?? '-',
            ]]
            : [];
        
    

        // ---  Kewaliasuhan untuk Pegawai ---
        $kew = DB::table('pegawai as p')
            ->join('biodata as b', 'p.biodata_id', '=', 'b.id')
            ->join('santri as s', 'b.id', '=', 's.biodata_id')
            ->leftJoin('wali_asuh as wa', 's.id', '=', 'wa.id_santri')
            ->leftJoin('anak_asuh as aa', 's.id', '=', 'aa.id_santri')
            ->leftJoin('kewaliasuhan as kw', function ($j) {
                $j->on('kw.id_wali_asuh', 'wa.id')
                ->orOn('kw.id_anak_asuh', 'aa.id');
            })
            ->leftJoin('grup_wali_asuh as g', 'g.id', '=', 'wa.id_grup_wali_asuh')
            ->where('b.id', $bioId) 
            ->selectRaw(implode(', ', [
                'g.nama_grup',
                "CASE WHEN wa.id IS NOT NULL THEN 'Wali Asuh' ELSE 'Anak Asuh' END as role",
                "GROUP_CONCAT(
                    CASE
                    WHEN wa.id IS NOT NULL THEN (
                        select bio2.nama
                        from biodata bio2
                        join santri s3 on bio2.id = s3.biodata_id
                        join wali_asuh wa3 on wa3.id_santri = s3.id
                        where wa3.id = kw.id_wali_asuh
                    )
                    ELSE (
                        select bio.nama
                        from biodata bio
                        join santri s2 on bio.id = s2.biodata_id
                        join anak_asuh aa2 on aa2.id_santri = s2.id
                        where aa2.id = kw.id_anak_asuh
                    )
                    END
                    SEPARATOR ', '
                ) as relasi"
            ]))
            ->groupBy('g.nama_grup', 'wa.id', 'aa.id')
            ->get();

            $data['Status_Santri']['Kewaliasuhan'] = $kew->isNotEmpty()
            ? $kew->map(fn($k) => [
                'group'   => $k->nama_grup,
                'sebagai' => $k->role,
                $k->role === 'Anak Asuh' ? 'Nama Wali Asuh' : 'Nama Anak Asuh' => $k->relasi ?? '-',
            ])
            : [];

            // --- Perizinan untuk Pegawai (via Santri -> Biodata) ---
            $izin = DB::table('perizinan as pp')
                    ->leftJoin('santri as s', 'pp.santri_id', '=', 's.id')
                    ->leftJoin('biodata as b', 's.biodata_id', '=', 'b.id')
                    ->leftJoin('pegawai as p', 'b.id', '=', 'p.biodata_id')
                    ->where('b.id', $bioId) // Cari berdasarkan pegawai ID
                    ->select([
                        DB::raw("CONCAT(pp.tanggal_mulai,' s/d ',pp.tanggal_akhir) as tanggal"),
                        'pp.keterangan',
                        DB::raw("CASE WHEN TIMESTAMPDIFF(SECOND,pp.tanggal_mulai,pp.tanggal_akhir) >= 86400
                                    THEN CONCAT(FLOOR(TIMESTAMPDIFF(SECOND,pp.tanggal_mulai,pp.tanggal_akhir) / 86400), ' Hari | Bermalam')
                                    ELSE CONCAT(FLOOR(TIMESTAMPDIFF(SECOND,pp.tanggal_mulai,pp.tanggal_akhir) / 3600), ' Jam')
                            END as lama_waktu"),
                        'pp.status as status_kembali',
                    ])
                    
                    ->get();
    
                    $data['Status_Santri']['Info_Perizinan'] = $izin->isNotEmpty()
                    ? $izin->map(fn($z) => [
                        'tanggal'        => $z->tanggal,
                        'keterangan'     => $z->keterangan,
                        'lama_waktu'     => $z->lama_waktu,
                        'status' => $z->status_kembali,
                    ])
                    : [];

        // -- Domisili detail -- 
            $dom = DB::table('riwayat_domisili as rd')
                ->join('santri as s', 's.id', '=', 'rd.santri_id')
                ->where('biodata_id', $bioId) // bioId ini dari base yang kamu ambil di awal
                // ->where('rd.santri_id', $santri->id) 
                ->join('wilayah as w', 'rd.wilayah_id', '=', 'w.id')
                ->join('blok as bl', 'rd.blok_id', '=', 'bl.id')
                ->join('kamar as km', 'rd.kamar_id', '=', 'km.id')
                ->select([
                    'km.nama_kamar',
                    'bl.nama_blok',
                    'w.nama_wilayah',
                    'rd.tanggal_masuk',
                    'rd.tanggal_keluar'
                ])
                ->get();

                $data['Domisili'] = $dom->isNotEmpty()
                ? $dom->map(fn($d) => [
                    'kamar'             => $d->nama_kamar,
                    'blok'              => $d->nama_blok,
                    'wilayah'           => $d->nama_wilayah,
                    'tanggal_ditempati' => $d->tanggal_masuk,
                    'tanggal_pindah'    => $d->tanggal_keluar ?? '-',
                ])
                : [];



        // --- 8. Pendidikan ---
        $pend = DB::table('riwayat_pendidikan as rp')
            // Relasi dengan santri, karena riwayat pendidikan berhubungan dengan santri
            ->join('santri', 'santri.id', '=', 'rp.santri_id')
            // Relasi santri dengan biodata
            ->join('biodata', 'biodata.id', '=', 'santri.biodata_id')
            // Relasi biodata dengan pegawai
            ->join('pegawai', 'biodata.id', '=', 'pegawai.biodata_id')
            // Filter berdasarkan id pegawai
            ->where('biodata.id', $bioId)
            ->join('lembaga as l', 'rp.lembaga_id', '=', 'l.id')
            ->leftJoin('jurusan as j', 'rp.jurusan_id', '=', 'j.id')
            ->leftJoin('kelas as k', 'rp.kelas_id', '=', 'k.id')
            ->leftJoin('rombel as r', 'rp.rombel_id', '=', 'r.id')
            ->select([
                'rp.no_induk',
                'l.nama_lembaga',
                'j.nama_jurusan',
                'k.nama_kelas',
                'r.nama_rombel',
                'rp.tanggal_masuk',
                'rp.tanggal_keluar'
            ])
            ->get();

        $data['Pendidikan'] = $pend->isNotEmpty()
            ? $pend->map(fn($p) => [
                'no_induk'     => $p->no_induk,
                'nama_lembaga' => $p->nama_lembaga,
                'nama_jurusan' => $p->nama_jurusan,
                'nama_kelas'   => $p->nama_kelas ?? '-',
                'nama_rombel'  => $p->nama_rombel ?? '-',
                'tahun_masuk'  => $p->tanggal_masuk,
                'tahun_lulus'  => $p->tanggal_keluar ?? '-',
            ])
            : [];

        // --- Riwayat Karyawan ---
        $karyawan = DB::table('pegawai')
            ->join('biodata', 'pegawai.biodata_id', '=', 'biodata.id')
            ->leftJoin('karyawan', 'karyawan.pegawai_id', '=', 'pegawai.id')
            // ->leftJoin('riwayat_jabatan_karyawan', 'riwayat_jabatan_karyawan.karyawan_id', '=', 'karyawan.id')
            ->where('biodata.id', $bioId)
            ->select(
                'karyawan.keterangan_jabatan',
                DB::raw("
                    CONCAT(
                        'Sejak ', DATE_FORMAT(karyawan.tanggal_mulai, '%e %b %Y'),
                        ' Sampai ',
                        IFNULL(DATE_FORMAT(karyawan.tanggal_selesai, '%e %b %Y'), 'Sekarang')
                    ) AS masa_jabatan
                ")
            )
            ->orderBy('karyawan.tanggal_mulai', 'asc')
            ->get();

            $data['Karyawan'] = $karyawan
            ->filter(fn($item) => $item->keterangan_jabatan !== null && $item->masa_jabatan !== null)
            ->map(fn($item) => [
                'keterangan_jabatan' => $item->keterangan_jabatan,
                'masa_jabatan'       => $item->masa_jabatan,
            ])
            ->values(); // supaya hasilnya berupa array indeks biasa (bukan collection key-preserved)
        

        // --- Ambil data pengajar dan riwayat materi ---
        $pengajar = DB::table('pengajar')
            ->join('pegawai', 'pegawai.id', '=', 'pengajar.pegawai_id')  // Join dengan pegawai
            ->leftJoin('lembaga', 'lembaga.id', '=', 'pengajar.lembaga_id')  // Join dengan lembaga
            ->join('biodata', 'pegawai.biodata_id', '=', 'biodata.id')  // Join dengan biodata
            ->leftJoin('golongan', 'golongan.id', '=', 'pengajar.golongan_id')  // Join dengan golongan
            ->leftJoin('kategori_golongan', 'kategori_golongan.id', '=', 'golongan.kategori_golongan_id')  // Join dengan kategori golongan
            ->leftJoin('materi_ajar', 'materi_ajar.pengajar_id', '=', 'pengajar.id')  // Join dengan materi ajar
            ->where('biodata.id', $bioId)  // Filter berdasarkan ID pegawai
            ->select(
                'lembaga.nama_lembaga',
                'pengajar.jabatan as PekerjaanKontrak',
                'kategori_golongan.nama_kategori_golongan',
                'golongan.nama_golongan',
                DB::raw("
                    CONCAT(
                        'Sejak ', DATE_FORMAT(pengajar.tahun_masuk, '%e %M %Y %H:%i:%s'),
                        ' sampai ',
                        IFNULL(DATE_FORMAT(pengajar.tahun_akhir, '%e %M %Y %H:%i:%s'), 'saat ini')
                    ) AS keterangan
                "),
                DB::raw("
                    CONCAT(
                        FLOOR(SUM(materi_ajar.jumlah_menit) / 60), ' jam ',
                        MOD(SUM(materi_ajar.jumlah_menit), 60), ' menit'
                    ) AS total_waktu_materi
                "),
                DB::raw('COUNT(DISTINCT materi_ajar.id) as total_materi')
            )
            ->groupBy(
                'lembaga.nama_lembaga',
                'pengajar.jabatan',
                'kategori_golongan.nama_kategori_golongan',
                'golongan.nama_golongan',
                'pengajar.tahun_masuk',
                'pengajar.tahun_akhir'
            )
            ->get();

            $data['Pengajar'] = $pengajar->isNotEmpty()
            ? $pengajar->map(fn($item) => [
                'lembaga'                 => $item->nama_lembaga ?? '-',
                'pekerjaan_kontrak'      => $item->PekerjaanKontrak ?? '-',
                'kategori_golongan'      => $item->nama_kategori_golongan ?? '-',
                'nama_golongan'          => $item->nama_golongan ?? '-',
                'periode_ajar'           => $item->keterangan ?? '-',
                'total_jam_materi'       => $item->total_waktu_materi ?? '0 jam 0 menit',
                'jumlah_materi_diajarkan'=> $item->total_materi ?? 0,
            ])
            : [];
    
    

        // --- Ambil data pengurus dan riwayat jabatan ---
        $pengurus = DB::table('pengurus')
            ->join('pegawai', 'pegawai.id', '=', 'pengurus.pegawai_id')
            ->join('biodata', 'pegawai.biodata_id', '=', 'biodata.id')
            ->where('biodata.id', $bioId)
            ->select(
                'pengurus.keterangan_jabatan',
                DB::raw("
                    CONCAT(
                        'Sejak ', DATE_FORMAT(pengurus.tanggal_mulai, '%e %b %Y'),
                        ' Sampai ',
                        IFNULL(DATE_FORMAT(pengurus.tanggal_akhir, '%e %b %Y'), 'Sekarang')
                    ) AS masa_jabatan
                ")
            )
            ->orderBy('pengurus.tanggal_mulai', 'asc')
            ->get();
            $data['Pengurus'] = $pengurus->isNotEmpty()
            ? $pengurus->map(fn($item) => [
                'keterangan_jabatan' => $item->keterangan_jabatan ?? '-',
                'masa_jabatan'       => $item->masa_jabatan ?? '-',
            ])
            : [];






        // --- 9. Catatan Afektif ---
        $af = DB::table('catatan_afektif as ca')
            ->join('santri', 'santri.id', '=', 'ca.id_santri')
            ->join('biodata', 'biodata.id', '=', 'santri.biodata_id')
            ->join('pegawai as p', 'biodata.id', '=', 'p.biodata_id')
            ->where('biodata.id', $bioId)
            ->latest('ca.created_at')
            ->first();

            $data['Catatan_Progress']['Afektif'] = $af
            ? [
                'kebersihan'               => $af->kebersihan_nilai ?? '-',
                'tindak_lanjut_kebersihan' => $af->kebersihan_tindak_lanjut ?? '-',
                'kepedulian'               => $af->kepedulian_nilai ?? '-',
                'tindak_lanjut_kepedulian' => $af->kepedulian_tindak_lanjut ?? '-',
                'akhlak'                   => $af->akhlak_nilai ?? '-',
                'tindak_lanjut_akhlak'     => $af->akhlak_tindak_lanjut ?? '-',
            ]
            : [];

        // --- 10. Catatan Kognitif ---
        $kg = DB::table('catatan_kognitif as ck')
            ->join('santri', 'santri.id', '=', 'ck.id_santri')
            ->join('biodata', 'biodata.id', '=', 'santri.biodata_id')
            ->join('pegawai as p', 'biodata.id', '=', 'p.biodata_id')
            ->where('biodata.id', $bioId)
            ->latest('ck.created_at')
            ->first();

            $data['Catatan_Progress']['Kognitif'] = $kg
            ? [
                'kebahasaan'                      => $kg->kebahasaan_nilai ?? '-',
                'tindak_lanjut_kebahasaan'        => $kg->kebahasaan_tindak_lanjut ?? '-',
                'baca_kitab_kuning'               => $kg->baca_kitab_kuning_nilai ?? '-',
                'tindak_lanjut_baca_kitab_kuning' => $kg->baca_kitab_kuning_tindak_lanjut ?? '-',
                'hafalan_tahfidz'                 => $kg->hafalan_tahfidz_nilai ?? '-',
                'tindak_lanjut_hafalan_tahfidz'   => $kg->hafalan_tahfidz_tindak_lanjut ?? '-',
                'furudul_ainiyah'                 => $kg->furudul_ainiyah_nilai ?? '-',
                'tindak_lanjut_furudul_ainiyah'   => $kg->furudul_ainiyah_tindak_lanjut ?? '-',
                'tulis_alquran'                   => $kg->tulis_alquran_nilai ?? '-',
                'tindak_lanjut_tulis_alquran'     => $kg->tindak_lanjut_tulis_alquran ?? '-',
                'baca_alquran'                    => $kg->baca_alquran_nilai ?? '-',
                'tindak_lanjut_baca_alquran'      => $kg->baca_alquran_tindak_lanjut ?? '-',
            ]
            : [];

            // --- 10. Kunjungan Mahrom ---
            $kun = DB::table('pengunjung_mahrom as pm')
                ->join('santri as s', 'pm.santri_id', '=', 's.id')
                ->join('biodata as b', 's.biodata_id', '=', 'b.id')
                ->join('pegawai as p', 'b.id', '=', 'p.biodata_id')
                ->where('b.id', $bioId)
                ->select(['pm.nama_pengunjung', 'pm.tanggal'])
                ->get();
    
                $data['Kunjungan_Mahrom'] = $kun->isNotEmpty()
                ? $kun->map(fn($k) => [
                    'nama'    => $k->nama_pengunjung,
                    'tanggal' => $k->tanggal,
                ])
                : [];
                // --- 11. Khadam ---
                $kh = DB::table('khadam as kh')
                ->where('kh.biodata_id', $bioId)
                ->select(['kh.keterangan', 'kh.tanggal_mulai', 'kh.tanggal_akhir'])
                ->first();
    
                $data['Khadam'] = $kh
                ? [
                    'keterangan'    => $kh->keterangan,
                    'tanggal_mulai' => $kh->tanggal_mulai,
                    'tanggal_akhir' => $kh->tanggal_akhir,
                ]
                : [];
            return $data;
  }
}