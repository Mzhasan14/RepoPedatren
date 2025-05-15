<?php

namespace App\Http\Controllers\Api\Pegawai;

use App\Exports\Pegawai\KaryawanExport;
use App\Http\Controllers\api\FilterController;
use App\Http\Controllers\Controller;
use App\Http\Requests\Pegawai\KaryawanFormulirRequest;
use App\Http\Resources\PdResource;
use App\Models\JenisBerkas;
use App\Models\Pegawai\Karyawan;
use App\Services\FilterKaryawanService;
use App\Services\Karyawan\KaryawanService;
use App\Services\Pegawai\Filters\FilterKaryawanService as FiltersFilterKaryawanService;
use App\Services\Pegawai\Filters\Formulir\KaryawanService as FormulirKaryawanService;
use App\Services\Pegawai\KaryawanService as PegawaiKaryawanService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;



class KaryawanController extends Controller
{
    private PegawaiKaryawanService $karyawanService;
    private FiltersFilterKaryawanService $filterController;
    private FormulirKaryawanService $formulirKaryawanService;

    public function __construct(FormulirKaryawanService $formulirKaryawanService,PegawaiKaryawanService $karyawanService, FiltersFilterKaryawanService $filterController,)
    {
        $this->karyawanService = $karyawanService;
        $this->filterController = $filterController;
        $this->formulirKaryawanService = $formulirKaryawanService;
    }

    /**
     * Display a listing of the resource.
     */

