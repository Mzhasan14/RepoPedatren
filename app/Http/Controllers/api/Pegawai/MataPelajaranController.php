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
use Illuminate\Database\Eloquent\ModelNotFoundException;

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
                    'message' => $result['message'] ?? 'Gagal menonaktifkan materi.',
                ], 200); // masih 200 seperti di contohmu
            }

            return response()->json([
                'status' => true,
                'message' => 'Materi berhasil dinonaktifkan.',
                'data' => $result['data'] ?? null,
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal nonaktifkan materi ajar: '.$e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat menonaktifkan materi.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function getAllJadwal(Request $request)
    {
        try {
            // Validasi parameter wajib
            if (
                blank($request->input('lembaga_id')) ||
                blank($request->input('jurusan_id')) ||
                blank($request->input('kelas_id')) ||
                blank($request->input('rombel_id')) ||
                blank($request->input('semester_id'))
            ) {
                return response()->json([
                    'status' => 'success',
                    'meta' => null,
                    'data' => []
                ]);
            }

            // Bangun query jadwal pelajaran
            $query = $this->JadwalService->getAllJadwalQuery($request);

            // Terapkan filter tambahan (jika ada)
            $query = $this->FilterJadwalPelajaranService->applyJadwalFilters($query, $request);

            // Ambil hasil akhir
            $results = $query->get();

            // Format hasil sesuai hari
            $formatted = $this->JadwalService->groupJadwalByHari($results);

            if ($results->isNotEmpty()) {
                $meta = $results->first();
                $metaInfo = [
                    'lembaga' => $meta->nama_lembaga,
                    'jurusan' => $meta->nama_jurusan,
                    'kelas' => $meta->nama_kelas,
                    'rombel' => $meta->nama_rombel,
                    'semester' => 'Semester ' . $meta->semester
                ];
            } else {
                // Ambil data referensi jika jadwal kosong
                $metaInfo = [
                    'lembaga' => optional(DB::table('lembaga')->find($request->lembaga_id))->nama_lembaga,
                    'jurusan' => optional(DB::table('jurusan')->find($request->jurusan_id))->nama_jurusan,
                    'kelas' => optional(DB::table('kelas')->find($request->kelas_id))->nama_kelas,
                    'rombel' => optional(DB::table('rombel')->find($request->rombel_id))->nama_rombel,
                    'semester' => optional(DB::table('semester')->find($request->semester_id))->semester
                        ? 'Semester ' . DB::table('semester')->find($request->semester_id)->semester
                        : null,
                ];
            }

            return response()->json([
                'status' => 'success',
                'meta' => $metaInfo,
                'data' => $formatted
            ]);
        } catch (\Throwable $e) {
            Log::error("[JadwalPelajaranController] Error: {$e->getMessage()}", [
                'trace' => $e->getTraceAsString()
            ]);

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
    public function delete($id)
    {
        try {
            $this->JadwalService->deleteById($id);

            return response()->json([
                'status' => 'success',
                'message' => 'Data jadwal pelajaran berhasil dihapus.',
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Jadwal pelajaran tidak ditemukan.',
            ], 404);
        } catch (\Throwable $e) {
            Log::error("[JadwalPelajaranController::delete] Error: {$e->getMessage()}");

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan pada server.',
            ], 500);
        }
    }
}
