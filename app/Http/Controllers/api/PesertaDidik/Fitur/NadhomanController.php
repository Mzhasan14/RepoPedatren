<?php

namespace App\Http\Controllers\api\PesertaDidik\Fitur;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\PesertaDidik\NadhomanRequest;
use App\Services\PesertaDidik\Fitur\NadhomanService;
use App\Services\PesertaDidik\Filters\FilterPesertaDidikService;

class NadhomanController extends Controller
{
    private NadhomanService $service;
    private FilterPesertaDidikService $filter;
    
    public function __construct(NadhomanService $service, FilterPesertaDidikService $filter)
    {
        $this->service = $service;
        $this->filter = $filter;

    }

    public function store(NadhomanRequest $request)
    {
        try {
            $validated = $request->validated();
            $result = $this->service->setoranNadhoman($validated);

            return response()->json([
                'success' => true,
                'message' => 'Setoran nadhoman berhasil disimpan.',
                'data'    => $result,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan setoran nadhoman.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function listSetoran(Request $request)
    {
        try {
            $result = $this->service->listSetoran($request->santri_id);

            return response()->json([
                'success' => true,
                'data'    => $result,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data setoran nadhoman.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function listRekap(Request $request)
    {
        try {
            $result = $this->service->listRekap($request->santri_id);

            return response()->json([
                'success' => true,
                'data'    => $result,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data rekap nadhoman.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
    public function getAllRekap(Request $request)
    {
        try {
            $query = $this->service->getAllRekap($request);
            $query = $this->filter->pesertaDidikFilters($query, $request);

            $perPage     = (int) $request->input('limit', 25);
            $currentPage = (int) $request->input('page', 1);

            $results = $query->paginate($perPage, ['*'], 'page', $currentPage);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data rekap nadhoman.',
                'error'   => $e->getMessage(),
            ], 500);
        }

        if ($results->isEmpty()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Data kosong',
                'data'    => [],
            ], 200);
        }

        $formatted = $this->service->formatData($results);

        return response()->json([
            'total_data'   => $results->total(),
            'current_page' => $results->currentPage(),
            'per_page'     => $results->perPage(),
            'total_pages'  => $results->lastPage(),
            'data'         => $formatted,
        ]);
    }
    public function getSetoranDanRekap(Request $request, $id)
    {
        try {
            $result = $this->service->getSetoranDanRekapNadhoman($request, $id);

            return response()->json([
                'success' => true,
                'data' => $result,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data rekap Nadhoman.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
