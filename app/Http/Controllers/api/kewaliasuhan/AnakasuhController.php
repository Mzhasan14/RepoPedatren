<?php

namespace App\Http\Controllers\api\kewaliasuhan;

use App\Models\Santri;
use App\Models\Biodata;
use App\Exports\BaseExport;
use Illuminate\Http\Request;
use App\Models\Peserta_didik;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\PdResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Kewaliasuhan\Anak_asuh;
use App\Models\Kewaliasuhan\Kewaliasuhan;
use Illuminate\Support\Facades\Validator;
use App\Services\Kewaliasuhan\AnakasuhService;
use App\Http\Requests\Kewaliasuhan\anakAsuhRequest;
use App\Services\Kewaliasuhan\DetailAnakasuhService;
use App\Http\Requests\Kewaliasuhan\KeluarAnakasuhRequest;
use App\Http\Requests\Kewaliasuhan\PindahAnakasuhRequest;
use App\Http\Requests\Kewaliasuhan\tambahAnakasuhRequest;
use App\Services\Kewaliasuhan\Filters\FilterAnakasuhService;

class AnakasuhController extends Controller
{
    private AnakasuhService $anakasuhService;

    private FilterAnakasuhService $filterAnakasuhService;

    private DetailAnakasuhService $detailAnakasuhService;

    public function __construct(AnakasuhService $anakasuhService, FilterAnakasuhService $filterAnakasuhService, DetailAnakasuhService $detailAnakasuhService)
    {
        $this->anakasuhService = $anakasuhService;
        $this->filterAnakasuhService = $filterAnakasuhService;
        $this->detailAnakasuhService = $detailAnakasuhService;
    }

