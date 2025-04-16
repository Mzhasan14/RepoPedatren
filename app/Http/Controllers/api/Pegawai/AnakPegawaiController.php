<?php

namespace App\Http\Controllers\Api\Pegawai;

use App\Http\Controllers\api\FilterController;
use App\Http\Controllers\Controller;
use App\Http\Resources\PdResource;
use App\Models\JenisBerkas;
use App\Models\Kewilayahan\Kamar;
use App\Models\Pegawai\AnakPegawai;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;

class AnakPegawaiController extends Controller
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
        $anakPegawai = AnakPegawai::all();
        return new PdResource(true,'List data Anak pegawai',$anakPegawai);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),
        [
            'id_peserta_didik' => 'required|exists:peserta_didik,id',
            'id_pegawai' => 'required|exists:pegawai,id',
            'status' => 'nullable|boolean',
            'created_by' => 'required|exists:users,id',
        ]);

        if ($validator->fails()){
            return response()->json([
                'status' => false,
                'message' =>'Data gagal ditambahkan',
                'data' => $validator->errors()
            ]);
        }

        $anakPegawai = AnakPegawai::create($validator->validated());
        return new PdResource(true,'data berhasil ditambahkan',$anakPegawai);
    }

    public function show(string $id)
    {
        $anakPegawai = AnakPegawai::findOrFail($id);
        return new PdResource(true,'data berhasil ditampilkan',$anakPegawai);

    }

    public function update(Request $request, string $id)
    {
        $anakPegawai = AnakPegawai::findOrFail($id);
        $validator = Validator::make($request->all(),
        [
            'id_peserta_didik' => 'required|exists:peserta_didik,id',
            'id_pegawai' => 'required|exists:pegawai,id',
            'status' => 'required|boolean',
            'updated_by' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()){
            return response()->json([
                'status' => false,
                'message' =>'Data gagal ditambahkan',
                'data' => $validator->errors()
            ]);
        }
        $anakPegawai->update($validator->validated());
        return new PdResource(true,'data berhasil diupdate',$anakPegawai);

    }
    public function destroy(string $id)
    {
        $anakPegawai = AnakPegawai::findOrFail($id);
        $anakPegawai->delete();
        return new PdResource(true,'data berhasil dihapus',$anakPegawai);

    }
    public function getAllAnakpegawai(Request $request)
    {
        try {
            $query = AnakPegawai::active()
                ->join('pegawai as pg', 'pg.id', '=', 'anak_pegawai.id_pegawai')
                ->leftJoin('peserta_didik as pd', 'pd.id', '=', 'anak_pegawai.id_peserta_didik')
                ->leftJoin('biodata as b', 'b.id', '=', 'pd.id_biodata')
                ->leftJoin('pelajar as pl', 'pl.id_peserta_didik', '=', 'pd.id')
                ->leftJoin('pendidikan_pelajar as pp', 'pp.id_pelajar', '=', 'pl.id')
                ->leftJoin('lembaga as l', 'pp.id_lembaga', '=', 'l.id')
                ->leftJoin('jurusan as j', 'pp.id_jurusan', '=', 'j.id')
                ->leftJoin('kelas as k', 'pp.id_kelas', '=', 'k.id')
                ->leftJoin('rombel as r', 'pp.id_rombel', '=', 'r.id')
                ->leftJoin('santri as s', 's.id_peserta_didik', '=', 'pd.id')
                ->leftJoin('domisili_santri as ds', 'ds.id_santri', '=', 's.id')
                ->leftJoin('wilayah as w', 'ds.id_wilayah', '=', 'w.id')
                ->leftJoin('blok as bl', 'ds.id_blok', '=', 'bl.id')
                ->leftJoin('kamar as km', 'ds.id_kamar', '=', 'km.id')
                ->leftJoin('kabupaten as kb', 'kb.id', '=', 'b.id_kabupaten')
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
                        ->where('br.id_jenis_berkas', function ($query) {
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
                ->select([
                    'anak_pegawai.id',
                    'b.nama',
                    'wp.niup',
                    's.nis',
                    DB::raw("COALESCE(b.nik, b.no_passport) as identitas"),
                    'kb.nama_kabupaten as asal_kota',
                    'l.nama_lembaga',
                    'j.nama_jurusan',
                    'k.nama_kelas',
                    'r.nama_rombel',
                    'w.nama_wilayah',
                    'bl.nama_blok',
                    'km.nama_kamar',
                    'anak_pegawai.created_at',
                    'anak_pegawai.updated_at',
                    DB::raw("COALESCE(MAX(br.file_path), 'default.jpg') as foto_profil")
                ])
                ->groupBy([
                    'anak_pegawai.id',
                    'b.nama',
                    'wp.niup',
                    's.nis',
                    'b.nik',
                    'b.no_passport',
                    'kb.nama_kabupaten',
                    'l.nama_lembaga',
                    'j.nama_jurusan',
                    'k.nama_kelas',
                    'r.nama_rombel',
                    'w.nama_wilayah',
                    'bl.nama_blok',
                    'km.nama_kamar',
                    'anak_pegawai.created_at',
                    'anak_pegawai.updated_at',
                    'br.file_path'
                ]);
     // Filter Umum (Alamat dan Jenis Kelamin)
        $query = $this->filterController->applyCommonFilters($query, $request);

        $query = $this->filter->applySearchFilter($query, $request);
        $query = $this->filter->applyWilayahFilter($query, $request);
        $query = $this->filter->applyLembagaFilter($query, $request);
        $query = $this->filter->applyWargaPesantrenFilter($query, $request);
        $query = $this->filter->applyStatusFilter($query, $request);
        $query = $this->filter->applyAngkatanFilter($query, $request);
        $query = $this->filter->applyPhoneFilter($query, $request);
        $query = $this->filter->applySortFilter($query, $request);
        $query = $this->filter->applyPemberkasanFilter($query, $request);

            $perPage = $request->input('limit', 25);
            $currentPage = $request->input('page', 1);
            $results = $query->paginate($perPage, ['*'], 'page', $currentPage);
    
            if ($results->isEmpty()) {
                return response()->json([
                    'status'  => 'success',
                    'message' => 'Data kosong',
                    'data'    => []
                ]);
            }
    
            $formatted = $results->map(function ($item) {
                return [
                    'id' => $item->id,
                    'nama' => $item->nama,
                    'niup' => $item->niup ?? '-',
                    'nis' => $item->nis ?? '-',
                    'identitas' => $item->identitas,
                    'lembaga' => $item->nama_lembaga ?? '-',
                    'jurusan' => $item->nama_jurusan ?? '-',
                    'kelas' => $item->nama_kelas ?? '-',
                    'rombel' => $item->nama_rombel ?? '-',
                    'wilayah' => $item->nama_wilayah ?? '-',
                    'blok' => $item->nama_blok ?? '-',
                    'kamar' => $item->nama_kamar ?? '-',
                    'asal_kota' => $item->asal_kota,
                    'tgl_input' => Carbon::parse($item->created_at)->translatedFormat('d F Y H:i:s'),
                    'tgl_update' => Carbon::parse($item->updated_at)->translatedFormat('d F Y H:i:s'),
                    'foto_profil' => url($item->foto_profil)
                ];
            });
    
            return response()->json([
                'total_data'   => $results->total(),
                'current_page' => $results->currentPage(),
                'per_page'     => $results->perPage(),
                'total_pages'  => $results->lastPage(),
                'data'         => $formatted
            ]);
        } catch (\Exception $e) {
            Log::error("Error in getAllAnakpegawai: " . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
    
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan pada server.'
            ], 500);
        }
    }
    
    
    // public function getAllAnakpegawai(Request $request)
    // {
    //     $query = AnakPegawai::Active()
    //     ->leftJoin('peserta_didik','peserta_didik.id','=','anak_pegawai.id_peserta_didik')
    //     ->leftJoin('pelajar','pelajar.id_peserta_didik','peserta_didik.id')
    //     ->join('biodata','biodata.id','peserta_didik.id_biodata')
    //     ->leftJoin('warga_pesantren as wp','biodata.id','=','wp.id_biodata')
    //     ->leftJoin('kabupaten','kabupaten.id','biodata.id_kabupaten')
    //     ->leftJoin('pendidikan_pelajar','pendidikan_pelajar.id_pelajar','=','pelajar.id')
    //     ->leftJoin('lembaga','lembaga.id','=','pendidikan_pelajar.id_lembaga')
    //     ->leftJoin('jurusan','jurusan.id','=','pendidikan_pelajar.id_jurusan')
    //     ->leftJoin('kelas','kelas.id','=','pendidikan_pelajar.id_kelas')
    //     ->leftJoin('rombel','rombel.id','=','pendidikan_pelajar.id_rombel')
    //     ->leftJoin('pegawai','pegawai.id','=','anak_pegawai.id_pegawai')
    //     ->leftJoin('santri', 'peserta_didik.id', '=', 'santri.id_peserta_didik')
    //     ->leftJoin('berkas', 'berkas.id_biodata', '=', 'biodata.id')
    //     ->leftJoin('jenis_berkas', 'berkas.id_jenis_berkas', '=', 'jenis_berkas.id')
    //     ->leftJoin('domisili_santri','domisili_santri.id_santri','santri.id')
    //     ->leftjoin('wilayah', 'domisili_santri.id_wilayah', '=', 'wilayah.id')
    //     ->leftjoin('blok', 'domisili_santri.id_blok', '=', 'blok.id')
    //     ->leftjoin('kamar', 'domisili_santri.id_kamar', '=', 'kamar.id')
    //                         ->select(
    //                             'anak_pegawai.id',
    //                             'biodata.nama',
    //                             'wp.niup',
    //                             'santri.nis',
    //                             DB::raw("COALESCE(biodata.nik, biodata.no_passport) as identitas"),
    //                             'kabupaten.nama_kabupaten',
    //                             DB::raw("GROUP_CONCAT(DISTINCT lembaga.nama_lembaga SEPARATOR ', ') as lembaga"),
    //                             DB::raw("GROUP_CONCAT(DISTINCT jurusan.nama_jurusan SEPARATOR ', ') as jurusan"),
    //                             DB::raw("GROUP_CONCAT(DISTINCT kelas.nama_kelas SEPARATOR ', ') as kelas"),
    //                             'wilayah.nama_wilayah',
    //                             'blok.nama_blok',
    //                             'kamar.nama_kamar',
    //                             DB::raw("DATE_FORMAT(anak_pegawai.updated_at, '%Y-%m-%d %H:%i:%s') AS tgl_update"),
    //                             DB::raw("DATE_FORMAT(anak_pegawai.created_at, '%Y-%m-%d %H:%i:%s') AS tgl_input"),
    //                             DB::raw("COALESCE(MAX(berkas.file_path), 'default.jpg') as foto_profil")
    //                         )
    //                         ->groupBy(
    //                             'anak_pegawai.id', 
    //                             'biodata.nama', 
    //                             'wp.niup',
    //                             'santri.nis',
    //                             'biodata.nik', 
    //                             'biodata.no_passport',
    //                             'wilayah.nama_wilayah',
    //                             'blok.nama_blok',
    //                             'kamar.nama_kamar',
    //                             'kabupaten.nama_kabupaten',
    //                             'anak_pegawai.updated_at',
    //                             'anak_pegawai.created_at'
    //                         ); 
        
    //     // Filter Umum (Alamat dan Jenis Kelamin)
    //     $query = $this->filterController->applyCommonFilters($query, $request);

    //     // Filter Search
    //     if ($request->filled('search')) {
    //         $search = strtolower($request->search);
    
    //         $query->where(function ($q) use ($search) {
    //             $q->where('biodata.nik', 'LIKE', "%$search%")
    //                 ->orWhere('biodata.no_passport', 'LIKE', "%$search%")
    //                 ->orWhere('biodata.nama', 'LIKE', "%$search%")
    //                 ->orWhere('biodata.niup', 'LIKE', "%$search%")
    //                 ->orWhere('lembaga.nama_lembaga', 'LIKE', "%$search%")
    //                 ->orWhere('wilayah.nama_wilayah', 'LIKE', "%$search%")
    //                 ->orWhere('kabupaten.nama_kabupaten', 'LIKE', "%$search%")
    //                 ->orWhereDate('anak_pegawai.created_at', '=', $search) // Tgl Input
    //                 ->orWhereDate('anak_pegawai.updated_at', '=', $search); // Tgl Update
    //                 });
    //     }
    //     // Filter Wilayah
    //     if ($request->filled('wilayah')) {
    //         $wilayah = strtolower($request->wilayah);
    //         $query->where('wilayah.nama_wilayah', $wilayah);
    //         if ($request->filled('blok')) {
    //             $blok = strtolower($request->blok);
    //             $query->where('blok.nama_blok', $blok);
    //             if ($request->filled('kamar')) {
    //                 $kamar = strtolower($request->kamar);
    //                 $query->where('kamar.nama_kamar', $kamar);
    //             }
    //         }
    //     }

    //     // Filter Lembaga
    //     if ($request->filled('lembaga')) {
    //         $query->where('lembaga.nama_lembaga', strtolower($request->lembaga));
    //         if ($request->filled('jurusan')) {
    //             $query->where('jurusan.nama_jurusan', strtolower($request->jurusan));
    //             if ($request->filled('kelas')) {
    //                 $query->where('kelas.nama_kelas', strtolower($request->kelas));
    //                 if ($request->filled('rombel')) {
    //                     $query->where('rombel.nama_rombel', strtolower($request->rombel));
    //                 }
    //             }
    //         }
    //     }

    //     // Filter Status Warga Pesantren
    //     if ($request->filled('warga_pesantren')) {
    //         if (strtolower($request->warga_pesantren) === 'memiliki niup') {
    //             // Hanya tampilkan data yang memiliki NIUP
    //             $query->whereNotNull('biodata.niup')->where('biodata.niup', '!=', '');
    //         } elseif (strtolower($request->warga_pesantren) === 'tidak memiliki niup') {
    //             // Hanya tampilkan data yang tidak memiliki NIUP
    //             $query->whereNull('biodata.niup')->orWhereRaw("TRIM(biodata.niup) = ''");
    //         }
    //     }

    //     // Filter semua status
    //     if ($request->filled('semua_status')) {
    //         $entitas = strtolower($request->semua_status); 
            
    //         if ($entitas == 'pelajar') {
    //             $query->whereNotNull('pelajar.id'); 
    //         } elseif ($entitas == 'santri') {
    //             $query->whereNotNull('santri.id');
    //         } elseif ($entitas == 'pelajar dan santri') {
    //             $query->whereNotNull('pelajar.id')->whereNotNull('santri.id');
    //         }
    //     }


    //     // Filter Angkatan Pelajar
    //     if ($request->filled('angkatan_pelajar')) {
    //         $query->where('pelajar.angkatan', strtolower($request->angkatan_pelajar));
    //     }

    //     // Filter Angkatan Santri
    //     if ($request->filled('angkatan_santri')) {
    //         $query->where('santri.angkatan', strtolower($request->angkatan_santri));
    //     }

    //     // Filter No Telepon
    //     if ($request->filled('phone_number')) {
    //         if (strtolower($request->phone_number) === 'mempunyai') {
    //             // Hanya tampilkan data yang memiliki nomor telepon
    //             $query->whereNotNull('biodata.no_telepon')->where('biodata.no_telepon', '!=', '');
    //         } elseif (strtolower($request->phone_number) === 'tidak mempunyai') {
    //             // Hanya tampilkan data yang tidak memiliki nomor telepon
    //             $query->whereNull('biodata.no_telepon')->orWhere('biodata.no_telepon', '');
    //         }
    //     }

    //     // Filter Sort By
    //     if ($request->filled('sort_by')) {
    //         $sort_by = strtolower($request->sort_by);
    //         $allowedSorts = ['nama', 'niup', 'angkatan', 'jenis kelamin', 'tempat lahir'];
        
    //         if (in_array($sort_by, $allowedSorts)) { // Validasi hanya jika sort_by ada di daftar
    //             if ($sort_by === 'angkatan') {
    //                 $query->orderBy('pelajar.angkatan', 'asc'); // Pastikan tabelnya benar
    //             } else {
    //                 $query->orderBy($sort_by, 'asc');
    //             }
    //         }
    //     }

    //     // Filter Sort Order
    //     if ($request->filled('sort_order')) {
    //         $sortOrder = strtolower($request->sort_order) == 'desc' ? 'desc' : 'asc';
    //         $query->orderBy('anak_pegawai.id', $sortOrder);
    //     }

    //     // Filter Pemberkasan (Lengkap / Tidak Lengkap)
    //     if ($request->filled('pemberkasan')) {
    //         $jumlahBerkasWajib = JenisBerkas::where('wajib', 1)->count();
    //         $pemberkasan = strtolower($request->pemberkasan);
    //         if ($pemberkasan == 'lengkap') {
    //             $query->havingRaw('COUNT(DISTINCT berkas.id) >= ?', [$jumlahBerkasWajib]);
    //         } elseif ($pemberkasan == 'tidak lengkap') {
    //             $query->havingRaw('COUNT(DISTINCT berkas.id) < ?', [$jumlahBerkasWajib]);
    //         }
    //     }

    //     // Ambil jumlah data per halaman (default 10 jika tidak diisi)
    //     $perPage = $request->input('limit', 25);

    //     // Ambil halaman saat ini (jika ada)
    //     $currentPage = $request->input('page', 1);

    //     // Menerapkan pagination ke hasil
    //     $hasil = $query->paginate($perPage, ['*'], 'page', $currentPage);


    //     // Jika Data Kosong
    //     if ($hasil->isEmpty()) {
    //         return response()->json([
    //             "status" => "error",
    //             "message" => "Data tidak ditemukan",
    //             "code" => 404
    //         ], 404);
    //     }

    //     return response()->json([
    //         "total_data" => $hasil->total(),
    //         "current_page" => $hasil->currentPage(),
    //         "per_page" => $hasil->perPage(),
    //         "total_pages" => $hasil->lastPage(),
    //         "data" => $hasil->map(function ($item) {
    //             return [
    //                 "id" => $item->id,
    //                 "nama" => $item->nama,
    //                 "niup" => $item->niup,
    //                 "nis" => $item->nis,
    //                 "NIK/no.Passport" => $item->identitas,
    //                 "jurusan" => $item->jurusan,
    //                 "kelas" => $item->kelas,
    //                 "wilayah" => $item->nama_wilayah,
    //                 "blok" => $item->nama_blok,
    //                 "kamar" => $item->nama_kamar,
    //                 "asal_kota" => $item->nama_kabupaten,
    //                 "lembaga" => $item->lembaga,
    //                 "tgl_update" => $item->tgl_update,
    //                 "tgl_input" => $item->tgl_input,
    //                 "foto_profil" => url($item->foto_profil)
    //             ];
    //         })
    //     ]);
    // }

    // formDetail Anak Pegawai PerID
    private function formDetail($idAnakPegawai)
    {

    try 
        {
        $biodata = AnakPegawai::where('anak_pegawai.id',$idAnakPegawai)
                        ->leftJoin('peserta_didik as pd','pd.id','anak_pegawai.id_peserta_didik')
                        ->leftJoin('pelajar as p', 'pd.id', '=', 'p.id_peserta_didik')
                        ->join('biodata as b', 'pd.id_biodata', '=', 'b.id')
                        ->leftJoin('warga_pesantren as wp', 'b.id', '=', 'wp.id_biodata')
                        ->leftJoin('berkas as br', 'b.id', '=', 'br.id_biodata')
                        ->leftJoin('keluarga as k', 'b.id', '=', 'k.id_biodata')
                        ->leftJoin('kecamatan as kc', 'b.id_kecamatan', '=', 'kc.id')
                        ->leftJoin('kabupaten as kb', 'b.id_kabupaten', '=', 'kb.id')
                        ->leftJoin('provinsi as pv', 'b.id_provinsi', '=', 'pv.id')
                        ->leftJoin('negara as ng', 'b.id_negara', '=', 'ng.id')
                        ->where('anak_pegawai.status',1)
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
        // DATA KELUARGA (Jika Ada)

        $keluarga = AnakPegawai::where('anak_pegawai.id', $idAnakPegawai)
            ->join('peserta_didik as pd','pd.id','anak_pegawai.id_peserta_didik')
            ->leftJoin('pelajar as p', 'pd.id', '=', 'p.id_peserta_didik')
            ->join('biodata as b_anak', 'pd.id_biodata', '=', 'b_anak.id')
            ->join('keluarga as k_anak', 'b_anak.id', '=', 'k_anak.id_biodata')
            ->leftJoin('keluarga as k_ortu', 'k_anak.no_kk', '=', 'k_ortu.no_kk')
            ->join('orang_tua_wali', 'k_ortu.id_biodata', '=', 'orang_tua_wali.id_biodata')
            ->join('biodata as b_ortu', 'orang_tua_wali.id_biodata', '=', 'b_ortu.id')
            ->join('hubungan_keluarga', 'orang_tua_wali.id_hubungan_keluarga', '=', 'hubungan_keluarga.id')
            ->where('anak_pegawai.status',1)
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
            ->where('k_saudara.no_kk', function ($query) use ($idAnakPegawai) {
                $query->select('k_anak.no_kk')
                    ->from('peserta_didik as pd')
                    ->join('biodata as b_anak', 'pd.id_biodata', '=', 'b_anak.id')
                    ->join('keluarga as k_anak', 'b_anak.id', '=', 'k_anak.id_biodata')
                    ->where('pd.id', $idAnakPegawai)
                    ->limit(1);
            })
            ->whereNotIn('k_saudara.id_biodata', function ($query) {
                $query->select('id_biodata')->from('orang_tua_wali');
            })
            ->whereNotIn('k_saudara.id_biodata', function ($query) use ($idAnakPegawai) {
                $query->select('id_biodata')
                    ->from('peserta_didik')
                    ->where('id', $idAnakPegawai);
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
            // Data Pendidikan (jika ada)
        $pelajar = AnakPegawai::where('anak_pegawai.id',$idAnakPegawai)
                ->join('peserta_didik as pd','pd.id','anak_pegawai.id_peserta_didik')
                ->join('pelajar as p', 'p.id_peserta_didik', '=', 'pd.id')
                ->join('pendidikan_pelajar as pp', 'pp.id_pelajar', '=', 'p.id')
                ->join('lembaga as l', 'pp.id_lembaga', '=', 'l.id')
                ->leftJoin('jurusan as j', 'pp.id_jurusan', '=', 'j.id')
                ->leftJoin('kelas as k', 'pp.id_kelas', '=', 'k.id')
                ->leftJoin('rombel as r', 'pp.id_rombel', '=', 'r.id')
                ->where('anak_pegawai.status',1)
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
            // Status Santri(jikka ada)
        $santri = AnakPegawai::where('anak_pegawai.id', $idAnakPegawai)
                                ->leftJoin('peserta_didik as pd', 'anak_pegawai.id_peserta_didik', '=', 'pd.id') 
                                ->join('santri as s', 's.id_peserta_didik', '=', 'pd.id')
                                ->where('anak_pegawai.status', 1)
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

        //  DOMISILI (Jika Ada)

        $domisili = AnakPegawai::where('anak_pegawai.id', $idAnakPegawai)
                            ->leftJoin('peserta_didik as pd', 'anak_pegawai.id_peserta_didik', '=', 'pd.id') 
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

        // KEWALI ASUHAN (Jika Ada)

        $kewaliasuhan = AnakPegawai::where('anak_pegawai.id', $idAnakPegawai)
                            ->leftJoin('peserta_didik as pd', 'anak_pegawai.id_peserta_didik', '=', 'pd.id') 
                            ->join('santri as s', 's.id_peserta_didik', '=', 'pd.id')
                            ->LeftJoin('pelajar as p', 'p.id_peserta_didik', '=', 'pd.id')
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
        
        
        //  Warga Pesantren (Jika Ada)
        $Wargapesantren = AnakPegawai::where('anak_pegawai.id', $idAnakPegawai)
                        ->leftJoin('peserta_didik', 'anak_pegawai.id_peserta_didik', '=', 'peserta_didik.id') 
                        ->join('biodata', 'peserta_didik.id_biodata', '=', 'biodata.id') 
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
        $catatanAfektif = AnakPegawai::where('anak_pegawai.id',$idAnakPegawai)
                        ->join('peserta_didik as pd','pd.id','anak_pegawai.id_peserta_didik')
                        ->join('santri as s', 's.id_peserta_didik', '=', 'pd.id')
                        ->leftJoin('pelajar as p', 'p.id_peserta_didik', '=', 'pd.id')
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
        $catatanKognitif = AnakPegawai::where('anak_pegawai.id',$idAnakPegawai)
                        ->join('peserta_didik as pd','anak_pegawai.id_peserta_didik','pd.id')
                        ->join('santri as s', 's.id_peserta_didik', '=', 'pd.id')
                        ->leftJoin('pelajar as p', 'p.id_peserta_didik', '=', 'pd.id')
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
                ->join('anak_pegawai as ap', 'pd.id', '=', 'ap.id_peserta_didik')
                ->where('ap.id', $idAnakPegawai)
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
     // **Mengambil Data Anak Pegawai (Detail)
     public function getAnakPegawai($id)
     {
        // Validasi bahwa ID adalah UUID
        if (!Str::isUuid($id)) {
            return response()->json(['error' => 'ID tidak valid'], 400);
        }

        try {
            // Cari data peserta didik berdasarkan UUID
            $anakPegawai = AnakPegawai::find($id);
            if (!$anakPegawai) {
                return response()->json(['error' => 'Data tidak ditemukan'], 404);
            }

            // Ambil detail peserta didik dari fungsi helper
            $data = $this->formDetail($anakPegawai->id);
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
