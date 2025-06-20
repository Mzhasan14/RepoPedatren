<?php

namespace App\Http\Controllers\api\Administrasi;

use App\Http\Controllers\Controller;
use App\Http\Requests\Administrasi\PengunjungMahromRequest;
use App\Services\Administrasi\Filters\FilterPengunjungMahromService;
use App\Services\Administrasi\PengunjungMahromService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PengunjungMahromController extends Controller
{
    private PengunjungMahromService $pengunjung;

    private FilterPengunjungMahromService $filter;

    public function __construct(PengunjungMahromService $pengunjung, FilterPengunjungMahromService $filter)
    {
        $this->pengunjung = $pengunjung;
        $this->filter = $filter;
    }

    public function getAllPengunjung(Request $request)
    {
        try {
            $query = $this->pengunjung->getAllPengunjung($request);
            $query = $this->filter->pengunjungFilters($query, $request);
            $query->latest('pm.created_at');

            $perPage = (int) $request->input('limit', 25);
            $currentPage = (int) $request->input('page', 1);
            $results = $query->paginate($perPage, ['*'], 'page', $currentPage);
        } catch (\Throwable $e) {
            Log::error("[PengunjungController] Error: {$e->getMessage()}");

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

        $formatted = $this->pengunjung->formatData($results);

        return response()->json([
            'total_data' => $results->total(),
            'current_page' => $results->currentPage(),
            'per_page' => $results->perPage(),
            'total_pages' => $results->lastPage(),
            'data' => $formatted,
        ]);
    }

    public function store(PengunjungMahromRequest $request)
    {
        try {
            $result = $this->pengunjung->store($request->validated());
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
            Log::error('Gagal tambah pengunjung mahrom: '.$e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $result = $this->pengunjung->show($id);
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
            Log::error('Gagal ambil detail pengunjung mahrom: '.$e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat menampilkan data.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(PengunjungMahromRequest $request, $id)
    {
        try {
            $result = $this->pengunjung->update($request->validated(), $id);
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
            Log::error('Gagal update pengunjung mahrom: '.$e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
