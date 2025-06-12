<?php

namespace App\Http\Controllers\api\PesertaDidik;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Services\PesertaDidik\AnakPegawaiService;
use App\Http\Requests\PesertaDidik\CreateAnakPegawaiRequest;
use App\Services\PesertaDidik\Filters\FilterAnakPegawaiService;

class AnakPegawaiController extends Controller
{
    private AnakPegawaiService $anakPegawaiService;
    private FilterAnakPegawaiService $filterController;

    public function __construct(AnakPegawaiService $anakPegawaiService, FilterAnakPegawaiService $filterController)
    {
        $this->anakPegawaiService = $anakPegawaiService;
        $this->filterController = $filterController;
    }

    public function getAllAnakPegawai(Request $request): JsonResponse
    {
        try {
            $query = $this->anakPegawaiService->getAllAnakPegawai($request);
            $query = $this->filterController->anakPegawaiFilters($query, $request);
            $query = $query->distinct()->latest('b.id');

            $perPage     = (int) $request->input('limit', 25);
            $currentPage = (int) $request->input('page', 1);
            $results     = $query->paginate($perPage, ['*'], 'page', $currentPage);
        } catch (\Throwable $e) {
            Log::error("[AnakPegawaiController] Error: {$e->getMessage()}");
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

        $formatted = $this->anakPegawaiService->formatData($results);

        return response()->json([
            'total_data'   => $results->total(),
            'current_page' => $results->currentPage(),
            'per_page'     => $results->perPage(),
            'total_pages'  => $results->lastPage(),
            'data'         => $formatted,
        ]);
    }

    public function store(CreateAnakPegawaiRequest $request)
    {
        try {
            // Simpan peserta didik dengan menggunakan service
            $pesertaDidik = $this->anakPegawaiService->store($request->validated());

            // Response sukses dengan status 201
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
}
