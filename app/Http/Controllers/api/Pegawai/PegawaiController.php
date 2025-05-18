<?php

namespace App\Http\Controllers\Api\Pegawai;

use App\Exports\Pegawai\PegawaiExport;
use App\Http\Controllers\api\FilterController;
use App\Http\Controllers\Controller;
use App\Http\Requests\Pegawai\CreatePegawaiRequest;
use App\Http\Requests\Pegawai\PegawaiRequest;
use App\Http\Resources\PdResource;
use App\Models\JenisBerkas;
use App\Models\Pegawai\Pegawai;
use App\Services\FilterPegawaiService;
use App\Services\Pegawai\Filters\FilterPegawaiService as FiltersFilterPegawaiService;
use App\Services\Pegawai\PegawaiService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;


class PegawaiController extends Controller
{
    private PegawaiService $pegawaiService;
    private FiltersFilterPegawaiService $filterController;

    public function __construct(PegawaiService $pegawaiService, FiltersFilterPegawaiService $filterController)
    {
        $this->pegawaiService = $pegawaiService;
        $this->filterController = $filterController;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreatePegawaiRequest $request)
    {
        $validated = $request->validated();

        try {
            $pegawai = $this->pegawaiService->store($validated);

            return response()->json([
                'status'  => 'success',
                'message' => $pegawai['message'] ?? 'Pegawai berhasil ditambahkan.',
                'data'    => $pegawai['data'] ?? $pegawai,
            ]);
        } catch (\Exception $e) {
            Log::error("[PegawaiController] Store Error: {$e->getMessage()}");

            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage() ?? 'Terjadi kesalahan pada server.',
            ], 400);
        }
    }

    /**
     * Display a paginated listing of the resource with filters.
     */
    public function dataPegawai(Request $request)
    {
        try {
            $query = $this->pegawaiService->getAllPegawai($request);
            $query = $this->filterController->applyAllFilters($query, $request);

            $perPage     = (int) $request->input('limit', 25);
            $currentPage = (int) $request->input('page', 1);
            $results     = $query->paginate($perPage, ['*'], 'page', $currentPage);
        } catch (\Throwable $e) {
            Log::error("[PegawaiController] Error: {$e->getMessage()}");
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

        $formatted = $this->pegawaiService->formatData($results);

        return response()->json([
            "total_data"   => $results->total(),
            "current_page" => $results->currentPage(),
            "per_page"     => $results->perPage(),
            "total_pages"  => $results->lastPage(),
            "data"         => $formatted
        ]);
    }

    /**
     * Export Pegawai data to Excel.
     */
    public function pegawaiExport()
    {
        return Excel::download(new PegawaiExport, 'data_pegawai.xlsx');
    }
}
