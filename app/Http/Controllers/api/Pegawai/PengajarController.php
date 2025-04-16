<?php

namespace App\Http\Controllers\Api\Pegawai;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Biodata;
use Illuminate\Http\Request;
use App\Models\Pegawai\Pengajar;
use App\Http\Resources\PdResource;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\FilterController;
use App\Models\JenisBerkas;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;

class PengajarController extends Controller
{

    protected $filterController;
    protected $filter;

    public function __construct()
    {
        // Inisialisasi controller filter
        $this->filterController = new FilterController();
        $this->filter = new FilterKepegawaianController();
    }

    public function index()
    {
        $pengajar = Pengajar::all();
        return new PdResource(true, 'Data berhasil ditampilkan', $pengajar);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_pegawai'   => 'required|integer',
            'id_golongan'  => 'required|integer',
            'id_lembaga'   => 'required|integer',
            'created_by'   => 'required|integer',
            'status'       => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Data gagal ditambahkan',
                'data' => $validator->errors()
            ]);
        }

        $pengajar = Pengajar::create($validator->validated());
        return new PdResource(true, 'Data berhasil ditambahkan', $pengajar);
    }

    public function show(string $id)
    {
        $pengajar = Pengajar::findOrFail($id);
        return new PdResource(true, 'Data berhasil ditampilkan', $pengajar);
    }
    public function update(Request $request, string $id)
    {
        $pengajar = Pengajar::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'id_pegawai'   => 'required|integer',
            'id_golongan'  => 'required|integer',
            'id_lembaga'   => 'required|integer',
            'updated_by'   => 'nullable|integer',
            'status'       => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Data gagal ditambahkan',
                'data' => $validator->errors()
            ]);
        }
        $pengajar->update($validator->validated());
        return new PdResource(true, 'Data berhasil diupdate', $pengajar);
    }
    public function destroy(string $id)
    {
        $pengajar = Pengajar::findOrFail($id);
        $pengajar->delete();
        return new PdResource(true, 'Data berhasil dihapus', $pengajar);
    }

    public function filterPengajar(Request $request)
    {
        try {
        $query = Pengajar::Active()
            ->join('pegawai', 'pengajar.id_pegawai', '=', 'pegawai.id')
            ->join('biodata as b', 'pegawai.id_biodata', '=', 'b.id')
            ->leftJoin('peserta_didik as pd','b.id','pd.id_biodata')
            ->leftJoin('santri as s','pd.id','s.id_peserta_didik')
            ->leftJoin('domisili_santri as ds','s.id','ds.id_santri')
            ->leftJoin('wilayah as w','ds.id_wilayah','w.id')
            ->leftJoin('warga_pesantren as wp','b.id','wp.id_biodata')
            ->leftJoin('kabupaten as kb','kb.id','b.id_kabupaten')
            ->leftJoin('lembaga as l', 'pegawai.id_lembaga', '=', 'l.id')
            ->leftJoin('golongan as g', 'pengajar.id_golongan', '=', 'g.id')
            ->leftJoin('kategori_golongan as kg', 'g.id_kategori_golongan', '=', 'kg.id')
            ->leftJoin('entitas_pegawai','entitas_pegawai.id_pegawai','=','pegawai.id')
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
            ->leftJoin('materi_ajar', function ($join) {
                $join->on('materi_ajar.id_pengajar', '=', 'pengajar.id')
                     ->where('materi_ajar.status', 1);
            })
            ->select(
                'pengajar.id',
                'b.nama',
                'wp.niup',
                DB::raw("TIMESTAMPDIFF(YEAR, b.tanggal_lahir, CURDATE()) AS umur"),
                DB::raw("
                GROUP_CONCAT(DISTINCT materi_ajar.nama_materi SEPARATOR ', ') AS daftar_materi"),
                DB::raw("
                CONCAT(
                    FLOOR(SUM(DISTINCT materi_ajar.jumlah_menit) / 60), ' jam ',
                    MOD(SUM(DISTINCT materi_ajar.jumlah_menit), 60), ' menit'
                ) AS total_waktu_materi
            "),     
                DB::raw("COUNT(DISTINCT materi_ajar.nama_materi) AS total_materi"),
                DB::raw("
                CASE 
                    WHEN TIMESTAMPDIFF(YEAR, entitas_pegawai.tanggal_masuk, COALESCE(entitas_pegawai.tanggal_keluar, CURDATE())) = 0 
                    THEN CONCAT('Belum setahun sejak ', DATE_FORMAT(entitas_pegawai.tanggal_masuk, '%Y-%m-%d'))
                    ELSE CONCAT(TIMESTAMPDIFF(YEAR, entitas_pegawai.tanggal_masuk, COALESCE(entitas_pegawai.tanggal_keluar, CURDATE())), ' Tahun sejak ', DATE_FORMAT(entitas_pegawai.tanggal_masuk, '%Y-%m-%d'))
                END AS masa_kerja"),
                'g.nama_golongan',
                'b.nama_pendidikan_terakhir',
                DB::raw("DATE_FORMAT(pengajar.updated_at, '%Y-%m-%d %H:%i:%s') AS tgl_update"),
                DB::raw("DATE_FORMAT(pengajar.created_at, '%Y-%m-%d %H:%i:%s') AS tgl_input"),
                'l.nama_lembaga',    
                DB::raw("COALESCE(MAX(br.file_path), 'default.jpg') as foto_profil")
                )   
                 ->groupBy(
                    'pengajar.id',
                    'b.nama',
                    'wp.niup',
                    'b.tanggal_lahir',
                    'g.nama_golongan',
                    'b.nama_pendidikan_terakhir',
                    'pengajar.updated_at',
                    'pengajar.created_at',
                    'l.nama_lembaga',
                    'entitas_pegawai.tanggal_masuk',
                    'entitas_pegawai.tanggal_keluar'
                )->distinct();   
        // 🔹 Terapkan filter umum (lokasi & jenis kelamin)
        $query = $this->filterController->applyCommonFilters($query, $request);
        $query = $this->filter->applySearchFilter($query, $request);
        $query = $this->filter->applylembagaKaryawanFilter($query, $request);
        $query = $this->filter->applyGolonganJabatanFilter($query, $request);
        $query = $this->filter->applyGolonganFilter($query, $request);
        $query = $this->filter->applyMateriAjarFilter($query, $request);
        $query = $this->filter->applyMasaKerjaFilter($query, $request);
        $query = $this->filter->applyJabatanPengajarFilter($query, $request);
        $query = $this->filter->applyWargaPesantrenFilter($query, $request);
        $query = $this->filter->applyPemberkasanFilter($query, $request);
        $query = $this->filter->applyUmurFilter($query, $request);
        $query = $this->filter->applyPhoneFilter($query, $request);
        
        $onePage = $request->input('limit', 25);

        $currentPage =  $request->input('page', 1);

        $hasil = $query->paginate($onePage, ['*'], 'page', $currentPage);


        // Jika Data Kosong
        if ($hasil->isEmpty()) {
            return response()->json([
                "status" => "error",
                "message" => "Data tidak ditemukan",
                "code" => 404
            ], 404);
        }
        return response()->json([
            "total_data" => $hasil->total(),
            "current_page" => $hasil->currentPage(),
            "per_page" => $hasil->perPage(),
            "total_pages" => $hasil->lastPage(),
            "data" => $hasil->map(function ($item) {
                return [
                    "id" => $item->id,
                    "nama" => $item->nama,
                    "niup" => $item->niup,
                    "umur" => $item->umur,
                    "daftar_materi" => $item->daftar_materi,
                    "total_waktu_materi" => $item->total_waktu_materi,
                    "total_materi" => $item->total_materi,
                    "masa_kerja" => $item->masa_kerja,
                    "golongan" => $item->nama_golongan,
                    "pendidikan_terakhir" => $item->nama_pendidikan_terakhir,
                    "tgl_update" => $item->tgl_update,
                    "tgl_input" => $item->tgl_input,
                    "lembaga" => $item->nama_lembaga,
                    "foto_profil" => url($item->foto_profil)
                ];
            })
        ]);
    } catch (\Exception $e) {
        return response()->json([
            "status" => "error",
            "message" => "Terjadi kesalahan saat memproses data",
            "code" => 500,
            "error_detail" => $e->getMessage()
        ], 500);
        }
    }
    private function formDetail($idPengajar)
    {
    
        try
        {
        $biodata = Pengajar::where('pengajar.id',$idPengajar)
                        ->join('pegawai','pegawai.id','=','pengajar.id_pegawai')
                        ->join('biodata as b', 'pegawai.id_biodata', '=', 'b.id')
                        ->leftJoin('warga_pesantren as wp', 'b.id', '=', 'wp.id_biodata')
                        ->leftJoin('berkas as br', 'b.id', '=', 'br.id_biodata')
                        ->leftJoin('keluarga as k', 'b.id', '=', 'k.id_biodata')
                        ->leftJoin('kecamatan as kc', 'b.id_kecamatan', '=', 'kc.id')
                        ->leftJoin('kabupaten as kb', 'b.id_kabupaten', '=', 'kb.id')
                        ->leftJoin('provinsi as pv', 'b.id_provinsi', '=', 'pv.id')
                        ->leftJoin('negara as ng', 'b.id_negara', '=', 'ng.id')
                        ->where('pengajar.status',1)
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
        //  DATA KELUARGA (Jika Ada)

        $keluarga = Pengajar::where('pengajar.id', $idPengajar)
            ->join('pegawai','pegawai.id','=','pengajar.id_pegawai')
            ->join('biodata as b_anak', 'pegawai.id_biodata', '=', 'b_anak.id')
            ->join('peserta_didik as pd','b_anak.id','pd.id_biodata')
            ->join('pelajar as p', 'pd.id', '=', 'p.id_peserta_didik')
            ->join('keluarga as k_anak', 'b_anak.id', '=', 'k_anak.id_biodata')
            ->leftJoin('keluarga as k_ortu', 'k_anak.no_kk', '=', 'k_ortu.no_kk')
            ->join('orang_tua_wali', 'k_ortu.id_biodata', '=', 'orang_tua_wali.id_biodata')
            ->join('biodata as b_ortu', 'orang_tua_wali.id_biodata', '=', 'b_ortu.id')
            ->join('hubungan_keluarga', 'orang_tua_wali.id_hubungan_keluarga', '=', 'hubungan_keluarga.id')
            ->where('pengajar.status', 1)
            ->select(
                'b_ortu.nama',
                'b_ortu.nik',
                DB::raw("'Orang Tua' as hubungan"),
                'hubungan_keluarga.nama_status',
                'orang_tua_wali.wali'
            )
            ->get();

        // Ambil data saudara kandung (peserta didik lain dalam KK yang sama, tetapi bukan orang tua/wali)
        $saudara = DB::table('keluarga as k_saudara')
            ->join('biodata as b_saudara', 'k_saudara.id_biodata', '=', 'b_saudara.id')
            ->where('k_saudara.no_kk', function ($query) use ($idPengajar) {
                $query->select('k_anak.no_kk')
                    ->from('peserta_didik as pd')
                    ->join('biodata as b_anak', 'pd.id_biodata', '=', 'b_anak.id')
                    ->join('keluarga as k_anak', 'b_anak.id', '=', 'k_anak.id_biodata')
                    ->where('pd.id', $idPengajar)
                    ->limit(1);
            })
            ->whereNotIn('k_saudara.id_biodata', function ($query) {
                $query->select('id_biodata')->from('orang_tua_wali');
            })
            ->whereNotIn('k_saudara.id_biodata', function ($query) use ($idPengajar) {
                $query->select('id_biodata')
                    ->from('peserta_didik')
                    ->where('id', $idPengajar);
            })
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
                    "wali"   => $item->wali  ?? '-',
                ];
            });
        }
               //  STATUS SANTRI (Jika Ada)

        $santri = Pengajar::where('pengajar.id', $idPengajar)
                                ->join('pegawai','pegawai.id','=','pengajar.id_pegawai')
                                ->join('biodata as b', 'pegawai.id_biodata', '=', 'b.id')
                                ->leftJoin('peserta_didik as pd', 'pd.id_biodata', '=', 'b.id') 
                                ->join('santri as s', 's.id_peserta_didik', '=', 'pd.id')
                                ->where('pengajar.status', 1)
                                ->select(
                                    's.nis',
                                    's.tanggal_masuk_santri',
                                    's.tanggal_keluar_santri'
                                )
                                ->get();
                
                            if ($santri->isNotEmpty()) {
                                $data['Santri'] = $santri->map(function ($item) {
                                    return [
                                        'Nis'           => $item->nis,
                                        'Tanggal_Mulai' => $item->tanggal_masuk_santri,
                                        'Tanggal_Akhir' => $item->tanggal_keluar_santri ?? "-",
                                    ];
                                });
                            }

        //  DOMISILI (Jika Ada)**
        
        $domisili = Pengajar::where('pengajar.id', $idPengajar)
                            ->join('pegawai','pegawai.id','=','pengajar.id_pegawai')
                            ->join('biodata as b', 'pegawai.id_biodata', '=', 'b.id')
                            ->leftJoin('peserta_didik as pd', 'pd.id_biodata', '=', 'b.id') 
                            ->join('santri as s', 's.id_peserta_didik', '=', 'pd.id')
                            ->join('domisili_santri as ds', 'ds.id_santri', '=', 's.id')
                            ->join('wilayah as w', 'ds.id_wilayah', '=', 'w.id')
                            ->join('blok as bl', 'ds.id_blok', '=', 'bl.id')
                            ->join('kamar as km', 'ds.id_kamar', '=', 'km.id')
                            ->select(
                                'km.nama_kamar',
                                'bl.nama_blok',
                                'w.nama_wilayah',
                                'ds.tanggal_masuk',
                                'ds.tanggal_keluar'
                            )->distinct()
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

        // WALI ASUH (Jika Ada)

        $kewaliasuhan = Pengajar::where('pengajar.id', $idPengajar)
                            ->join('pegawai','pegawai.id','=','pengajar.id_pegawai')
                            ->join('biodata as b', 'pegawai.id_biodata', '=', 'b.id')
                            ->leftJoin('peserta_didik as pd', 'pd.id_biodata', '=', 'b.id') 
                            ->join('santri as s', 's.id_peserta_didik', '=', 'pd.id')
                            ->join('pelajar as p', 'p.id_peserta_didik', '=', 'pd.id')
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
                            ->havingRaw('relasi_santri IS NOT NULL') // Filter untuk menghindari hasil NULL
                            ->select(
                                'grup_wali_asuh.nama_grup',
                                DB::raw("CASE 
                                 WHEN wali_asuh.id IS NOT NULL THEN 'Wali Asuh'
                                 WHEN anak_asuh.id IS NOT NULL THEN 'Anak Asuh'
                                 ELSE 'Bukan Wali Asuh atau Anak Asuh'
                             END as status_santri"),
                                DB::raw("CASE 
                                 WHEN wali_asuh.id IS NOT NULL THEN GROUP_CONCAT(DISTINCT bio_anak.nama SEPARATOR ', ')
                                 WHEN anak_asuh.id IS NOT NULL THEN GROUP_CONCAT(DISTINCT bio_wali.nama SEPARATOR ', ')
                                 ELSE NULL
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
        
        //  PENDIDIKAN (Jika Ada)

        $pelajar = Pengajar::where('pengajar.id', $idPengajar)
                        ->join('pegawai','pegawai.id','=','pengajar.id_pegawai')
                        ->join('biodata as b','b.id','=','pegawai.id_biodata')
                        ->join('peserta_didik as pd','b.id','pd.id_biodata')
                        ->join('pelajar as p', 'p.id_peserta_didik', '=', 'pd.id')
                        ->join('pendidikan_pelajar as pp', 'pp.id_pelajar', '=', 'p.id')
                        ->join('lembaga as l', 'pp.id_lembaga', '=', 'l.id')
                        ->leftJoin('jurusan as j', 'pp.id_jurusan', '=', 'j.id')
                        ->leftJoin('kelas as k', 'pp.id_kelas', '=', 'k.id')
                        ->leftJoin('rombel as r', 'pp.id_rombel', '=', 'r.id')
                        ->where('pengajar.status', 1)
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
                                'tahun_lulus'  => $item->tanggal_keluar_pelajar ?? "Masih Aktif",
                            ];
                        });
                    }
        //  Pengajar

        $pengajar = Pengajar::where('pengajar.id', $idPengajar)
                        ->join('pegawai','pegawai.id','=','pengajar.id_pegawai')
                        ->leftJoin('lembaga','lembaga.id','=','pegawai.id_lembaga')
                        ->join('biodata', 'pegawai.id_biodata', '=', 'biodata.id')
                        ->leftJoin('golongan','golongan.id','=','pengajar.id_golongan')
                        ->leftJoin('kategori_golongan','kategori_golongan.id','=','golongan.id_kategori_golongan')
                        ->leftJoin('materi_ajar','materi_ajar.id_pengajar','=','pengajar.id')
                        ->select(
                            'lembaga.nama_lembaga',
                            'pengajar.jabatan as PekerjaanKontrak',
                            'kategori_golongan.nama_kategori_golongan',
                            'golongan.nama_golongan',
                            DB::raw("
                                CONCAT(
                                    'Sejak ', DATE_FORMAT(pengajar.tahun_masuk, '%e %M %Y %H:%i:%s'),
                                    ' sampai ',
                                    IFNULL(DATE_FORMAT(pengajar.tahun_keluar, '%e %M %Y %H:%i:%s'), 'saat ini')
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
                            'pengajar.tahun_keluar'
                        )
                        ->first();
        if ($pengajar) {
            $data['pengajar'] = [
                "nama_lembaga" => $pengajar->nama_lembaga, // BENAR
                "PekerjaanKontrak" => $pengajar->PekerjaanKontrak,
                "kategori_golongan" => $pengajar->nama_kategori_golongan,
                "golongan" => $pengajar->nama_golongan,
                "keterangan" => $pengajar->keterangan,
                "total_waktu_materi" => $pengajar->total_waktu_materi, // Harus sesuai dengan nama di SELECT
                "total_materi" => $pengajar->total_materi, // Harus sesuai dengan nama di SELECT
                ];
        }

        // Warga Pesantren (Jika Ada)
        $Wargapesantren = Pengajar::where('pengajar.id', $idPengajar)
                        ->join('pegawai','pegawai.id','=','pengajar.id_pegawai')
                        ->join('biodata', 'pegawai.id_biodata', '=', 'biodata.id') 
                        ->leftJoin('peserta_didik', 'peserta_didik.id_biodata', '=', 'biodata.id') 
                        ->leftjoin('warga_pesantren as wp','wp.id_biodata','biodata.id')
                        ->select(
                            'wp.niup',
                            DB::raw("
                            CASE 
                                WHEN wp.status = 1 THEN 'Iya'
                                ELSE 'Tidak'
                            END AS aktif
                        ")
                        )->first();
        if ($Wargapesantren) { 
            $data['WargaPesantren'] = [
                "niup" => $Wargapesantren->niup,
                "aktif" => $Wargapesantren->aktif
            ];
        }
                     // catatan afektif (Jika ada)
        $catatanAfektif = Pengajar::where('pengajar.id',$idPengajar)
                        ->join('pegawai','pegawai.id','=','pengajar.id_pegawai')
                        ->join('biodata','biodata.id','pegawai.id_biodata')
                        ->join('peserta_didik as pd','biodata.id','pd.id_biodata')
                        ->join('santri as s', 's.id_peserta_didik', '=', 'pd.id')
                        ->join('pelajar as p', 'p.id_peserta_didik', '=', 'pd.id')
                        ->join('catatan_afektif as ca', 's.id', '=', 'ca.id_santri')
                        ->where('pd.status', true)
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
        
                    if ($catatanAfektif) {
                        $data['Catatan_Progress']['afektif'] = [
                            'Keterangan' => [
                                'kebersihan'               => $catatanAfektif->kebersihan_nilai ?? "-",
                                'tindak_lanjut_kebersihan' => $catatanAfektif->kebersihan_tindak_lanjut ?? "-",
                                'kepedulian'               => $catatanAfektif->kepedulian_nilai ?? "-",
                                'tindak_lanjut_kepedulian' => $catatanAfektif->kepedulian_tindak_lanjut ?? "-",
                                'akhlak'                   => $catatanAfektif->akhlak_nilai ?? "-",
                                'tindak_lanjut_akhlak'     => $catatanAfektif->akhlak_tindak_lanjut ?? "-",
                            ]
                        ];
                    }
                        // catatan kognitif (jika ada)
        $catatanKognitif = Pengajar::where('pengajar.id',$idPengajar)
                        ->join('pegawai','pegawai.id','=','pengajar.id_pegawai')
                        ->join('biodata','biodata.id','pegawai.id_biodata')
                        ->join('peserta_didik as pd','biodata.id','pd.id_biodata')
                        ->join('santri as s', 's.id_peserta_didik', '=', 'pd.id')
                        ->join('pelajar as p', 'p.id_peserta_didik', '=', 'pd.id')
                        ->join('catatan_kognitif as ck', 's.id', '=', 'ck.id_santri')
                        ->where('pd.status', true)
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
        
                    if ($catatanKognitif) {
                        $data['Catatan_Progress']['Kognitif'] = [
                            'Keterangan' => [
                                'kebahasaan'                      => $catatanKognitif->kebahasaan_nilai ?? "-",
                                'tindak_lanjut_kebahasaan'        => $catatanKognitif->kebahasaan_tindak_lanjut ?? "-",
                                'baca_kitab_kuning'               => $catatanKognitif->baca_kitab_kuning_nilai ?? "-",
                                'tindak_lanjut_baca_kitab_kuning' => $catatanKognitif->baca_kitab_kuning_tindak_lanjut ?? "-",
                                'hafalan_tahfidz'                 => $catatanKognitif->hafalan_tahfidz_nilai ?? "-",
                                'tindak_lanjut_hafalan_tahfidz'   => $catatanKognitif->hafalan_tahfidz_tindak_lanjut ?? "-",
                                'furudul_ainiyah'                 => $catatanKognitif->furudul_ainiyah_nilai ?? "-",
                                'tindak_lanjut_furudul_ainiyah'   => $catatanKognitif->furudul_ainiyah_tindak_lanjut ?? "-",
                                'tulis_alquran'                   => $catatanKognitif->tulis_alquran_nilai ?? "-",
                                'tindak_lanjut_tulis_alquran'     => $catatanKognitif->tulis_alquran_tindak_lanjut ?? "-",
                                'baca_alquran'                    => $catatanKognitif->baca_alquran_nilai ?? "-",
                                'tindak_lanjut_baca_alquran'      => $catatanKognitif->baca_alquran_tindak_lanjut ?? "-",
                            ]
                        ];
                    }
            // Data Kunjungan Mahrom (jika aada)
            $pengunjung = DB::table('pengunjung_mahrom')
                ->join('santri as s', 'pengunjung_mahrom.id_santri', '=', 's.id')
                ->join('peserta_didik as pd', 's.id_peserta_didik', '=', 'pd.id')
                ->join('biodata','biodata.id','pd.id_biodata')
                ->join('pegawai','biodata.id','pegawai.id_biodata')
                ->join('pengajar','pengajar.id_pegawai','=','pegawai.id')
                ->where('pengajar.id', $idPengajar)
                ->where('pd.status', true)
                ->select(
                    'pengunjung_mahrom.nama_pengunjung',
                    'pengunjung_mahrom.tanggal'
                )
                ->get();

            if ($pengunjung->isNotEmpty()) {
                $data['Kunjungan_Mahrom']['Di_kunjungi_oleh'] = $pengunjung->map(function ($item) {
                    return [
                        'Nama'    => $item->nama_pengunjung,
                        'Tanggal' => Carbon::parse($item->tanggal)->format('d-m-Y'),
                    ];
                });
            }
        return $data;
    }catch (\Exception $e) {
        Log::error("Error in formDetailPelajar: " . $e->getMessage());
        return ['error' => 'Terjadi kesalahan pada server'];
    }
    }
         // **Mengambil Data Pengajar ( Detail)**
         public function getPengajar($idPengajar)
         {
         // Validasi bahwa ID adalah UUID
         if (!Str::isUuid($idPengajar)) {
            return response()->json(['error' => 'ID tidak valid'], 400);
        }

        try {
            // Cari data peserta didik berdasarkan UUID
            $pengajar = Pengajar::find($idPengajar);
            if (!$pengajar) {
                return response()->json(['error' => 'Data tidak ditemukan'], 404);
            }

            // Ambil detail peserta didik dari fungsi helper
            $data = $this->formDetail($pengajar->id);
            if (empty($data)) {
                return response()->json(['error' => 'Data Kosong'], 200);
            }

            return response()->json($data, 200);
        } catch (\Exception $e) {
            Log::error("Error in getDetailPelajar: " . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan pada server'], 500);
        }
         }
}
