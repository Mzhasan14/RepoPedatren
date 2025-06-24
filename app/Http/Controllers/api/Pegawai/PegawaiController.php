<?php

namespace App\Http\Controllers\api\Pegawai;

use App\Exports\BaseExport;
use App\Exports\Pegawai\PegawaiExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Pegawai\CreatePegawaiRequest;
use App\Models\Pegawai\Pegawai;
use App\Services\Pegawai\Filters\FilterPegawaiService as FiltersFilterPegawaiService;
use App\Services\Pegawai\PegawaiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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

            // Jika status false, artinya error, langsung return response error
            if (isset($pegawai['status']) && $pegawai['status'] === false) {
                return response()->json([
                    'status' => 'error',
                    'message' => $pegawai['message'] ?? 'Gagal menyimpan data pegawai.',
                    'error' => $pegawai['error'] ?? null,
                ], 400);
            }

            // Jika sukses, return response sukses
            return response()->json([
                'status' => 'success',
                'message' => $pegawai['message'] ?? 'Pegawai berhasil ditambahkan.',
                'data' => $pegawai['data'] ?? $pegawai,
            ]);
        } catch (\Exception $e) {
            Log::error("[PegawaiController] Store Error: {$e->getMessage()}");

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage() ?? 'Terjadi kesalahan pada server.',
            ], 500);
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

            $perPage = (int) $request->input('limit', 25);
            $currentPage = (int) $request->input('page', 1);
            $results = $query->paginate($perPage, ['*'], 'page', $currentPage);
        } catch (\Throwable $e) {
            Log::error("[PegawaiController] Error: {$e->getMessage()}");

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

        $formatted = $this->pegawaiService->formatData($results);

        return response()->json([
            'total_data' => $results->total(),
            'current_page' => $results->currentPage(),
            'per_page' => $results->perPage(),
            'total_pages' => $results->lastPage(),
            'data' => $formatted,
        ]);
    }

    /**
     * Export Pegawai data to Excel.
     */
    public function exportExcel(Request $request)
    {
        // Kolom default untuk export
        $defaultExportFields = [
            'nama_lengkap',
            'jenis_kelamin',
            'status_aktif',
        ];

        // Urutan kolom export
        $columnOrder = [
            'no_kk',               // paling depan
            'nik',
            'niup',
            'nama_lengkap',
            'tempat_tanggal_lahir',
            'jenis_kelamin',
            'alamat',
            'pendidikan_terakhir',
            'status_aktif',
        ];

        // Ambil kolom dari request checkbox
        $optionalFields = $request->input('fields', []);

        // Gabung default + optional, sesuai urutan columnOrder
        $fields = array_unique(array_merge($defaultExportFields, $optionalFields));
        $fields = array_values(array_intersect($columnOrder, $fields));

        // Query dari service
        $query = $this->pegawaiService->getExportPegawaiQuery($fields, $request);

        // Jika ada filter tambahan, aktifkan jika kamu pakai
        $query = $this->filterController->applyAllFilters($query, $request);

        $query = $query->latest('b.created_at');

        // Ambil semua atau limit
        $results = $request->input('all') === 'true'
            ? $query->get()
            : $query->limit((int) $request->input('limit', 100))->get();

        // Format dan heading
        $addNumber = true;
        $formatted = $this->pegawaiService->formatDataExport($results, $fields, $addNumber);
        $headings = $this->pegawaiService->getFieldExportHeadings($fields, $addNumber);

        $now = now()->format('Y-m-d_H-i-s');
        $filename = "pegawai_{$now}.xlsx";

        return Excel::download(new BaseExport($formatted, $headings), $filename);
    }
}
