<?php

namespace App\Http\Controllers\api\PesertaDidik;

use App\Exports\BaseExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\PesertaDidik\CreateKhadamRequest;
use App\Services\PesertaDidik\Filters\FilterKhadamService;
use App\Services\PesertaDidik\KhadamService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

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
            $query = $query->latest('b.created_at');

            $perPage = (int) $request->input('limit', 25);
            $currentPage = (int) $request->input('page', 1);
            $results = $query->paginate($perPage, ['*'], 'page', $currentPage);
        } catch (\Throwable $e) {
            Log::error("[KhadamController] Error: {$e->getMessage()}");

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

        $formatted = $this->khadamService->formatData($results);

        return response()->json([
            'total_data' => $results->total(),
            'current_page' => $results->currentPage(),
            'per_page' => $results->perPage(),
            'total_pages' => $results->lastPage(),
            'data' => $formatted,
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
                'data' => $khadamService,
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
            'keterangan',
            'tanggal_mulai',
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
            'keterangan',
            'tanggal_mulai',
            'anak_ke',
            'jumlah_saudara',
            'alamat',
            'nis',
            'domisili_santri',
            'angkatan_santri',
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
        $query = $this->khadamService->getExportKhadamQuery($fields, $request);
        $query = $this->filterController->khadamFilters($query, $request);
        $query = $query->latest('b.created_at');

        // Jika export all, ambil semua data, else limit
        $results = $request->input('all') === 'true'
            ? $query->get()
            : $query->limit((int) $request->input('limit', 100))->get();

        $addNumber = true;
        $formatted = $this->khadamService->formatDataExport($results, $fields, $addNumber);
        $headings = $this->khadamService->getFieldExportHeadings($fields, $addNumber);

        $now = now()->format('Y-m-d_H-i-s');
        $filename = "khadam_{$now}.xlsx";

        return Excel::download(new BaseExport($formatted, $headings), $filename);
    }
}
