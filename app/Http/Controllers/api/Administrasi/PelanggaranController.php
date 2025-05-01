<?php

namespace App\Http\Controllers\Api\Administrasi;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Services\Administrasi\PelanggaranService;
use App\Services\Administrasi\Filters\FilterPelanggaranService;

class PelanggaranController extends Controller
{
    private PelanggaranService $pelanggaranService;
    private FilterPelanggaranService $filterController;

    public function __construct(FilterPelanggaranService $filterController, PelanggaranService $pelanggaranService)
    {
        $this->pelanggaranService = $pelanggaranService;
        $this->filterController = $filterController;
    }

    public function getAllPelanggaran(Request $request)
    {
        try {
            $query = $this->pelanggaranService->getAllPelanggaran($request);
            $query = $this->filterController->pelanggaranFilters($query, $request);

            $perPage     = (int) $request->input('limit', 25);
            $currentPage = (int) $request->input('page', 1);
            $results     = $query->paginate($perPage, ['*'], 'page', $currentPage);
        } catch (\Throwable $e) {
            Log::error("[PelanggaranController] Error: {$e->getMessage()}");
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

        $formatted = $this->pelanggaranService->formatData($results);

        return response()->json([
            'total_data'   => $results->total(),
            'current_page' => $results->currentPage(),
            'per_page'     => $results->perPage(),
            'total_pages'  => $results->lastPage(),
            'data'         => $formatted,
        ]);
    }
}
