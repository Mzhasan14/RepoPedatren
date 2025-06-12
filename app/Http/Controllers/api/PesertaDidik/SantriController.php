<?php

namespace App\Http\Controllers\api\PesertaDidik;

use App\Exports\BaseExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Services\PesertaDidik\SantriService;
use App\Services\PesertaDidik\Filters\FilterSantriService;

class SantriController extends Controller
{
    private SantriService $santriService;
    private FilterSantriService $filterController;

    public function __construct(SantriService $santriService, FilterSantriService $filterController)
    {
        $this->santriService = $santriService;
        $this->filterController = $filterController;
    }

    // Santri Domisili
    public function getAllSantri(Request $request)
    {
        try {
            $query = $this->santriService->getAllSantri($request);
            $query = $this->filterController->santriFilters($query, $request);

            $query = $query->latest('b.id');

            $perPage     = (int) $request->input('limit', 25);
            $currentPage = (int) $request->input('page', 1);
            $results     = $query->paginate($perPage, ['*'], 'page', $currentPage);
        } catch (\Throwable $e) {
            Log::error("[SantriController] Error: {$e->getMessage()}");
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

        $formatted = $this->santriService->formatData($results);

        return response()->json([
            "total_data"   => $results->total(),
            "current_page" => $results->currentPage(),
            "per_page"     => $results->perPage(),
            "total_pages"  => $results->lastPage(),
            "data"         => $formatted
        ]);
    }

    public function exportExcel(Request $request)
    {
        // Daftar kolom default untuk export (export utama, bukan tampilan list)
        $defaultExportFields = [
            'nama',
            'jenis_kelamin',
            'nis',
            'angkatan_santri',
            'angkatan_pelajar',
        ];

        $columnOrder = [
            'no_kk',           // di depan
            'nik',
            'niup',
            'nama',
            'tempat_tanggal_lahir',
            'jenis_kelamin',
            'anak_ke',
            'jumlah_saudara',
            'nis',
            'domisili_santri',
            'angkatan_santri',
            'status',
            'no_induk',
            'pendidikan',
            'angkatan_pelajar',
            'ibu_kandung',
            'ayah_kandung'
        ];

        // Ambil kolom optional tambahan dari checkbox user (misal ['no_kk','nik',...])
        $optionalFields = $request->input('fields', []);

        // Gabung kolom default export + kolom optional (hindari duplikat)
        $fields = array_unique(array_merge($defaultExportFields, $optionalFields));
        $fields = array_values(array_intersect($columnOrder, $fields));

        // Gunakan query khusus untuk export (boleh mirip dengan list)
        $query = $this->santriService->getExportSantriQuery($fields, $request);
        $query = $this->filterController->santriFilters($query, $request);

        $query = $query->latest('b.id');

        // Jika user centang "all", ambil semua, else gunakan limit/pagination
        $results = $request->input('all') === 'true'
            ? $query->get()
            : $query->limit((int) $request->input('limit', 100))->get();

        // Format data sesuai urutan dan field export
        $addNumber = true; // Supaya kolom No selalu muncul
        $formatted = $this->santriService->formatDataExport($results, $fields, $addNumber);
        $headings  = $this->santriService->getFieldExportHeadings($fields, $addNumber);

        $now = now()->format('Y-m-d_H-i-s');
        $filename = "santri_{$now}.xlsx";

        return Excel::download(new BaseExport($formatted, $headings), $filename);
    }
}
