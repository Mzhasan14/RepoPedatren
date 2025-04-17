<?php

namespace App\Http\Controllers\api\PesertaDidik;

use App\Models\Santri;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\Controller;
use App\Http\Controllers\api\FilterController;

class SantriController extends Controller
{
    protected $filterController;
    protected $filterUmum;

    public function __construct()
    {
        // Inisialisasi controller filter
        $this->filterController = new FilterPesertaDidikController();
        $this->filterUmum = new FilterController();
    }

    /**
     * Fungsi untuk mengambil Tampilan awal santri.
     */
    public function getAllSantri(Request $request)
    {
        try {
            // Bangun query dasar dengan join dan alias tabel
            $query = DB::table('peserta_didik as pd')
                ->join('biodata as b', 'pd.id_biodata', '=', 'b.id')
                ->join('santri as s', function ($join) {
                    $join->on('pd.id', '=', 's.id_peserta_didik')
                        ->where('s.status_santri', 'aktif');
                })
                ->join('domisili_santri as ds', function ($join) {
                    $join->on('s.id', '=', 'ds.id_santri')
                        ->where('ds.status', true);
                })
                ->join('wilayah as w', 'ds.id_wilayah', '=', 'w.id')
                ->leftjoin('blok as bl', 'ds.id_blok', '=', 'bl.id')
                ->leftjoin('kamar as km', 'ds.id_kamar', '=', 'km.id')
                ->leftJoin('warga_pesantren as wp', function ($join) {
                    $join->on('b.id', '=', 'wp.id_biodata')
                        ->where('wp.status', true)
                        ->whereRaw('wp.id = (
                            select max(wp2.id) 
                            from warga_pesantren as wp2 
                            where wp2.id_biodata = b.id 
                              and wp2.status = true
                         )');
                })
                ->leftJoin('kabupaten as kb', 'kb.id', '=', 'b.id_kabupaten')
                ->leftJoin('berkas as br', function ($join) {
                    $join->on('b.id', '=', 'br.id_biodata')
                        ->where('br.id_jenis_berkas', '=', function ($query) {
                            $query->select('id')
                                ->from('jenis_berkas')
                                ->where('nama_jenis_berkas', 'Pas foto')
                                ->limit(1);
                        })
                        ->whereRaw('br.id = (
                            select max(b2.id) 
                            from berkas as b2 
                            where b2.id_biodata = b.id 
                              and b2.id_jenis_berkas = br.id_jenis_berkas
                         )');
                })
                ->leftJoin('pelajar as p', function ($join) {
                    $join->on('p.id_peserta_didik', '=', 'pd.id')
                        ->where('p.status_pelajar', 'aktif');
                })
                ->leftJoin('pendidikan_pelajar as pp', function ($join) {
                    $join->on('pp.id_pelajar', '=', 'p.id')
                        ->where('pp.status', true);
                })
                ->leftJoin('lembaga as l', 'pp.id_lembaga', '=', 'l.id')
                ->leftJoin('jurusan as j', 'pp.id_jurusan', '=', 'j.id')
                ->leftJoin('kelas as k', 'pp.id_kelas', '=', 'k.id')
                ->leftJoin('rombel as r', 'pp.id_rombel', '=', 'r.id')
                ->select(
                    's.id',
                    's.nis',
                    'b.nama',
                    'wp.niup',
                    'km.nama_kamar',
                    'bl.nama_blok',
                    'l.nama_lembaga',
                    'w.nama_wilayah',
                    DB::raw("CONCAT('Kab. ', kb.nama_kabupaten) as kota_asal"),
                    's.created_at',
                    's.updated_at',
                    DB::raw("COALESCE(MAX(br.file_path), 'default.jpg') as foto_profil")
                )
                ->groupBy(
                    's.id',
                    's.nis',
                    'b.nama',
                    'wp.niup',
                    'km.nama_kamar',
                    'bl.nama_blok',
                    'l.nama_lembaga',
                    'w.nama_wilayah',
                    'kb.nama_kabupaten',
                    's.created_at',
                    's.updated_at',
                    'br.file_path'
                );

            // Terapkan filter umum (contoh: filter alamat dan jenis kelamin)
            $query = $this->filterUmum->applyCommonFilters($query, $request);

            // Terapkan filter-filter terpisah
            $query = $this->filterController->applyWilayahFilter($query, $request);
            $query = $this->filterController->applyLembagaPendidikanFilter($query, $request);
            $query = $this->filterController->applyStatusPesertaFilter($query, $request);
            $query = $this->filterController->applyStatusWargaPesantrenFilter($query, $request);
            $query = $this->filterController->applySorting($query, $request);
            $query = $this->filterController->applyKewaliasuhan($query, $request);

            // Pagination: batasi jumlah data per halaman (default 25)
            $perPage     = $request->input('limit', 25);
            $currentPage = $request->input('page', 1);
            $results     = $query->paginate($perPage, ['*'], 'page', $currentPage);
        } catch (\Exception $e) {
            Log::error("Error in getAllSantri: " . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan pada server'
            ], 500);
        }