    public function index($id)
    {
        try {
            $result = $this->formulirKaryawanService->index($id);
            if (!$result['status']) {
                return response()->json([
                    'message' => $result['message'] ?? 'Data tidak ditemukan.'
                ], 200);
            }
            return response()->json([
                'message' => 'Data berhasil ditampilkan',
                'data' => $result['data']
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal ambil data Karyawan: ' . $e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat menampilkan data.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(KaryawanFormulirRequest $request, $bioId)
    {
        try {
            $result = $this->formulirKaryawanService->store($request->validated(), $bioId);
            if (!$result['status']) {
                return response()->json([
                    'message' => $result['message']
                ], 200);
            }
            return response()->json([
                'message' => 'Data berhasil ditambah',
                'data' => $result['data']
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal tambah Karyawan: ' . $e->getMessage());
            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function edit($id)
    {
        try {
            $result = $this->formulirKaryawanService->edit($id);
            if (!$result['status']) {
                return response()->json([
                    'message' => $result['message'] ?? 'Data tidak ditemukan.'
                ], 200);
            }
            return response()->json([
                'message' => 'Detail data berhasil ditampilkan',
                'data' => $result['data']
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal ambil detail Karyawan: ' . $e->getMessage());
            return response()->json([
                'message' => 'Terjadi kesalahan saat menampilkan data.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(KaryawanFormulirRequest $request, $id)
    {
        try {
            $result = $this->formulirKaryawanService->update($request->validated(), $id);

            if (!$result['status']) {
                return response()->json([
                    'message' => $result['message']
                ], 200);
            }
            return response()->json([
                'message' => 'Data berhasil diperbarui',
                'data' => $result['data']
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal update Karyawan: ' . $e->getMessage());
            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function dataKaryawan(Request $request)
    {
        try {
            $query = $this->karyawanService->getAllKaryawan($request);
            $query = $this->filterController->applyAllFilters($query, $request);

            $perPage     = (int) $request->input('limit', 25);
            $currentPage = (int) $request->input('page', 1);
            $results     = $query->paginate($perPage, ['*'], 'page', $currentPage);
        } catch (\Throwable $e) {
            Log::error("[PelajarController] Error: {$e->getMessage()}");
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

        $formatted = $this->karyawanService->formatData($results);

        return response()->json([
            "total_data"   => $results->total(),
            "current_page" => $results->currentPage(),
            "per_page"     => $results->perPage(),
            "total_pages"  => $results->lastPage(),
            "data"         => $formatted
        ]);
    }
        public function karyawanExport()
        {
            return Excel::download(new KaryawanExport, 'data_karyawan.xlsx');
        }
    // public function dataKaryawan(Request $request)
    // {
    // try
    //     {
    //    // 1) Ambil ID untuk jenis berkas "Pas foto"
    //     $pasFotoId = DB::table('jenis_berkas')
    //             ->where('nama_jenis_berkas', 'Pas foto')
    //             ->value('id');

    //     // 2) Subquery: foto terakhir per biodata
    //     $fotoLast = DB::table('berkas')
    //             ->select('biodata_id', DB::raw('MAX(id) AS last_id'))
    //             ->where('jenis_berkas_id', $pasFotoId)
    //             ->groupBy('biodata_id');
    //     // 3) Subquery: warga pesantren terakhir per biodata
    //     $wpLast = DB::table('warga_pesantren')
    //             ->select('biodata_id', DB::raw('MAX(id) AS last_id'))
    //             ->where('status', true)
    //             ->groupBy('biodata_id');
    //     // 4) Query utama
    //     $query = Karyawan::Active()
    //                     // join pegawai yang hanya berstatus true atau akif
    //                     ->join('pegawai',function ($join){
    //                         $join->on('pegawai.id','=','karyawan.pegawai_id')
    //                             ->where('pegawai.status',1);
    //                     })
    //                     ->join('biodata as b','b.id','=','pegawai.biodata_id')
    //                     ->leftJoin('golongan as g','g.id','=','karyawan.golongan_id')
    //                     ->leftJoin('kategori_golongan as kg','kg.id','=','g.kategori_golongan_id')
    //                     // join ke warga pesantren terakhir true (NIUP)
    //                     ->leftJoinSub($wpLast, 'wl', fn($j) => $j->on('b.id', '=', 'wl.biodata_id')) 
    //                     ->leftJoin('warga_pesantren AS wp', 'wp.id', '=', 'wl.last_id') 
    //                     // join berkas pas foto terakhir
    //                     ->leftJoinSub($fotoLast, 'fl', fn($j) => $j->on('b.id', '=', 'fl.biodata_id'))                            
    //                     ->leftJoin('berkas AS br', 'br.id', '=', 'fl.last_id')
    //                     ->leftJoin('lembaga as l','l.id','=','karyawan.lembaga_id')
    //                     // Join riwayat Jabatan karyawan mengambil data yang terbaru
    //                     ->leftJoin('riwayat_jabatan_karyawan', function ($join) {
    //                         $join->on('riwayat_jabatan_karyawan.karyawan_id', '=', 'karyawan.id')
    //                             ->whereRaw('riwayat_jabatan_karyawan.tanggal_mulai = (
    //                                 SELECT MAX(tanggal_mulai) 
    //                                 FROM riwayat_jabatan_karyawan 
    //                                 WHERE riwayat_jabatan_karyawan.karyawan_id = karyawan.id
    //                             )');
    //                     })
    //                     ->where('karyawan.status_aktif','aktif')
    //                     ->select(
    //                         'karyawan.id',
    //                         'b.nama',
    //                         'wp.niup',
    //                         'b.nik',
    //                         DB::raw("TIMESTAMPDIFF(YEAR, b.tanggal_lahir, CURDATE()) AS umur"),
    //                         'riwayat_jabatan_karyawan.keterangan_jabatan as KeteranganJabatan',
    //                         'l.nama_lembaga',
    //                         'karyawan.jabatan',
    //                         'g.nama_golongan',
    //                         'b.nama_pendidikan_terakhir as pendidikanTerakhir',
    //                         DB::raw("DATE_FORMAT(karyawan.updated_at, '%Y-%m-%d %H:%i:%s') AS tgl_update"),
    //                         DB::raw("DATE_FORMAT(karyawan.created_at, '%Y-%m-%d %H:%i:%s') AS tgl_input"),
    //                         DB::raw("COALESCE(MAX(br.file_path), 'default.jpg') as foto_profil")
    //                         )->groupBy(
    //                             'karyawan.id', 
    //                             'b.nama',
    //                             'b.nik',
    //                             'wp.niup',
    //                             'b.tanggal_lahir',
    //                             'riwayat_jabatan_karyawan.keterangan_jabatan',
    //                             'l.nama_lembaga',
    //                             'karyawan.jabatan',
    //                             'g.nama_golongan',
    //                             'b.nama_pendidikan_terakhir',
    //                             'karyawan.updated_at',
    //                             'karyawan.created_at',
    //                         );
    //           // Terapkan filter dan pagination
    //           $query = $this->filterController->applyAllFilters($query, $request);

    //     $perPage     = (int) $request->input('limit', 25);
    //     $currentPage = (int) $request->input('page', 1);
    //     $results     = $query->paginate($perPage, ['*'], 'page', $currentPage);
    //     }
    //     catch (\Exception $e) {
    //         Log::error('Error fetching data Pengajar: ' . $e->getMessage());
    //         return response()->json([
    //             "status" => "error",
    //             "message" => "Terjadi kesalahan saat mengambil data pengajar",
    //             "code" => 500
    //         ], 500);
    //     }
    //     // Jika Data Kosong
    //     if ($results->isEmpty()) {
    //         return response()->json([
    //             "status" => "error",
    //             "message" => "Data tidak ditemukan",
    //             "code" => 404
    //         ], 404);
    //     }
    //     // Format Data Response
    //     $formatData = collect($results->items())->map(fn($item) => [
    //         "id" => $item->id,
    //         "nama" => $item->nama,
    //         "niup" => $item->niup ?? "-",
    //         "nik" => $item->nik,
    //         "umur" => $item->umur,
    //         "KeteranganJabatan" => $item->KeteranganJabatan,
    //         "lembaga" => $item->nama_lembaga,
    //         "jenisJabatan" => $item->jabatan,
    //         "golongan" => $item->nama_golongan,
    //         "pendidikanTerakhir" => $item->pendidikanTerakhir,
    //         "tgl_update" => $item->tgl_update,
    //         "tgl_input" => $item->tgl_input,
    //         "foto_profil" => url($item->foto_profil)
    //     ]) ;

    //     // Data Response ke Json
    //     return response()->json([
    //         "total_data" => $results->total(),
    //         "current_page" => $results->currentPage(),
    //         "per_page" => $results->perPage(),
    //         "total_pages" => $results->lastPage(),
    //         "data" => $formatData
    //     ]);

    // }
    // private function getFormTampilanList($perPage,$currentPage)
    // {
    //     return Karyawan::Active()
    //     ->join('pegawai','pegawai.id','=','karyawan.id_pegawai')
    //     ->join('biodata','biodata.id','=','pegawai.id_biodata')
    //     ->leftJoin('kabupaten','kabupaten.id','biodata.id_kabupaten')
    //     ->leftJoin('golongan','golongan.id','=','karyawan.id_golongan')
    //     ->leftJoin('kategori_golongan','kategori_golongan.id','=','golongan.id_kategori_golongan')
    //     ->leftJoin('berkas', 'berkas.id_biodata', '=', 'biodata.id')
    //     ->leftJoin('jenis_berkas', 'berkas.id_jenis_berkas', '=', 'jenis_berkas.id')
    //     ->leftJoin('pengajar','pengajar.id_pegawai','=','pegawai.id')
    //     ->leftJoin('lembaga','lembaga.id','=','pegawai.id_lembaga')
    //     ->select(
    //         'karyawan.id',
    //         'biodata.nama',
    //         'biodata.niup',
    //         'biodata.nik',
    //         DB::raw("TIMESTAMPDIFF(YEAR, biodata.tanggal_lahir, CURDATE()) AS umur"),
    //         'karyawan.keterangan_jabatan as KeteranganJabatan',
    //         'lembaga.nama_lembaga',
    //         'karyawan.jabatan',
    //         'golongan.nama_golongan',
    //         'biodata.nama_pendidikan_terakhir as pendidikanTerakhir',
    //         DB::raw("DATE_FORMAT(karyawan.updated_at, '%Y-%m-%d %H:%i:%s') AS tgl_update"),
    //         DB::raw("DATE_FORMAT(karyawan.created_at, '%Y-%m-%d %H:%i:%s') AS tgl_input"),
    //         DB::raw("COALESCE(MAX(berkas.file_path), 'default.jpg') as foto_profil")
    //         )->groupBy(
    //             'karyawan.id', 
    //             'biodata.nama',
    //             'biodata.nik',
    //             'biodata.niup',
    //             'biodata.tanggal_lahir',
    //             'karyawan.keterangan_jabatan',
    //             'lembaga.nama_lembaga',
    //             'karyawan.jabatan',
    //             'golongan.nama_golongan',
    //             'biodata.nama_pendidikan_terakhir',
    //             'karyawan.updated_at',
    //             'karyawan.created_at',
    //         )->distinct() // Menghindari duplikasi data
    //          ->paginate($perPage, ['*'], 'page', $currentPage);
    // }

    // private function formDetail($idKaryawan)
    // {
    //     try
    //     {
    //                 // --- Ambil basic karyawan + biodata + keluarga ---
    //     $base = DB::table('karyawan')
    //                 ->join('pegawai', 'karyawan.pegawai_id', '=', 'pegawai.id')
    //                 ->join('biodata as b', 'pegawai.biodata_id', '=', 'b.id')
    //                 ->leftJoin('keluarga as k', 'b.id', '=', 'k.id_biodata')
    //                 ->where('karyawan.id', $idKaryawan)
    //                 ->select([
    //                     'karyawan.id as karyawan_id',
    //                     'b.id as biodata_id',
    //                     'k.no_kk',
    //                 ])
    //                 ->first();

    //     if (! $base) {
    //         return ['error' => 'Karyawan tidak ditemukan'];
    //     }
    //     $karyawanId  = $base->karyawan_id;
    //     $bioId     = $base->biodata_id;
    //     $noKk      = $base->no_kk;

    //     // --- Biodata detail ---
    //     $biodata = DB::table('biodata as b')
    //     ->leftJoin('warga_pesantren as wp', function ($j) {
    //         $j->on('b.id', 'wp.biodata_id')
    //           ->where('wp.status', true)
    //           ->whereRaw('wp.id = (
    //               select max(id)
    //                  from warga_pesantren
    //                 where biodata_id = b.id and status = true
    //             )');
    //         })
    //     ->leftJoin('berkas as br', function ($j) {
    //         $j->on('b.id', 'br.biodata_id')
    //           ->where('br.jenis_berkas_id', function ($q) {
    //               $q->select('id')
    //                 ->from('jenis_berkas')
    //                 ->where('nama_jenis_berkas', 'Pas foto')
    //                 ->limit(1);
    //           })
    //             ->whereRaw('br.id = (
    //                   select max(id)
    //                   from berkas
    //                   where biodata_id = b.id
    //                     and jenis_berkas_id = br.jenis_berkas_id
    //               )');
    //         })
    //         ->leftJoin('kecamatan as kc', 'b.kecamatan_id', '=', 'kc.id')
    //         ->leftJoin('kabupaten as kb', 'b.kabupaten_id', '=', 'kb.id')
    //         ->leftJoin('provinsi as pv', 'b.provinsi_id', '=', 'pv.id')
    //         ->leftJoin('negara as ng', 'b.negara_id', '=', 'ng.id')
    //         ->where('b.id', $bioId)
    //         ->selectRaw(implode(', ', [
    //             'COALESCE(b.nik, b.no_passport) as identitas',
    //             'wp.niup',
    //             'b.nama',
    //             'b.jenis_kelamin',
    //             "CONCAT(b.tempat_lahir, ', ', DATE_FORMAT(b.tanggal_lahir, '%e %M %Y')) as ttl",
    //             "CONCAT(b.anak_keberapa, ' dari ', b.dari_saudara, ' bersaudara') as anak_ke",
    //             "CONCAT(TIMESTAMPDIFF(YEAR, b.tanggal_lahir, CURDATE()), ' tahun') as umur",
    //             'kc.nama_kecamatan',
    //             'kb.nama_kabupaten',
    //             'pv.nama_provinsi',
    //             'ng.nama_negara',
    //             "COALESCE(br.file_path,'default.jpg') as foto"
    //         ]))
    //         ->first();

    //     $data['Biodata'] = [
    //         'nokk'               => $noKk ?? '-',
    //         'nik_nopassport'     => $biodata->identitas,
    //         'niup'               => $biodata->niup ?? '-',
    //         'nama'               => $biodata->nama,
    //         'jenis_kelamin'      => $biodata->jenis_kelamin,
    //         'tempat_tanggal_lahir' => $biodata->ttl,
    //         'anak_ke'            => $biodata->anak_ke,
    //         'umur'               => $biodata->umur,
    //         'kecamatan'          => $biodata->nama_kecamatan ?? '-',
    //         'kabupaten'          => $biodata->nama_kabupaten ?? '-',
    //         'provinsi'           => $biodata->nama_provinsi ?? '-',
    //         'warganegara'        => $biodata->nama_negara ?? '-',
    //         'foto_profil' => isset($biodata->foto) ? URL::to($biodata->foto) : URL::to('default.jpg'),

    //     ];


    //     // -- Keluarga Detail -- 
    //     $ortu = DB::table('keluarga as k')
    //                 ->where('k.no_kk', $noKk)
    //                 ->join('orang_tua_wali as ow', 'k.id_biodata', '=', 'ow.id_biodata')
    //                 ->join('biodata as bo', 'ow.id_biodata', '=', 'bo.id')
    //                 ->join('hubungan_keluarga as hk', 'ow.id_hubungan_keluarga', '=', 'hk.id')
    //                 ->select([
    //                     'bo.nama',
    //                     'bo.nik',
    //                     DB::raw("hk.nama_status as status"),
    //                     'ow.wali'
    //                 ])
    //                 ->get();

    //     // Ambil semua id biodata yang sudah menjadi orang tua / wali
    //     $excluded = DB::table('orang_tua_wali')->pluck('id_biodata')->toArray();

    //     // Ambil saudara kandung (tidak termasuk orang tua/wali dan bukan pegawai itu sendiri)
    //     $saudara = DB::table('keluarga as k')
    //                 ->where('k.no_kk', $noKk)
    //                 ->whereNotIn('k.id_biodata', $excluded)
    //                 ->where('k.id_biodata', '!=', $bioId)
    //                 ->join('biodata as bs', 'k.id_biodata', '=', 'bs.id')
    //                 ->select([
    //                     'bs.nama',
    //                     'bs.nik',
    //                     DB::raw("'Saudara Kandung' as status"),
    //                     DB::raw("NULL as wali")
    //                 ])
    //                 ->get();

    //     // Merge ortu dan saudara jadi satu
    //     $keluarga = $ortu->merge($saudara);

    //     // Mapping hasil akhir
    //     if ($keluarga->isNotEmpty()) {
    //     $data['Keluarga'] = $keluarga->map(fn($i) => [
    //         'nama'   => $i->nama,
    //         'nik'    => $i->nik,
    //         'status' => $i->status,
    //         'wali'   => $i->wali ?? '-',
    //         ]);
    //     }

    //     // ---  Informasi Karyawan yang juga Santri ---
    //     $santriInfo = DB::table('santri as s')
    //             ->where('biodata_id', $bioId)
    //             ->select('s.nis', 's.tanggal_masuk', 's.tanggal_keluar')
    //             ->first();
    
    //     if ($santriInfo) {
    //         $data['Santri'] = [[
    //                 'NIS'           => $santriInfo->nis,
    //                 'Tanggal_Mulai' => $santriInfo->tanggal_masuk,
    //                 'Tanggal_Akhir' => $santriInfo->tanggal_keluar ?? '-',
    //             ]];
    //     }

    //     // -- Domisili detail -- 
    //     // Cari santri berdasarkan biodata_id Karyawaan
    //     $santri = DB::table('santri')
    //         ->where('biodata_id', $bioId) // bioId ini dari base di awal
    //         ->first();

    //     if ($santri) {
    //         $dom = DB::table('riwayat_domisili as rd')
    //             ->where('rd.santri_id', $santri->id) 
    //             ->join('wilayah as w', 'rd.wilayah_id', '=', 'w.id')
    //             ->join('blok as bl', 'rd.blok_id', '=', 'bl.id')
    //             ->join('kamar as km', 'rd.kamar_id', '=', 'km.id')
    //             ->select([
    //                 'km.nama_kamar',
    //                 'bl.nama_blok',
    //                 'w.nama_wilayah',
    //                 'rd.tanggal_masuk',
    //                 'rd.tanggal_keluar'
    //             ])
    //             ->get();

    //         if ($dom->isNotEmpty()) {
    //             $data['Domisili'] = $dom->map(function($d) {
    //                 return [
    //                     'kamar'             => $d->nama_kamar,
    //                     'blok'              => $d->nama_blok,
    //                     'wilayah'           => $d->nama_wilayah,
    //                     'tanggal_ditempati' => $d->tanggal_masuk,
    //                     'tanggal_pindah'    => $d->tanggal_keluar ?? '-',
    //                 ];
    //             })->toArray();
                
    //         }
    //     }

    //     // --- 5. Kewaliasuhan untuk Karyawan ---
    //     $kew = DB::table('karyawan as k')
    //         ->join('pegawai as p', 'k.pegawai_id', '=', 'p.id') 
    //         ->join('biodata as b', 'p.biodata_id', '=', 'b.id')
    //         ->join('santri as s', 'b.id', '=', 's.biodata_id')
    //         ->leftJoin('wali_asuh as wa', 's.id', '=', 'wa.id_santri')
    //         ->leftJoin('anak_asuh as aa', 's.id', '=', 'aa.id_santri')
    //         ->leftJoin('kewaliasuhan as kw', function ($j) {
    //             $j->on('kw.id_wali_asuh', 'wa.id')
    //             ->orOn('kw.id_anak_asuh', 'aa.id');
    //         })
    //         ->leftJoin('grup_wali_asuh as g', 'g.id', '=', 'wa.id_grup_wali_asuh')
    //         ->where('k.id', $karyawanId) 
    //         ->selectRaw(implode(', ', [
    //             'g.nama_grup',
    //             "CASE WHEN wa.id IS NOT NULL THEN 'Wali Asuh' ELSE 'Anak Asuh' END as role",
    //             "GROUP_CONCAT(
    //                 CASE
    //                 WHEN wa.id IS NOT NULL THEN (
    //                     select bio2.nama
    //                     from biodata bio2
    //                     join santri s3 on bio2.id = s3.biodata_id
    //                     join wali_asuh wa3 on wa3.id_santri = s3.id
    //                     where wa3.id = kw.id_wali_asuh
    //                 )
    //                 ELSE (
    //                     select bio.nama
    //                     from biodata bio
    //                     join santri s2 on bio.id = s2.biodata_id
    //                     join anak_asuh aa2 on aa2.id_santri = s2.id
    //                     where aa2.id = kw.id_anak_asuh
    //                 )
    //                 END
    //                 SEPARATOR ', '
    //             ) as relasi"
    //         ]))
    //         ->groupBy('g.nama_grup', 'wa.id', 'aa.id')
    //         ->get();

    //     if ($kew->isNotEmpty()) {
    //         $data['Status_Karyawan']['Kewaliasuhan'] = $kew->map(fn($k) => [
    //             'group'   => $k->nama_grup,
    //             'sebagai' => $k->role,
    //             $k->role === 'Anak Asuh'
    //                 ? 'Nama Wali Asuh'
    //                 : 'Nama Anak Asuh'
    //             => $k->relasi ?? '-',
    //         ]);
    //     }

    //     // --- 6. Perizinan untuk karyawan (via Santri -> Biodata) ---
    //     $izin = DB::table('perizinan as pp')
    //             ->leftJoin('santri as s', 'pp.santri_id', '=', 's.id')
    //             ->leftJoin('biodata as b', 's.biodata_id', '=', 'b.id')
    //             ->leftJoin('pegawai as p', 'b.id', '=', 'p.biodata_id')
    //             ->join('karyawan as k', 'p.id', '=', 'k.pegawai_id')
    //             ->where('k.id', $karyawanId) // Cari berdasarkan pegawai ID
    //             ->select([
    //                 DB::raw("CONCAT(pp.tanggal_mulai,' s/d ',pp.tanggal_akhir) as tanggal"),
    //                 'pp.keterangan',
    //                 DB::raw("CASE WHEN TIMESTAMPDIFF(SECOND,pp.tanggal_mulai,pp.tanggal_akhir) >= 86400
    //                             THEN CONCAT(FLOOR(TIMESTAMPDIFF(SECOND,pp.tanggal_mulai,pp.tanggal_akhir) / 86400), ' Hari | Bermalam')
    //                             ELSE CONCAT(FLOOR(TIMESTAMPDIFF(SECOND,pp.tanggal_mulai,pp.tanggal_akhir) / 3600), ' Jam')
    //                     END as lama_waktu"),
    //                 'pp.status_kembali'
    //             ])
    //             ->get();

    //     if ($izin->isNotEmpty()) {
    //         $data['Status_Karyawan']['Info_Perizinan'] = $izin->map(fn($z) => [
    //             'tanggal'        => $z->tanggal,
    //             'keterangan'     => $z->keterangan,
    //             'lama_waktu'     => $z->lama_waktu,
    //             'status_kembali' => $z->status_kembali,
    //         ]);
    //     } 
    //     // --- 8. Pendidikan ---
    //     $pend = DB::table('riwayat_pendidikan as rp')
    //         // Relasi dengan santri, karena riwayat pendidikan berhubungan dengan santri
    //         ->join('santri', 'santri.id', '=', 'rp.santri_id')
    //         // Relasi santri dengan biodata
    //         ->join('biodata', 'biodata.id', '=', 'santri.biodata_id')
    //         // Relasi biodata dengan pegawai
    //         ->join('pegawai', 'biodata.id', '=', 'pegawai.biodata_id')
    //         // Relasi pegawai dengan karyawan
    //         ->join('karyawan', 'karyawan.pegawai_id', '=', 'pegawai.id')
    //         ->where('karyawan.id', $karyawanId)
    //         ->join('lembaga as l', 'rp.lembaga_id', '=', 'l.id')
    //         ->leftJoin('jurusan as j', 'rp.jurusan_id', '=', 'j.id')
    //         ->leftJoin('kelas as k', 'rp.kelas_id', '=', 'k.id')
    //         ->leftJoin('rombel as r', 'rp.rombel_id', '=', 'r.id')
    //         ->select([
    //             'rp.no_induk',
    //             'l.nama_lembaga',
    //             'j.nama_jurusan',
    //             'k.nama_kelas',
    //             'r.nama_rombel',
    //             'rp.tanggal_masuk',
    //             'rp.tanggal_keluar'
    //         ])
    //         ->get();

    //     if ($pend->isNotEmpty()) {
    //         $data['Pendidikan'] = $pend->map(fn($p) => [
    //             'no_induk'     => $p->no_induk,
    //             'nama_lembaga' => $p->nama_lembaga,
    //             'nama_jurusan' => $p->nama_jurusan,
    //             'nama_kelas'   => $p->nama_kelas ?? '-',
    //             'nama_rombel'  => $p->nama_rombel ?? '-',
    //             'tahun_masuk'  => $p->tanggal_masuk,
    //             'tahun_lulus'  => $p->tanggal_keluar ?? '-',
    //         ]);
    //     }

    //     // --- Riwayat Karyawan ---
    //     $karyawan = DB::table('karyawan')
    //         ->join('pegawai', 'karyawan.pegawai_id', '=', 'pegawai.id')
    //         ->join('biodata', 'pegawai.biodata_id', '=', 'biodata.id')
    //         ->leftJoin('riwayat_jabatan_karyawan', 'riwayat_jabatan_karyawan.karyawan_id', '=', 'karyawan.id')
    //         ->where('karyawan.id', $karyawanId)
    //         ->select(
    //             'riwayat_jabatan_karyawan.keterangan_jabatan',
    //             DB::raw("
    //                 CONCAT(
    //                     'Sejak ', DATE_FORMAT(riwayat_jabatan_karyawan.tanggal_mulai, '%e %b %Y'),
    //                     ' Sampai ',
    //                     IFNULL(DATE_FORMAT(riwayat_jabatan_karyawan.tanggal_selesai, '%e %b %Y'), 'Sekarang')
    //                 ) AS masa_jabatan
    //             ")
    //         )
    //         ->orderBy('riwayat_jabatan_karyawan.tanggal_mulai', 'asc')
    //         ->distinct()
    //         ->get();

    //     if ($karyawan->isNotEmpty()) {
    //         $data['Karyawan'] = $karyawan->map(fn($item) => [
    //             'keterangan_jabatan' => $item->keterangan_jabatan ?? "-",
    //             'masa_jabatan'       => $item->masa_jabatan ?? "-",
    //         ]);
    //     }

    //     // --- 9. Catatan Afektif ---
    //     $af = DB::table('catatan_afektif as ca')
    //         ->join('santri', 'santri.id', '=', 'ca.id_santri')
    //         ->join('biodata', 'biodata.id', '=', 'santri.biodata_id')
    //         ->join('pegawai as p', 'biodata.id', '=', 'p.biodata_id')
    //         ->join('karyawan as k', 'p.id', '=', 'k.pegawai_id')
    //         ->where('k.id', $karyawanId)
    //         ->latest('ca.created_at')
    //         ->first();

    //     if ($af) {
    //         $data['Catatan_Progress']['Afektif'] = [
    //             'kebersihan'               => $af->kebersihan_nilai ?? '-',
    //             'tindak_lanjut_kebersihan' => $af->kebersihan_tindak_lanjut ?? '-',
    //             'kepedulian'               => $af->kepedulian_nilai ?? '-',
    //             'tindak_lanjut_kepedulian' => $af->kepedulian_tindak_lanjut ?? '-',
    //             'akhlak'                   => $af->akhlak_nilai ?? '-',
    //             'tindak_lanjut_akhlak'     => $af->akhlak_tindak_lanjut ?? '-',
    //         ];
    //     }

    //     // --- 10. Catatan Kognitif ---
    //     $kg = DB::table('catatan_kognitif as ck')
    //         ->join('santri', 'santri.id', '=', 'ck.id_santri')
    //         ->join('biodata', 'biodata.id', '=', 'santri.biodata_id')
    //         ->join('pegawai as p', 'biodata.id', '=', 'p.biodata_id')
    //         ->join('karyawan as k', 'p.id', '=', 'k.pegawai_id')
    //         ->where('k.id', $karyawanId)
    //         ->latest('ck.created_at')
    //         ->first();

    //     if ($kg) {
    //         $data['Catatan_Progress']['Kognitif'] = [
    //             'kebahasaan'                      => $kg->kebahasaan_nilai ?? '-',
    //             'tindak_lanjut_kebahasaan'        => $kg->kebahasaan_tindak_lanjut ?? '-',
    //             'baca_kitab_kuning'               => $kg->baca_kitab_kuning_nilai ?? '-',
    //             'tindak_lanjut_baca_kitab_kuning' => $kg->baca_kitab_kuning_tindak_lanjut ?? '-',
    //             'hafalan_tahfidz'                 => $kg->hafalan_tahfidz_nilai ?? '-',
    //             'tindak_lanjut_hafalan_tahfidz'   => $kg->hafalan_tahfidz_tindak_lanjut ?? '-',
    //             'furudul_ainiyah'                 => $kg->furudul_ainiyah_nilai ?? '-',
    //             'tindak_lanjut_furudul_ainiyah'   => $kg->furudul_ainiyah_tindak_lanjut ?? '-',
    //             'tulis_alquran'                   => $kg->tulis_alquran_nilai ?? '-',
    //             'tindak_lanjut_tulis_alquran'     => $kg->tindak_lanjut_tulis_alquran ?? '-',
    //             'baca_alquran'                    => $kg->baca_alquran_nilai ?? '-',
    //             'tindak_lanjut_baca_alquran'      => $kg->baca_alquran_tindak_lanjut ?? '-',
    //         ];
    //     }

    //         // --- 10. Kunjungan Mahrom ---
    //         $kun = DB::table('pengunjung_mahrom as pm')
    //             ->join('santri as s', 'pm.santri_id', '=', 's.id')
    //             ->join('biodata as b', 's.biodata_id', '=', 'b.id')
    //             ->join('pegawai as p', 'b.id', '=', 'p.biodata_id')
    //             ->join('karyawan as k', 'p.id', '=', 'k.pegawai_id')
    //             ->where('k.id', $karyawanId)
    //             ->select(['pm.nama_pengunjung', 'pm.tanggal'])
    //             ->get();
    
    //         if ($kun->isNotEmpty()) {
    //             $data['Kunjungan_Mahrom'] = $kun->map(fn($k) => [
    //                 'nama'    => $k->nama_pengunjung,
    //                 'tanggal' => $k->tanggal,
    //             ]);
    //         }
    //             // --- 11. Khadam ---
    //             $kh = DB::table('khadam as kh')
    //             ->where('kh.biodata_id', $bioId)
    //             ->select(['kh.keterangan', 'kh.tanggal_mulai', 'kh.tanggal_akhir'])
    //             ->first();
    
    //         if ($kh) {
    //             $data['Khadam'] = [
    //                 'keterangan'    => $kh->keterangan,
    //                 'tanggal_mulai' => $kh->tanggal_mulai,
    //                 'tanggal_akhir' => $kh->tanggal_akhir ?? "-",
    //             ];
    //         }
    //     return $data;
    // }catch (\Exception $e) {
    //     Log::error("Error in formDetailKaryawan: " . $e->getMessage());
    //     return ['error' => 'Terjadi kesalahan pada server'];
    // }
    // }
    //  // **Mengambil Data Karyawan ( Detail)**
    //  public function getKaryawan($idKaryawan)
    //  {
    //     // Validasi bahwa ID adalah UUID
    //     if (!Str::isUuid($idKaryawan)) {
    //         return response()->json(['error' => 'ID tidak valid'], 400);
    //     }

    //     try {
    //         // Cari data peserta didik berdasarkan UUID
    //         $Karyawan = Karyawan::find($idKaryawan);
    //         if (!$Karyawan) {
    //             return response()->json(['error' => 'Data tidak ditemukan'], 404);
    //         }

    //         // Ambil detail peserta didik dari fungsi helper
    //         $data = $this->formDetail($Karyawan->id);
    //         if (empty($data)) {
    //             return response()->json(['error' => 'Data Kosong'], 200);
    //         }

    //         return response()->json($data, 200);
    //     } catch (\Exception $e) {
    //         Log::error("Error in getDetailKaryawan: " . $e->getMessage());
    //         return response()->json(['error' => 'Terjadi kesalahan pada server'], 500);
    //     }
    //  }
//     public function getKaryawan($idKaryawan)
// {
//     $data = $this->formDetail($idKaryawan);

//     // Hapus elemen dengan nilai NULL atau array kosong
//     $filteredData = array_filter($data, function ($value) {
//         if (is_array($value)) {
//             return !empty(array_filter($value, fn($v) => !is_null($v) && $v !== ''));
//         }
//         return !is_null($value) && $value !== '';
//     });

//     return response()->json([
//         "data" => [$filteredData],
//     ]);
// }

     
}
