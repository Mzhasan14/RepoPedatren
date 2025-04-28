<?php

namespace App\Http\Controllers\api\keluarga;

use App\Models\OrangTuaWali;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\PdResource;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Services\FilterOrangtuaService;
use Illuminate\Support\Facades\Validator;

class OrangTuaWaliController extends Controller
{
    private FilterOrangtuaService $filterController;

    public function __construct(FilterOrangtuaService $filterController)
    {
        $this->filterController = $filterController;
    }

    /**
     * Get all Orang Tua with filters and pagination
     *
     * @param Request $request
     * @return JsonResponse
     */

    public function getAllOrangtua(Request $request) :JsonResponse {
        // 1) Ambil ID untuk jenis berkas "Pas foto"
        $pasFotoId = DB::table('jenis_berkas')
        ->where('nama_jenis_berkas', 'Pas foto')
        ->value('id');

        // 2) Subquery: foto terakhir per biodata
        $fotoLast = DB::table('berkas')
            ->select('biodata_id', DB::raw('MAX(id) AS last_id'))
            ->where('jenis_berkas_id', $pasFotoId)
            ->groupBy('biodata_id');

        // 3) Query utama: data orang_tua all
        $query = DB::table('orang_tua_wali AS o')
            ->join('biodata AS b', 'o.id_biodata', '=', 'b.id')
            // join berkas pas foto terakhir
            ->leftJoinSub($fotoLast, 'fl', fn($j) => $j->on('b.id', '=', 'fl.biodata_id'))
            ->leftJoin('berkas AS br', 'br.id', '=', 'fl.last_id')
            ->join('hubungan_keluarga AS hk', 'hk.id', '=', 'o.id_hubungan_keluarga')
            ->join('keluarga AS kel', 'b.id', '=', 'kel.id_biodata') //dari orangtua ke tabel keluarga
            ->join('keluarga as ka', 'kel.no_kk', '=', 'ka.no_kk') //dari keluarga ke keluarga lainnya
            ->join('biodata as ba', 'ka.id_biodata', '=', 'ba.id') //dari keluarga ke anak
            ->leftJoin('kabupaten AS kb', 'kb.id', '=', 'b.kabupaten_id')
            // hanya yang berstatus aktif
            ->where(fn($q) => $q->where('o.status', true))
            ->select([
                'o.id',
                DB::raw("COALESCE(b.nik, b.no_passport) AS identitas"),
                'b.nama',
                'b.no_telepon AS telepon_1',
                'b.no_telepon_2 AS telepon_2',
                'kb.nama_kabupaten AS kota_asal',
                'o.created_at',
                // ambil updated_at terbaru antar s, rp, rd
                DB::raw("
                        GREATEST(
                            o.updated_at,
                            hk.updated_at,
                            kel.updated_at
                        ) AS updated_at
                    "),
                DB::raw("COALESCE(br.file_path, 'default.jpg') AS foto_profil"),
             ])
            ->orderBy('o.id');

            // Terapkan filter dan pagination
            $query = $this->filterController->applyAllFilters($query, $request);


            $perPage     = (int) $request->input('limit', 25);
            $currentPage = (int) $request->input('page', 1);
            $results     = $query->paginate($perPage, ['*'], 'page', $currentPage);

            if ($results->isEmpty()) {
                return response()->json([
                    'status'  => 'success',
                    'message' => 'Data kosong',
                    'data'    => [],
                ], 200);
            }

            $formatted = collect($results->items())->map(fn($item) => [
                'id'               => $item->id,
                'nik_or_passport'  => $item->identitas,
                'nama'             => $item->nama,
                'telepon_1'             => $item->telepon_1,
                'telepon_2'          => $item->telepon_2,
                'kota_asal'        => $item->kota_asal,
                'tgl_update'       => Carbon::parse($item->updated_at)->translatedFormat('d F Y H:i:s') ?? '-',
                'tgl_input'        => Carbon::parse($item->created_at)->translatedFormat('d F Y H:i:s'),
                'foto_profil'      => url($item->foto_profil),
            ]);

            return response()->json([
                'total_data'   => $results->total(),
                'current_page' => $results->currentPage(),
                'per_page'     => $results->perPage(),
                'total_pages'  => $results->lastPage(),
                'data'         => $formatted,
            ]);
    }


    // public function index()
    // {
    //     $ortu = OrangTuaWali::Active()->latest()->paginate(5);
    //     return new PdResource(true, 'List Orang Tua', $ortu);
    // }

    /**
     * Store a newly created resource in storage.
     */
    // public function store(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'id_biodata' => 'required|exists:biodata,id',
    //         'id_hubungan_keluarga' => 'required|string',
    //         'wali' => 'nullable',
    //         'pekerjaan' => 'nullable|string',
    //         'penghasilan' => 'nullable|integer',
    //         'wafat'=>'nullable',
    //         'status' => 'nullable',
    //         'created_by' => 'required|exist:users,id'
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json($validator->errors(), 422);
    //     }

    //     $ortu = OrangTuaWali::create($validator->validated());
    //     return new PdResource(true, 'Data berhasil Ditambah', $ortu);
    // }

    // /**
    //  * Display the specified resource.
    //  */
    // public function show(string $id)
    // {
    //     $ortu = OrangTuaWali::findOrFail($id);
    //     return new PdResource(true, 'detail data', $ortu);
    // }

    // /**
    //  * Update the specified resource in storage.
    //  */
    // public function update(Request $request, string $id)
    // {
    //     $ortu = OrangTuaWali::findOrFail($id);

    //     $validator = Validator::make($request->all(), [
    //         'id_biodata' => 'required|exists:biodata,id',
    //         'id_hubungan_keluarga' => 'required|string',
    //         'wali' => 'nullable',
    //         'pekerjaan' => 'nullable|string',
    //         'penghasilan' => 'nullable|integer',
    //         'wafat' => 'nullable',
    //         'status' => 'nullable',
    //         'updated_by' => 'required|exist:users,id'
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json($validator->errors(), 422);
    //     }

    //     $ortu->update($validator->validated());
    //     return new PdResource(true, 'data berhasil diubah', $ortu);
    // }

    // /**
    //  * Remove the specified resource from storage.
    //  */
    // public function destroy(string $id)
    // {
    //     $ortu = OrangTuaWali::findOrFail($id);

    //     $ortu->delete();
    //     return new PdResource(true, 'Data berhasil dihapus', null);
    // }


    // public function orangTuaWali(Request $request)
    // {
    //     $query = OrangTuaWali::Active()
    //         ->join('biodata', 'orang_tua_wali.id_biodata', '=', 'biodata.id')
    //         ->join('keluarga', 'biodata.id', '=', 'keluarga.id_biodata')
    //         ->leftjoin('berkas', 'berkas.id_biodata', '=', 'biodata.id')
    //         ->leftJoin('jenis_berkas', 'berkas.id_jenis_berkas', '=', 'jenis_berkas.id')
    //         ->leftjoin('kabupaten', 'biodata.id_kabupaten', '=', 'kabupaten.id')
    //         ->select(
    //             'orang_tua_wali.id',
    //             DB::raw("COALESCE(biodata.nik, biodata.no_passport) as identitas"),
    //             'biodata.nama',
    //             'biodata.no_telepon',
    //             'biodata.no_telepon_2',
    //             DB::raw("CONCAT('Kab. ', kabupaten.nama_kabupaten) as kota_asal"),
    //             'biodata.updated_at as tanggal_update',
    //             'biodata.created_at as tanggal_input',
    //             DB::raw("COALESCE(MAX(berkas.file_path), 'default.jpg') as foto_profil")
    //         )
    //         ->groupBy(
    //             'orang_tua_wali.id',
    //             'biodata.nik',
    //             'biodata.no_passport',
    //             'biodata.nama',
    //             'biodata.no_telepon',
    //             'biodata.no_telepon_2',
    //             'kabupaten.nama_kabupaten',
    //             'tanggal_update',
    //             'tanggal_input'
    //         );


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
    //             "code" => 200
    //         ], 200);
    //     }

    //     return response()->json([
    //         "total_data" => $hasil->total(),
    //         "current_page" => $hasil->currentPage(),
    //         "per_page" => $hasil->perPage(),
    //         "total_pages" => $hasil->lastPage(),
    //         "data" => $hasil->map(function ($item) {
    //             return [
    //                 "id" => $item->id,
    //                 "nik/no_passport" => $item->identitas,
    //                 "nama" => $item->nama,
    //                 "no_telepon" => $item->no_telepon,
    //                 "no_telepon_2" => $item->no_telepon_2,
    //                 "nama_kabupaten" => $item->kota_asal,
    //                 "tanggal_update" => $item->tanggal_update,
    //                 "tanggal_input" => $item->tanggal_input,
    //                 "foto_profil" => url($item->foto_profil)
    //             ];
    //         })
    //     ]);
    // }

    // public function wali(Request $request)
    // {
    //     $query = OrangTuaWali::Active()
    //         ->join('biodata', 'orang_tua_wali.id_biodata', '=', 'biodata.id')
    //         ->leftjoin('keluarga', 'biodata.id', '=', 'keluarga.id_biodata')
    //         ->leftjoin('peserta_didik', 'biodata.id', '=', 'peserta_didik.id_biodata')
    //         ->leftjoin('santri', 'peserta_didik.id', '=', 'santri.id_peserta_didik')
    //         ->leftjoin('pelajar', 'peserta_didik.id', '=', 'pelajar.id_peserta_didik')
    //         ->leftjoin('berkas', 'berkas.id_biodata', '=', 'biodata.id')
    //         ->leftJoin('jenis_berkas', 'berkas.id_jenis_berkas', '=', 'jenis_berkas.id')
    //         ->leftjoin('kabupaten', 'biodata.id_kabupaten', '=', 'kabupaten.id')
    //         ->select(
    //             'biodata.id',
    //             DB::raw("COALESCE(biodata.nik, biodata.no_passport) as identitas"),
    //             'biodata.nama',
    //             'biodata.no_telepon',
    //             'biodata.no_telepon_2',
    //             DB::raw("CONCAT('Kab. ', kabupaten.nama_kabupaten) as kota_asal"),
    //             'biodata.updated_at as tanggal_update',
    //             'biodata.created_at as tanggal_input',
    //             DB::raw("COALESCE(MAX(berkas.file_path), 'default.jpg') as foto_profil")
    //         )
    //         ->groupBy(
    //             'biodata.id',
    //             'biodata.nik',
    //             'biodata.no_passport',
    //             'biodata.nama',
    //             'biodata.no_telepon',
    //             'biodata.no_telepon_2',
    //             'kabupaten.nama_kabupaten',
    //             'tanggal_update',
    //             'tanggal_input'
    //         )->where('orang_tua_wali.wali', true);


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
    //                 "nik/no_passport" => $item->identitas,
    //                 "nama" => $item->nama,
    //                 "no_telepon" => $item->no_telepon,
    //                 "no_telepon_2" => $item->no_telepon_2,
    //                 "nama_kabupaten" => $item->kota_asal,
    //                 "tanggal_update" => $item->tanggal_update,
    //                 "tanggal_input" => $item->tanggal_input,
    //                 "foto_profil" => url($item->foto_profil)
    //             ];
    //         })
    //     ]);
    // }
}
