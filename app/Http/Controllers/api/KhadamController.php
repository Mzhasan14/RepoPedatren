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

class KhadamController extends Controller
{
    protected $filterController;

    public function __construct(FilterController $filterController)
    {
        $this->filterController = $filterController;
    }

    public function getAllKhadam(Request $request)
    {
        $query = DB::table('khadam as kh')
            ->join('biodata as b', 'kh.id_biodata', '=', 'b.id')
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
            ->where('kh.status', true)
            ->select(
                'kh.id',
                'wp.niup',
                DB::raw("COALESCE(b.nik, b.no_passport) as identitas"),
                'b.nama',
                'kh.keterangan',
                'b.created_at',
                'b.updated_at',
                DB::raw("COALESCE(br.file_path, 'default.jpg') as foto_profil")
            );


        // Filter Umum (Alamat dan Jenis Kelamin)
        $query = $this->filterController->applyCommonFilters($query, $request);

        // Filter Wilayah
        if ($request->filled('wilayah')) {
            $wilayah = strtolower($request->wilayah);
            $query->leftjoin('blok', 'santri.id_blok', '=', 'blok.id')
                ->leftjoin('kamar', 'santri.id_kamar', '=', 'kamar.id')
                ->where('wilayah.nama_wilayah', $wilayah);
            if ($request->filled('blok')) {
                $blok = strtolower($request->blok);
                $query->where('blok.nama_blok', $blok);
                if ($request->filled('kamar')) {
                    $kamar = strtolower($request->kamar);
                    $query->where('kamar.nama_kamar', $kamar);
                }
            }
        }

        // Filter Lembaga
        if ($request->filled('lembaga')) {
            $query->where('lembaga.nama_lembaga', $request->lembaga);
            if ($request->filled('jurusan')) {
                $query->leftJoin('jurusan', 'pelajar.id_jurusan', '=', 'jurusan.id')
                    ->leftJoin('kelas', 'pelajar.id_kelas', '=', 'kelas.id')
                    ->leftJoin('rombel', 'pelajar.id_rombel', '=', 'rombel.id');
                $query->where('jurusan.nama_jurusan', $request->jurusan);
                if ($request->filled('kelas')) {
                    $query->where('kelas.nama_kelas', $request->kelas);
                    if ($request->filled('rombel')) {
                        $query->where('rombel.nama_rombel', $request->rombel);
                    }
                }
            }
        }

        // Filter Status Warga Pesantren
        if ($request->filled('warga_pesantren')) {
            $warga_pesantren = strtolower($request->warga_pesantren);
            if ($warga_pesantren == 'memiliki niup') {
                $query->whereNotNull('biodata.niup');
            } else if ($warga_pesantren == 'tanpa niup') {
                $query->whereNull('biodata.niup');
            }
        }

        // Filter Smartcard
        if ($request->filled('smartcard')) {
            $smartcard = strtolower($request->smartcard);
            if ($smartcard == 'memiliki smartcard') {
                $query->whereNotNull('biodata.smartcard');
            } else if ($smartcard == 'tanpa smartcard') {
                $query->whereNull('biodata.smartcard');
            }
        }

        // Filter No Telepon
        if ($request->filled('phone_number')) {
            $phone_number = strtolower($request->phone_number);
            if ($phone_number == 'memiliki phone number') {
                $query->whereNotNull('biodata.no_telepon')
                    ->where('biodata.no_telepon', '!=', '');
            } else if ($phone_number == 'tidak ada phone number') {
                $query->whereNull('biodata.no_telepon')
                    ->where('biodata.no_telepon', '=', '');
            }
        }

        // Filter Pemberkasan (Lengkap / Tidak Lengkap)
        if ($request->filled('pemberkasan')) {
            $pemberkasan = strtolower($request->pemberkasan);
            switch ($pemberkasan) {
                case 'tidak ada berkas':
                    $query->whereNull('br.id_biodata');
                    break;
                case 'tidak ada foto diri':
                    $query->where('br.id_jenis_berkas', 4)->whereNull('br.file_path');
                    break;
                case 'memiliki foto diri':
                    $query->where('br.id_jenis_berkas', 4)->whereNotNull('br.file_path');
                    break;
                case 'tidak ada kk':
                    $query->where('br.id_jenis_berkas', 1)->whereNull('br.file_path');
                    break;
                case 'tidak ada akta kelahiran':
                    $query->where('br.id_jenis_berkas', 3)->whereNull('br.file_path');
                    break;
                case 'tidak ada ijazah':
                    $query->where('br.id_jenis_berkas', 5)->whereNull('br.file_path');
                    break;
                default:
                    $query->whereRaw('0 = 1');
                    break;
            }
        }

        // Ambil jumlah data per halaman (default 10 jika tidak diisi)
        $perPage = $request->input('limit', 25);

        // Ambil halaman saat ini (jika ada)
        $currentPage = $request->input('page', 1);

        // Menerapkan pagination ke hasil
        $hasil = $query->paginate($perPage, ['*'], 'page', $currentPage);


        // Jika Data Kosong
        if ($hasil->isEmpty()) {
            return response()->json([
                "status" => "succes",
                "message" => "Data Kosong",
                "code" => 200
            ], 200);
        }

        return response()->json([
            "total_data" => $hasil->total(),
            "current_page" => $hasil->currentPage(),
            "per_page" => $hasil->perPage(),
            "total_pages" => $hasil->lastPage(),
            "data" => $hasil->map(function ($item) {
                return [
                    "id_khadam" => $item->id,
                    "niup" => $item->niup,
                    "nama" => $item->nama,
                    "keterangan" => $item->keterangan,
                    "tgl_update" => Carbon::parse($item->updated_at)->translatedFormat('d F Y H:i:s'),
                    "tgl_input" =>  Carbon::parse($item->created_at)->translatedFormat('d F Y H:i:s'),
                    "foto_profil" => url($item->foto_profil)
                ];
            })
        ]);
    }

