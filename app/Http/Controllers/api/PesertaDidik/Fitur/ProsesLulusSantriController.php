<?php

namespace App\Http\Controllers\api\PesertaDidik\Fitur;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Requests\PesertaDidik\ProsesLulusSantriRequest;
use App\Services\PesertaDidik\Fitur\ProsesLulusSantriService;
use App\Services\PesertaDidik\Filters\FilterListDataLulusSantriService;

class ProsesLulusSantriController extends Controller
{
    private ProsesLulusSantriService $data;
    private FilterListDataLulusSantriService $filters;

    public function __construct(ProsesLulusSantriService $data, FilterListDataLulusSantriService $filters)
    {
        $this->data = $data;
        $this->filters = $filters;
    }

    public function prosesLulus(ProsesLulusSantriRequest $request)
    {
        try {
            $validated = $request->validated();
            $result = $this->data->prosesLulusSantri($validated);

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => [
                    'berhasil' => $result['data_berhasil'],
                    'gagal' => $result['data_gagal'],
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses permintaan.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function batalLulus(ProsesLulusSantriRequest $request)
    {
        try {
            $validated = $request->validated();
           
            $result = $this->data->batalLulusSantri($validated);

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => [
                    'berhasil' => $result['data_berhasil'],
                    'gagal' => $result['data_gagal'],
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses permintaan.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function listDataLulus(Request $request): JsonResponse
    {
        try {
            $query = $this->data->listSantriLulus($request);
            $query = $this->filters->listDataLulusSantriFilters($query, $request);

            $perPage = (int) $request->input('limit', 25);
            $currentPage = (int) $request->input('page', 1);

            $results = $query->paginate($perPage, ['*'], 'page', $currentPage);
        } catch (\Throwable $e) {
            Log::error("[ProsesLulusSantriController] Error: {$e->getMessage()}");

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

        $formatted = $this->data->formatData($results);

        return response()->json([
            'total_data' => $results->total(),
            'current_page' => $results->currentPage(),
            'per_page' => $results->perPage(),
            'total_pages' => $results->lastPage(),
            'data' => $formatted,
        ]);
    }
}
