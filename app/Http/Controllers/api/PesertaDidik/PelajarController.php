<?php

namespace App\Http\Controllers\api\PesertaDidik;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PesertaDidik\PelajarExport;
use App\Services\PesertaDidik\Filters\FilterPelajarService;
use App\Services\PesertaDidik\PelajarService;

class PelajarController extends Controller
{
    private PelajarService $pelajarService;
    private FilterPelajarService $filterController;

    public function __construct(PelajarService $pelajarService, FilterPelajarService $filterController)
    {
        $this->pelajarService = $pelajarService;
        $this->filterController = $filterController;
    }

    public function getAllPelajar(Request $request)
    {
        try {
            $query = $this->pelajarService->getAllPelajar($request);
            $query = $this->filterController->pelajarFilters($query, $request);
            $query = $query->latest('b.id');

            $perPage     = (int) $request->input('limit', 25);
            $currentPage = (int) $request->input('page', 1);
            $results     = $query->paginate($perPage, ['*'], 'page', $currentPage);
        } catch (\Throwable $e) {
            Log::error("[PelajarController] Error: {$e->getMessage()}");
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

        $formatted = $this->pelajarService->formatData($results);

        return response()->json([
            "total_data"   => $results->total(),
            "current_page" => $results->currentPage(),
            "per_page"     => $results->perPage(),
            "total_pages"  => $results->lastPage(),
            "data"         => $formatted
        ]);
    }

    // public function pelajarExport(Request $request, FilterPelajarService $filterService)
    // {
    //     return Excel::download(new PelajarExport($request, $filterService), 'pelajar.xlsx');
    // }
}
