<?php

namespace App\Http\Controllers\api\PesertaDidik;

use App\Exports\BaseExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Services\PesertaDidik\NonDomisiliService;
use App\Services\PesertaDidik\Filters\FilterNonDomisiliService;

class NonDomisiliController extends Controller
{
    private NonDomisiliService $nonDomisili;

    private FilterNonDomisiliService $filter;

    public function __construct(nonDomisiliService $nonDomisili, FilterNonDomisiliService $filter)
    {
        $this->nonDomisili = $nonDomisili;
        $this->filter = $filter;
    }

    // Santri Non Domisili
    public function getNonDomisili(Request $request)
    {
        try {
            $query = $this->nonDomisili->getAllNonDomisili($request);
            $query = $this->filter->nonDomisiliFilters($query, $request);
            $query = $query->latest('b.created_at');

            $perPage = (int) $request->input('limit', 25);
            $currentPage = (int) $request->input('page', 1);
            $results = $query->paginate($perPage, ['*'], 'page', $currentPage);
        } catch (\Throwable $e) {
            Log::error("[NonDomisiliController] Error: {$e->getMessage()}");

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

        $formatted = $this->nonDomisili->formatData($results);

        return response()->json([
            'total_data' => $results->total(),
            'current_page' => $results->currentPage(),
            'per_page' => $results->perPage(),
            'total_pages' => $results->lastPage(),
            'data' => $formatted,
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
            'alamat',
            'nis',
            'angkatan_santri',
            'status',
            'no_induk',
            'pendidikan',
            'angkatan_pelajar',
            'ibu_kandung',
            'ayah_kandung',
        ];

        // Ambil kolom optional tambahan dari checkbox user (misal ['no_kk','nik',...])
        $optionalFields = $request->input('fields', []);

        // Gabung kolom default export + kolom optional (hindari duplikat)
        $fields = array_unique(array_merge($defaultExportFields, $optionalFields));
        $fields = array_values(array_intersect($columnOrder, $fields));

        // Gunakan query khusus untuk export (boleh mirip dengan list)
        $query = $this->nonDomisili->getExportNonDomisiliQuery($fields, $request);
        $query = $this->filter->nonDomisiliFilters($query, $request);

        $query = $query->latest('b.created_at');

        // Jika user centang "all", ambil semua, else gunakan limit/pagination
        $results = $request->input('all') === 'true'
            ? $query->get()
            : $query->limit((int) $request->input('limit', 100))->get();

        // Format data sesuai urutan dan field export
        $addNumber = true; // Supaya kolom No selalu muncul
        $formatted = $this->nonDomisili->formatDataExport($results, $fields, $addNumber);
        $headings = $this->nonDomisili->getFieldExportHeadings($fields, $addNumber);

        $now = now()->format('Y-m-d_H-i-s');
        $filename = "santri_non_domisili_{$now}.xlsx";

        return Excel::download(new BaseExport($formatted, $headings), $filename);
    }
}
