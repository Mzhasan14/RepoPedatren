<?php

namespace App\Http\Controllers\api\PesertaDidik;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PesertaDidik\PesertaDidikExport;
use App\Services\PesertaDidik\PesertaDidikService;
use App\Http\Requests\PesertaDidik\CreatePesertaDidikRequest;
use App\Services\PesertaDidik\Filters\FilterPesertaDidikService;

class PesertaDidikController extends Controller
{
    private PesertaDidikService $pesertaDidik;
    private FilterPesertaDidikService $filter;

    public function __construct(
        PesertaDidikService $pesertaDidik,
        FilterPesertaDidikService $filter
    ) {
        $this->pesertaDidik = $pesertaDidik;
        $this->filter = $filter;
    }

    public function getAllPesertaDidik(Request $request): JsonResponse
    {
        try {
            $query = $this->pesertaDidik->getAllPesertaDidik($request);
            $query = $this->filter->pesertaDidikFilters($query, $request);

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

        $formatted = $this->pesertaDidik->formatData($results);

        return response()->json([
            'total_data'   => $results->total(),
            'current_page' => $results->currentPage(),
            'per_page'     => $results->perPage(),
            'total_pages'  => $results->lastPage(),
            'data'         => $formatted,
        ]);
    }

    public function store(CreatePesertaDidikRequest $request)
    {
        try {
            $pesertaDidik = $this->pesertaDidik->store($request->validated());

            return response()->json([
                'message' => 'Peserta Didik berhasil disimpan.',
                'data' => $pesertaDidik
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            // Tangani error umum (misalnya database, validasi, dll)
            return response()->json([
                'message' => 'Terjadi kesalahan saat menyimpan data.',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $this->pesertaDidik->destroy($id);
            return response()->json([
                'message' => 'Peserta Didik berhasil dihapus.',
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat menghapus data.',
                'error'   => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Export Peserta Didik
    public function pesertaDidikExport()
    {
        return Excel::download(new PesertaDidikExport, 'pesertadidik.xlsx');
    }
}
