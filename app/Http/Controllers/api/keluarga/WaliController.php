<?php

namespace App\Http\Controllers\api\keluarga;

use App\Http\Controllers\Controller;
use App\Models\Biodata;
use App\Services\Keluarga\DetailWaliService;
use App\Services\Keluarga\Filters\FilterWaliService;
use App\Services\Keluarga\WaliService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WaliController extends Controller
{
    private WaliService $waliService;

    private DetailWaliService $detailWaliService;

    private FilterWaliService $filterController;

    public function __construct(WaliService $waliService, FilterWaliService $filterController, DetailWaliService $detailWaliService)
    {
        $this->waliService = $waliService;
        $this->filterController = $filterController;
        $this->detailWaliService = $detailWaliService;
    }

    /**
     * Get all Wali with filters and pagination
     */
    public function getAllWali(Request $request): JsonResponse
    {
        // 1) Ambil ID untuk jenis berkas "Pas foto"
        $query = $this->waliService->getAllWali($request);
        $query = $this->filterController->waliFilters($query, $request);

        $perPage = (int) $request->input('limit', 25);
        $currentPage = (int) $request->input('page', 1);
        $results = $query->paginate($perPage, ['*'], 'page', $currentPage);

        if ($results->isEmpty()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Data kosong',
                'data' => [],
            ], 200);
        }

        $formatted = $this->waliService->formatData($results);

        return response()->json([
            'total_data' => $results->total(),
            'current_page' => $results->currentPage(),
            'per_page' => $results->perPage(),
            'total_pages' => $results->lastPage(),
            'data' => $formatted,
        ]);
    }

    public function getDetailWali(string $bioId)
    {
        $wali = Biodata::find($bioId);
        if (! $wali) {
            return response()->json([
                'status' => 'error',
                'message' => 'ID Wali tidak ditemukan',
                'data' => [],
            ], 404);
        }

        $data = $this->detailWaliService->getDetailWali($bioId);

        return response()->json([
            'status' => true,
            'data' => $data,
        ], 200);
    }
    /**
     * Display a listing of the resource.
     */
    // public function index()
    // {
    //     //
    // }

    // /**
    //  * Store a newly created resource in storage.
    //  */
    // public function store(Request $request)
    // {
    //     //
    // }

    // /**
    //  * Display the specified resource.
    //  */
    // public function show(string $id)
    // {
    //     //
    // }

    // /**
    //  * Update the specified resource in storage.
    //  */
    // public function update(Request $request, string $id)
    // {
    //     //
    // }

    // /**
    //  * Remove the specified resource from storage.
    //  */
    // public function destroy(string $id)
    // {
    //     //
    // }
}
