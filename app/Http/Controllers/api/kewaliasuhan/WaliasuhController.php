<?php

namespace App\Http\Controllers\api\kewaliasuhan;

use App\Models\Santri;
use App\Models\Biodata;
use Illuminate\Http\Request;
use App\Models\Peserta_didik;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\PdResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Kewaliasuhan\Wali_asuh;
use Database\Seeders\PesertaDidikSeeder;
use App\Models\Kewaliasuhan\Kewaliasuhan;
use Illuminate\Support\Facades\Validator;
use App\Services\Kewaliasuhan\WaliasuhService;
use App\Http\Requests\Kewaliasuhan\waliAsuhRequest;
use App\Services\Kewaliasuhan\DetailWaliasuhService;
use App\Http\Requests\Kewaliasuhan\KeluarWaliasuhRequest;
use App\Services\Kewaliasuhan\Filters\FilterWaliasuhService;

class WaliasuhController extends Controller
{

    private WaliasuhService $waliasuhService;
    private FilterWaliasuhService $filterWaliasuhService;
    private DetailWaliasuhService $detailWaliasuhService;

    public function __construct(WaliasuhService $waliasuhService, FilterWaliasuhService $filterWaliasuhService, DetailWaliasuhService $detailWaliasuhService){
        $this->waliasuhService = $waliasuhService;
        $this->filterWaliasuhService = $filterWaliasuhService;
        $this->detailWaliasuhService = $detailWaliasuhService;
    }

    public function getAllWaliasuh(Request $request) {
        $query = $this->waliasuhService->getAllWaliasuh($request);
        $query = $this->filterWaliasuhService->WaliasuhFilters($query, $request);

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

        $formatted = $this->waliasuhService->formatData($results);

        return response()->json([
            "total_data"   => $results->total(),
            "current_page" => $results->currentPage(),
            "per_page"     => $results->perPage(),
            "total_pages"  => $results->lastPage(),
            "data"         => $formatted
        ]);
    }

    public function getDetailWaliasuh(string $bioId)
    {
        $waliasuh = Biodata::find($bioId);
        if (!$waliasuh) {
            return response()->json([
                'status' => 'error',
                'message' => 'ID wali asuh tidak ditemukan',
                'data' => []
            ], 404);
        }

        $data = $this->detailWaliasuhService->getDetailWaliasuh($bioId);

        return response()->json([
            'status' => true,
            'data'    => $data,
        ], 200);
    }

