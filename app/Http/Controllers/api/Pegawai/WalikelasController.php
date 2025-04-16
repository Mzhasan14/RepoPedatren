<?php

namespace App\Http\Controllers\Api\Pegawai;

use App\Http\Controllers\api\FilterController;
use App\Http\Controllers\Controller;
use App\Http\Resources\PdResource;
use App\Models\Pegawai\WaliKelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class WalikelasController extends Controller
{
    protected $filterController;
    protected $filter;

    public function __construct()
    {
        // Inisialisasi controller filter
        $this->filterController = new FilterController();
        $this->filter = new FilterKepegawaianController();
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $walikelas = WaliKelas::all();
        return new PdResource(true,'Data berhasil ditampilkan',$walikelas);
    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'id_pengajar'  => 'required|integer',
            'id_rombel'    => 'required|integer',
            'jumlah_murid' => 'required|string|min:1',
            'created_by'   => 'required|integer',
            'status'       => 'required|boolean',
        ]);

        if ($validator->fails()){
            return response()->json([
                'status' => false,
                'message' => 'Data Gagal ditambahkan',
                'data'=> $validator->errors()
            ]);
        }
        $walikelas = WaliKelas::create($validator->validated());
        return new PdResource(true,'Data berhasil ditambahkan',$walikelas);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $walikelas = WaliKelas::findOrFail($id);
        return new PdResource(true,'Data berhasil ditampilkan',$walikelas);
    }
    public function update(Request $request, string $id)
    {
        $walikelas = WaliKelas::findOrFail($id);
        $validator = Validator::make($request->all(),[
            'id_pengajar'  => 'required|integer',
            'id_rombel'    => 'required|integer',
            'jumlah_murid' => 'required|string|min:1',
            'updated_by'   => 'nullable|integer',
            'status'       => 'required|boolean',
        ]);

        if ($validator->fails()){
            return response()->json([
                'status' => false,
                'message' => 'Data Gagal ditambahkan',
                'data'=> $validator->errors()
            ]);
        }
        $walikelas->update($validator->validated());
        return new PdResource(true,'Data berhasil diupdate',$walikelas);
    }


    public function destroy(string $id)
    {
        $walikelas = WaliKelas::findOrFail($id);
        $walikelas->delete();
        return new PdResource(true,'Data berhasil dihapus',$walikelas);
    }
    public function dataWalikelas(Request $request)
    {
    try
    {
        $query = WaliKelas::Active()
                            ->join('pengajar','pengajar.id','=','wali_kelas.id_pengajar')
                            ->join('pegawai','pegawai.id','=','pengajar.id_pegawai')
                            ->join('biodata as b','b.id','=','pegawai.id_biodata')  
                            ->leftJoin('kabupaten as kb','kb.id','b.id_kabupaten')
                            ->leftJoin('peserta_didik as pd','b.id','pd.id_biodata')
                            ->leftJoin('santri as s','pd.id','s.id_peserta_didik')
                            ->leftJoin('domisili_santri as ds','s.id','ds.id_santri')
                            ->leftJoin('wilayah as w','ds.id_wilayah','w.id')
                            ->leftJoin('warga_pesantren as wp','b.id','wp.id_biodata')
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
                            ->leftJoin('rombel as r','r.id','=','pegawai.id_rombel')
                            ->leftJoin('kelas as k','k.id','=','pegawai.id_kelas')
                            ->leftJoin('jurusan as j','j.id','=','pegawai.id_jurusan')
                            ->leftJoin('lembaga as l','l.id','=','pegawai.id_lembaga')
                            ->select(
                                'wali_kelas.id as id',
                                'b.nama',
                                'wp.niup',
                                DB::raw("COALESCE(b.nik, b.no_passport) as identitas"),
                                'b.jenis_kelamin',
                                'l.nama_lembaga',
                                'k.nama_kelas',
                                'r.gender_rombel',
                                DB::raw("CONCAT(wali_kelas.jumlah_murid, ' pelajar') as jumlah_murid"),
                                'r.nama_rombel',
                                DB::raw("DATE_FORMAT(wali_kelas.updated_at, '%Y-%m-%d %H:%i:%s') AS tgl_update"),
                                DB::raw("DATE_FORMAT(wali_kelas.created_at, '%Y-%m-%d %H:%i:%s') AS tgl_input"),
                                DB::raw("COALESCE(MAX(br.file_path), 'default.jpg') as foto_profil")
                            )->groupBy(
                                'wali_kelas.id', 
                                'b.nama', 
                                'wp.niup', 
                                'l.nama_lembaga', 
                                'k.nama_kelas', 
                                'r.nama_rombel',
                                'b.nik',
                                'b.no_passport',
                                'r.gender_rombel',
                                'b.jenis_kelamin',
                                'wali_kelas.jumlah_murid',
                                'wali_kelas.updated_at',
                                'wali_kelas.created_at',
                            );
                                
        $query = $this->filterController->applyCommonFilters($query, $request);
        $query = $this->filter->applySearchFilter($query, $request);
        $query = $this->filter->applyLembagaFilter($query, $request);
        $query = $this->filter->applyGerderRombelFilter($query, $request);
        $query = $this->filter->applyPhoneFilter($query, $request);


        // Ambil jumlah data per halaman (default 10 jika tidak diisi)
        $perPage = $request->input('limit', 25);

        // Ambil halaman saat ini (jika ada)
        $currentPage = $request->input('page', 1);

        // Menerapkan pagination ke hasil
        $hasil = $query->paginate($perPage, ['*'], 'page', $currentPage);


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
                    "NIK/No.Passport" => $item->identitas,
                    "JenisKelamin" => $item->jenis_kelamin,
                    "lembaga" => $item->nama_lembaga,
                    "kelas" => $item->nama_kelas,
                    "GenderRombel" => $item->gender_rombel,
                    "JumlahMurid" => $item->jumlah_murid,
                    "rombel" => $item->nama_rombel,
                    "tgl_update" => $item->tgl_update,
                    "tgl_input" => $item->tgl_input,
                    "foto_profil" => url($item->foto_profil)
                ];
            })
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Terjadi kesalahan pada server',
            'error' => $e->getMessage(),
        ], 500);
    }
    }
    private function formDetail($idWalikelas)
    {
        try{
        $biodata = WaliKelas::where('wali_kelas.id',$idWalikelas)
                        ->join('pengajar','pengajar.id','=','wali_kelas.id_pengajar')
                        ->join('pegawai','pegawai.id','=','pengajar.id_pegawai')
                        ->join('biodata as b', 'pegawai.id_biodata', '=', 'b.id')
                        ->leftJoin('warga_pesantren as wp', 'b.id', '=', 'wp.id_biodata')
                        ->leftJoin('berkas as br', 'b.id', '=', 'br.id_biodata')
                        ->leftJoin('keluarga as k', 'b.id', '=', 'k.id_biodata')
                        ->leftJoin('kecamatan as kc', 'b.id_kecamatan', '=', 'kc.id')
                        ->leftJoin('kabupaten as kb', 'b.id_kabupaten', '=', 'kb.id')
                        ->leftJoin('provinsi as pv', 'b.id_provinsi', '=', 'pv.id')
                        ->leftJoin('negara as ng', 'b.id_negara', '=', 'ng.id')
                        ->where('wali_kelas.status',1)
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

        $keluarga = WaliKelas::where('wali_kelas.id',$idWalikelas)
            ->join('pengajar','pengajar.id','=','wali_kelas.id_pengajar')
            ->join('pegawai','pegawai.id','=','pengajar.id_pegawai')
            ->join('biodata as b_anak', 'pegawai.id_biodata', '=', 'b_anak.id')
            ->join('peserta_didik as pd','b_anak.id','pd.id_biodata')
            ->join('pelajar as p', 'pd.id', '=', 'p.id_peserta_didik')
            ->join('keluarga as k_anak', 'b_anak.id', '=', 'k_anak.id_biodata')
            ->leftJoin('keluarga as k_ortu', 'k_anak.no_kk', '=', 'k_ortu.no_kk')
            ->join('orang_tua_wali', 'k_ortu.id_biodata', '=', 'orang_tua_wali.id_biodata')
            ->join('biodata as b_ortu', 'orang_tua_wali.id_biodata', '=', 'b_ortu.id')
            ->join('hubungan_keluarga', 'orang_tua_wali.id_hubungan_keluarga', '=', 'hubungan_keluarga.id')
            ->where('wali_kelas.status', 1)
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
            ->where('k_saudara.no_kk', function ($query) use ($idWalikelas) {
                $query->select('k_anak.no_kk')
                    ->from('peserta_didik as pd')
                    ->join('biodata as b_anak', 'pd.id_biodata', '=', 'b_anak.id')
                    ->join('keluarga as k_anak', 'b_anak.id', '=', 'k_anak.id_biodata')
                    ->where('pd.id', $idWalikelas)
                    ->limit(1);
            })
            ->whereNotIn('k_saudara.id_biodata', function ($query) {
                $query->select('id_biodata')->from('orang_tua_wali');
            })
            ->whereNotIn('k_saudara.id_biodata', function ($query) use ($idWalikelas) {
                $query->select('id_biodata')
                    ->from('peserta_didik')
                    ->where('id', $idWalikelas);
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
               // STATUS SANTRI (Jika Ada)

        $santri = WaliKelas::where('wali_kelas.id',$idWalikelas)
                                ->join('pengajar','pengajar.id','=','wali_kelas.id_pengajar')
                                ->join('pegawai','pegawai.id','=','pengajar.id_pegawai')
                                ->join('biodata as b', 'pegawai.id_biodata', '=', 'b.id')
                                ->leftJoin('peserta_didik as pd', 'pd.id_biodata', '=', 'b.id') 
                                ->join('santri as s', 's.id_peserta_didik', '=', 'pd.id')
                                ->where('wali_kelas.status', 1)
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

        // DOMISILI (Jika Ada)

        $domisili = WaliKelas::where('wali_kelas.id',$idWalikelas)
                            ->join('pengajar','pengajar.id','=','wali_kelas.id_pengajar')
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

        //  WALI ASUH (Jika Ada)

        $kewaliasuhan = WaliKelas::where('wali_kelas.id',$idWalikelas)
                            ->join('pengajar','pengajar.id','=','wali_kelas.id_pengajar')
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

        $pelajar = WaliKelas::where('wali_kelas.id',$idWalikelas)
                        ->join('pengajar','pengajar.id','=','wali_kelas.id_pengajar')
                        ->join('pegawai','pegawai.id','=','pengajar.id_pegawai')
                        ->join('biodata as b','b.id','=','pegawai.id_biodata')
                        ->join('peserta_didik as pd','b.id','pd.id_biodata')
                        ->join('pelajar as p', 'p.id_peserta_didik', '=', 'pd.id')
                        ->join('pendidikan_pelajar as pp', 'pp.id_pelajar', '=', 'p.id')
                        ->join('lembaga as l', 'pp.id_lembaga', '=', 'l.id')
                        ->leftJoin('jurusan as j', 'pp.id_jurusan', '=', 'j.id')
                        ->leftJoin('kelas as k', 'pp.id_kelas', '=', 'k.id')
                        ->leftJoin('rombel as r', 'pp.id_rombel', '=', 'r.id')
                        ->where('wali_kelas.status', 1)
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

        // // **6. Pengajar**

        $pengajar = WaliKelas::where('wali_kelas.id',$idWalikelas)
                        ->join('pengajar','pengajar.id','=','wali_kelas.id_pengajar')
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

        // // **6. Warga Pesantren (Jika Ada)**
        $Wargapesantren = WaliKelas::where('wali_kelas.id',$idWalikelas)
                        ->join('pengajar','pengajar.id','=','wali_kelas.id_pengajar')
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

        return $data;
    }catch (\Exception $e) {
        Log::error("Error in formDetailPelajar: " . $e->getMessage());
        return ['error' => 'Terjadi kesalahan pada server'];
    }
    }
         // **Mengambil Data Pengajar ( Detail)**
         public function getWalikelas($id)
         {
            // Validasi bahwa ID adalah UUID
            if (!Str::isUuid($id)) {
                return response()->json(['error' => 'ID tidak valid'], 400);
            }

            try {
                // Cari data peserta didik berdasarkan UUID
                $walikelas = WaliKelas::find($id);
                if (!$walikelas) {
                    return response()->json(['error' => 'Data tidak ditemukan'], 404);
                }

                // Ambil detail peserta didik dari fungsi helper
                $data = $this->formDetail($walikelas->id);
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