<?php

namespace App\Http\Controllers\api\PesertaDidik;

use App\Exports\BaseExport;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
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
            $query = $query->latest('b.created_at');

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
            'ayah_kandung'
        ];

        $optionalFields = $request->input('fields', []);

        // Gabung default + optional, urutkan sesuai $columnOrder, tidak ada duplikat
        $fields = array_unique(array_merge($defaultExportFields, $optionalFields));
        $fields = array_values(array_intersect($columnOrder, $fields));

        // Ambil query export (sudah pakai base query utama!)
        $query = $this->anakPegawaiService->getExportAnakPegawaiQuery($fields, $request);
        $query = $this->filterController->anakPegawaiFilters($query, $request);
        $query = $query->latest('b.created_at');

        // Jika export all, ambil semua data, else limit
        $results = $request->input('all') === 'true'
            ? $query->get()
            : $query->limit((int) $request->input('limit', 100))->get();

        $addNumber = true;
        $formatted = $this->anakPegawaiService->formatDataExport($results, $fields, $addNumber);
        $headings  = $this->anakPegawaiService->getFieldExportHeadings($fields, $addNumber);

        $now = now()->format('Y-m-d_H-i-s');
        $filename = "anak_pegawai_{$now}.xlsx";

        return Excel::download(new BaseExport($formatted, $headings), $filename);
    }
}
