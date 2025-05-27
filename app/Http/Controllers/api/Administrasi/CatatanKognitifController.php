<?php

namespace App\Http\Controllers\Api\Administrasi;

use App\Http\Controllers\Controller;
use App\Http\Requests\Administrasi\CatatanKognitifRequest;
use App\Http\Requests\Administrasi\CreateCatatanKognitifRequest;
use App\Http\Requests\Administrasi\Formulir\Catatan\UpdateKognitifRequest;
use App\Http\Requests\Administrasi\KeluarKognitifRequest;
use App\Services\Administrasi\CatatanKognitifService;
use App\Services\Administrasi\Filters\FilterCatatanKognitifService;
use App\Services\Pegawai\Filters\Formulir\CatatanKognitifService as FormulirCatatanKognitifService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CatatanKognitifController extends Controller
{

    private CatatanKognitifService $catatanService;
    private FilterCatatanKognitifService $filterController;
    private FormulirCatatanKognitifService $formulirCatatan;

    public function __construct(FormulirCatatanKognitifService $formulirCatatan, CatatanKognitifService $catatanService, FilterCatatanKognitifService $filterController)
    {
        $this->catatanService = $catatanService;
        $this->filterController = $filterController;
        $this->formulirCatatan = $formulirCatatan;
    }
    /**
     * Display a listing of the resource.
     */
    public function index($id)
    {
        try {
            $result = $this->formulirCatatan->index($id);
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
            Log::error('Gagal ambil data Catatan-afektif: ' . $e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat menampilkan data.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(CatatanKognitifRequest $request, $bioId)
    {
        try {
            $result = $this->formulirCatatan->store($request->validated(), $bioId);
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
            Log::error('Gagal tambah catatan-afektif: ' . $e->getMessage());
            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses data',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function keluarKognitif(KeluarKognitifRequest $request, $id)
    {
        try {
            $validated = $request->validated();
            $result = $this->formulirCatatan->keluarKognitif($validated, $id);

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
            Log::error('Gagal me nonaktifkan catatan afektif: ' . $e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses data',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function edit($id)
    {
        try {
            $result = $this->formulirCatatan->edit($id);
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
            Log::error('Gagal ambil detail catatan-afektif: ' . $e->getMessage());
            return response()->json([
                'message' => 'Terjadi kesalahan saat menampilkan data.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(UpdateKognitifRequest $request, $id)
    {
        try {
            $result = $this->formulirCatatan->update($request->validated(), $id);

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

    public function getCatatanKognitif(Request $request)
    {
        try {
            $query = $this->catatanService->getAllCatatanKognitif($request);
            $query = $this->filterController->applyAllFilters($query, $request);

            $perPage     = (int) $request->input('limit', 25);
            $currentPage = (int) $request->input('page', 1);
            $results     = $query->paginate($perPage, ['*'], 'page', $currentPage);
        } catch (\Throwable $e) {
            Log::error("[CatatanKognitifController] Error: {$e->getMessage()}");
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

        $formatted = $this->catatanService->formatData($results, $request->kategori ?? null);

        return response()->json([
            "total_data"   => $results->total(),
            "current_page" => $results->currentPage(),
            "per_page"     => $results->perPage(),
            "total_pages"  => $results->lastPage(),
            "data"         => $formatted
        ]);
    }
    public function storeCatatanKognitif(CreateCatatanKognitifRequest $request)
    {
        try {
            $result = $this->catatanService->storeCatatanKognitif($request->validated());
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
            Log::error('Gagal tambah catatan-afektif: ' . $e->getMessage());
            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses data',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
