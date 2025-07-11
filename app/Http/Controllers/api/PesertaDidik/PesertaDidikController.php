<?php

namespace App\Http\Controllers\api\PesertaDidik;

use App\Exports\BaseExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\PesertaDidik\CreatePesertaDidikRequest;
use App\Services\PesertaDidik\Filters\FilterPesertaDidikService;
use App\Services\PesertaDidik\PesertaDidikService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

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

    // Untuk LIST
    public function getAllPesertaDidik(Request $request): JsonResponse
    {
        try {
            $query = $this->pesertaDidik->getAllPesertaDidik($request);
            $query = $this->filter->pesertaDidikFilters($query, $request);
            $query = $query->latest('b.created_at');

            $perPage = (int) $request->input('limit', 25);
            $currentPage = (int) $request->input('page', 1);

            $results = $query->paginate($perPage, ['*'], 'page', $currentPage);
        } catch (\Throwable $e) {
            Log::error("[PesertaDidikController] Error: {$e->getMessage()}");

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

        $formatted = $this->pesertaDidik->formatData($results);

        return response()->json([
            'total_data' => $results->total(),
            'current_page' => $results->currentPage(),
            'per_page' => $results->perPage(),
            'total_pages' => $results->lastPage(),
            'data' => $formatted,
        ]);
    }

    public function store(CreatePesertaDidikRequest $request)
    {
        try {
            $pesertaDidik = $this->pesertaDidik->store($request->validated());

            return response()->json([
                'message' => 'Peserta Didik berhasil disimpan.',
                'data' => $pesertaDidik,
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            // Tangani error umum (misalnya database, validasi, dll)
            return response()->json([
                'message' => 'Terjadi kesalahan saat menyimpan data.',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Untuk EXPORT
    public function exportExcel(Request $request)
    {
        $defaultExportFields = [
            'nama',
            'tempat_lahir',
            'tanggal_lahir',
            'jenis_kelamin',
            'nis',
            'angkatan_santri',
            'no_induk',
            'lembaga',
            'jurusan',
            'kelas',
            'rombel',
            'angkatan_pelajar',
        ];

        $columnOrder = [
            'no_kk',
            'nik',
            'niup',
            'nama',
            'tempat_lahir',
            'tanggal_lahir',
            'jenis_kelamin',
            'anak_ke',
            'jumlah_saudara',
            'alamat',
            'nis',
            'domisili_santri',
            'angkatan_santri',
            'status',
            'no_induk',
            'lembaga',
            'jurusan',
            'kelas',
            'rombel',
            'angkatan_pelajar',
            'ibu_kandung',
        ];

        $optionalFields = $request->input('fields', []);

        // Gabung default + optional, urutkan sesuai $columnOrder, tidak ada duplikat
        $fields = array_unique(array_merge($defaultExportFields, $optionalFields));
        $fields = array_values(array_intersect($columnOrder, $fields));

        // Ambil query export (sudah pakai base query utama!)
        $query = $this->pesertaDidik->getExportPesertaDidikQuery($fields, $request);
        $query = $this->filter->pesertaDidikFilters($query, $request);
        $query = $query->latest('b.created_at');

        // Jika export all, ambil semua data, else limit
        $results = $request->input('all') === 'true'
            ? $query->get()
            : $query->limit((int) $request->input('limit', 100))->get();

        $addNumber = true;
        $formatted = $this->pesertaDidik->formatDataExport($results, $fields, $addNumber);
        $headings = $this->pesertaDidik->getFieldExportHeadings($fields, $addNumber);

        $now = now()->format('Y-m-d_H-i-s');
        $filename = "peserta_didik_{$now}.xlsx";

        return Excel::download(new BaseExport($formatted, $headings), $filename);
    }
}
