<?php

namespace App\Http\Controllers\api\keluarga;

use Illuminate\Support\Str;
use App\Models\OrangTuaWali;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\PdResource;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Services\Keluarga\OrangtuaWaliService;
use App\Services\Keluarga\DetailOrangtuaService;
use App\Http\Requests\Keluarga\OrangtuaWaliRequest;
use App\Services\Keluarga\FIlters\FilterOrangtuaService;

class OrangTuaWaliController extends Controller
{
    private OrangtuaWaliService $orangtuaWaliService;
    private DetailOrangtuaService $detailOrangtuaService;
    private FilterOrangtuaService $filterController;


    public function __construct(OrangtuaWaliService $orangtuaWaliService, FilterOrangtuaService $filterController, DetailOrangtuaService $detailOrangtuaService)
    {
        $this->orangtuaWaliService = $orangtuaWaliService;
        $this->filterController = $filterController;
        $this->detailOrangtuaService = $detailOrangtuaService;
    }

    /**
     * Get all Orang Tua with filters and pagination
     *
     * @param Request $request
     * @return JsonResponse
     */

    public function getAllOrangtua(Request $request)
    {
        $query = $this->orangtuaWaliService->getAllOrangtua($request);
        $query = $this->filterController->OrangtuaFilters($query, $request);

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

        $formatted = $this->orangtuaWaliService->formatData($results);

        return response()->json([
            "total_data"   => $results->total(),
            "current_page" => $results->currentPage(),
            "per_page"     => $results->perPage(),
            "total_pages"  => $results->lastPage(),
            "data"         => $formatted
        ]);
    }

    public function getDetailOrangtua(string $OrangtuaId)
    {
        $ortu = OrangTuaWali::find($OrangtuaId);
        if (!$ortu) {
            return response()->json([
                'status' => 'error',
                'message' => 'ID Orangtua tidak ditemukan',
                'data' => []
            ], 404);
        }

        $data = $this->detailOrangtuaService->getDetailOrangtua($OrangtuaId);

        return response()->json([
            'status' => true,
            'data'    => $data,
        ], 200);
    }


    // public function index()
    // {
    //     $ortu = OrangTuaWali::Active()->latest()->paginate(5);
    //     return new PdResource(true, 'List Orang Tua', $ortu);
    // }

    /**
     * Store a newly created resource in storage.
     */
    public function store(OrangtuaWaliRequest $request)
    {
        try {
            $validated = $request->validated();
            $result = $this->orangtuaWaliService->store($validated);
            if (!$result['status']) {
                return response()->json([
                    'message' => $result['message']
                ], 200);
            }
            return response()->json([
                'message' => 'Data berhasil ditambah',
                'data' => $result['data']
            ]);
        }
         catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function edit($id)
    {
        $result = $this->orangtuaWaliService->edit($id);
        if (!$result['status']) {
            return response()->json([
                'message' => $result['message'] ?? 'Data tidak ditemukan.',
            ], 200);
        }
        return response()->json(
            [
                'message' => 'Detail data berhasil ditampilkan',
                'data' => $result['data']
            ]
        );
    }

    public function update(OrangtuaWaliRequest $request, $id) {
        $result = $this->orangtuaWaliService->update($request->validated(),$id);
        if (!$result['status']) {
            return response()->json([
                'message' => $result['message'] ??
                'Data tidak ditemukan.'
            ],200);
        }
        return response()->json([
            'message' => 'Orang tua berhasil diperbarui',
            'data' => $result['data']
        ]);
    }

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
    public function destroy(string $id)
    {    
        $ortu = OrangTuaWali::findOrFail($id);
        if (!$ortu) {
            return response()->json([
                'success' => false,
                'message' => 'Orang tua tidak ditemukan'
            ], 404);
        }
        // $ortu->delete();
        OrangTuaWali::where('id', $id)
            ->update([
                'status' => false,
                'deleted_at' => now(),
                'deleted_by' => Auth::id()
            ]);
            
        return response()->json([
            'status' => true,
            'message' => 'data orang tua berhasil dihapus.'
        ]);
    }


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