    public function getAllAnakasuh(Request $request)
    {
        $query = $this->anakasuhService->getAllAnakasuh($request);
        $query = $this->filterAnakasuhService->AnakasuhFilters($query, $request);

        $perPage = (int) $request->input('limit', 25);
        $currentPage = (int) $request->input('page', 1);
        $results = $query->paginate($perPage, ['*'], 'page', $currentPage);

        if ($results->isEmpty()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Data kosong',
                'data' => [],
            ], 200);
        }

        $formatted = $this->anakasuhService->formatData($results);

        return response()->json([
            'total_data' => $results->total(),
            'current_page' => $results->currentPage(),
            'per_page' => $results->perPage(),
            'total_pages' => $results->lastPage(),
            'data' => $formatted,
        ]);
    }

    public function getDetailAnakasuh(string $bioId)
    {
        $Anakasuh = Biodata::find($bioId);
        if (! $Anakasuh) {
            return response()->json([
                'status' => 'error',
                'message' => 'ID anak asuh tidak ditemukan',
                'data' => [],
            ], 404);
        }

        $data = $this->detailAnakasuhService->getDetailAnakasuh($bioId);

        return response()->json([
            'status' => true,
            'data' => $data,
        ], 200);
    }

    public function index($bioId): JsonResponse
    {
        try {
            $result = $this->anakasuhService->index($bioId);
            if (! $result['status']) {
                return response()->json([
                    'message' => $result['message'],
                ], 404);
            }

            return response()->json([
                'status' => true,
                'data' => $result['data'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(anakAsuhRequest $request)
    {
        try {
            $validated = $request->validated();
            $result = $this->anakasuhService->store($validated);

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => [
                    'berhasil' => $result['data_baru'],
                    'gagal' => $result['data_gagal'],
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses permintaan.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function formStore(tambahAnakasuhRequest $request, $bioId): JsonResponse
    {
        try {
            $validated = $request->validated();
            $result = $this->anakasuhService->formStore($validated, $bioId);

            if (! $result['status']) {
                return response()->json([
                    'message' => $result['message'],
                ], 200);
            }

            return response()->json([
                'message' => 'Data berhasil ditambah',
                'data' => $result['data'],
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal tambah anakasuh: ' . $e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $result = $this->anakasuhService->show($id);

            if (! $result['status']) {
                return response()->json([
                    'message' => $result['message'] ?? 'Data tidak ditemukan.',
                ], 200);
            }

            return response()->json([
                'message' => 'Detail data berhasil ditampilkan',
                'data' => $result['data'],
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal ambil detail anakasuh: ' . $e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat menampilkan data.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(tambahAnakasuhRequest $request, $id)
    {
        try {
        $result = $this->anakasuhService->update($request->validated(), $id);
        if (! $result['status']) {
            return response()->json([
                'message' => $result['message'] ??
                    'Data tidak ditemukan.',
            ], 200);
        }

        return response()->json([
            'message' => 'anakasuh berhasil diperbarui',
            'data' => $result['data'],
        ]);
    } catch (\Exception $e) {
            Log::error('Gagal update anakasuh: ' . $e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses data.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function keluarAnakasuh(KeluarAnakasuhRequest $request, $id): JsonResponse
    {
        try {
            $validated = $request->validated();
            $result = $this->anakasuhService->keluarAnakasuh($validated, $id);

            if (! $result['status']) {
                return response()->json([
                    'message' => $result['message'],
                ], 200);
            }

            return response()->json([
                'message' => 'Data berhasil diperbarui',
                'data' => $result['data'],
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal keluar anakasuh: ' . $e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function pindahAnakasuh(PindahAnakasuhRequest $request, $id): JsonResponse
    {
        try {
            $validated = $request->validated();
            $result = $this->anakasuhService->pindahAnakasuh($validated, $id);

            if (! $result['status']) {
                return response()->json([
                    'message' => $result['message'],
                ], 200);
            }

            return response()->json([
                'message' => 'kewaliasuhan baru berhasil dibuat',
                'data' => $result['data'],
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal pindah anak asuh: ' . $e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        DB::transaction(function () use ($id) {
            $anakasuh = Anak_Asuh::findOrFail($id);
            if (! $anakasuh) {
                return response()->json([
                    'success' => false,
                    'message' => 'anak asuh tidak ditemukan',
                ], 404);
            }
            $originalAttributes = $anakasuh->getAttributes();
            $relations = Kewaliasuhan::where('id_wali_asuh', $id)->get();
            // 1. Nonaktifkan semua relasi
            Kewaliasuhan::where('id_wali_asuh', $id)
                ->update([
                    'tanggal_berakhir' => now(),
                    'status' => false,
                    'deleted_at' => now(),
                    'deleted_by' => Auth::id(),
                ]);

            // 2. Nonaktifkan wali asuh
            Anak_Asuh::where('id', $id)
                ->update([
                    'status' => false,
                    'deleted_at' => now(),
                    'deleted_by' => Auth::id(),
                ]);

            activity('anak_asuh_delete')
                ->performedOn($anakasuh)
                ->withProperties([
                    'old_attributes' => $originalAttributes,
                    'deleted_by' => Auth::id(),
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'affected_relations' => $relations->pluck('id'), // ID relasi yang dinonaktifkan
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
                        'deleted_by' => Auth::id(),
                    ])
                    ->event('nonaktif_relasi_wali_asuh')
                    ->log('Relasi kewaliasuhan dinonaktifkan karena wali asuh dinonaktifkan');
            }
        });

        return response()->json(['message' => 'Anak asuh dihapus']);
    }

    public function exportExcel(Request $request)
    {
        $defaultExportFields = [
            'nis',
            'nama',
            'kamar',
            'grup',
            'angkatan',
            'kota_asal',
            'tanggal_input',
            'tanggal_update'
        ];

        $columnOrder = [
            'nis',
            'nama',
            'kamar',
            'grup',
            'angkatan',
            'kota_asal',
            'tanggal_input',
            'tanggal_update'
        ];

        // Ambil kolom dari checkbox user (opsional)
        $optionalFields = $request->input('fields', []);

        // Gabungkan default dan optional (hindari duplikat & atur urutan)
        $fields = array_unique(array_merge($defaultExportFields, $optionalFields));
        $fields = array_values(array_intersect($columnOrder, $fields));

        /** @var AnakasuhService $service */
        $service = app(AnakasuhService::class);

        // Ambil query dari service (sudah bisa disaring via request)
        $query = $service->getExportAnakasuhQuery($fields, $request);

        // Ambil semua atau per halaman berdasarkan request
        $results = $request->input('all') === 'true'
            ? $query->get()
            : $query->limit((int) $request->input('limit', 100))->get();

        // Format data dan heading
        $addNumber = true;
        $formatted = $service->formatDataExportAnakasuh($results, $fields, $addNumber);
        $headings = $service->getFieldExportAnakasuhHeadings($fields, $addNumber);

        $filename = 'anak_asuh_' . now()->format('Y-m-d_H-i-s') . '.xlsx';

        return Excel::download(new \App\Exports\BaseExport($formatted, $headings), $filename);
    }

    /**
     * Display a listing of the resource.
     */
    // public function index()
    // {
    //      $anakAsuh = Anak_asuh::Active()->latest()->paginate(5);
    //     return new PdResource(true, 'list anak asuh', $anakAsuh);
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

    //     $anakAsuh = Anak_asuh::create($validator->validated());

    //     return new PdResource(true, 'Data berhasil ditambah', $anakAsuh);
    // }

    // /**
    //  * Display the specified resource.
    //  */
    // public function show(string $id)
    // {
    //     $anakAsuh = Anak_asuh::findOrFail($id);
    //     return new PdResource(true, 'Detail data', $anakAsuh);
    // }

    // /**
    //  * Update the specified resource in storage.
    //  */
    // public function update(Request $request, string $id)
    // {
    //     $anakAsuh = Anak_asuh::findOrFail($id);

    //     $validator = Validator::make($request->all(), [
    //         'id_peserta_didik' => 'required|exists:peserta_didik,id',
    //         'id_grup_wali_asuh' => 'required|exists:grup_wali_asuh,id',
    //         'updated_by' => 'nullable',
    //         'status' => 'nullable'
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json($validator->errors(), 422);
    //     }

    //     $anakAsuh->update($request->validated());
    //     return new PdResource(true, 'Data berhasil diubah', $anakAsuh);
    // }

    // /**
    //  * Remove the specified resource from storage.
    //  */
    // public function destroy(string $id)
    // {
    //     $anakAsuh = Anak_asuh::findOrFail($id);
    //     $anakAsuh->delete();
    //     return new PdResource(true,'Data berhasil dihapus',null);
    // }

    // public function anakAsuh() {
    //     $anakAsuh = Santri::join('peserta_didik','santri.id_peserta_didik','=','peserta_didik.id')
    //     ->join('biodata','peserta_didik.id_biodata','=','biodata.id')
    //     ->join('anak_asuh','santri.nis','=','anak_asuh.nis')
    //     ->join('kamar','santri.id_kamar','=','kamar.id')
    //     ->join('grup_wali_asuh','anak_asuh.id_grup_wali_asuh','=','grup_wali_asuh.id')
    //     // ->join('desa','biodata.id_desa','=','desa.id')
    //     // ->join('kecamatan','desa.id_kecamatan','=','kecamatan.id')
    //     ->join('kabupaten','biodata.id_kabupaten','=','kabupaten.id')
    //     ->select(
    //         'anak_asuh.id as id_anak_asuh',
    //         'biodata.nama',
    //         'santri.nis',
    //         'kamar.nama_kamar',
    //         'grup_wali_asuh.nama_grup',
    //         'kabupaten.nama_kabupaten',
    //         DB::raw('YEAR(santri.tanggal_masuk) as angkatan'),
    //         'anak_asuh.updated_at as Tanggal_Update',
    //         'anak_asuh.created_at as Tanggal_Input',
    //     )
    //     ->get();

    //     return response()->json([
    //         'status' => true,
    //         'message' => 'list data anak asuh',
    //         'data' => $anakAsuh
    //     ]);
    // }
}
