<?php

namespace App\Http\Controllers\api\PesertaDidik;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PesertaDidik\PesertaDidikExport;
use App\Services\PesertaDidik\BersaudaraService;
use App\Services\PesertaDidik\PesertaDidikService;
use App\Services\PesertaDidik\Filters\FilterPesertaDidikService;

class PesertaDidikController extends Controller
{
    private PesertaDidikService $pesertaDidikService;
    private FilterPesertaDidikService $filterController;
    private BersaudaraService $bersaudaraService;

    public function __construct(PesertaDidikService $pesertaDidikService, FilterPesertaDidikService $filterController, BersaudaraService $bersaudaraService)
    {
        $this->pesertaDidikService = $pesertaDidikService;
        $this->filterController = $filterController;
        $this->bersaudaraService = $bersaudaraService;

    }

    public function getAllPesertaDidik(Request $request): JsonResponse
    {
        try {

            $query = $this->pesertaDidikService->getAllPesertaDidik($request);
            $query = $this->filterController->pesertaDidikFilters($query, $request);

            $perPage     = (int) $request->input('limit', 25);
            $currentPage = (int) $request->input('page', 1);

            $results     = $query->paginate($perPage, ['*'], 'page', $currentPage);
        } catch (\Throwable $e) {
            Log::error("[PesertaDidikController] Error: {$e->getMessage()}");
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

        $formatted = $this->pesertaDidikService->formatData($results);

        return response()->json([
            'total_data'   => $results->total(),
            'current_page' => $results->currentPage(),
            'per_page'     => $results->perPage(),
            'total_pages'  => $results->lastPage(),
            'data'         => $formatted,
        ]);
    }

    public function getAllBersaudara(Request $request)
    {
        try {
            $query = $this->bersaudaraService->getAllBersaudara($request);
            $query = $this->filterController->bersaudaraFilters($query, $request);
           
            $perPage     = (int) $request->input('limit', 25);
            $currentPage = (int) $request->input('page', 1);
            $results     = $query->paginate($perPage, ['*'], 'page', $currentPage);
        } catch (\Throwable $e) {
            Log::error("[BersaudaraController] Error: {$e->getMessage()}");
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

        // Format data untuk response
        $formatted = $this->bersaudaraService->formatData($results);

        return response()->json([
            "total_data"   => $results->total(),
            "current_page" => $results->currentPage(),
            "per_page"     => $results->perPage(),
            "total_pages"  => $results->lastPage(),
            "data"         => $formatted
        ]);
    }

    public function pesertaDidikExport(Request $request, FilterPesertaDidikService $filterService)
    {
        return Excel::download(new PesertaDidikExport($request, $filterService), 'peserta_didik.xlsx');
    }

    public function bersaudaraExport(Request $request, FilterPesertaDidikService $filterService)
    {
        return Excel::download(new PesertaDidikExport($request, $filterService), 'peserta_didik_bersaudara.xlsx');
    }
}
