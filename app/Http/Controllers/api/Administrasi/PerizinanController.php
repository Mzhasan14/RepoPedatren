<?php

namespace App\Http\Controllers\Api\Administrasi;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Services\Administrasi\Filters\FilterPerizinanService;
use App\Services\Administrasi\PerizinanService;

class PerizinanController extends Controller
{
    private PerizinanService $perizinanService;
    private FilterPerizinanService $filterController;

    public function __construct(FilterPerizinanService $filterController, PerizinanService $perizinanService)
    {
        $this->filterController = $filterController;
        $this->perizinanService = $perizinanService;
    }

    public function getAllPerizinan(Request $request)
    {
        try {
            $query = $this->perizinanService->getAllPerizinan($request);
            $query = $this->filterController->perizinanFilters($query, $request);

            $perPage     = (int) $request->input('limit', 25);
            $currentPage = (int) $request->input('page', 1);
            $results     = $query->paginate($perPage, ['*'], 'page', $currentPage);
        } catch (\Throwable $e) {
            Log::error("[PerizinanController] Error: {$e->getMessage()}");
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

        $formatted = $this->perizinanService->formatData($results);
        
        return response()->json([
            'total_data'   => $results->total(),
            'current_page' => $results->currentPage(),
            'per_page'     => $results->perPage(),
            'total_pages'  => $results->lastPage(),
            'data'         => $formatted,
        ]);
    }
}