    public function formDetailKhadam($idKhadam)
    {
        try {
            // Query Biodata beserta data terkait
            $biodata = DB::table('khadam as kh')
                ->join('biodata as b', 'kh.id_biodata', '=', 'b.id')
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
                         ->whereRaw('br.id = (
                            select max(b2.id) 
                            from berkas as b2 
                            where b2.id_biodata = b.id 
                              and b2.id_jenis_berkas = br.id_jenis_berkas
                         )');
                })
                ->leftJoin('keluarga as k', 'b.id', '=', 'k.id_biodata')
                ->leftJoin('kecamatan as kc', 'b.id_kecamatan', '=', 'kc.id')
                ->leftJoin('kabupaten as kb', 'b.id_kabupaten', '=', 'kb.id')
                ->leftJoin('provinsi as pv', 'b.id_provinsi', '=', 'pv.id')
                ->leftJoin('negara as ng', 'b.id_negara', '=', 'ng.id')
                ->where('kh.id', $idKhadam)
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

            /*
             * Query Data Keluarga: Mengambil data keluarga, orang tua/wali beserta hubungannya.
             */
            $keluarga = DB::table('khadam as kh')
                ->join('biodata as b_kh', 'kh.id_biodata', '=', 'b_kh.id')
                ->join('keluarga as k_anak', 'b_kh.id', '=', 'k_anak.id_biodata')
                ->leftJoin('keluarga as k_ortu', 'k_anak.no_kk', '=', 'k_ortu.no_kk')
                ->join('orang_tua_wali', 'k_ortu.id_biodata', '=', 'orang_tua_wali.id_biodata')
                ->join('biodata as b_ortu', 'orang_tua_wali.id_biodata', '=', 'b_ortu.id')
                ->join('hubungan_keluarga', 'orang_tua_wali.id_hubungan_keluarga', '=', 'hubungan_keluarga.id')
                ->where('kh.id', $idKhadam)
                ->select(
                    'b_ortu.nama',
                    'b_ortu.nik',
                    DB::raw("'Orang Tua' as hubungan"),
                    'hubungan_keluarga.nama_status',
                    'orang_tua_wali.wali'
                )
                ->get();

