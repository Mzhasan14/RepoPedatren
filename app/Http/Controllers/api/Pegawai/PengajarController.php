<?php

namespace App\Http\Controllers\api\Pegawai;

use App\Exports\Pegawai\PengajarExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Pegawai\KeluarPengajarRequest;
use App\Http\Requests\Pegawai\PengajarResquest;
use App\Http\Requests\Pegawai\PindahPengajarRequest;
use App\Http\Requests\Pegawai\TambahMateriAjarRequest;
use App\Http\Requests\Pegawai\UpdatePengajarRequest;
use App\Services\Pegawai\Filters\FilterPengajarService;
use App\Services\Pegawai\Filters\Formulir\PengajarService as FormulirPengajarService;
use App\Services\Pegawai\PengajarService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class PengajarController extends Controller
{
    private PengajarService $pengajarService;

    private FilterPengajarService $filterController;

    private FormulirPengajarService $formulirPengajarService;

    public function __construct(
        FormulirPengajarService $formulirPengajarService,
        PengajarService $pengajarService,
        FilterPengajarService $filterController
    ) {
        $this->pengajarService = $pengajarService;
        $this->filterController = $filterController;
        $this->formulirPengajarService = $formulirPengajarService;
    }

    public function index($id)
    {
        try {
            $result = $this->formulirPengajarService->index($id);

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
            Log::error('Gagal ambil data pengajar: '.$e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat menampilkan data.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function edit($id)
    {
        try {
            $result = $this->formulirPengajarService->show($id);

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
            Log::error('Gagal ambil data pengajar: '.$e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat menampilkan data.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(PengajarResquest $request, $bioId)
    {
        try {
            $result = $this->formulirPengajarService->store($request->validated(), $bioId);

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
            Log::error('Gagal tambah Pengajar: '.$e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(UpdatePengajarRequest $request, string $id)
    {
        try {
            $result = $this->formulirPengajarService->update($request->validated(), $id);

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
            Log::error('Gagal update Pengajar: '.$e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getallPengajar(Request $request)
    {
        try {
            $query = $this->pengajarService->getAllPengajar($request);
            $query = $this->filterController->applyPengajarFilters($query, $request);

            $perPage = (int) $request->input('limit', 25);
            $currentPage = (int) $request->input('page', 1);
            $results = $query->paginate($perPage, ['*'], 'page', $currentPage);
        } catch (\Throwable $e) {
            Log::error("[PengajarController] Error: {$e->getMessage()}");

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

        $formatted = $this->pengajarService->formatData($results);

        return response()->json([
            'total_data' => $results->total(),
            'current_page' => $results->currentPage(),
            'per_page' => $results->perPage(),
            'total_pages' => $results->lastPage(),
            'data' => $formatted,
        ]);
    }

    public function pindahPengajar(PindahPengajarRequest $request, $id)
    {
        try {
            $validated = $request->validated();
            $result = $this->formulirPengajarService->pindahPengajar($validated, $id);

            if (! $result['status']) {
                return response()->json([
                    'message' => $result['message'],
                ], 200);
            }

            return response()->json([
                'message' => 'Pengajar baru berhasil dibuat',
                'data' => $result['data'],
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal pindah Pengajar: '.$e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function keluarPengajar(KeluarPengajarRequest $request, $id)
    {
        try {
            $validated = $request->validated();
            $result = $this->formulirPengajarService->keluarPengajar($validated, $id);

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
            Log::error('Gagal keluar Pengajar: '.$e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function nonaktifkan(string $pengajarId, string $materiId)
    {
        try {
            $result = $this->formulirPengajarService->nonaktifkan($pengajarId, $materiId);

            if (! $result['status']) {
                return response()->json([
                    'message' => $result['message'] ?? 'Gagal menonaktifkan materi.',
                ], 200); // masih 200 seperti di contohmu
            }

            return response()->json([
                'message' => 'Materi berhasil dinonaktifkan.',
                'data' => $result['data'] ?? null,
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal nonaktifkan materi ajar: '.$e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat menonaktifkan materi.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function tambahMateri(TambahMateriAjarRequest $request, string $pengajarId)
    {
        try {
            $result = $this->formulirPengajarService->tambahMateri($pengajarId, $request->validated());

            if (! $result['status']) {
                return response()->json([
                    'message' => $result['message'] ?? 'Gagal menambahkan materi ajar.',
                ], 200);
            }

            return response()->json([
                'message' => 'Materi ajar berhasil ditambahkan.',
                'data' => $result['data'] ?? null,
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal menambahkan materi ajar: '.$e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat menambahkan materi ajar.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function pengajarExport()
    {
        return Excel::download(new PengajarExport, 'data_pengajar.xlsx');
    }
}
