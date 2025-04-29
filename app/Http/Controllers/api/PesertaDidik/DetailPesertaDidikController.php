<?php

namespace App\Http\Controllers\api\PesertaDidik;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\Controller;

class DetailPesertaDidikController extends Controller
{
    
    public function getDetailPesertaDidik(string $idSantri): array
    {
        try {
            // --- 1. Ambil basic santri + biodata_id + no_kk sekaligus ---
            $base = DB::table('santri as s')
                ->join('biodata as b', 's.biodata_id', '=', 'b.id')
                ->leftJoin('keluarga as k', 'b.id', '=', 'k.id_biodata')
                ->where('s.id', $idSantri)
                ->select([
                    's.id as santri_id',
                    'b.id as biodata_id',
                    'k.no_kk',
                ])
                ->first();
    
            if (! $base) {
                return ['error' => 'Santri tidak ditemukan'];
            }
    
            $santriId  = $base->santri_id;
            $bioId     = $base->biodata_id;
            $noKk      = $base->no_kk;
    
            // --- 2. Biodata detail ---
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
                'foto_profil'        => URL::to($biodata->foto),
            ];
    
            // --- 3. Data Keluarga (Orang tua/wali & saudara) ---
            // Orang tua / wali
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
    
            // Saudara kandung
            $excluded = DB::table('orang_tua_wali')->pluck('id_biodata')->toArray();
            $saudara = DB::table('keluarga as k')
                ->where('k.no_kk', $noKk)
                ->whereNotIn('k.id_biodata', $excluded)
                ->where('k.id_biodata', '!=', $bioId)
                ->join('biodata as bs', 'k.id_biodata', '=', 'bs.id')
                ->select([
                    'bs.nama',
                    'bs.nik',
                    DB::raw("'Saudara Kandung' as status"),
                    DB::raw("NULL as wali")
                ])
                ->get();
    
            $keluarga = $ortu->merge($saudara);
            if ($keluarga->isNotEmpty()) {
                $data['Keluarga'] = $keluarga->map(fn($i) => [
                    'nama'   => $i->nama,
                    'nik'    => $i->nik,
                    'status' => $i->status,
                    'wali'   => $i->wali,
                ]);
            }
    
            // --- 4. Informasi Santri ---
            $santriInfo = DB::table('santri as s')
                ->join('biodata as b', 's.biodata_id', '=', 'b.id')
                ->where('s.biodata_id', $bioId)
                ->select('nis', 'tanggal_masuk', 'tanggal_keluar')
                ->get();
    
            if ($santriInfo) {
                if ($keluarga->isNotEmpty()) {
                    $data['Status_Santri']['Santri'] = $santriInfo->map(fn($s) => [
                        'NIS'           => $s->nis,
                        'Tanggal_Mulai' => $s->tanggal_masuk,
                        'Tanggal_Akhir' => $s->tanggal_keluar ?? '-',
                    ]);
                }
            }
    
            // --- 5. Kewaliasuhan ---
            $kew = DB::table('santri as s')
                ->where('s.id', $santriId)
                ->leftJoin('wali_asuh as wa', 's.id', '=', 'wa.id_santri')
                ->leftJoin('anak_asuh as aa', 's.id', '=', 'aa.id_santri')
                ->leftJoin('kewaliasuhan as kw', function ($j) {
                    $j->on('kw.id_wali_asuh', 'wa.id')
                      ->orOn('kw.id_anak_asuh', 'aa.id');
                })
                ->leftJoin('grup_wali_asuh as g', 'g.id', '=', 'wa.id_grup_wali_asuh')
                ->selectRaw(implode(', ', [
                    'g.nama_grup',
                    "CASE WHEN wa.id IS NOT NULL THEN 'Wali Asuh' ELSE 'Anak Asuh' END as role",
                    "GROUP_CONCAT(
                        CASE
                          WHEN wa.id IS NOT NULL THEN (
                            select bio2.nama from biodata bio2
                            join santri s3 on bio2.id = s3.biodata_id
                            join wali_asuh wa3 on wa3.id_santri = s3.id
                            where wa3.id = kw.id_wali_asuh
                          )
                          ELSE (
                            select bio.nama from biodata bio
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
    
            if ($kew->isNotEmpty()) {
                $data['Status_Santri']['Kewaliasuhan'] = $kew->map(fn($k) => [
                    'group'        => $k->nama_grup,
                    'sebagai'      => $k->role,
                    $k->role === 'Anak Asuh'
                        ? 'Nama Wali Asuh'
                        : 'Nama Anak Asuh'
                    => $k->relasi ?? '-',
                ]);
            }
    
            // --- 6. Perizinan ---
            $izin = DB::table('perizinan')
                ->where('santri_id', $santriId)
                ->select([
                    DB::raw("CONCAT(tanggal_mulai,' s/d ',tanggal_akhir) as tanggal"),
                    'keterangan',
                    DB::raw("CASE WHEN TIMESTAMPDIFF(SECOND,tanggal_mulai,tanggal_akhir)>=86400
                                  THEN CONCAT(FLOOR(TIMESTAMPDIFF(SECOND,tanggal_mulai,tanggal_akhir)/86400),' Hari | Bermalam')
                                  ELSE CONCAT(FLOOR(TIMESTAMPDIFF(SECOND,tanggal_mulai,tanggal_akhir)/3600),' Jam')
                           END as lama_waktu"),
                    'status_kembali'
                ])
                ->get();
    
            if ($izin->isNotEmpty()) {
                $data['Status_Santri']['Info_Perizinan'] = $izin->map(fn($z) => [
                    'tanggal'        => $z->tanggal,
                    'keterangan'     => $z->keterangan,
                    'lama_waktu'     => $z->lama_waktu,
                    'status_kembali' => $z->status_kembali,
                ]);
            }
    
            // --- 7. Domisili ---
            $dom = DB::table('riwayat_domisili as rd')
                ->where('rd.santri_id', $santriId)
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
    
            if ($dom->isNotEmpty()) {
                $data['Domisili'] = $dom->map(fn($d) => [
                    'kamar'            => $d->nama_kamar,
                    'blok'             => $d->nama_blok,
                    'wilayah'          => $d->nama_wilayah,
                    'tanggal_ditempati'=> $d->tanggal_masuk,
                    'tanggal_pindah'   => $d->tanggal_keluar ?? '-',
                ]);
            }
    
            // --- 8. Pendidikan ---
            $pend = DB::table('riwayat_pendidikan as rp')
                ->where('rp.santri_id', $santriId)
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
    
            if ($pend->isNotEmpty()) {
                $data['Pendidikan'] = $pend->map(fn($p) => [
                    'no_induk'     => $p->no_induk,
                    'nama_lembaga' => $p->nama_lembaga,
                    'nama_jurusan' => $p->nama_jurusan,
                    'nama_kelas'   => $p->nama_kelas ?? '-',
                    'nama_rombel'  => $p->nama_rombel ?? '-',
                    'tahun_masuk'  => $p->tanggal_masuk,
                    'tahun_lulus'  => $p->tanggal_keluar ?? '-',
                ]);
            }
    
            // --- 9. Catatan Afektif & Kognitif ---
            $af = DB::table('catatan_afektif as ca')
                ->where('ca.id_santri', $santriId)
                ->latest('ca.created_at')
                ->first();
    
            if ($af) {
                $data['Catatan_Progress']['Afektif'] = [
                    'kebersihan'               => $af->kebersihan_nilai ?? '-',
                    'tindak_lanjut_kebersihan' => $af->kebersihan_tindak_lanjut ?? '-',
                    'kepedulian'               => $af->kepedulian_nilai ?? '-',
                    'tindak_lanjut_kepedulian' => $af->kepedulian_tindak_lanjut ?? '-',
                    'akhlak'                   => $af->akhlak_nilai ?? '-',
                    'tindak_lanjut_akhlak'     => $af->akhlak_tindak_lanjut ?? '-',
                ];
            }
    
            $kg = DB::table('catatan_kognitif as ck')
                ->where('ck.id_santri', $santriId)
                ->latest('ck.created_at')
                ->first();
    
            if ($kg) {
                $data['Catatan_Progress']['Kognitif'] = [
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
                ];
            }
    
            // --- 10. Kunjungan Mahrom ---
            $kun = DB::table('pengunjung_mahrom as pm')
                ->where('pm.santri_id', $santriId)
                ->select(['pm.nama_pengunjung', 'pm.tanggal'])
                ->get();
    
            if ($kun->isNotEmpty()) {
                $data['Kunjungan_Mahrom'] = $kun->map(fn($k) => [
                    'nama'    => $k->nama_pengunjung,
                    'tanggal' => $k->tanggal,
                ]);
            }
    
            // --- 11. Khadam ---
            $kh = DB::table('khadam as kh')
                ->where('kh.biodata_id', $bioId)
                ->select(['kh.keterangan', 'kh.tanggal_mulai', 'kh.tanggal_akhir'])
                ->first();
    
            if ($kh) {
                $data['Khadam'] = [
                    'keterangan'    => $kh->keterangan,
                    'tanggal_mulai' => $kh->tanggal_mulai,
                    'tanggal_akhir' => $kh->tanggal_akhir,
                ];
            }
    
            return $data;
        } catch (\Exception $e) {
            Log::error("Error DetailPesertaDidikSantri: " . $e->getMessage());
            return ['error' => 'Terjadi kesalahan pada server'];
        }
    }
}
// {
//     /**
//      * Fungsi untuk mengambil detail peserta didik secara menyeluruh.
//      */
//     private function formDetailPesertaDidik($idPesertaDidik)
//     {
//         try {
//             // Query Biodata beserta data terkait
//             $biodata = DB::table('peserta_didik as pd')
//                 ->join('biodata as b', 'pd.id_biodata', '=', 'b.id')
//                 ->leftJoin('warga_pesantren as wp', function ($join) {
//                     $join->on('b.id', '=', 'wp.id_biodata')
//                         ->where('wp.status', true)
//                         ->whereRaw('wp.id = (
//                             select max(wp2.id) 
//                             from warga_pesantren as wp2 
//                             where wp2.id_biodata = b.id 
//                               and wp2.status = true
//                          )');
//                 })
//                 ->leftJoin('berkas as br', function ($join) {
//                     $join->on('b.id', '=', 'br.id_biodata')
//                         ->where('br.id_jenis_berkas', '=', function ($query) {
//                             $query->select('id')
//                                 ->from('jenis_berkas')
//                                 ->where('nama_jenis_berkas', 'Pas foto')
//                                 ->limit(1);
//                         })
//                         ->whereRaw('br.id = (select max(b2.id) from berkas as b2 where b2.id_biodata = b.id and b2.id_jenis_berkas = br.id_jenis_berkas)');
//                 })
//                 ->leftJoin('keluarga as k', 'b.id', '=', 'k.id_biodata')
//                 ->leftJoin('kecamatan as kc', 'b.id_kecamatan', '=', 'kc.id')
//                 ->leftJoin('kabupaten as kb', 'b.id_kabupaten', '=', 'kb.id')
//                 ->leftJoin('provinsi as pv', 'b.id_provinsi', '=', 'pv.id')
//                 ->leftJoin('negara as ng', 'b.id_negara', '=', 'ng.id')
//                 ->where('pd.id', $idPesertaDidik)
//                 ->select(
//                     'k.no_kk',
//                     DB::raw("COALESCE(b.nik, b.no_passport) as identitas"),
//                     'wp.niup',
//                     'b.nama',
//                     'b.jenis_kelamin',
//                     DB::raw("CONCAT(b.tempat_lahir, ', ', DATE_FORMAT(b.tanggal_lahir, '%e %M %Y')) as tempat_tanggal_lahir"),
//                     DB::raw("CONCAT(b.anak_keberapa, ' dari ', b.dari_saudara, ' Bersaudara') as anak_dari"),
//                     DB::raw("CONCAT(TIMESTAMPDIFF(YEAR, b.tanggal_lahir, CURDATE()), ' tahun') as umur"),
//                     'kc.nama_kecamatan',
//                     'kb.nama_kabupaten',
//                     'pv.nama_provinsi',
//                     'ng.nama_negara',
//                     DB::raw("COALESCE(MAX(br.file_path), 'default.jpg') as foto_profil")
//                 )
//                 ->groupBy(
//                     'k.no_kk',
//                     'b.nik',
//                     'b.no_passport',
//                     'wp.niup',
//                     'b.nama',
//                     'b.jenis_kelamin',
//                     'b.tempat_lahir',
//                     'b.tanggal_lahir',
//                     'b.anak_keberapa',
//                     'b.dari_saudara',
//                     'kc.nama_kecamatan',
//                     'kb.nama_kabupaten',
//                     'pv.nama_provinsi',
//                     'ng.nama_negara'
//                 )
//                 ->first();

//             if (!$biodata) {
//                 return ['error' => 'Data tidak ditemukan'];
//             }

//             // Format data Biodata
//             $data = [];
//             $data['Biodata'] = [
//                 "nokk"                 => $biodata->no_kk ?? '-',
//                 "nik_nopassport"       => $biodata->identitas,
//                 "niup"                 => $biodata->niup ?? '-',
//                 "nama"                 => $biodata->nama,
//                 "jenis_kelamin"        => $biodata->jenis_kelamin,
//                 "tempat_tanggal_lahir" => $biodata->tempat_tanggal_lahir,
//                 "anak_ke"              => $biodata->anak_dari,
//                 "umur"                 => $biodata->umur,
//                 "kecamatan"            => $biodata->nama_kecamatan ?? '-',
//                 "kabupaten"            => $biodata->nama_kabupaten ?? '-',
//                 "provinsi"             => $biodata->nama_provinsi ?? '-',
//                 "warganegara"          => $biodata->nama_negara ?? '-',
//                 "foto_profil"          => URL::to($biodata->foto_profil)
//             ];

//             // Query Data Keluarga: Mengambil data keluarga, orang tua/wali beserta hubungannya.
//             $keluarga = DB::table('peserta_didik as pd')
//                 ->join('biodata as b_anak', 'pd.id_biodata', '=', 'b_anak.id')
//                 ->join('keluarga as k_anak', 'b_anak.id', '=', 'k_anak.id_biodata')
//                 ->leftJoin('keluarga as k_ortu', 'k_anak.no_kk', '=', 'k_ortu.no_kk')
//                 ->join('orang_tua_wali', 'k_ortu.id_biodata', '=', 'orang_tua_wali.id_biodata')
//                 ->join('biodata as b_ortu', 'orang_tua_wali.id_biodata', '=', 'b_ortu.id')
//                 ->join('hubungan_keluarga', 'orang_tua_wali.id_hubungan_keluarga', '=', 'hubungan_keluarga.id')
//                 ->where('pd.id', $idPesertaDidik)
//                 ->select(
//                     'b_ortu.nama',
//                     'b_ortu.nik',
//                     DB::raw("'Orang Tua' as hubungan"),
//                     'hubungan_keluarga.nama_status',
//                     'orang_tua_wali.wali'
//                 )
//                 ->get();

//             // Ambil nomor KK dan id biodata peserta didik dari tabel keluarga
//             $noKk = DB::table('peserta_didik as pd')
//                 ->join('biodata as b_anak', 'pd.id_biodata', '=', 'b_anak.id')
//                 ->join('keluarga as k_anak', 'b_anak.id', '=', 'k_anak.id_biodata')
//                 ->where('pd.id', $idPesertaDidik)
//                 ->value('k_anak.no_kk');

//             $currentBiodataId = DB::table('peserta_didik as pd')
//                 ->join('biodata as b_anak', 'pd.id_biodata', '=', 'b_anak.id')
//                 ->where('pd.id', $idPesertaDidik)
//                 ->value('b_anak.id');

//             // Kumpulan id biodata dari orang tua/wali yang harus dikecualikan
//             $excludedIds = DB::table('orang_tua_wali')
//                 ->pluck('id_biodata')
//                 ->toArray();

//             // Ambil data saudara kandung (anggota keluarga lain dalam KK yang sama, dari semua tabel terkait)
//             $saudara = DB::table('keluarga as k_saudara')
//                 ->join('biodata as b_saudara', 'k_saudara.id_biodata', '=', 'b_saudara.id')
//                 ->where('k_saudara.no_kk', $noKk)
//                 ->whereNotIn('k_saudara.id_biodata', $excludedIds)
//                 ->where('k_saudara.id_biodata', '!=', $currentBiodataId)
//                 ->select(
//                     'b_saudara.nama',
//                     'b_saudara.nik',
//                     DB::raw("'Saudara Kandung' as hubungan"),
//                     DB::raw("NULL as nama_status"),
//                     DB::raw("NULL as wali")
//                 )
//                 ->get();

//             // Jika terdapat data saudara, gabungkan dengan data keluarga
//             if ($saudara->isNotEmpty()) {
//                 $keluarga = $keluarga->merge($saudara);
//             }

//             // Siapkan output data
//             if ($keluarga->isNotEmpty()) {
//                 $data['Keluarga'] = $keluarga->map(function ($item) {
//                     return [
//                         "nama"   => $item->nama,
//                         "nik"    => $item->nik,
//                         "status" => $item->nama_status ?? $item->hubungan,
//                         "wali"   => $item->wali,
//                     ];
//                 });
//             }

//             // Data Status Santri
//             $santri = DB::table('peserta_didik as pd')
//                 ->join('santri', 'santri.id_peserta_didik', '=', 'pd.id')
//                 ->where('pd.id', $idPesertaDidik)
//                 ->select(
//                     'santri.nis',
//                     'santri.tanggal_masuk',
//                     'santri.tanggal_keluar'
//                 )
//                 ->get();

//             if ($santri->isNotEmpty()) {
//                 $data['Status_Santri']['Santri'] = $santri->map(function ($item) {
//                     return [
//                         'Nis'           => $item->nis,
//                         'Tanggal_Mulai' => $item->tanggal_masuk,
//                         'Tanggal_Akhir' => $item->tanggal_keluar ?? "-",
//                     ];
//                 });
//             }

//             // Data Kewaliasuhan
//             $kewaliasuhan = DB::table('peserta_didik')
//                 ->join('santri', 'santri.id_peserta_didik', '=', 'peserta_didik.id')
//                 ->leftJoin('wali_asuh', 'santri.id', '=', 'wali_asuh.id_santri')
//                 ->leftJoin('anak_asuh', 'santri.id', '=', 'anak_asuh.id_santri')
//                 ->leftJoin('grup_wali_asuh', 'grup_wali_asuh.id', '=', 'wali_asuh.id_grup_wali_asuh')
//                 ->leftJoin('kewaliasuhan', function ($join) {
//                     $join->on('kewaliasuhan.id_wali_asuh', '=', 'wali_asuh.id')
//                         ->orOn('kewaliasuhan.id_anak_asuh', '=', 'anak_asuh.id');
//                 })
//                 ->leftJoin('anak_asuh as anak_asuh_data', 'kewaliasuhan.id_anak_asuh', '=', 'anak_asuh_data.id')
//                 ->leftJoin('santri as santri_anak', 'anak_asuh_data.id_santri', '=', 'santri_anak.id')
//                 ->leftJoin('peserta_didik as pd_anak', 'santri_anak.id_peserta_didik', '=', 'pd_anak.id')
//                 ->leftJoin('biodata as bio_anak', 'pd_anak.id_biodata', '=', 'bio_anak.id')
//                 ->leftJoin('wali_asuh as wali_asuh_data', 'kewaliasuhan.id_wali_asuh', '=', 'wali_asuh_data.id')
//                 ->leftJoin('santri as santri_wali', 'wali_asuh_data.id_santri', '=', 'santri_wali.id')
//                 ->leftJoin('peserta_didik as pd_wali', 'santri_wali.id_peserta_didik', '=', 'pd_wali.id')
//                 ->leftJoin('biodata as bio_wali', 'pd_wali.id_biodata', '=', 'bio_wali.id')
//                 ->where('peserta_didik.id', $idPesertaDidik)
//                 ->havingRaw('relasi_santri IS NOT NULL') // Filter untuk menghindari hasil NULL
//                 ->select(
//                     'grup_wali_asuh.nama_grup',
//                     DB::raw("CASE 
//                             WHEN wali_asuh.id IS NOT NULL THEN 'Wali Asuh'
//                             WHEN anak_asuh.id IS NOT NULL THEN 'Anak Asuh'
//                         END as status"),
//                     DB::raw("CASE 
//                             WHEN wali_asuh.id IS NOT NULL THEN GROUP_CONCAT(DISTINCT bio_anak.nama SEPARATOR ', ')
//                             WHEN anak_asuh.id IS NOT NULL THEN GROUP_CONCAT(DISTINCT bio_wali.nama SEPARATOR ', ')
//                         END as relasi_santri")
//                 )
//                 ->groupBy(
//                     'grup_wali_asuh.nama_grup',
//                     'wali_asuh.id',
//                     'anak_asuh.id'
//                 )
//                 ->get();

//             if ($kewaliasuhan->isNotEmpty()) {
//                 $data['Status_Santri']['Kewaliasuhan'] = $kewaliasuhan->map(function ($item) {
//                     return [
//                         'group'   => $item->nama_grup ?? '-',
//                         'Sebagai' => $item->status,
//                         $item->status === 'Anak Asuh' ? 'Nama Wali Asuh' : 'Nama Anak Asuh'
//                         => $item->relasi_santri ?? "-",
//                     ];
//                 });
//             }

//             // Data Perizinan
//             $perizinan = DB::table('perizinan as p')
//                 ->join('peserta_didik as pd', 'p.id_peserta_didik', '=', 'pd.id')
//                 ->where('pd.id', $idPesertaDidik)
//                 ->select(
//                     DB::raw("CONCAT(p.tanggal_mulai, ' s/d ', p.tanggal_akhir) as tanggal"),
//                     'p.keterangan',
//                     DB::raw("CASE 
//                             WHEN TIMESTAMPDIFF(SECOND, p.tanggal_mulai, p.tanggal_akhir) >= 86400 
//                             THEN CONCAT(FLOOR(TIMESTAMPDIFF(SECOND, p.tanggal_mulai, p.tanggal_akhir) / 86400), ' Hari | Bermalam')
//                             ELSE CONCAT(FLOOR(TIMESTAMPDIFF(SECOND, p.tanggal_mulai, p.tanggal_akhir) / 3600), ' Jam')
//                         END as lama_waktu"),
//                     'p.status_kembali'
//                 )
//                 ->get();

//             if ($perizinan->isNotEmpty()) {
//                 $data['Status_santri']['Info_Perizinan'] = $perizinan->map(function ($item) {
//                     return [
//                         'tanggal'        => $item->tanggal,
//                         'keterangan'     => $item->keterangan,
//                         'lama_waktu'     => $item->lama_waktu,
//                         'status_kembali' => $item->status_kembali,
//                     ];
//                 });
//             }

//             // Data riwayat domisili
//             $domisili = DB::table('peserta_didik as pd')
//                 ->join('riwayat_domisili as rd', 'rd.id_peserta_didik', '=', 'pd.id')
//                 ->join('wilayah as w', 'rd.id_wilayah', '=', 'w.id')
//                 ->join('blok as bl', 'rd.id_blok', '=', 'bl.id')
//                 ->join('kamar as km', 'rd.id_kamar', '=', 'km.id')
//                 ->where('pd.id', $idPesertaDidik)
//                 ->select(
//                     'km.nama_kamar',
//                     'bl.nama_blok',
//                     'w.nama_wilayah',
//                     'rd.tanggal_masuk',
//                     'rd.tanggal_keluar'
//                 )
//                 ->get();

//             if ($domisili->isNotEmpty()) {
//                 $data['Domisili'] = $domisili->map(function ($item) {
//                     return [
//                         'Kamar'             => $item->nama_kamar,
//                         'Blok'              => $item->nama_blok,
//                         'Wilayah'           => $item->nama_wilayah,
//                         'tanggal_ditempati' => $item->tanggal_masuk,
//                         'tanggal_pindah'    => $item->tanggal_keluar ?? "-",
//                     ];
//                 });
//             }

//             // Data Pendidikan (Pelajar)
//             $pelajar = DB::table('peserta_didik as pd')
//                 ->join('riwayat_pendidikan as rp', 'rp.id_peserta_didik', '=', 'pd.id')
//                 ->join('lembaga as l', 'rp.id_lembaga', '=', 'l.id')
//                 ->leftJoin('jurusan as j', 'rp.id_jurusan', '=', 'j.id')
//                 ->leftJoin('kelas as k', 'rp.id_kelas', '=', 'k.id')
//                 ->leftJoin('rombel as r', 'rp.id_rombel', '=', 'r.id')
//                 ->where('pd.id', $idPesertaDidik)
//                 ->select(
//                     'rp.no_induk',
//                     'l.nama_lembaga',
//                     'j.nama_jurusan',
//                     'k.nama_kelas',
//                     'r.nama_rombel',
//                     'rp.tanggal_masuk',
//                     'rp.tanggal_keluar'
//                 )
//                 ->get();

//             if ($pelajar->isNotEmpty()) {
//                 $data['Pendidikan'] = $pelajar->map(function ($item) {
//                     return [
//                         'no_induk'     => $item->no_induk,
//                         'nama_lembaga' => $item->nama_lembaga,
//                         'nama_jurusan' => $item->nama_jurusan,
//                         'nama_kelas'   => $item->nama_kelas ?? "-",
//                         'nama_rombel'  => $item->nama_rombel ?? "-",
//                         'tahun_masuk'  => $item->tanggal_masuk,
//                         'tahun_lulus'  => $item->tanggal_keluar ?? "-",
//                     ];
//                 });
//             }

//             // Catatan Afektif Peserta Didik
//             $afektif = DB::table('peserta_didik as pd')
//                 ->join('santri as s', 's.id_peserta_didik', '=', 'pd.id')
//                 ->join('catatan_afektif as ca', 's.id', '=', 'ca.id_santri')
//                 ->where('pd.id', $idPesertaDidik)
//                 ->select(
//                     'ca.kebersihan_nilai',
//                     'ca.kebersihan_tindak_lanjut',
//                     'ca.kepedulian_nilai',
//                     'ca.kepedulian_tindak_lanjut',
//                     'ca.akhlak_nilai',
//                     'ca.akhlak_tindak_lanjut'
//                 )
//                 ->latest('ca.created_at')
//                 ->first();

//             if ($afektif) {
//                 $data['Catatan_Progress']['Afektif'] = [
//                     'Keterangan' => [
//                         'kebersihan'               => $afektif->kebersihan_nilai ?? "-",
//                         'tindak_lanjut_kebersihan' => $afektif->kebersihan_tindak_lanjut ?? "-",
//                         'kepedulian'               => $afektif->kepedulian_nilai ?? "-",
//                         'tindak_lanjut_kepedulian' => $afektif->kepedulian_tindak_lanjut ?? "-",
//                         'akhlak'                   => $afektif->akhlak_nilai ?? "-",
//                         'tindak_lanjut_akhlak'     => $afektif->akhlak_tindak_lanjut ?? "-",
//                     ]
//                 ];
//             }

//             // Catatan Kognitif Peserta Didik
//             $kognitif = DB::table('peserta_didik as pd')
//                 ->join('santri as s', 's.id_peserta_didik', '=', 'pd.id')
//                 ->join('catatan_kognitif as ck', 's.id', '=', 'ck.id_santri')
//                 ->where('pd.id', $idPesertaDidik)
//                 ->select(
//                     'ck.kebahasaan_nilai',
//                     'ck.kebahasaan_tindak_lanjut',
//                     'ck.baca_kitab_kuning_nilai',
//                     'ck.baca_kitab_kuning_tindak_lanjut',
//                     'ck.hafalan_tahfidz_nilai',
//                     'ck.hafalan_tahfidz_tindak_lanjut',
//                     'ck.furudul_ainiyah_nilai',
//                     'ck.furudul_ainiyah_tindak_lanjut',
//                     'ck.tulis_alquran_nilai',
//                     'ck.tulis_alquran_tindak_lanjut',
//                     'ck.baca_alquran_nilai',
//                     'ck.baca_alquran_tindak_lanjut'
//                 )
//                 ->latest('ck.created_at')
//                 ->first();

//             if ($kognitif) {
//                 $data['Catatan_Progress']['Kognitif'] = [
//                     'Keterangan' => [
//                         'kebahasaan'                      => $kognitif->kebahasaan_nilai ?? "-",
//                         'tindak_lanjut_kebahasaan'        => $kognitif->kebahasaan_tindak_lanjut ?? "-",
//                         'baca_kitab_kuning'               => $kognitif->baca_kitab_kuning_nilai ?? "-",
//                         'tindak_lanjut_baca_kitab_kuning' => $kognitif->baca_kitab_kuning_tindak_lanjut ?? "-",
//                         'hafalan_tahfidz'                 => $kognitif->hafalan_tahfidz_nilai ?? "-",
//                         'tindak_lanjut_hafalan_tahfidz'   => $kognitif->hafalan_tahfidz_tindak_lanjut ?? "-",
//                         'furudul_ainiyah'                 => $kognitif->furudul_ainiyah_nilai ?? "-",
//                         'tindak_lanjut_furudul_ainiyah'   => $kognitif->furudul_ainiyah_tindak_lanjut ?? "-",
//                         'tulis_alquran'                   => $kognitif->tulis_alquran_nilai ?? "-",
//                         'tindak_lanjut_tulis_alquran'     => $kognitif->tulis_alquran_tindak_lanjut ?? "-",
//                         'baca_alquran'                    => $kognitif->baca_alquran_nilai ?? "-",
//                         'tindak_lanjut_baca_alquran'      => $kognitif->baca_alquran_tindak_lanjut ?? "-",
//                     ]
//                 ];
//             }

//             // Data Kunjungan Mahrom
//             $pengunjung = DB::table('pengunjung_mahrom as pm')
//                 ->join('santri', 'pm.id_santri', '=', 'santri.id')
//                 ->join('peserta_didik as pd', 'santri.id_peserta_didik', '=', 'pd.id')
//                 ->where('pd.id', $idPesertaDidik)
//                 ->select(
//                     'pm.nama_pengunjung',
//                     'pm.tanggal'
//                 )
//                 ->get();

//             if ($pengunjung->isNotEmpty()) {
//                 $data['Kunjungan_Mahrom']['Di_kunjungi_oleh'] = $pengunjung->map(function ($item) {
//                     return [
//                         'Nama'    => $item->nama_pengunjung,
//                         'Tanggal' => $item->tanggal,
//                     ];
//                 });
//             }

//             // khadam
//             $khadam = DB::table('khadam as kh')
//                 ->join('biodata as b', 'kh.id_biodata', '=', 'b.id')
//                 ->join('peserta_didik as pd', 'pd.id_biodata', '=', 'b.id')
//                 ->where('pd.id', $idPesertaDidik)
//                 ->select(
//                     'kh.keterangan',
//                     'tanggal_mulai',
//                     'tanggal_akhir',
//                 )
//                 ->first();

//             if ($khadam) {
//                 $data['Khadam'] = [
//                     'keterangan' => $khadam->keterangan,
//                     'tanggal_mulai' => $khadam->tanggal_mulai,
//                     'tanggal_akhir' => $khadam->tanggal_akhir,
//                 ];
//             }

//             return $data;
//         } catch (\Exception $e) {
//             Log::error("Error in formDetailPesertaDidik: " . $e->getMessage());
//             return ['error' => 'Terjadi kesalahan pada server'];
//         }
//     }

//     /**
//      * Method publik untuk mengembalikan detail peserta didik dalam response JSON.
//      */
//     public function getDetailPesertaDidik($id)
//     {
//         // Validasi bahwa ID adalah UUID
//         if (!Str::isUuid($id)) {
//             return response()->json(['error' => 'ID tidak valid'], 400);
//         }

//         try {
//             // Cari data peserta didik berdasarkan UUID
//             $pesertaDidik = PesertaDidik::find($id);
//             if (!$pesertaDidik) {
//                 return response()->json(['error' => 'Data tidak ditemukan'], 404);
//             }

//             // Ambil detail peserta didik dari fungsi helper
//             $data = $this->formDetailPesertaDidik($pesertaDidik->id);
//             if (empty($data)) {
//                 return response()->json(['error' => 'Data Kosong'], 200);
//             }

//             return response()->json($data, 200);
//         } catch (\Exception $e) {
//             Log::error("Error in getDetailPesertaDidik: " . $e->getMessage());
//             return response()->json(['error' => 'Terjadi kesalahan pada server'], 500);
//         }
//     }
// }
// {
//     /**
//      * Fungsi untuk mengambil detail peserta didik secara menyeluruh.
//      */
//     public function formDetailPesertaDidik(string $idPesertaDidik)
//     {
//         try {
//             // 1) Eager‐load semua relasi “latest” dan master lookup
//             $peserta = PesertaDidik::with([
//                 'biodata.kecamatan',
//                 'biodata.kabupaten',
//                 'biodata.provinsi',
//                 'biodata.negara',
//                 'biodata.wargaPesantrenAktif',
//                 'biodata.pasFoto',
//                 'biodata.keluarga.biodataDetail',    // untuk data nama/nik anggota
//                 'santri',
//                 'santri.catatanAfektifLatest',
//                 'santri.catatanKognitifLatest',
//                 'santri.riwayatDomisili.wilayah',
//                 'santri.riwayatDomisili.blok',
//                 'santri.riwayatDomisili.kamar',
//                 'santri.riwayatPendidikan.lembaga',
//                 'santri.riwayatPendidikan.jurusan',
//                 'santri.riwayatPendidikan.kelas',
//                 'santri.riwayatPendidikan.rombel',
//                 'santri.kunjunganMahrom',
//                 'santri.waliAsuh.grupWaliAsuh',       // jika Anda punya relasi GrupWaliAsuh
//                 'santri.anakAsuh',
//                 'santri.khadam',
//             ])->findOrFail($idPesertaDidik);

//             $b = $peserta->biodata;
//             $tanggal = Carbon::parse($b->tanggal_lahir);

//             $noKK = optional($b->keluarga->first())->no_kk ?? '-';
//             $data['Biodata'] = [
//                 'nokk'                 => $noKK,
//                 'nik_nopassport'       => $b->nik ?? $b->no_passport,
//                 'niup'                 => optional($b->wargaPesantrenAktif)->niup ?? '-',
//                 'nama'                 => $b->nama,
//                 'jenis_kelamin'        => $b->jenis_kelamin,
//                 'tempat_tanggal_lahir' => "{$b->tempat_lahir}, " . $tanggal->format('j F Y'),
//                 'anak_ke'              => "{$b->anak_keberapa} dari {$b->dari_saudara} Bersaudara",
//                 'umur'                 => $tanggal->age . ' tahun',
//                 'kecamatan'            => optional($b->kecamatan)->nama_kecamatan ?? '-',
//                 'kabupaten'            => optional($b->kabupaten)->nama_kabupaten  ?? '-',
//                 'provinsi'             => optional($b->provinsi)->nama_provinsi    ?? '-',
//                 'warganegara'          => optional($b->negara)->nama_negara        ?? '-',
//                 'foto_profil'          => url(optional($b->pasFoto)->file_path ?? 'default.jpg'),
//             ];


//             // 3) Siapkan Keluarga (Orang Tua + Saudara)
//             $parentIds = OrangTuaWali::pluck('id_biodata')->toArray();
//             $parents  = $b->keluarga->whereIn('id_biodata', $parentIds);
//             $siblings = $b->keluarga
//                 ->whereNotIn('id_biodata', $parentIds)
//                 ->where('id_biodata', '!=', $b->id);

//             $keluarga = $parents->map(fn($k) => [
//                 'nama'   => $k->biodataDetail->nama,
//                 'nik'    => $k->biodataDetail->nik,
//                 'status' => 'Orang Tua',
//                 'wali'   => $k->orphinPivot->wali,   // asumsikan pivot memberi kolom wali
//             ])->merge(
//                 $siblings->map(fn($s) => [
//                     'nama'   => $s->biodataDetail->nama,
//                     'nik'    => $s->biodataDetail->nik,
//                     'status' => 'Saudara Kandung',
//                     'wali'   => null,
//                 ])
//             )->values();

//             if ($keluarga->isNotEmpty()) {
//                 $data['Keluarga'] = $keluarga;
//             }

//             // 4) Status Santri & Catatan
//             if ($peserta->santri) {
//                 $s = $peserta->santri;
//                 $data['Status_Santri']['Santri'] = [[
//                     'Nis'           => $s->nis,
//                     'Tanggal_Mulai' => $s->tanggal_masuk,
//                     'Tanggal_Akhir' => $s->tanggal_keluar ?? '-',
//                 ]];

//                 if ($s->catatanAfektifLatest) {
//                     $c = $s->catatanAfektifLatest;
//                     $data['Catatan_Progress']['Afektif'] = [
//                         'Keterangan' => [
//                             'kebersihan'                   => $c->kebersihan_nilai,
//                             'tindak_lanjut_kebersihan'     => $c->kebersihan_tindak_lanjut,
//                             'kepedulian'                   => $c->kepedulian_nilai,
//                             'tindak_lanjut_kepedulian'     => $c->kepedulian_tindak_lanjut,
//                             'akhlak'                       => $c->akhlak_nilai,
//                             'tindak_lanjut_akhlak'         => $c->akhlak_tindak_lanjut,
//                         ],
//                     ];
//                 }

//                 if ($s->catatanKognitifLatest) {
//                     $c = $s->catatanKognitifLatest;
//                     $data['Catatan_Progress']['Kognitif'] = [
//                         'Keterangan' => [
//                             'kebahasaan'                    => $c->kebahasaan_nilai,
//                             'tindak_lanjut_kebahasaan'      => $c->kebahasaan_tindak_lanjut,
//                             'baca_kitab_kuning'             => $c->baca_kitab_kuning_nilai,
//                             'tindak_lanjut_baca_kitab_kuning' => $c->baca_kitab_kuning_tindak_lanjut,
//                             'hafalan_tahfidz'               => $c->hafalan_tahfidz_nilai,
//                             'tindak_lanjut_hafalan_tahfidz' => $c->hafalan_tahfidz_tindak_lanjut,
//                             'furudul_ainiyah'               => $c->furudul_ainiyah_nilai,
//                             'tindak_lanjut_furudul_ainiyah' => $c->furudul_ainiyah_tindak_lanjut,
//                             'tulis_alquran'                 => $c->tulis_alquran_nilai,
//                             'tindak_lanjut_tulis_alquran'   => $c->tulis_alquran_tindak_lanjut,
//                             'baca_alquran'                  => $c->baca_alquran_nilai,
//                             'tindak_lanjut_baca_alquran'    => $c->baca_alquran_tindak_lanjut,
//                         ],
//                     ];
//                 }

//                 // 5) Riwayat Domisili
//                 if ($s->riwayatDomisili->isNotEmpty()) {
//                     $data['Domisili'] = $s->riwayatDomisili->map(fn($d) => [
//                         'Kamar'             => $d->kamar->nama_kamar,
//                         'Blok'              => $d->blok->nama_blok,
//                         'Wilayah'           => $d->wilayah->nama_wilayah,
//                         'tanggal_ditempati' => $d->tanggal_masuk,
//                         'tanggal_pindah'    => $d->tanggal_keluar ?? '-',
//                     ]);
//                 }

//                 // 6) Riwayat Pendidikan
//                 if ($s->riwayatPendidikan->isNotEmpty()) {
//                     $data['Pendidikan'] = $s->riwayatPendidikan->map(fn($p) => [
//                         'no_induk'     => $p->no_induk,
//                         'nama_lembaga' => $p->lembaga->nama_lembaga,
//                         'nama_jurusan' => optional($p->jurusan)->nama_jurusan ?? '-',
//                         'nama_kelas'   => optional($p->kelas)->nama_kelas        ?? '-',
//                         'nama_rombel'  => optional($p->rombel)->nama_rombel      ?? '-',
//                         'tahun_masuk'  => $p->tanggal_masuk,
//                         'tahun_lulus'  => $p->tanggal_keluar ?? '-',
//                     ]);
//                 }

//                 // 7) Kunjungan Mahrom
//                 if ($s->kunjunganMahrom->isNotEmpty()) {
//                     $data['Kunjungan_Mahrom']['Di_kunjungi_oleh'] = $s->kunjunganMahrom->map(fn($k) => [
//                         'Nama'    => $k->nama_pengunjung,
//                         'Tanggal' => $k->tanggal,
//                     ]);
//                 }

//                 // 8) Kewaliasuhan (Wali Asuh / Anak Asuh)
//                 // — mirip pattern di atas, cukup eager‐load dan mapping koleksi

//                 // 9) Khadam
//                 if ($s->khadam) {
//                     $data['Khadam'] = [
//                         'keterangan'    => $s->khadam->keterangan,
//                         'tanggal_mulai' => $s->khadam->tanggal_mulai,
//                         'tanggal_akhir' => $s->khadam->tanggal_akhir,
//                     ];
//                 }
//             }

//             return $data;
//         } catch (ModelNotFoundException $e) {
//             return ['error' => 'Data tidak ditemukan'];
//         } catch (\Throwable $e) {
//             Log::error($e->getMessage());
//             return ['error' => 'Terjadi kesalahan pada server'];
//         }
//     }


//      /**
//      * Endpoint untuk mengambil detail peserta didik berdasarkan UUID
//      *
//      * @param  string  $id  UUID peserta didik
//      * @return JsonResponse
//      */
//     public function getDetailPesertaDidik(string $id): JsonResponse
//     {
//         // Validasi UUID
//         if (! Str::isUuid($id)) {
//             return response()->json(['error' => 'ID tidak valid'], 400);
//         }

//         try {
//             // Cari atau throw ModelNotFoundException
//             $pesertaDidik = PesertaDidik::findOrFail($id);

//             // Panggil helper formDetailPesertaDidik (mengembalikan array)
//             $data = $this->formDetailPesertaDidik($id);

//             if (empty($data)) {
//                 // Bisa juga jadi data memang tidak lengkap
//                 return response()->json(['error' => 'Data Kosong'], 200);
//             }

//             return response()->json($data, 200);
//         }
//         catch (ModelNotFoundException $e) {
//             // UUID valid tapi tidak ditemukan di DB
//             return response()->json(['error' => 'Data tidak ditemukan'], 404);
//         }
//         catch (\Throwable $e) {
//             // Kesalahan lain (misal DB down, bug, dll)
//             Log::error("Error in getDetailPesertaDidik: " . $e->getMessage());
//             return response()->json(['error' => 'Terjadi kesalahan pada server'], 500);
//         }
//     }
// }