            // Ambil No KK dari id khadam
            $noKk = DB::table('khadam as kh')
                ->join('biodata as b_kh', 'kh.id_biodata', '=', 'b_kh.id')
                ->join('keluarga as k', 'b_kh.id', '=', 'k.id_biodata')
                ->where('kh.id', $idKhadam)
                ->value('k.no_kk');

            // Ambil semua id_biodata dari peserta_didik, pegawai, dan khadam
            $excludedIds = DB::table('orang_tua_wali')->pluck('id_biodata')->toArray();

            $idBiodataKhadam = DB::table('khadam as kh')
                ->join('biodata as b', 'kh.id_biodata', '=', 'b.id')
                ->where('kh.id', $idKhadam)
                ->value('b.id');

            // Cari semua anggota keluarga (bisa dari pd, pegawai, khadam) dalam KK yang sama
            $saudara = DB::table('keluarga as k_saudara')
                ->join('biodata as b_saudara', 'k_saudara.id_biodata', '=', 'b_saudara.id')
                ->where('k_saudara.no_kk', $noKk)
                ->whereNotIn('k_saudara.id_biodata', $excludedIds)
                ->where('k_saudara.id_biodata', '!=', $idBiodataKhadam)
                ->select(
                    'b_saudara.nama',
                    'b_saudara.nik',
                    DB::raw("'Saudara Kandung' as hubungan"),
                    DB::raw("NULL as nama_status"),
                    DB::raw("NULL as wali")
                )
                ->get();

