<?php

namespace App\Http\Controllers\Api\Pegawai;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pegawai\CreateJadwalPelajaranRequest;
use App\Http\Requests\Pegawai\CreateMataPelajaran;
use App\Http\Requests\Pegawai\StoreJadwalPelajaranRequest;
use App\Http\Requests\Pegawai\UpdateJadwalPelajaranRequest;
use App\Models\Pegawai\JamPelajaran;
use App\Services\Pegawai\Filters\FilterJadwalPelajaranService;
use App\Services\Pegawai\Filters\FilterMataPelajaranService;
use App\Services\Pegawai\Filters\Formulir\JadwalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class MataPelajaranController extends Controller
{
    
        private JadwalService $JadwalService;

        private FilterMataPelajaranService $filterMataPelajaran;

        private FilterJadwalPelajaranService $FilterJadwalPelajaranService;

        public function __construct(
        JadwalService $JadwalService,
        FilterMataPelajaranService $filterMataPelajaran,
        FilterJadwalPelajaranService $FilterJadwalPelajaranService
        ) {
            $this->FilterJadwalPelajaranService = $FilterJadwalPelajaranService;
            $this->JadwalService = $JadwalService;
            $this->filterMataPelajaran = $filterMataPelajaran;
        }
    public function index()
    {
        try {
            $result = $this->JadwalService->list();

            return response()->json([
                'message' => 'Data jam pelajaran berhasil ditampilkan.',
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal ambil data jam pelajaran: ' . $e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat menampilkan data.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(CreateJadwalPelajaranRequest $request)
    {
        try {
            $result = $this->JadwalService->create($request->validated());

            if (! $result['status']) {
                return response()->json([
                    'message' => $result['message'] ?? 'Gagal menambah jam pelajaran.',
                ], 422);
            }

            return response()->json([
                'message' => 'Jam pelajaran berhasil ditambahkan.',
                'data' => $result['data'],
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal menambahkan jam pelajaran: ' . $e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat menambah data.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function show($id)
    {
        try {
            $result = $this->JadwalService->show($id);

            if (! $result['status']) {
                return response()->json([
                    'message' => $result['message'] ?? 'Jam pelajaran tidak ditemukan.',
                ], 404);
            }

            return response()->json([
                'message' => 'Jam pelajaran berhasil ditemukan.',
                'data' => $result['data'],
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal menampilkan jam pelajaran: ' . $e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat mengambil data.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(CreateJadwalPelajaranRequest $request, $id)
    {
        try {
            $result = $this->JadwalService->update($id, $request->validated());

            if (! $result['status']) {
                return response()->json([
                    'message' => $result['message'] ?? 'Gagal mengubah jam pelajaran.',
                ], 422);
            }

            return response()->json([
                'message' => 'Jam pelajaran berhasil diperbarui.',
                'data' => $result['data'],
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal memperbarui jam pelajaran: ' . $e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat memperbarui data.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $result = $this->JadwalService->delete($id);

            if (! $result['status']) {
                return response()->json([
                    'message' => $result['message'] ?? 'Gagal menghapus jam pelajaran.',
                ], 422);
            }

            return response()->json([
                'message' => $result['message'] ?? 'Jam pelajaran berhasil dihapus.',
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal menghapus jam pelajaran: ' . $e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat menghapus data.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function getAllMapel(Request $request)
    {
        try {
            $query = $this->JadwalService->getAllMapelQuery($request);
            $query = $this->filterMataPelajaran->applyMapelFilters($query, $request); // filter eksternal

            $perPage = (int) $request->input('limit', 25);
            $currentPage = (int) $request->input('page', 1);
            $results = $query->paginate($perPage, ['*'], 'page', $currentPage);

            $formatted = $this->JadwalService->formatData($results);

            return response()->json([
                'total_data' => $results->total(),
                'current_page' => $results->currentPage(),
                'per_page' => $results->perPage(),
                'total_pages' => $results->lastPage(),
                'data' => $formatted,
            ]);
        } catch (\Throwable $e) {
            Log::error("[MataPelajaranController] Error: {$e->getMessage()}");

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan pada server',
            ], 500);
        }
    }
    public function createMataPelajaran(CreateMataPelajaran $request)
    {
        try {
            $result = $this->JadwalService->createMataPelajaran($request->validated());

            if (! $result['status']) {
                return response()->json([
                    'message' => $result['message'] ?? 'Gagal menambahkan mata pelajaran.',
                ], 422);
            }

            return response()->json([
                'message' => 'Mata pelajaran berhasil ditambahkan.',
                'data' => $result['data'],
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal menambahkan mata pelajaran: ' . $e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat menambah data.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function DestroyMapel(string $materiId)
    {
        try {
            $result = $this->JadwalService->DestroyMapel($materiId);

            if (! $result['status']) {
                return response()->json([
                    'message' => $result['message'] ?? 'Gagal menghapus materi.',
                ], 200); // masih 200 seperti di contohmu
            }

            return response()->json([
                'message' => 'Materi berhasil dihapus.',
                'data' => $result['data'] ?? null,
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal nonaktifkan materi ajar: '.$e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat menghapus materi.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function getAllJadwal(Request $request)
    {
        try {
            // Pastikan semua parameter dikirim dan tidak kosong
            if (
                blank($request->input('lembaga_id')) ||
                blank($request->input('jurusan_id')) ||
                blank($request->input('kelas_id'))
            ) {
                return response()->json([
                    'status' => 'success',
                    'meta' => null,
                    'data' => []
                ]);
            }

            // Proses query setelah semua filter valid
            $query = $this->JadwalService->getAllJadwalQuery($request);
            $query = $this->FilterJadwalPelajaranService->applyJadwalFilters($query, $request);
            $results = $query->get();

            $formatted = $this->JadwalService->groupJadwalByHari($results);

            $meta = $results->first();
            $metaInfo = $meta ? [
                'lembaga' => $meta->nama_lembaga,
                'jurusan' => $meta->nama_jurusan,
                'kelas' => $meta->nama_kelas,
                'semester' => 'Semester ' . $meta->semester
            ] : null;

            return response()->json([
                'status' => 'success',
                'meta' => $metaInfo,
                'data' => $formatted
            ]);
        } catch (\Throwable $e) {
            Log::error("[JadwalPelajaranController] Error: {$e->getMessage()}");

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan pada server'
            ], 500);
        }
    }
    public function storeJadwal(StoreJadwalPelajaranRequest $request)
    {
        $result = $this->JadwalService->storeJadwalMataPelajaran($request->validated());

        if (! $result['status']) {
            return response()->json(['message' => $result['message']], 422);
        }

        return response()->json([
            'message' => $result['message'],
            'data'    => $result['data'],
        ]);
    }
    public function showJadwal(int $id)
    {
        $result = $this->JadwalService->getById($id);

        if (! $result['status']) {
            return response()->json(['message' => $result['message']], 404);
        }

        return response()->json([
            'message' => $result['message'],
            'data' => $result['data'],
        ]);
    }
    public function updateJadwal(UpdateJadwalPelajaranRequest $request, int $id)
    {
        $result = $this->JadwalService->updateJadwal($id, $request->validated());

        if (! $result['status']) {
            return response()->json(['message' => $result['message']], 422);
        }

        return response()->json([
            'message' => $result['message'],
            'data' => $result['data'],
        ]);
    }
    public function batchDelete(Request $request)
    {
        try {
            $this->JadwalService->deleteBatchByIds($request->input('selected_ids', []));

            return response()->json([
                'status' => 'success',
                'message' => 'Data jadwal pelajaran berhasil dihapus.',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error("[JadwalPelajaranController::batchDelete] Error: {$e->getMessage()}");

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan pada server.',
            ], 500);
        }
    }
}
