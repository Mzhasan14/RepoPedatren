<?php

namespace App\Http\Controllers\api\Pegawai;

use App\Exports\Pegawai\PengurusExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Pegawai\KeluarPengurusRequest;
use App\Http\Requests\Pegawai\PengurusRequest;
use App\Http\Requests\Pegawai\PindahPengurusRequest;
use App\Services\Pegawai\Filters\FilterPengurusService as FiltersFilterPengurusService;
use App\Services\Pegawai\Filters\Formulir\PengurusService as FormulirPengurusService;
use App\Services\Pegawai\PengurusService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class PengurusController extends Controller
{
    private PengurusService $pengurusService;

    private FiltersFilterPengurusService $filterController;

    private FormulirPengurusService $formulirPengurus;

    public function __construct(
        FormulirPengurusService $formulirPengurus,
        PengurusService $pengurusService,
        FiltersFilterPengurusService $filterController
    ) {
        $this->pengurusService = $pengurusService;
        $this->filterController = $filterController;
        $this->formulirPengurus = $formulirPengurus;
    }

    public function index($id)
    {
        try {
            $result = $this->formulirPengurus->index($id);

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
            Log::error('Gagal ambil data Pengurus: '.$e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat menampilkan data.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function edit($id)
    {
        try {
            $result = $this->formulirPengurus->show($id);

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
            Log::error('Gagal ambil detail Pengurus: '.$e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat menampilkan data.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(PengurusRequest $request, $bioId)
    {
        try {
            $result = $this->formulirPengurus->store($request->validated(), $bioId);

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
            Log::error('Gagal tambah Pengurus: '.$e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(PengurusRequest $request, $id)
    {
        try {
            $result = $this->formulirPengurus->update($request->validated(), $id);

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
            Log::error('Gagal update Pengurus: '.$e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function dataPengurus(Request $request)
    {
        try {
            $query = $this->pengurusService->getAllPengurus($request);
            $query = $this->filterController->applyAllFilters($query, $request);

            $perPage = (int) $request->input('limit', 25);
            $currentPage = (int) $request->input('page', 1);
            $results = $query->paginate($perPage, ['*'], 'page', $currentPage);
        } catch (\Throwable $e) {
            Log::error("[PengurusController] Error: {$e->getMessage()}");

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

        $formatted = $this->pengurusService->formatData($results);

        return response()->json([
            'total_data' => $results->total(),
            'current_page' => $results->currentPage(),
            'per_page' => $results->perPage(),
            'total_pages' => $results->lastPage(),
            'data' => $formatted,
        ]);
    }

    public function pindahPengurus(PindahPengurusRequest $request, $id)
    {
        try {
            $validated = $request->validated();
            $result = $this->formulirPengurus->pindahPengurus($validated, $id);

            if (! $result['status']) {
                return response()->json([
                    'message' => $result['message'],
                ], 200);
            }

            return response()->json([
                'message' => 'Pengurus baru berhasil dibuat',
                'data' => $result['data'],
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal pindah Pengurus: '.$e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function keluarPengurus(KeluarPengurusRequest $request, $id)
    {
        try {
            $validated = $request->validated();
            $result = $this->formulirPengurus->keluarPengurus($validated, $id);

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
            Log::error('Gagal keluar Pengurus: '.$e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function pengurusExport()
    {
        return Excel::download(new PengurusExport, 'data_pengurus.xlsx');
    }
}
