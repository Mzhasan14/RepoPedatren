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
use App\Http\Requests\Pegawai\PengajarResquest;
use App\Models\JenisBerkas;
use App\Services\Pegawai\Filters\FilterPengajarService;
use App\Services\Pegawai\Filters\Formulir\PengajarService as FormulirPengajarService;
use App\Services\Pegawai\PengajarService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;

class PengajarController extends Controller
{

    private PengajarService $pengajarService;
    private FilterPengajarService $filterController;
    private FormulirPengajarService $formulirPengajarService;

    public function __construct(FormulirPengajarService $formulirPengajarService , PengajarService $pengajarService, FilterPengajarService $filterController)
    {
        $this->pengajarService = $pengajarService;
        $this->filterController = $filterController;
        $this->formulirPengajarService = $formulirPengajarService;
    }

    public function index($id)
    {
        try {
            $result = $this->formulirPengajarService->index($id);
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
            Log::error('Gagal ambil data pengajar: ' . $e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat menampilkan data.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function edit($id)
    {
        try {
            $result = $this->formulirPengajarService->edit($id);
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
            Log::error('Gagal ambil data pengajar: ' . $e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat menampilkan data.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
        public function store(PengajarResquest $request, $bioId)
    {
        try {
            $result = $this->formulirPengajarService->store($request->validated(), $bioId);
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
            Log::error('Gagal tambah Pengajar: ' . $e->getMessage());
            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(PengajarResquest $request, string $id)
    {
            try {
            $result = $this->formulirPengajarService->update($request->validated(), $id);
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
            Log::error('Gagal update Pengajar: ' . $e->getMessage());
            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getallPengajar(Request $request)
    {
        try {
            $query = $this->pengajarService->getAllPengajar($request);
            $query = $this->filterController->applyPengajarFilters($query, $request);

            $perPage     = (int) $request->input('limit', 25);
            $currentPage = (int) $request->input('page', 1);
            $results     = $query->paginate($perPage, ['*'], 'page', $currentPage);
        } catch (\Throwable $e) {
            Log::error("[PegngajarController] Error: {$e->getMessage()}");
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

        $formatted = $this->pengajarService->formatData($results);

        return response()->json([
            "total_data"   => $results->total(),
            "current_page" => $results->currentPage(),
            "per_page"     => $results->perPage(),
            "total_pages"  => $results->lastPage(),
            "data"         => $formatted
        ]);
    }

    // public function filterPengajar(Request $request)
    // {
    //     try {
    //     // 1) Ambil ID untuk jenis berkas "Pas foto"
    //     $pasFotoId = DB::table('jenis_berkas')
    //             ->where('nama_jenis_berkas', 'Pas foto')
    //             ->value('id');

    //     // 2) Subquery: foto terakhir per biodata
    //     $fotoLast = DB::table('berkas')
    //             ->select('biodata_id', DB::raw('MAX(id) AS last_id'))
    //             ->where('jenis_berkas_id', $pasFotoId)
    //             ->groupBy('biodata_id');
    //             // 3) Subquery: warga pesantren terakhir per biodata
    //     $wpLast = DB::table('warga_pesantren')
    //             ->select('biodata_id', DB::raw('MAX(id) AS last_id'))
    //             ->where('status', true)
    //             ->groupBy('biodata_id');
    //     // 4) Query utama
    //     $query = Pengajar::Active()
    //         // relasi ke pegawai
    //         ->join('pegawai', 'pengajar.pegawai_id', '=', 'pegawai.id')
    //         // relasi ke biodata
    //         ->join('biodata as b', 'pegawai.biodata_id', '=', 'b.id')
    //         // relasi ke warga pesantren terakhir true (NIUP)
    //         ->leftJoinSub($wpLast, 'wl', fn($j) => $j->on('b.id', '=', 'wl.biodata_id')) 
    //         ->leftJoin('warga_pesantren AS wp', 'wp.id', '=', 'wl.last_id') 
    //         // relasi ke lembaga
    //         ->leftJoin('lembaga as l', 'pengajar.lembaga_id', '=', 'l.id')
    //         // relasi ke golongan
    //         ->leftJoin('golongan as g', 'pengajar.golongan_id', '=', 'g.id')
    //         // relasi ke kategori golongan
    //         ->leftJoin('kategori_golongan as kg', 'g.kategori_golongan_id', '=', 'kg.id')
    //          // relasi ke berkas pas foto terakhir
    //         ->leftJoinSub($fotoLast, 'fl', fn($j) => $j->on('b.id', '=', 'fl.biodata_id'))
    //         ->leftJoin('berkas AS br', 'br.id', '=', 'fl.last_id')
    //         ->leftJoin('materi_ajar', function ($join) {
    //             $join->on('materi_ajar.pengajar_id', '=', 'pengajar.id')
    //                  ->where('materi_ajar.status', 1);
    //         })
    //         ->where('pengajar.status_aktif', 'aktif' )
    //         ->select(
    //             'pengajar.id',
    //             'b.nama',
    //             'wp.niup',
    //             DB::raw("TIMESTAMPDIFF(YEAR, b.tanggal_lahir, CURDATE()) AS umur"),
    //             DB::raw("
    //             GROUP_CONCAT(DISTINCT materi_ajar.nama_materi SEPARATOR ', ') AS daftar_materi"),
    //             DB::raw("
    //             CONCAT(
    //                 FLOOR(SUM(DISTINCT materi_ajar.jumlah_menit) / 60), ' jam ',
    //                 MOD(SUM(DISTINCT materi_ajar.jumlah_menit), 60), ' menit'
    //             ) AS total_waktu_materi
    //         "),     
    //             DB::raw("COUNT(DISTINCT materi_ajar.nama_materi) AS total_materi"),
    //             DB::raw("
    //             CASE 
    //                 WHEN TIMESTAMPDIFF(YEAR, pengajar.tahun_masuk, COALESCE(pengajar.tahun_akhir, CURDATE())) = 0 
    //                 THEN CONCAT(
    //                     'Belum setahun sejak ', DATE_FORMAT(pengajar.tahun_masuk, '%Y-%m-%d'),
    //                     ' sampai ', 
    //                     IF(pengajar.tahun_akhir IS NOT NULL, DATE_FORMAT(pengajar.tahun_akhir, '%Y-%m-%d'), 'saat ini')
    //                 )
    //                 ELSE CONCAT(
    //                     TIMESTAMPDIFF(YEAR, pengajar.tahun_masuk, COALESCE(pengajar.tahun_akhir, CURDATE())), 
    //                     ' Tahun sejak ', DATE_FORMAT(pengajar.tahun_masuk, '%Y-%m-%d'),
    //                     ' sampai ', 
    //                     IF(pengajar.tahun_akhir IS NOT NULL, DATE_FORMAT(pengajar.tahun_akhir, '%Y-%m-%d'), 'saat ini')
    //                 )
    //             END AS masa_kerja
    //         "),            
    //             'g.nama_golongan',
    //             'b.nama_pendidikan_terakhir',
    //             DB::raw("DATE_FORMAT(pengajar.updated_at, '%Y-%m-%d %H:%i:%s') AS tgl_update"),
    //             DB::raw("DATE_FORMAT(pengajar.created_at, '%Y-%m-%d %H:%i:%s') AS tgl_input"),
    //             'l.nama_lembaga',    
    //             DB::raw("COALESCE(MAX(br.file_path), 'default.jpg') as foto_profil")
    //             )   
    //              ->groupBy(
    //                 'pengajar.id',
    //                 'b.nama',
    //                 'wp.niup',
    //                 'b.tanggal_lahir',
    //                 'g.nama_golongan',
    //                 'b.nama_pendidikan_terakhir',
    //                 'pengajar.updated_at',
    //                 'pengajar.created_at',
    //                 'l.nama_lembaga',
    //                 'pengajar.tahun_masuk',
    //                 'pengajar.tahun_akhir'
    //             );   
    //        // Terapkan filter dan pagination
    //        $query = $this->filterController->applyAllFilters($query, $request);

        

    //        $perPage     = (int) $request->input('limit', 25);
    //        $currentPage = (int) $request->input('page', 1);
    //        $results     = $query->paginate($perPage, ['*'], 'page', $currentPage);
    //        }
    //        catch (\Exception $e) {
    //            Log::error('Error fetching data Pengajar: ' . $e->getMessage());
    //            return response()->json([
    //                "status" => "error",
    //                "message" => "Terjadi kesalahan saat mengambil data pengajar",
    //                "code" => 500
    //            ], 500);
    //        }
    //        // Jika Data Kosong
    //        if ($results->isEmpty()) {
    //            return response()->json([
    //                "status" => "error",
    //                "message" => "Data tidak ditemukan",
    //                "code" => 404
    //            ], 404);
    //        }
    //     // Format data untuk response
    //     $formatData = collect($results->items())->map(fn($item) => [
    //             "id" => $item->id,
    //             "nama" => $item->nama,
    //             "niup" => $item->niup ?? '-',
    //             "umur" => $item->umur,
    //             "daftar_materi" => $item->daftar_materi ?? '-',
    //             "total_waktu_materi" => $item->total_waktu_materi ?? '-',
    //             "total_materi" => $item->total_materi ?? '-',
    //             "masa_kerja" => $item->masa_kerja,
    //             "golongan" => $item->nama_golongan ?? '-',
    //             "pendidikan_terakhir" => $item->nama_pendidikan_terakhir,
    //             "tgl_update" => $item->tgl_update,
    //             "tgl_input" => $item->tgl_input,
    //             "lembaga" => $item->nama_lembaga ?? '-',
    //             "foto_profil" => url($item->foto_profil)
    //         ]);
    //     // Format response Json
    //     return response()->json([
    //         "total_data" => $results->total(),
    //         "current_page" => $results->currentPage(),
    //         "per_page" => $results->perPage(),
    //         "total_pages" => $results->lastPage(),
    //         "data" => $formatData,
    //     ]);

    // }
    private function formDetail($idPengajar)
    {
    
        try
        {
            // --- Ambil basic Pengajar + biodata + keluarga ---
            $base = DB::table('pengajar')
                    ->join('pegawai', 'pengajar.pegawai_id', '=', 'pegawai.id')
                    ->join('biodata as b', 'pegawai.biodata_id', '=', 'b.id')
                    ->leftJoin('keluarga as k', 'b.id', '=', 'k.id_biodata')
                    ->where('pengajar.id', $idPengajar)
                    ->select([
                        'pengajar.id as pengajar_id',
                        'b.id as biodata_id',
                        'k.no_kk',
                    ])
                    ->first();

        if (! $base) {
            return ['error' => 'Pengajar tidak ditemukan'];
        }
        $pengajarId  = $base->pengajar_id;
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


        // -- Keluarga Detail -- 
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

        // Ambil semua id biodata yang sudah menjadi orang tua / wali
        $excluded = DB::table('orang_tua_wali')->pluck('id_biodata')->toArray();

        // Ambil saudara kandung (tidak termasuk orang tua/wali dan bukan pegawai itu sendiri)
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

        // Merge ortu dan saudara jadi satu
        $keluarga = $ortu->merge($saudara);

        // Mapping hasil akhir
        if ($keluarga->isNotEmpty()) {
        $data['Keluarga'] = $keluarga->map(fn($i) => [
            'nama'   => $i->nama,
            'nik'    => $i->nik,
            'status' => $i->status,
            'wali'   => $i->wali ?? '-',
            ]);
        }

        // ---  Informasi Pengajar yang juga Santri ---
        $santriInfo = DB::table('santri as s')
                ->where('biodata_id', $bioId)
                ->select('s.nis', 's.tanggal_masuk', 's.tanggal_keluar')
                ->first();
    
        if ($santriInfo) {
            $data['Santri'] = [[
                    'NIS'           => $santriInfo->nis,
                    'Tanggal_Mulai' => $santriInfo->tanggal_masuk,
                    'Tanggal_Akhir' => $santriInfo->tanggal_keluar ?? '-',
                ]];
        }

        // -- Domisili detail -- 
        // Cari santri berdasarkan biodata_id Pengajar
        $santri = DB::table('santri')
            ->where('biodata_id', $bioId) // bioId ini dari base di awal
            ->first();

        if ($santri) {
            $dom = DB::table('riwayat_domisili as rd')
                ->where('rd.santri_id', $santri->id) 
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
                $data['Domisili'] = $dom->map(function($d) {
                    return [
                        'kamar'             => $d->nama_kamar,
                        'blok'              => $d->nama_blok,
                        'wilayah'           => $d->nama_wilayah,
                        'tanggal_ditempati' => $d->tanggal_masuk,
                        'tanggal_pindah'    => $d->tanggal_keluar ?? '-',
                    ];
                })->toArray();
                
            }
        }

        // --- 5. Kewaliasuhan untuk Karyawan ---
        $kew = DB::table('pengajar as k')
            ->join('pegawai as p', 'k.pegawai_id', '=', 'p.id') 
            ->join('biodata as b', 'p.biodata_id', '=', 'b.id')
            ->join('santri as s', 'b.id', '=', 's.biodata_id')
            ->leftJoin('wali_asuh as wa', 's.id', '=', 'wa.id_santri')
            ->leftJoin('anak_asuh as aa', 's.id', '=', 'aa.id_santri')
            ->leftJoin('kewaliasuhan as kw', function ($j) {
                $j->on('kw.id_wali_asuh', 'wa.id')
                ->orOn('kw.id_anak_asuh', 'aa.id');
            })
            ->leftJoin('grup_wali_asuh as g', 'g.id', '=', 'wa.id_grup_wali_asuh')
            ->where('k.id', $pengajarId) 
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

        if ($kew->isNotEmpty()) {
            $data['Status_Pengajar']['Kewaliasuhan'] = $kew->map(fn($k) => [
                'group'   => $k->nama_grup,
                'sebagai' => $k->role,
                $k->role === 'Anak Asuh'
                    ? 'Nama Wali Asuh'
                    : 'Nama Anak Asuh'
                => $k->relasi ?? '-',
            ]);
        }

        // --- 6. Perizinan untuk karyawan (via Santri -> Biodata) ---
        $izin = DB::table('perizinan as pp')
                ->leftJoin('santri as s', 'pp.santri_id', '=', 's.id')
                ->leftJoin('biodata as b', 's.biodata_id', '=', 'b.id')
                ->leftJoin('pegawai as p', 'b.id', '=', 'p.biodata_id')
                ->join('pengajar as k', 'p.id', '=', 'k.pegawai_id')
                ->where('k.id', $pengajarId) // Cari berdasarkan pegawai ID
                ->select([
                    DB::raw("CONCAT(pp.tanggal_mulai,' s/d ',pp.tanggal_akhir) as tanggal"),
                    'pp.keterangan',
                    DB::raw("CASE WHEN TIMESTAMPDIFF(SECOND,pp.tanggal_mulai,pp.tanggal_akhir) >= 86400
                                THEN CONCAT(FLOOR(TIMESTAMPDIFF(SECOND,pp.tanggal_mulai,pp.tanggal_akhir) / 86400), ' Hari | Bermalam')
                                ELSE CONCAT(FLOOR(TIMESTAMPDIFF(SECOND,pp.tanggal_mulai,pp.tanggal_akhir) / 3600), ' Jam')
                        END as lama_waktu"),
                    'pp.status_kembali'
                ])
                ->get();

        if ($izin->isNotEmpty()) {
            $data['Status_Pengajar']['Info_Perizinan'] = $izin->map(fn($z) => [
                'tanggal'        => $z->tanggal,
                'keterangan'     => $z->keterangan,
                'lama_waktu'     => $z->lama_waktu,
                'status_kembali' => $z->status_kembali,
            ]);
        } 
        // --- 8. Pendidikan ---
        $pend = DB::table('riwayat_pendidikan as rp')
            // Relasi dengan santri, karena riwayat pendidikan berhubungan dengan santri
            ->join('santri', 'santri.id', '=', 'rp.santri_id')
            // Relasi santri dengan biodata
            ->join('biodata', 'biodata.id', '=', 'santri.biodata_id')
            // Relasi biodata dengan pegawai
            ->join('pegawai', 'biodata.id', '=', 'pegawai.biodata_id')
            // Relasi pegawai dengan karyawan
            ->join('pengajar', 'pengajar.pegawai_id', '=', 'pegawai.id')
            ->where('pengajar.id', $pengajarId)
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

        // --- Ambil data pengajar dan riwayat materi ---
        $pengajar = DB::table('pengajar')
            ->join('pegawai', 'pegawai.id', '=', 'pengajar.pegawai_id')  // Join dengan pegawai
            ->leftJoin('lembaga', 'lembaga.id', '=', 'pegawai.lembaga_id')  // Join dengan lembaga
            ->join('biodata', 'pegawai.biodata_id', '=', 'biodata.id')  // Join dengan biodata
            ->leftJoin('golongan', 'golongan.id', '=', 'pengajar.golongan_id')  // Join dengan golongan
            ->leftJoin('kategori_golongan', 'kategori_golongan.id', '=', 'golongan.kategori_golongan_id')  // Join dengan kategori golongan
            ->leftJoin('materi_ajar', 'materi_ajar.pengajar_id', '=', 'pengajar.id')  // Join dengan materi ajar
            ->where('pengajar.id', $pengajarId)  // Filter berdasarkan ID pengajar
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
            ->first();  // Ambil data pertama

        // Memasukkan data ke dalam array jika data ditemukan
        if ($pengajar) {
            $data['pengajar'] = [
                "nama_lembaga" => $pengajar->nama_lembaga,  // Nama lembaga
                "PekerjaanKontrak" => $pengajar->PekerjaanKontrak,  // Jabatan sebagai pekerjaan kontrak
                "kategori_golongan" => $pengajar->nama_kategori_golongan,  // Kategori golongan
                "golongan" => $pengajar->nama_golongan,  // Nama golongan
                "keterangan" => $pengajar->keterangan,  // Keterangan waktu
                "total_waktu_materi" => $pengajar->total_waktu_materi,  // Total waktu materi
                "total_materi" => $pengajar->total_materi,  // Total materi yang diajarkan
            ];
        }

        // --- 9. Catatan Afektif ---
        $af = DB::table('catatan_afektif as ca')
            ->join('santri', 'santri.id', '=', 'ca.id_santri')
            ->join('biodata', 'biodata.id', '=', 'santri.biodata_id')
            ->join('pegawai as p', 'biodata.id', '=', 'p.biodata_id')
            ->join('pengajar as k', 'p.id', '=', 'k.pegawai_id')
            ->where('k.id', $pengajarId)
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

        // --- 10. Catatan Kognitif ---
        $kg = DB::table('catatan_kognitif as ck')
            ->join('santri', 'santri.id', '=', 'ck.id_santri')
            ->join('biodata', 'biodata.id', '=', 'santri.biodata_id')
            ->join('pegawai as p', 'biodata.id', '=', 'p.biodata_id')
            ->join('pengajar as k', 'p.id', '=', 'k.pegawai_id')
            ->where('k.id', $pengajarId)
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
                ->join('santri as s', 'pm.santri_id', '=', 's.id')
                ->join('biodata as b', 's.biodata_id', '=', 'b.id')
                ->join('pegawai as p', 'b.id', '=', 'p.biodata_id')
                ->join('pengajar as k', 'p.id', '=', 'k.pegawai_id')
                ->where('k.id', $pengajarId)
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
                    'tanggal_akhir' => $kh->tanggal_akhir ?? "-",
                ];
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
