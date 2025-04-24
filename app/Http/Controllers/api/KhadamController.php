<?php

namespace App\Http\Controllers\Api;

use App\Models\Khadam;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\Controller;
use App\Services\FilterKhadamService;

class KhadamController extends Controller
{
    private FilterKhadamService $filterController;

    public function __construct(FilterKhadamService $filterController)
    {
        $this->filterController = $filterController;
    }

    public function getAllKhadam(Request $request)
    {
        try {
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

            $query = DB::table('khadam as kh')
                ->join('biodata as b', 'kh.biodata_id', '=', 'b.id')
                // join berkas pas foto terakhir
                ->leftJoinSub($fotoLast, 'fl', fn($j) => $j->on('b.id', '=', 'fl.biodata_id'))
                ->leftJoin('berkas AS br', 'br.id', '=', 'fl.last_id')
                // join warga pesantren terakhir (NIUP)
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

            // Terapkan filter dan pagination
            $query = $this->filterController->khadamFilters($query, $request);

            $perPage     = (int) $request->input('limit', 25);
            $currentPage = (int) $request->input('page', 1);
            $results     = $query->paginate($perPage, ['*'], 'page', $currentPage);
        } catch (\Throwable $e) {
            Log::error("[KhadamController] Error: {$e->getMessage()}");
            return response()->json([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan pada server',
            ], 500);
        }

        if ($results->isEmpty()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Data kosong',
                'data'    => [],
            ], 200);
        }

        $formatted = collect($results->items())->map(fn($item) => [
            "id_khadam" => $item->id,
            "niup" => $item->niup ?? '-',
            "nama" => $item->nama,
            "keterangan" => $item->keterangan,
            "tgl_update" => Carbon::parse($item->updated_at)->translatedFormat('d F Y H:i:s') ?? '-',
            "tgl_input" =>  Carbon::parse($item->created_at)->translatedFormat('d F Y H:i:s'),
            "foto_profil" => url($item->foto_profil)
        ]);

        return response()->json([
            'total_data'   => $results->total(),
            'current_page' => $results->currentPage(),
            'per_page'     => $results->perPage(),
            'total_pages'  => $results->lastPage(),
            'data'         => $formatted,
        ]);
    }

    public function getDetailKhadam(string $idKhadam): array
    {
        try {
            // --- 1. Ambil basic santri + biodata_id + no_kk sekaligus ---
            $base = DB::table('khadam as kh')
                ->join('biodata as b', 'kh.biodata_id', '=', 'b.id')
                ->leftJoin('keluarga as k', 'b.id', '=', 'k.id_biodata')
                ->where('kh.id', $idKhadam)
                ->select([
                    'kh.id as khadam_id',
                    'b.id as biodata_id',
                    'k.no_kk',
                ])
                ->first();
    
            if (! $base) {
                return ['error' => 'Khadam tidak ditemukan'];
            }
    
            $khadamId  = $base->khadam_id;
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
            $santriInfo = DB::table('santri')
                ->where('id', $khadamId)
                ->select('nis', 'tanggal_masuk', 'tanggal_keluar')
                ->first();
    
            if ($santriInfo) {
                $data['Status_Santri']['Santri'] = [[
                    'NIS'           => $santriInfo->nis,
                    'Tanggal_Mulai' => $santriInfo->tanggal_masuk,
                    'Tanggal_Akhir' => $santriInfo->tanggal_keluar ?? '-',
                ]];
            }
    
            // --- 5. Kewaliasuhan ---
            $kew = DB::table('santri as s')
                ->where('s.id', $khadamId)
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
                ->where('santri_id', $khadamId)
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
                ->where('rd.santri_id', $khadamId)
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
                ->where('rp.santri_id', $khadamId)
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
                ->where('ca.id_santri', $khadamId)
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
                ->where('ck.id_santri', $khadamId)
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
                ->where('pm.santri_id', $khadamId)
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
            Log::error("Error formDetailSantri: " . $e->getMessage());
            return ['error' => 'Terjadi kesalahan pada server'];
        }
    }
}
