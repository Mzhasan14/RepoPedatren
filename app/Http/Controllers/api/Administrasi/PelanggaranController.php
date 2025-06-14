<?php

namespace App\Http\Controllers\api\Administrasi;

use App\Http\Controllers\Controller;
use App\Http\Requests\Administrasi\BerkasPelanggaranRequest;
use App\Http\Requests\Administrasi\PelanggaranRequest;
use App\Services\Administrasi\Filters\FilterPelanggaranService;
use App\Services\Administrasi\PelanggaranService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
            Log::error('Gagal ambil data pelanggaran: '.$e->getMessage());

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
            Log::error('Gagal tambah pelanggaran: '.$e->getMessage());

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
            Log::error('Gagal ambil detail pelanggaran: '.$e->getMessage());

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
            Log::error('Gagal update pelanggaran: '.$e->getMessage());

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
            Log::error('Gagal tambah berkas pelanggaran: '.$e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
