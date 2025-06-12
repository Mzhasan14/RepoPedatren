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
            $query = $query->latest('b.id');

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

    public function exportExcel(Request $request)
    {
        // Daftar kolom default untuk export (export utama, bukan tampilan list)
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
            'no_kk',           // di depan
            'nik',
            'niup',
            'nama',
            'tempat_lahir',
            'tanggal_lahir',
            'jenis_kelamin',
            'anak_ke',
            'jumlah_saudara',
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
            'ibu_kandung'
        ];

        // Ambil kolom optional tambahan dari checkbox user (misal ['no_kk','nik',...])
        $optionalFields = $request->input('fields', []);

        // Gabung kolom default export + kolom optional (hindari duplikat)
        $fields = array_unique(array_merge($defaultExportFields, $optionalFields));
        $fields = array_values(array_intersect($columnOrder, $fields));

        // Gunakan query khusus untuk export (boleh mirip dengan list)
        $query = $this->pesertaDidik->getExportPesertaDidikQuery($fields, $request);
        $query = $this->filter->pesertaDidikFilters($query, $request);
        $query = $query->latest('b.id');

        // Jika user centang "all", ambil semua, else gunakan limit/pagination
        $results = $request->input('all') === 'true'
            ? $query->get()
            : $query->limit((int) $request->input('limit', 100))->get();

        // Format data sesuai urutan dan field export
        $addNumber = true; // Supaya kolom No selalu muncul
        $formatted = $this->pesertaDidik->formatDataExport($results, $fields, $addNumber);
        $headings  = $this->pesertaDidik->getFieldExportHeadings($fields, $addNumber);

        $now = now()->format('Y-m-d_H-i-s');
        $filename = "peserta_didik_{$now}.xlsx";

        return Excel::download(new PesertaDidikExport($formatted, $headings), $filename);
    }
}