    public function index($id): JsonResponse
    {
        try {
            $result = $this->waliasuhService->index($id);

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
            Log::error('Gagal ambil data khadam: ' . $e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat menampilkan data.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(waliAsuhRequest $request, $bioId): JsonResponse
    {
        try {
            $validated = $request->validated();
            $result = $this->waliasuhService->store($validated, $bioId);

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
            Log::error('Gagal tambah khadam: ' . $e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $result = $this->waliasuhService->show($id);

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
            Log::error('Gagal ambil detail khadam: ' . $e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat menampilkan data.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(waliAsuhRequest $request, $id) {
        $result = $this->waliasuhService->update($request->validated(),$id);
        if (!$result['status']) {
            return response()->json([
                'message' => $result['message'] ??
                    'Data tidak ditemukan.'
            ], 200);
        }
        return response()->json([
            'message' => 'waliasuh berhasil diperbarui',
            'data' => $result['data']
        ]);
    }

    public function keluarWaliasuh(KeluarWaliasuhRequest $request, $id): JsonResponse
    {
        try {
            $validated = $request->validated();
            $result = $this->waliasuhService->keluarWaliasuh($validated, $id);

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
            Log::error('Gagal keluar khadam: ' . $e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id) {
        DB::transaction(function () use ($id) {
            $waliasuh = Wali_Asuh::findOrFail($id);
            if (!$waliasuh) {
                return response()->json([
                    'success' => false,
                    'message' => 'wali asuh tidak ditemukan'
                ], 404);
            }
            $originalAttributes = $waliasuh->getAttributes();
            $relations = Kewaliasuhan::where('id_wali_asuh', $id)->get();
            // 1. Nonaktifkan semua relasi
            Kewaliasuhan::where('id_wali_asuh', $id)
                ->update([
                    'tanggal_berakhir' =>now(),
                    'status' => false,
                    'deleted_at' => now(),
                    'deleted_by' => Auth::id()
                ]);

            // 2. Nonaktifkan wali asuh
            Wali_Asuh::where('id', $id)
                ->update([
                    'id_grup_wali_asuh' => null,
                    'status' => false,
                    'deleted_at' => now(),
                    'deleted_by' => Auth::id()
                ]);

            activity('anak_asuh_delete')
                ->performedOn($waliasuh)
                ->withProperties([
                    'old_attributes' => $originalAttributes,
                    'deleted_by' => Auth::id(),
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'affected_relations' => $relations->pluck('id') // ID relasi yang dinonaktifkan
                ])
                ->event('nonaktif_wali_asuh')
                ->log('Wali asuh dinonaktifkan beserta semua relasinya');

            // Log untuk setiap relasi yang dinonaktifkan
            foreach ($relations as $relation) {
                activity('kewaliasuhan_delete')
                    ->performedOn($relation)
                    ->withProperties([
                        'id_wali_asuh' => $relation->id_wali_asuh,
                        'id_anak_asuh' => $relation->id_anak_asuh,
                        'deleted_by' => Auth::id()
                    ])
                    ->event('nonaktif_relasi_wali_asuh')
                    ->log('Relasi kewaliasuhan dinonaktifkan karena wali asuh dinonaktifkan');
            }
        });

        return response()->json(['message' => 'Wali asuh dihapus']);

    }
    /**
     * Display a listing of the resource.
     */
    // public function index()
    // {
    //      $waliAsuh = Wali_asuh::Active()->latest()->paginate(5);
    //     return new PdResource(true, 'list wali asuh', $waliAsuh);
    // }

    // /**
    //  * Store a newly created resource in storage.
    //  */
    // public function store(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'id_peserta_didik' => 'required|exists:peserta_didik,id',
    //         'id_grup_wali_asuh' => 'required|exists:grup_wali_asuh,id',
    //         'created_by' => 'required',
    //         'status' => 'nullable',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json($validator->errors(), 422);
    //     }

    //     $waliAsuh = Wali_asuh::create($validator->validated());

    //     return new PdResource(true, 'Data berhasil ditambah', $waliAsuh);
    // }

    // /**
    //  * Display the specified resource.
    //  */
    // public function show(string $id)
    // {
    //     $waliAsuh = Wali_asuh::findOrFail($id);
    //     return new PdResource(true, 'Detail data', $waliAsuh);
    // }

    // /**
    //  * Update the specified resource in storage.
    //  */
    // public function update(Request $request, string $id)
    // {
    //     $waliAsuh = Wali_asuh::findOrFail($id);

    //     $validator = Validator::make($request->all(), [
    //         'id_peserta_didik' => 'required|exists:peserta_didik,id',
    //         'id_grup_wali_asuh' => 'required|exists:grup_wali_asuh,id',
    //         'updated_by' => 'nullable',
    //         'status' => 'nullable'
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json($validator->errors(), 422);
    //     }

    //     $waliAsuh->update($request->validated());
    //     return new PdResource(true, 'Data berhasil diubah', $waliAsuh);
    // }

    // /**
    //  * Remove the specified resource from storage.
    //  */
    // public function destroy(string $id)
    // {
    //     $waliAsuh = Wali_asuh::findOrFail($id);
    //     $waliAsuh->delete();
    //     return new PdResource(true,'Data berhasil dihapus',null);
    // }

    // public function waliAsuh() {
    //     $waliAsuh = Santri::join('wali_asuh','santri.nis','=','wali_asuh.nis')
    //     ->join('peserta_didik','santri.id_peserta_didik','=','peserta_didik.id')
    //     ->join('biodata','peserta_didik.id_biodata','=','biodata.id')
    //     ->join('grup_wali_asuh','grup_wali_asuh.id','=','wali_asuh.id_grup_wali_asuh')
    //     ->join('kamar','santri.id_kamar','=','kamar.id')
    //     ->join('blok','santri.id_blok','=','blok.id')
    //     ->join('wilayah','santri.id_wilayah','=','wilayah.id')
    //     // ->join('desa', 'biodata.id_desa', '=', 'desa.id')
    //     // ->join('kecamatan', 'desa.id_kecamatan', '=', 'kecamatan.id')
    //     ->leftjoin('berkas', 'biodata.id','=','berkas.id_biodata')
    //     ->leftjoin('jenis_berkas','berkas.id_jenis_berkas','=','jenis_berkas.id')
    //     ->join('kabupaten', 'biodata.id_kabupaten', '=', 'kabupaten.id')
    //     ->select(
    //         'wali_asuh.id as id_wali_asuh',
    //         'berkas.file_path as foto_profile',
    //         'biodata.nama',
    //         'santri.nis',
    //         'kamar.nama_kamar',
    //         'blok.nama_blok',
    //         'wilayah.nama_wilayah',
    //         'kabupaten.nama_kabupaten',
    //         DB::raw('YEAR(santri.tanggal_masuk) as angkatan'),
    //         'wali_asuh.updated_at as Tanggal_update',
    //         'wali_asuh.created_at as Tanggal_input'
    //     )
    //     ->get();

    //     return new PdResource(true, 'List data wali asuh', $waliAsuh);
    // }
}
