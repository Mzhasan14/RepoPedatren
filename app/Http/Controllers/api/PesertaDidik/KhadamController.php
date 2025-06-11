<?php

namespace App\Http\Controllers\Api\PesertaDidik;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PesertaDidik\KhadamExport;
use App\Services\PesertaDidik\KhadamService;
use App\Http\Requests\PesertaDidik\CreateKhadamRequest;
use App\Services\PesertaDidik\Filters\FilterKhadamService;

class KhadamController extends Controller
{
    private KhadamService $khadamService;
    private FilterKhadamService $filterController;

    public function __construct(KhadamService $khadamService, FilterKhadamService $filterController)
    {
        $this->khadamService = $khadamService;
        $this->filterController = $filterController;
    }

    public function getAllKhadam(Request $request)
    {
        try {
            $query = $this->khadamService->getAllKhadam($request);
            $query = $this->filterController->khadamFilters($query, $request);
            $query->latest('b.created_at');

            $perPage     = (int) $request->input('limit', 25);
            $currentPage = (int) $request->input('page', 1);
            $results     = $query->paginate($perPage, ['*'], 'page', $currentPage);
        } catch (\Throwable $e) {
            Log::error("[KhadamController] Error: {$e->getMessage()}");
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

        $formatted = $this->khadamService->formatData($results);

        return response()->json([
            'total_data'   => $results->total(),
            'current_page' => $results->currentPage(),
            'per_page'     => $results->perPage(),
            'total_pages'  => $results->lastPage(),
            'data'         => $formatted,
        ]);
    }

    public function store(CreateKhadamRequest $request)
    {
        try {
            // Simpan peserta didik dengan menggunakan service
            $khadamService = $this->khadamService->store($request->validated());

            // Response sukses dengan status 201
            return response()->json([
                'message' => 'Khadam berhasil disimpan.',
                'data' => $khadamService
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            // Tangani error umum (misalnya database, validasi, dll)
            return response()->json([
                'message' => 'Terjadi kesalahan saat menyimpan data.',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function khadamExport()
    {
        return Excel::download(new KhadamExport, 'khadam.xlsx');
    }
}
