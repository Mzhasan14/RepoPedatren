<?php

namespace App\Http\Controllers\api\PesertaDidik;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PesertaDidik\SantriExport;
use App\Services\PesertaDidik\Filters\FilterSantriService;
use App\Services\PesertaDidik\NonDomisiliService;
use App\Services\PesertaDidik\SantriService;

class SantriController extends Controller
{
    private SantriService $santriService;
    private FilterSantriService $filterController;
    private NonDomisiliService $nonDomisiliService;

    public function __construct(SantriService $santriService,FilterSantriService $filterController, NonDomisiliService $nonDomisiliService)
    {
        $this->santriService = $santriService;
        $this->nonDomisiliService = $nonDomisiliService;
        $this->filterController = $filterController;
    }

    // Santri Domisili
    public function getAllSantri(Request $request)
    {
        try {
            $query = $this->santriService->getAllSantri($request);
            $query = $this->filterController->santriFilters($query, $request);

            $perPage     = (int) $request->input('limit', 25);
            $currentPage = (int) $request->input('page', 1);
            $results     = $query->paginate($perPage, ['*'], 'page', $currentPage);
        } catch (\Throwable $e) {
            Log::error("[SantriController] Error: {$e->getMessage()}");
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

        $formatted = $this->santriService->formatData($results);

        return response()->json([
            "total_data"   => $results->total(),
            "current_page" => $results->currentPage(),
            "per_page"     => $results->perPage(),
            "total_pages"  => $results->lastPage(),
            "data"         => $formatted
        ]);
    }

    

    // public function santriExport(Request $request, FilterSantriService $filterService)
    // {
    //     return Excel::download(new SantriExport($request, $filterService), 'santri.xlsx');
    // }

   
}
