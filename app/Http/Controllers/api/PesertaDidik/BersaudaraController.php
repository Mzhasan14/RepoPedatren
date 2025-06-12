<?php

namespace App\Http\Controllers\api\PesertaDidik;

use App\Exports\PesertaDidik\BersaudaraExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Excel;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Exports\PesertaDidik\PesertaDidikExport;
use App\Services\PesertaDidik\BersaudaraService;
use App\Services\PesertaDidik\Filters\FilterBersaudaraService;

class BersaudaraController extends Controller
{
    private BersaudaraService $bersaudara;
    private FilterBersaudaraService $filter;
    public function __construct(BersaudaraService $bersaudara, FilterBersaudaraService $filter)
    {
        $this->bersaudara = $bersaudara;
        $this->filter = $filter;
    }

    public function getAllBersaudara(Request $request)
    {
        try {
            $query = $this->bersaudara->getAllBersaudara($request);
            $query = $this->filter->bersaudaraFilters($query, $request);
            $query = $query->latest('b.id');

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

        $formatted = $this->bersaudara->formatData($results);

        return response()->json([
            "total_data"   => $results->total(),
            "current_page" => $results->currentPage(),
            "per_page"     => $results->perPage(),
            "total_pages"  => $results->lastPage(),
            "data"         => $formatted
        ]);
    }

    // Export Peserta Didik Bersaudara Kandung
    // public function bersaudaraExport(Request $request, FilterBersaudaraService $filter)
    // {
    //     return Excel::download(new BersaudaraExport($request, $filter), 'peserta_didik_bersaudara.xlsx');
    // }
}