            if ($saudara->isNotEmpty()) {
                $keluarga = $keluarga->merge($saudara);
            }

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
            $santri = DB::table('khadam as kh')
                ->join('biodata as b', 'kh.id_biodata', '=', 'b.id')
                ->join('peserta_didik as pd', 'pd.id_biodata', '=', 'b.id')
                ->join('santri', 'santri.id_peserta_didik', '=', 'pd.id')
                ->where('kh.id', $idKhadam)
                ->select(
                    'santri.nis',
                    'santri.tanggal_masuk_santri',
                    'santri.tanggal_keluar_santri'
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
            $kewaliasuhan = DB::table('khadam as kh')
                ->join('biodata as b', 'kh.id_biodata', '=', 'b.id')
                ->join('peserta_didik as peserta_didik', 'peserta_didik.id_biodata', '=', 'b.id')
                ->join('santri', 'santri.id_peserta_didik', '=', 'peserta_didik.id')
                ->leftJoin('wali_asuh', 'santri.id', '=', 'wali_asuh.id_santri')
                ->leftJoin('anak_asuh', 'santri.id', '=', 'anak_asuh.id_santri')
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
                ->where('kh.id', $idKhadam)
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
            $perizinan = DB::table('perizinan as p')
                ->join('peserta_didik as pd', 'p.id_peserta_didik', '=', 'pd.id')
                ->join('biodata as b', 'pd.id_biodata', '=', 'b.id')
                ->join('khadam as kh', 'kh.id_biodata', '=', 'b.id')
                ->where('pd.id', $idKhadam)
                ->select(
                    DB::raw("CONCAT(p.tanggal_mulai, ' s/d ', p.tanggal_akhir) as tanggal"),
                    'p.keterangan',
                    DB::raw("CASE 
                            WHEN TIMESTAMPDIFF(SECOND, p.tanggal_mulai, p.tanggal_akhir) >= 86400 
                            THEN CONCAT(FLOOR(TIMESTAMPDIFF(SECOND, p.tanggal_mulai, p.tanggal_akhir) / 86400), ' Hari | Bermalam')
                            ELSE CONCAT(FLOOR(TIMESTAMPDIFF(SECOND, p.tanggal_mulai, p.tanggal_akhir) / 3600), ' Jam')
                        END as lama_waktu"),
                    'p.status_kembali'
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
            $domisili = DB::table('khadam as kh')
                ->join('biodata as b', 'kh.id_biodata', '=', 'b.id')
                ->join('peserta_didik as pd', 'pd.id_biodata', '=', 'b.id')
                ->join('santri as s', 's.id_peserta_didik', '=', 'pd.id')
                ->join('domisili_santri as ds', 'ds.id_santri', '=', 's.id')
                ->join('wilayah as w', 'ds.id_wilayah', '=', 'w.id')
                ->join('blok as bl', 'ds.id_blok', '=', 'bl.id')
                ->join('kamar as km', 'ds.id_kamar', '=', 'km.id')
                ->where('kh.id', $idKhadam)
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
            $pelajar = DB::table('khadam as kh')
                ->join('biodata as b', 'kh.id_biodata', '=', 'b.id')
                ->join('peserta_didik as pd', 'pd.id_biodata', '=', 'b.id')
                ->join('pelajar as p', 'p.id_peserta_didik', '=', 'pd.id')
                ->join('pendidikan_pelajar as pp', 'pp.id_pelajar', '=', 'p.id')
                ->join('lembaga as l', 'pp.id_lembaga', '=', 'l.id')
                ->leftJoin('jurusan as j', 'pp.id_jurusan', '=', 'j.id')
                ->leftJoin('kelas as k', 'pp.id_kelas', '=', 'k.id')
                ->leftJoin('rombel as r', 'pp.id_rombel', '=', 'r.id')
                ->where('kh.id', $idKhadam)
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
            $afektif = DB::table('khadam as kh')
                ->join('biodata as b', 'kh.id_biodata', '=', 'b.id')
                ->join('peserta_didik as pd', 'pd.id_biodata', '=', 'b.id')
                ->join('santri as s', 's.id_peserta_didik', '=', 'pd.id')
                ->join('catatan_afektif as ca', 's.id', '=', 'ca.id_santri')
                ->where('kh.id', $idKhadam)
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
            $kognitif = DB::table('khadam as kh')
                ->join('biodata as b', 'kh.id_biodata', '=', 'b.id')
                ->join('peserta_didik as pd', 'pd.id_biodata', '=', 'b.id')
                ->join('santri as s', 's.id_peserta_didik', '=', 'pd.id')
                ->join('catatan_kognitif as ck', 's.id', '=', 'ck.id_santri')
                ->where('kh.id', $idKhadam)
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
                ->join('santri', 'pengunjung_mahrom.id_santri', '=', 'santri.id')
                ->join('peserta_didik as pd', 'santri.id_peserta_didik', '=', 'pd.id')
                ->join('biodata as b', 'pd.id_biodata', '=', 'b.id')
                ->join('khadam as kh', 'b.id', '=', 'kh.id_biodata')
                ->where('kh.id', $idKhadam)
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
                ->where('kh.id', $idKhadam)
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
            Log::error("Error in formDetailPesertaDidik: " . $e->getMessage());
            return ['error' => 'Terjadi kesalahan pada server'];
        }
    }

    /**
     * Method publik untuk mengembalikan detail khadam dalam response JSON.
     */
    public function getDetailKhadam($id)
    {
        // Validasi bahwa ID adalah UUID
        if (!Str::isUuid($id)) {
            return response()->json(['error' => 'ID tidak valid'], 400);
        }

        try {
            // Cari data peserta didik berdasarkan UUID
            $khadam = Khadam::find($id);
            if (!$khadam) {
                return response()->json(['error' => 'Data tidak ditemukan'], 404);
            }

            // Ambil detail peserta didik dari fungsi helper
            $data = $this->formDetailKhadam($khadam->id);
            if (empty($data)) {
                return response()->json(['error' => 'Data Kosong'], 200);
            }

            return response()->json($data, 200);
        } catch (\Exception $e) {
            Log::error("Error in getDetailKhadam: " . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan pada server'], 500);
        }
    }
}
