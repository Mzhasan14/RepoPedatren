<?php

namespace App\Http\Controllers\Api\Administrasi;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Services\Administrasi\PerizinanService;
use App\Http\Requests\Administrasi\PerizinanRequest;
use App\Http\Requests\Administrasi\BerkasPerizinanRequest;
use App\Services\Administrasi\Filters\FilterPerizinanService;

class PerizinanController extends Controller
{
    private PerizinanService $perizinan;
    private FilterPerizinanService $filter;

    public function __construct(FilterPerizinanService $filter, PerizinanService $perizinan)
    {
        $this->filter = $filter;
        $this->perizinan = $perizinan;
    }

    public function getAllPerizinan(Request $request)
    {
        try {
            $query = $this->perizinan->getAllPerizinan($request);
            $query = $this->filter->perizinanFilters($query, $request);

            $perPage     = (int) $request->input('limit', 25);
            $currentPage = (int) $request->input('page', 1);
            $results     = $query->paginate($perPage, ['*'], 'page', $currentPage);
        } catch (\Throwable $e) {
            Log::error("[PerizinanController] Error: {$e->getMessage()}");
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

        $formatted = $this->perizinan->formatData($results);

        return response()->json([
            'total_data'   => $results->total(),
            'current_page' => $results->currentPage(),
            'per_page'     => $results->perPage(),
            'total_pages'  => $results->lastPage(),
            'data'         => $formatted,
        ]);
    }

    public function index($bioId)
    {
        try {
            $result = $this->perizinan->index($bioId);
            if (!$result['status']) {
                return response()->json([
                    'message' => $result['message'] ?? 'Data tidak ditemukan.'
                ], 200);
            }

            return response()->json([
                'message' => 'Data berhasil ditampilkan',
                'data' => $result['data']
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal ambil data perizinan: ' . $e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat menampilkan data.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(PerizinanRequest $request, $bioId)
    {
        try {
            $result = $this->perizinan->store($request->validated(), $bioId);
            if (!$result['status']) {
                return response()->json([
                    'message' => $result['message']
                ], 200);
            }

            return response()->json([
                'message' => 'Data berhasil ditambah',
                'data' => $result['data']
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal tambah perizinan: ' . $e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $result = $this->perizinan->show($id);
            if (!$result['status']) {
                return response()->json([
                    'message' => $result['message'] ?? 'Data tidak ditemukan.'
                ], 200);
            }

            return response()->json([
                'message' => 'Detail data berhasil ditampilkan',
                'data' => $result['data']
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal ambil detail perizinan: ' . $e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat menampilkan data.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(PerizinanRequest $request, $id)
    {
        try {
            $result = $this->perizinan->update($request->validated(), $id);
            if (!$result['status']) {
                return response()->json([
                    'message' => $result['message']
                ], 200);
            }

            return response()->json([
                'message' => 'Data berhasil diperbarui',
                'data' => $result['data']
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal update perizinan: ' . $e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function addBerkasPerizinan(BerkasPerizinanRequest $request, $id)
    {
        try {
            $result = $this->perizinan->addBerkasPerizinan($request->validated(), $id);
            if (!$result['status']) {
                return response()->json([
                    'message' => $result['message']
                ], 200);
            }

            return response()->json([
                'message' => 'Data berkas berhasil ditambah',
                'data' => $result['data']
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal tambah berkas perizinan: ' . $e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses data',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