        // Jika data kosong
        if ($results->isEmpty()) {
            return response()->json([
                'status'  => 'succes',
                'message' => 'Data Kosong',
                'data'    => []
            ], 200);
        }

        // Format data output agar mudah dipahami
        $formattedData = $results->map(function ($item) {
            return [
                "id_santri" => $item->id,
                "nis" => $item->nis,
                "nama" => $item->nama,
                "niup" => $item->niup ?? '-',
                "kamar" => $item->nama_kamar,
                "blok" => $item->nama_blok,
                "lembaga" => $item->nama_lembaga ?? '-',
                "wilayah" => $item->nama_wilayah,
                "tgl_update" => Carbon::parse($item->updated_at)->translatedFormat('d F Y H:i:s') ?? '-',
                "tgl_input" =>  Carbon::parse($item->created_at)->translatedFormat('d F Y H:i:s'),
                "foto_profil" => url($item->foto_profil)
            ];
        });

        // Kembalikan respon JSON dengan data yang sudah diformat
        return response()->json([
            "total_data"   => $results->total(),
            "current_page" => $results->currentPage(),
            "per_page"     => $results->perPage(),
            "total_pages"  => $results->lastPage(),
            "data"         => $formattedData
        ]);
    }

    /**
     * Fungsi untuk mengambil detail santri secara menyeluruh.
     */
    private function formDetailSantri($idSantri)
    {
        try {
            // Query Biodata beserta data terkait
            $biodata = DB::table('peserta_didik as pd')
                ->join('santri as s', 'pd.id', '=', 's.id_peserta_didik')
                ->join('biodata as b', 'pd.id_biodata', '=', 'b.id')
                ->leftJoin('warga_pesantren as wp', function ($join) {
                    $join->on('b.id', '=', 'wp.id_biodata')
                         ->where('wp.status', true)
                         ->whereRaw('wp.id = (
                            select max(wp2.id) 
                            from warga_pesantren as wp2 
                            where wp2.id_biodata = b.id 
                              and wp2.status = true
                         )');
                })
                ->leftJoin('berkas as br', function ($join) {
                    $join->on('b.id', '=', 'br.id_biodata')
                        ->where('br.id_jenis_berkas', '=', function ($query) {
                            $query->select('id')
                                ->from('jenis_berkas')
                                ->where('nama_jenis_berkas', 'Pas foto')
                                ->limit(1);
                        })
                        ->whereRaw('br.id = (select max(b2.id) from berkas as b2 where b2.id_biodata = b.id and b2.id_jenis_berkas = br.id_jenis_berkas)');
                })
                ->leftJoin('keluarga as k', 'b.id', '=', 'k.id_biodata')
                ->leftJoin('kecamatan as kc', 'b.id_kecamatan', '=', 'kc.id')
                ->leftJoin('kabupaten as kb', 'b.id_kabupaten', '=', 'kb.id')
                ->leftJoin('provinsi as pv', 'b.id_provinsi', '=', 'pv.id')
                ->leftJoin('negara as ng', 'b.id_negara', '=', 'ng.id')
                ->where('s.id', $idSantri)
                ->select(
                    'k.no_kk',
                    DB::raw("COALESCE(b.nik, b.no_passport) as identitas"),
                    'wp.niup',
                    'b.nama',
                    'b.jenis_kelamin',
                    DB::raw("CONCAT(b.tempat_lahir, ', ', DATE_FORMAT(b.tanggal_lahir, '%e %M %Y')) as tempat_tanggal_lahir"),
                    DB::raw("CONCAT(b.anak_keberapa, ' dari ', b.dari_saudara, ' Bersaudara') as anak_dari"),
                    DB::raw("CONCAT(TIMESTAMPDIFF(YEAR, b.tanggal_lahir, CURDATE()), ' tahun') as umur"),
                    'kc.nama_kecamatan',
                    'kb.nama_kabupaten',
                    'pv.nama_provinsi',
                    'ng.nama_negara',
                    DB::raw("COALESCE(MAX(br.file_path), 'default.jpg') as foto_profil")
                )
                ->groupBy(
                    'k.no_kk',
                    'b.nik',
                    'b.no_passport',
                    'wp.niup',
                    'b.nama',
                    'b.jenis_kelamin',
                    'b.tempat_lahir',
                    'b.tanggal_lahir',
                    'b.anak_keberapa',
                    'b.dari_saudara',
                    'kc.nama_kecamatan',
                    'kb.nama_kabupaten',
                    'pv.nama_provinsi',
                    'ng.nama_negara'
                )
                ->first();

            if (!$biodata) {
                return ['error' => 'Data tidak ditemukan'];
            }

            // Format data Biodata
            $data = [];
            $data['Biodata'] = [
                "nokk"                 => $biodata->no_kk ?? '-',
                "nik_nopassport"       => $biodata->identitas,
                "niup"                 => $biodata->niup ?? '-',
                "nama"                 => $biodata->nama,
                "jenis_kelamin"        => $biodata->jenis_kelamin,
                "tempat_tanggal_lahir" => $biodata->tempat_tanggal_lahir,
                "anak_ke"              => $biodata->anak_dari,
                "umur"                 => $biodata->umur,
                "kecamatan"            => $biodata->nama_kecamatan ?? '-',
                "kabupaten"            => $biodata->nama_kabupaten ?? '-',
                "provinsi"             => $biodata->nama_provinsi ?? '-',
                "warganegara"          => $biodata->nama_negara ?? '-',
                "foto_profil"          => URL::to($biodata->foto_profil)
            ];

            // Query Data Keluarga: Mengambil data keluarga, orang tua/wali beserta hubungannya.
            $keluarga = DB::table('santri as s')
                ->join('peserta_didik as pd', 's.id_peserta_didik', '=', 'pd.id')
                ->join('biodata as b_anak', 'pd.id_biodata', '=', 'b_anak.id')
                ->join('keluarga as k_anak', 'b_anak.id', '=', 'k_anak.id_biodata')
                ->leftJoin('keluarga as k_ortu', 'k_anak.no_kk', '=', 'k_ortu.no_kk')
                ->join('orang_tua_wali', 'k_ortu.id_biodata', '=', 'orang_tua_wali.id_biodata')
                ->join('biodata as b_ortu', 'orang_tua_wali.id_biodata', '=', 'b_ortu.id')
                ->join('hubungan_keluarga', 'orang_tua_wali.id_hubungan_keluarga', '=', 'hubungan_keluarga.id')
                ->where('s.id', $idSantri)
                ->select(
                    'b_ortu.nama',
                    'b_ortu.nik',
                    DB::raw("'Orang Tua' as hubungan"),
                    'hubungan_keluarga.nama_status',
                    'orang_tua_wali.wali'
                )
                ->get();

            // Ambil nomor KK dan id biodata pelajar dari tabel keluarga
            $noKk = DB::table('santri as s')
                ->join('peserta_didik as pd', 's.id_peserta_didik', '=', 'pd.id')
                ->join('biodata as b_anak', 'pd.id_biodata', '=', 'b_anak.id')
                ->join('keluarga as k_anak', 'b_anak.id', '=', 'k_anak.id_biodata')
                ->where('s.id', $idSantri)
                ->value('k_anak.no_kk');

            $currentBiodataId = DB::table('santri as s')
                ->join('peserta_didik as pd', 's.id_peserta_didik', '=', 'pd.id')
                ->join('biodata as b_anak', 'pd.id_biodata', '=', 'b_anak.id')
                ->where('s.id', $idSantri)
                ->value('b_anak.id');

            // Kumpulan id biodata dari orang tua/wali yang harus dikecualikan
            $excludedIds = DB::table('orang_tua_wali')
                ->pluck('id_biodata')
                ->toArray();

            // Ambil data saudara kandung (anggota keluarga lain dalam KK yang sama, dari semua tabel terkait)
            $saudara = DB::table('keluarga as k_saudara')
                ->join('biodata as b_saudara', 'k_saudara.id_biodata', '=', 'b_saudara.id')
                ->where('k_saudara.no_kk', $noKk)
                ->whereNotIn('k_saudara.id_biodata', $excludedIds)
                ->where('k_saudara.id_biodata', '!=', $currentBiodataId)
                ->select(
                    'b_saudara.nama',
                    'b_saudara.nik',
                    DB::raw("'Saudara Kandung' as hubungan"),
                    DB::raw("NULL as nama_status"),
                    DB::raw("NULL as wali")
                )
                ->get();

            // Jika terdapat data saudara, gabungkan dengan data keluarga
            if ($saudara->isNotEmpty()) {
                $keluarga = $keluarga->merge($saudara);
            }

            // Siapkan output data
            if ($keluarga->isNotEmpty()) {
                $data['Keluarga'] = $keluarga->map(function ($item) {
                    return [
                        "nama"   => $item->nama,
                        "nik"    => $item->nik,
                        "status" => $item->nama_status ?? $item->hubungan,
                        "wali"   => $item->wali,
                    ];
                });
            }

            // Data Status Santri
            $santri = DB::table('peserta_didik as pd')
                ->join('santri as s', 's.id_peserta_didik', '=', 'pd.id')
                ->where('s.id', $idSantri)
                ->select(
                    's.nis',
                    's.tanggal_masuk_santri',
                    's.tanggal_keluar_santri'
                )
                ->get();

            if ($santri->isNotEmpty()) {
                $data['Status_Santri']['Santri'] = $santri->map(function ($item) {
                    return [
                        'Nis'           => $item->nis,
                        'Tanggal_Mulai' => $item->tanggal_masuk_santri,
                        'Tanggal_Akhir' => $item->tanggal_keluar_santri ?? "-",
                    ];
                });
            }

            // Data Kewaliasuhan
            $kewaliasuhan = DB::table('peserta_didik')
                ->join('santri as s', 's.id_peserta_didik', '=', 'peserta_didik.id')
                ->leftJoin('wali_asuh', 's.id', '=', 'wali_asuh.id_santri')
                ->leftJoin('anak_asuh', 's.id', '=', 'anak_asuh.id_santri')
                ->leftJoin('grup_wali_asuh', 'grup_wali_asuh.id', '=', 'wali_asuh.id_grup_wali_asuh')
                ->leftJoin('kewaliasuhan', function ($join) {
                    $join->on('kewaliasuhan.id_wali_asuh', '=', 'wali_asuh.id')
                        ->orOn('kewaliasuhan.id_anak_asuh', '=', 'anak_asuh.id');
                })
                ->leftJoin('anak_asuh as anak_asuh_data', 'kewaliasuhan.id_anak_asuh', '=', 'anak_asuh_data.id')
                ->leftJoin('santri as santri_anak', 'anak_asuh_data.id_santri', '=', 'santri_anak.id')
                ->leftJoin('peserta_didik as pd_anak', 'santri_anak.id_peserta_didik', '=', 'pd_anak.id')
                ->leftJoin('biodata as bio_anak', 'pd_anak.id_biodata', '=', 'bio_anak.id')
                ->leftJoin('wali_asuh as wali_asuh_data', 'kewaliasuhan.id_wali_asuh', '=', 'wali_asuh_data.id')
                ->leftJoin('santri as santri_wali', 'wali_asuh_data.id_santri', '=', 'santri_wali.id')
                ->leftJoin('peserta_didik as pd_wali', 'santri_wali.id_peserta_didik', '=', 'pd_wali.id')
                ->leftJoin('biodata as bio_wali', 'pd_wali.id_biodata', '=', 'bio_wali.id')
                ->where('s.id', $idSantri)
                ->havingRaw('relasi_santri IS NOT NULL') // Filter untuk menghindari hasil NULL
                ->select(
                    'grup_wali_asuh.nama_grup',
                    DB::raw("CASE 
                            WHEN wali_asuh.id IS NOT NULL THEN 'Wali Asuh'
                            WHEN anak_asuh.id IS NOT NULL THEN 'Anak Asuh'
                        END as status_santri"),
                    DB::raw("CASE 
                            WHEN wali_asuh.id IS NOT NULL THEN GROUP_CONCAT(DISTINCT bio_anak.nama SEPARATOR ', ')
                            WHEN anak_asuh.id IS NOT NULL THEN GROUP_CONCAT(DISTINCT bio_wali.nama SEPARATOR ', ')
                        END as relasi_santri")
                )
                ->groupBy(
                    'grup_wali_asuh.nama_grup',
                    'wali_asuh.id',
                    'anak_asuh.id'
                )
                ->get();

            if ($kewaliasuhan->isNotEmpty()) {
                $data['Status_Santri']['Kewaliasuhan'] = $kewaliasuhan->map(function ($item) {
                    return [
                        'group'   => $item->nama_grup ?? '-',
                        'Sebagai' => $item->status_santri,
                        $item->status_santri === 'Anak Asuh' ? 'Nama Wali Asuh' : 'Nama Anak Asuh'
                        => $item->relasi_santri ?? "-",
                    ];
                });
            }

            // Data Perizinan
            $perizinan = DB::table('perizinan as pr')
                ->join('peserta_didik as pd', 'pr.id_peserta_didik', '=', 'pd.id')
                ->join('santri as s', 'pd.id', '=', 's.id_peserta_didik')
                ->where('s.id', $idSantri)
                ->select(
                    DB::raw("CONCAT(pr.tanggal_mulai, ' s/d ', pr.tanggal_akhir) as tanggal"),
                    'pr.keterangan',
                    DB::raw("CASE 
                            WHEN TIMESTAMPDIFF(SECOND, pr.tanggal_mulai, pr.tanggal_akhir) >= 86400 
                            THEN CONCAT(FLOOR(TIMESTAMPDIFF(SECOND, pr.tanggal_mulai, pr.tanggal_akhir) / 86400), ' Hari | Bermalam')
                            ELSE CONCAT(FLOOR(TIMESTAMPDIFF(SECOND, pr.tanggal_mulai, pr.tanggal_akhir) / 3600), ' Jam')
                        END as lama_waktu"),
                    'pr.status_kembali'
                )
                ->get();

            if ($perizinan->isNotEmpty()) {
                $data['Status_santri']['Info_Perizinan'] = $perizinan->map(function ($item) {
                    return [
                        'tanggal'        => $item->tanggal,
                        'keterangan'     => $item->keterangan,
                        'lama_waktu'     => $item->lama_waktu,
                        'status_kembali' => $item->status_kembali,
                    ];
                });
            }

            // Data Domisili Santri
            $domisili = DB::table('peserta_didik as pd')
                ->join('santri as s', 's.id_peserta_didik', '=', 'pd.id')
                ->join('domisili_santri as ds', 'ds.id_santri', '=', 's.id')
                ->join('wilayah as w', 'ds.id_wilayah', '=', 'w.id')
                ->join('blok as bl', 'ds.id_blok', '=', 'bl.id')
                ->join('kamar as km', 'ds.id_kamar', '=', 'km.id')
                ->where('s.id', $idSantri)
                ->select(
                    'km.nama_kamar',
                    'bl.nama_blok',
                    'w.nama_wilayah',
                    'ds.tanggal_masuk',
                    'ds.tanggal_keluar'
                )
                ->get();

            if ($domisili->isNotEmpty()) {
                $data['Domisili'] = $domisili->map(function ($item) {
                    return [
                        'Kamar'             => $item->nama_kamar,
                        'Blok'              => $item->nama_blok,
                        'Wilayah'           => $item->nama_wilayah,
                        'tanggal_ditempati' => $item->tanggal_masuk,
                        'tanggal_pindah'    => $item->tanggal_keluar ?? "-",
                    ];
                });
            }

            // Data Pendidikan (Pelajar)
            $pelajar = DB::table('peserta_didik as pd')
                ->join('santri as s', 'pd.id', '=', 's.id_peserta_didik')
                ->join('pelajar as p', 'p.id_peserta_didik', '=', 'pd.id')
                ->join('pendidikan_pelajar as pp', 'pp.id_pelajar', '=', 'p.id')
                ->join('lembaga as l', 'pp.id_lembaga', '=', 'l.id')
                ->leftJoin('jurusan as j', 'pp.id_jurusan', '=', 'j.id')
                ->leftJoin('kelas as k', 'pp.id_kelas', '=', 'k.id')
                ->leftJoin('rombel as r', 'pp.id_rombel', '=', 'r.id')
                ->where('s.id', $idSantri)
                ->select(
                    'p.no_induk',
                    'l.nama_lembaga',
                    'j.nama_jurusan',
                    'k.nama_kelas',
                    'r.nama_rombel',
                    'p.tanggal_masuk_pelajar',
                    'p.tanggal_keluar_pelajar'
                )
                ->get();

            if ($pelajar->isNotEmpty()) {
                $data['Pendidikan'] = $pelajar->map(function ($item) {
                    return [
                        'no_induk'     => $item->no_induk,
                        'nama_lembaga' => $item->nama_lembaga,
                        'nama_jurusan' => $item->nama_jurusan,
                        'nama_kelas'   => $item->nama_kelas ?? "-",
                        'nama_rombel'  => $item->nama_rombel ?? "-",
                        'tahun_masuk'  => $item->tanggal_masuk_pelajar,
                        'tahun_lulus'  => $item->tanggal_keluar_pelajar ?? "-",
                    ];
                });
            }

            // Catatan Afektif Peserta Didik
            $afektif = DB::table('peserta_didik as pd')
                ->join('santri as s', 's.id_peserta_didik', '=', 'pd.id')
                ->join('catatan_afektif as ca', 's.id', '=', 'ca.id_santri')
                ->where('s.id', $idSantri)
                ->select(
                    'ca.kebersihan_nilai',
                    'ca.kebersihan_tindak_lanjut',
                    'ca.kepedulian_nilai',
                    'ca.kepedulian_tindak_lanjut',
                    'ca.akhlak_nilai',
                    'ca.akhlak_tindak_lanjut'
                )
                ->latest('ca.created_at')
                ->first();

            if ($afektif) {
                $data['Catatan_Progress']['Afektif'] = [
                    'Keterangan' => [
                        'kebersihan'               => $afektif->kebersihan_nilai ?? "-",
                        'tindak_lanjut_kebersihan' => $afektif->kebersihan_tindak_lanjut ?? "-",
                        'kepedulian'               => $afektif->kepedulian_nilai ?? "-",
                        'tindak_lanjut_kepedulian' => $afektif->kepedulian_tindak_lanjut ?? "-",
                        'akhlak'                   => $afektif->akhlak_nilai ?? "-",
                        'tindak_lanjut_akhlak'     => $afektif->akhlak_tindak_lanjut ?? "-",
                    ]
                ];
            }

            // Catatan Kognitif Peserta Didik
            $kognitif = DB::table('peserta_didik as pd')
                ->join('santri as s', 's.id_peserta_didik', '=', 'pd.id')
                ->join('catatan_kognitif as ck', 's.id', '=', 'ck.id_santri')
                ->where('s.id', $idSantri)
                ->select(
                    'ck.kebahasaan_nilai',
                    'ck.kebahasaan_tindak_lanjut',
                    'ck.baca_kitab_kuning_nilai',
                    'ck.baca_kitab_kuning_tindak_lanjut',
                    'ck.hafalan_tahfidz_nilai',
                    'ck.hafalan_tahfidz_tindak_lanjut',
                    'ck.furudul_ainiyah_nilai',
                    'ck.furudul_ainiyah_tindak_lanjut',
                    'ck.tulis_alquran_nilai',
                    'ck.tulis_alquran_tindak_lanjut',
                    'ck.baca_alquran_nilai',
                    'ck.baca_alquran_tindak_lanjut'
                )
                ->latest('ck.created_at')
                ->first();

            if ($kognitif) {
                $data['Catatan_Progress']['Kognitif'] = [
                    'Keterangan' => [
                        'kebahasaan'                      => $kognitif->kebahasaan_nilai ?? "-",
                        'tindak_lanjut_kebahasaan'        => $kognitif->kebahasaan_tindak_lanjut ?? "-",
                        'baca_kitab_kuning'               => $kognitif->baca_kitab_kuning_nilai ?? "-",
                        'tindak_lanjut_baca_kitab_kuning' => $kognitif->baca_kitab_kuning_tindak_lanjut ?? "-",
                        'hafalan_tahfidz'                 => $kognitif->hafalan_tahfidz_nilai ?? "-",
                        'tindak_lanjut_hafalan_tahfidz'   => $kognitif->hafalan_tahfidz_tindak_lanjut ?? "-",
                        'furudul_ainiyah'                 => $kognitif->furudul_ainiyah_nilai ?? "-",
                        'tindak_lanjut_furudul_ainiyah'   => $kognitif->furudul_ainiyah_tindak_lanjut ?? "-",
                        'tulis_alquran'                   => $kognitif->tulis_alquran_nilai ?? "-",
                        'tindak_lanjut_tulis_alquran'     => $kognitif->tulis_alquran_tindak_lanjut ?? "-",
                        'baca_alquran'                    => $kognitif->baca_alquran_nilai ?? "-",
                        'tindak_lanjut_baca_alquran'      => $kognitif->baca_alquran_tindak_lanjut ?? "-",
                    ]
                ];
            }

            // Data Kunjungan Mahrom
            $pengunjung = DB::table('pengunjung_mahrom')
                ->join('santri as s', 'pengunjung_mahrom.id_santri', '=', 's.id')
                ->join('peserta_didik as pd', 's.id_peserta_didik', '=', 'pd.id')
                ->where('s.id', $idSantri)
                ->select(
                    'pengunjung_mahrom.nama_pengunjung',
                    'pengunjung_mahrom.tanggal'
                )
                ->get();

            if ($pengunjung->isNotEmpty()) {
                $data['Kunjungan_Mahrom']['Di_kunjungi_oleh'] = $pengunjung->map(function ($item) {
                    return [
                        'Nama'    => $item->nama_pengunjung,
                        'Tanggal' => $item->tanggal,
                    ];
                });
            }

            // khadam
            $khadam = DB::table('khadam as kh')
                ->join('biodata as b', 'kh.id_biodata', '=', 'b.id')
                ->join('peserta_didik as pd', 'pd.id_biodata', '=', 'b.id')
                ->join('santri as s', 'pd.id', '=', 's.id_peserta_didik')
                ->where('s.id', $idSantri)
                ->select(
                    'kh.keterangan',
                    'tanggal_mulai',
                    'tanggal_akhir',
                )
                ->first();

            if ($khadam) {
                $data['Khadam'] = [
                    'keterangan' => $khadam->keterangan,
                    'tanggal_mulai' => $khadam->tanggal_mulai,
                    'tanggal_akhir' => $khadam->tanggal_akhir,
                ];
            }

            return $data;
        } catch (\Exception $e) {
            Log::error("Error in formDetailSantri: " . $e->getMessage());
            return ['error' => 'Terjadi kesalahan pada server'];
        }
    }

    /**
     * Method publik untuk mengembalikan detail santri dalam response JSON.
     */
    public function getDetailSantri($id)
    {
        // Validasi bahwa ID adalah UUID
        if (!Str::isUuid($id)) {
            return response()->json(['error' => 'ID tidak valid'], 400);
        }

        try {
            // Cari data santri berdasarkan UUID
            $santri = Santri::find($id);
            if (!$santri) {
                return response()->json(['error santri' => 'Data tidak ditemukan'], 404);
            }

            // Ambil detail santri dari fungsi helper
            $data = $this->formDetailSantri($santri->id);
            if (empty($data)) {
                return response()->json(['error detail santri' => 'Data Kosong'], 200);
            }

            return response()->json($data, 200);
        } catch (\Exception $e) {
            Log::error("Error in getDetailSantri: " . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan pada server'], 500);
        }
    }
}
