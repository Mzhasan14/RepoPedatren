<?php

namespace App\Http\Controllers\api\PesertaDidik;

use App\Exports\BaseExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Services\PesertaDidik\BersaudaraService;
use App\Services\PesertaDidik\Filters\FilterBersaudaraService;

class BersaudaraController extends Controller
{
    private BersaudaraService $bersaudara;
    private FilterBersaudaraService $filter;
    public function __construct(BersaudaraService $bersaudara, FilterBersaudaraService $filter)
    {
        $this->bersaudara = $bersaudara;
        $this->filter = $filter;
    }

    public function getAllBersaudara(Request $request)
    {
        try {
            $query = $this->bersaudara->getAllBersaudara($request);
            $query = $this->filter->bersaudaraFilters($query, $request);
            $query = $query->orderByDesc('k.no_kk');

            $perPage     = (int) $request->input('limit', 25);
            $currentPage = (int) $request->input('page', 1);
            $results     = $query->paginate($perPage, ['*'], 'page', $currentPage);
        } catch (\Throwable $e) {
            Log::error("[BersaudaraController] Error: {$e->getMessage()}");
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

        $formatted = $this->bersaudara->formatData($results);

        return response()->json([
            "total_data"   => $results->total(),
            "current_page" => $results->currentPage(),
            "per_page"     => $results->perPage(),
            "total_pages"  => $results->lastPage(),
            "data"         => $formatted
        ]);
    }

    // Untuk EXPORT
    public function exportExcel(Request $request)
    {
        $defaultExportFields = [
            'no_kk',
            'nik',
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
            'ibu_kandung'
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
            'ibu_kandung'
        ];

        $optionalFields = $request->input('fields', []);

        // Gabung default + optional, urutkan sesuai $columnOrder, tidak ada duplikat
        $fields = array_unique(array_merge($defaultExportFields, $optionalFields));
        $fields = array_values(array_intersect($columnOrder, $fields));

        // Ambil query export (sudah pakai base query utama!)
        $query = $this->bersaudara->getExportBersaudaraQuery($fields, $request);
        $query = $this->filter->bersaudaraFilters($query, $request);
        $query = $query->orderByDesc('k.no_kk');

        // Jika export all, ambil semua data, else limit
        $results = $request->input('all') === 'true'
            ? $query->get()
            : $query->limit((int) $request->input('limit', 100))->get();

        $addNumber = true;
        $formatted = $this->bersaudara->formatDataExport($results, $fields, $addNumber);
        $headings  = $this->bersaudara->getFieldExportHeadings($fields, $addNumber);

        $now = now()->format('Y-m-d_H-i-s');
        $filename = "bersaudara_kandung_{$now}.xlsx";

        return Excel::download(new BaseExport($formatted, $headings), $filename);
    }
}
