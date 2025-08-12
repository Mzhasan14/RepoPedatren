<?php

namespace App\Http\Controllers\api\PesertaDidik\Fitur;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\PesertaDidik\TahfidzRequest;
use App\Services\PesertaDidik\Fitur\TahfidzService;
use App\Services\PesertaDidik\Filters\FilterPesertaDidikService;

class TahfidzController extends Controller
{
    private TahfidzService $service;
    private FilterPesertaDidikService $filter;
    public function __construct(TahfidzService $service, FilterPesertaDidikService $filter)
    {
        $this->service = $service;
        $this->filter = $filter;
    }

    public function store(TahfidzRequest $request)
    {
        try {
            $validated = $request->validated();
            $result = $this->service->setoranTahfidz($validated);

            return response()->json([
                'success' => true,
                'message' => 'Setoran tahfidz berhasil disimpan.',
                'data' => $result,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan setoran tahfidz.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // public function listSetoran($id)
    // {
    //     try {
    //         $result = $this->service->listSetoran($id);

    //         return response()->json([
    //             'success' => true,
    //             'data' => $result,
    //         ], 200);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Terjadi kesalahan saat mengambil data setoran tahfidz.',
    //             'error' => $e->getMessage(),
    //         ], 500);
    //     }
    // }

    // public function listRekap(Request $request)
    // {
    //     try {
    //         $result = $this->service->listRekap($request);

    //         return response()->json([
    //             'success' => true,
    //             'data' => $result,
    //         ], 200);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Terjadi kesalahan saat mengambil data rekap tahfidz.',
    //             'error' => $e->getMessage(),
    //         ], 500);
    //     }
    // }

    public function getSetoranDanRekap($id)
    {
        try {
            $result = $this->service->getSetoranDanRekap($id);

            return response()->json([
                'success' => true,
                'data' => $result,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data rekap tahfidz.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getAllRekap(Request $request)
    {
        try {
            $query = $this->service->getAllRekap($request);
            $query = $this->filter->pesertaDidikFilters($query, $request);

            $perPage = (int) $request->input('limit', 25);
            $currentPage = (int) $request->input('page', 1);

            $results = $query->paginate($perPage, ['*'], 'page', $currentPage);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data rekap tahfidz.',
                'error' => $e->getMessage(),
            ], 500);
        }

        if ($results->isEmpty()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Data kosong',
                'data' => [],
            ], 200);
        }

        $formatted = $this->service->formatData($results);

        return response()->json([
            'total_data' => $results->total(),
            'current_page' => $results->currentPage(),
            'per_page' => $results->perPage(),
            'total_pages' => $results->lastPage(),
            'data' => $formatted,
        ]);
    }
}
