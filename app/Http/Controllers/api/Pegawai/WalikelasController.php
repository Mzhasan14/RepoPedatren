<?php

namespace App\Http\Controllers\Api\Pegawai;

use App\Exports\Pegawai\WaliKelasExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Pegawai\KeluarWaliKelasRequest;
use App\Http\Requests\Pegawai\PindahWaliKelasRequest;
use App\Http\Requests\Pegawai\WaliKelasRequest;
use App\Services\Pegawai\Filters\FilterWaliKelasService as FiltersFilterWaliKelasService;
use App\Services\Pegawai\Filters\Formulir\WaliKelasService as FormulirWaliKelasService;
use App\Services\Pegawai\WaliKelasService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class WalikelasController extends Controller
{
    private WaliKelasService $walikelasService;
    private FiltersFilterWaliKelasService $filterController;
    private FormulirWaliKelasService $formulirwalikelas;

    public function __construct(
        FormulirWaliKelasService $formulirwalikelas,
        WaliKelasService $walikelasService,
        FiltersFilterWaliKelasService $filterController
    ) {
        $this->walikelasService = $walikelasService;
        $this->filterController = $filterController;
        $this->formulirwalikelas = $formulirwalikelas;
    }

    /**
     * Display listing data by ID.
     */
    public function index($id)
    {
        try {
            $result = $this->formulirwalikelas->index($id);

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
            Log::error('Gagal ambil data Wali Kelas: ' . $e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat menampilkan data.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show detail data.
     */
    public function edit($id)
    {
        try {
            $result = $this->formulirwalikelas->show($id);

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
            Log::error('Gagal ambil detail Wali Kelas: ' . $e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat menampilkan data.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store new Wali Kelas.
     */
    public function store(WaliKelasRequest $request, $bioId)
    {
        try {
            $result = $this->formulirwalikelas->store($request->validated(), $bioId);

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
            Log::error('Gagal tambah Wali Kelas: ' . $e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update Wali Kelas data.
     */
    public function update(WaliKelasRequest $request, $id)
    {
        try {
            $result = $this->formulirwalikelas->update($request->validated(), $id);

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
            Log::error('Gagal update Wali Kelas: ' . $e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get paginated and filtered list of Walikelas.
     */
    public function getDataWalikelas(Request $request)
    {
        try {
            $query = $this->walikelasService->getAllWalikelas($request);
            $query = $this->filterController->applyAllFilters($query, $request);

            $perPage = (int) $request->input('limit', 25);
            $currentPage = (int) $request->input('page', 1);
            $results = $query->paginate($perPage, ['*'], 'page', $currentPage);
        } catch (\Throwable $e) {
            Log::error("[WaliKelasController] Error: {$e->getMessage()}");

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan pada server',
            ], 500);
        }

        if ($results->isEmpty()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Data kosong',
                'data' => [],
            ], 200);
        }

        $formatted = $this->walikelasService->formatData($results);

        return response()->json([
            "total_data" => $results->total(),
            "current_page" => $results->currentPage(),
            "per_page" => $results->perPage(),
            "total_pages" => $results->lastPage(),
            "data" => $formatted
        ]);
    }

    /**
     * Handle pindah Wali Kelas.
     */
    public function pindahWalikelas(PindahWaliKelasRequest $request, $id)
    {
        try {
            $validated = $request->validated();
            $result = $this->formulirwalikelas->pindahWalikelas($validated, $id);

            if (!$result['status']) {
                return response()->json([
                    'message' => $result['message']
                ], 200);
            }

            return response()->json([
                'message' => 'Wali Kelas baru berhasil dibuat',
                'data' => $result['data']
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal pindah Wali Kelas: ' . $e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle keluar Wali Kelas.
     */
    public function keluarWalikelas(KeluarWaliKelasRequest $request, $id)
    {
        try {
            $validated = $request->validated();
            $result = $this->formulirwalikelas->keluarWalikelas($validated, $id);

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
            Log::error('Gagal keluar Wali Kelas: ' . $e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export Walikelas data to Excel.
     */
    public function waliKelasExport()
    {
        return Excel::download(new WaliKelasExport, 'data_Wali-Kelas.xlsx');
    }
}