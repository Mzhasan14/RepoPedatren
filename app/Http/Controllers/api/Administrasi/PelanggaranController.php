<?php

namespace App\Http\Controllers\api\Administrasi;

use App\Exports\BaseExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Services\Administrasi\PelanggaranService;
use App\Http\Requests\Administrasi\PelanggaranRequest;
use App\Http\Requests\Administrasi\BerkasPelanggaranRequest;
use App\Services\Administrasi\Filters\FilterPelanggaranService;

class PelanggaranController extends Controller
{
    private PelanggaranService $pelanggaran;

    private FilterPelanggaranService $filter;

    public function __construct(FilterPelanggaranService $filter, PelanggaranService $pelanggaran)
    {
        $this->pelanggaran = $pelanggaran;
        $this->filter = $filter;
    }

    public function getAllPelanggaran(Request $request)
    {
        try {
            $query = $this->pelanggaran->getAllPelanggaran($request);
            $query = $this->filter->pelanggaranFilters($query, $request);
            $query->latest('pl.created_at');

            $perPage = (int) $request->input('limit', 25);
            $currentPage = (int) $request->input('page', 1);
            $results = $query->paginate($perPage, ['*'], 'page', $currentPage);
        } catch (\Throwable $e) {
            Log::error("[PelanggaranController] Error: {$e->getMessage()}");

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

        $formatted = $this->pelanggaran->formatData($results);

        return response()->json([
            'total_data' => $results->total(),
            'current_page' => $results->currentPage(),
            'per_page' => $results->perPage(),
            'total_pages' => $results->lastPage(),
            'data' => $formatted,
        ]);
    }

    public function index($bioId)
    {
        try {
            $result = $this->pelanggaran->index($bioId);
            if (! $result['status']) {
                return response()->json([
                    'message' => $result['message'] ?? 'Data tidak ditemukan.',
                ], 200);
            }

            return response()->json([
                'message' => 'Data berhasil ditampilkan',
                'data' => $result['data'],
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal ambil data pelanggaran: ' . $e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat menampilkan data.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(PelanggaranRequest $request, $bioId)
    {
        try {
            $result = $this->pelanggaran->store($request->validated(), $bioId);
            if (! $result['status']) {
                return response()->json([
                    'message' => $result['message'],
                ], 200);
            }

            return response()->json([
                'message' => 'Data berhasil ditambah',
                'data' => $result['data'],
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal tambah pelanggaran: ' . $e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $result = $this->pelanggaran->show($id);
            if (! $result['status']) {
                return response()->json([
                    'message' => $result['message'] ?? 'Data tidak ditemukan.',
                ], 200);
            }

            return response()->json([
                'message' => 'Detail data berhasil ditampilkan',
                'data' => $result['data'],
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal ambil detail pelanggaran: ' . $e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat menampilkan data.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(PelanggaranRequest $request, $id)
    {
        try {
            $result = $this->pelanggaran->update($request->validated(), $id);
            if (! $result['status']) {
                return response()->json([
                    'message' => $result['message'],
                ], 200);
            }

            return response()->json([
                'message' => 'Data berhasil diperbarui',
                'data' => $result['data'],
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal update pelanggaran: ' . $e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function addBerkasPelanggaran(BerkasPelanggaranRequest $request, $id)
    {
        try {
            $result = $this->pelanggaran->addBerkasPelanggaran($request->validated(), $id);
            if (! $result['status']) {
                return response()->json([
                    'message' => $result['message'],
                ], 200);
            }

            return response()->json([
                'message' => 'Data berkas berhasil ditambah',
                'data' => $result['data'],
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal tambah berkas pelanggaran: ' . $e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function exportExcel(Request $request)
    {
        // **Default fields cerdas (paling penting + umum)**
        $defaultExportFields = [
            'nama_santri',
            'nis',
            'jenis_kelamin',
            'wilayah',
            'blok',
            'kamar',
            'lembaga',
            'kelas',
            'rombel',
            'status_pelanggaran',
            'jenis_pelanggaran',
            'jenis_putusan',
            'diproses_mahkamah',
            'pencatat',
            'keterangan',
        ];

        // Semua kemungkinan kolom (untuk urutan dan validasi)
        $columnOrder = [
            'nama_santri',
            'nis',
            'jenis_kelamin',
            'wilayah',
            'blok',
            'kamar',
            'lembaga',
            'jurusan',
            'kelas',
            'rombel',
            'status_pelanggaran',
            'jenis_pelanggaran',
            'jenis_putusan',
            'diproses_mahkamah',
            'pencatat',
            'keterangan',
        ];

        $optionalFields = $request->input('fields', []);

        // Gabungkan default + optional lalu pastikan urutan valid
        $fields = array_unique(array_merge($defaultExportFields, $optionalFields));
        $fields = array_values(array_intersect($columnOrder, $fields));

        // Ambil data query export dari service
        $query = $this->pelanggaran->getExportPelanggaranQuery($fields, $request);

        // Terapkan filter jika ada
        if (method_exists($this->filter, 'pelanggaranFilters')) {
            $query = $this->filter->pelanggaranFilters($query, $request);
        }

        $query = $query->latest('pl.id');

        // Jika all, ambil semua; kalau tidak, ambil limit
        $results = $request->input('all') === 'true'
            ? $query->get()
            : $query->limit((int) $request->input('limit', 100))->get();

        $addNumber = true;
        $formatted = $this->pelanggaran->formatDataExport($results, $fields, $addNumber);
        $headings = $this->pelanggaran->getFieldExportHeadings($fields, $addNumber);

        $now = now()->format('Y-m-d_H-i-s');
        $filename = "pelanggaran_{$now}.xlsx";

        return Excel::download(new BaseExport($formatted, $headings), $filename);
    }
}
