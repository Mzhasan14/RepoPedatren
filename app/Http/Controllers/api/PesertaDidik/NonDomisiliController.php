<?php

namespace App\Http\Controllers\api\PesertaDidik;

use App\Http\Controllers\Controller;
use App\Services\PesertaDidik\Filters\FilterNonDomisiliService;
use App\Services\PesertaDidik\NonDomisiliService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NonDomisiliController extends Controller
{
    private NonDomisiliService $nonDomisili;

    private FilterNonDomisiliService $filter;

    public function __construct(nonDomisiliService $nonDomisili, FilterNonDomisiliService $filter)
    {
        $this->nonDomisili = $nonDomisili;
        $this->filter = $filter;
    }

    // Santri Non Domisili
    public function getNonDomisili(Request $request)
    {
        try {
            $query = $this->nonDomisili->getAllNonDomisili($request);
            $query = $this->filter->nonDomisiliFilters($query, $request);
            $query = $query->latest('b.created_at');

            $perPage = (int) $request->input('limit', 25);
            $currentPage = (int) $request->input('page', 1);
            $results = $query->paginate($perPage, ['*'], 'page', $currentPage);
        } catch (\Throwable $e) {
            Log::error("[NonDomisiliController] Error: {$e->getMessage()}");

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

        $formatted = $this->nonDomisili->formatData($results);

        return response()->json([
            'total_data' => $results->total(),
            'current_page' => $results->currentPage(),
            'per_page' => $results->perPage(),
            'total_pages' => $results->lastPage(),
            'data' => $formatted,
        ]);
    }

    // public function nonDomisiliExport(Request $request, FilterSantriService $filterService)
    // {
    //     return Excel::download(new Export($request, $filterService), 'santri_non_domisili.xlsx');
    // }
}
