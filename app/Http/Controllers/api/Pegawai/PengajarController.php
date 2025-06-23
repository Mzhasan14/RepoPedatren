<?php

namespace App\Http\Controllers\api\Pegawai;

use App\Exports\BaseExport;
use App\Exports\Pegawai\PengajarExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Pegawai\KeluarPengajarRequest;
use App\Http\Requests\Pegawai\PengajarResquest;
use App\Http\Requests\Pegawai\PindahPengajarRequest;
use App\Http\Requests\Pegawai\SimpanJadwalRequest;
use App\Http\Requests\Pegawai\TambahMateriAjarRequest;
use App\Http\Requests\Pegawai\UpdateJadwalRequest;
use App\Http\Requests\Pegawai\UpdateMapelRequest;
use App\Http\Requests\Pegawai\UpdatePengajarRequest;
use App\Models\Pegawai\MataPelajaran;
use App\Services\Pegawai\Filters\FilterPengajarService;
use App\Services\Pegawai\Filters\Formulir\JadwalService;
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

    private JadwalService $JadwalService;
    
    public function __construct(
        FormulirPengajarService $formulirPengajarService,
        PengajarService $pengajarService,
        FilterPengajarService $filterController,
        JadwalService $JadwalService
    ) {
        $this->pengajarService = $pengajarService;
        $this->filterController = $filterController;
        $this->formulirPengajarService = $formulirPengajarService;
        $this->JadwalService = $JadwalService;
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

            // Optional: Clean daftar_mapel dari duplikat literal
            $results->getCollection()->transform(function ($item) {
                $item->daftar_mapel = collect(explode(', ', $item->daftar_mapel ?? ''))
                    ->map(fn($m) => trim($m))
                    ->unique()
                    ->implode(', ');
                return $item;
            });

            $formatted = $this->pengajarService->formatData($results);

            return response()->json([
                'total_data' => $results->total(),
                'current_page' => $results->currentPage(),
                'per_page' => $results->perPage(),
                'total_pages' => $results->lastPage(),
                'data' => $formatted,
            ]);
        } catch (\Throwable $e) {
            Log::error("[PengajarController] Error: {$e->getMessage()}");

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan pada server',
            ], 500);
        }
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
            $result = $this->formulirPengajarService->nonaktifkanMataPelajaran($pengajarId, $materiId);

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
            $result = $this->formulirPengajarService->tambahMataPelajaran($pengajarId, $request->validated());

        if (! $result['status']) {
            return response()->json([
                'message' => $result['message'] ?? 'Gagal menambahkan materi ajar.',
                'error' => $result['error'] ?? null,
            ], 422);
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
    public function showMateri($id)
    {
        $result = $this->formulirPengajarService->showMapelById($id);

        if (! $result['status']) {
            return response()->json([
                'message' => $result['message'],
            ], 404);
        }

        return response()->json([
            'message' => 'Data materi berhasil ditampilkan.',
            'data' => $result['data'],
        ]);
    }
    public function updateMateri(UpdateMapelRequest $request, int $id)
    {
        $result = $this->formulirPengajarService->updateMateri($id, $request->validated());

        if (! $result['status']) {
            return response()->json(['message' => $result['message']], 422);
        }

        return response()->json([
            'message' => $result['message'],
            'data' => $result['data'],
        ]);
    }
    public function showByMateriId($materiId)
    {
        try {
            $result = $this->JadwalService->getJadwalByMateriId($materiId);

            if (! $result['status']) {
                return response()->json([
                    'message' => $result['message'] ?? 'Data tidak ditemukan.',
                    'data' => [],
                ], 200);
            }

            return response()->json([
                'message' => 'Data jadwal berhasil ditampilkan',
                'data' => $result['data'],
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal ambil jadwal pelajaran: '.$e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat menampilkan data.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function simpan(SimpanJadwalRequest $request, $materi_id)
    {
        try {
            $materi = MataPelajaran::with('pengajar')->findOrFail($materi_id);
            $pengajar = $materi->pengajar;

            $result = $this->JadwalService->simpanJadwalPengajar(
                $request->validated(),
                $pengajar,
                $materi->id,
                $materi->nama_mapel
            );

            return response()->json([
                'message' => $result['status']
                    ? 'Jadwal berhasil disimpan'
                    : ($result['message'] ?? 'Gagal menyimpan jadwal.'),
                'data' => $result['data'] ?? null,
            ]);
        } catch (\Exception $e) {
            Log::error('Error simpan jadwal: ' . $e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat menyimpan data.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function hapus($id)
    {
        try {
            $result = $this->JadwalService->hapusJadwalPelajaran($id);

            if (! $result['status']) {
                return response()->json([
                    'message' => $result['message'] ?? 'Gagal menghapus.',
                ], 400);
            }

            return response()->json([
                'message' => $result['message'] ?? 'Berhasil menghapus.',
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal hapus jadwal: ' . $e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat menghapus jadwal.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateJadwal(UpdateJadwalRequest $request, $id)
    {
        $result = $this->JadwalService->updateJadwalPelajaran($request->validated(), $id);

        if (! $result['status']) {
            return response()->json([
                'message' => $result['message'] ?? 'Gagal update.',
            ], 400);
        }

        return response()->json([
            'message' => $result['message'] ?? 'Berhasil update.',
            'data' => $result['data'],
        ]);
    }

    public function PengajarExport(Request $request)
    {
        $defaultFields = ['nama_lengkap', 'jenis_kelamin', 'tanggal_mulai', 'tanggal_selesai', 'status_aktif'];

        $columnOrder = [
            'nama_lengkap',
            'nik',
            'no_kk',
            'niup',
            'jenis_kelamin',
            'jalan',
            'tempat_lahir',
            'tanggal_lahir',
            'pendidikan_terakhir',
            'email',
            'no_telepon',
            'lembaga',
            'golongan',
            'jabatan',
            'status_aktif',
        ];

        $optionalFields = $request->input('fields', []);
        $fields = array_unique(array_merge($defaultFields, $optionalFields));
        $fields = array_values(array_intersect($columnOrder, $fields));

        $query = $this->pengajarService->getExportPengajarQuery($fields, $request);
        $query = $query->latest('b.created_at');

        $results = $request->input('all') === 'true'
            ? $query->get()
            : $query->limit((int) $request->input('limit', 100))->get();

        $addNumber = true;
        $formatted = $this->pengajarService->formatDataExport($results, $fields, $addNumber);
        $headings = $this->pengajarService->getFieldExportHeadings($fields, $addNumber);

        $now = now()->format('Y-m-d_H-i-s');
        return Excel::download(new BaseExport($formatted, $headings), "pengajar_{$now}.xlsx");
    }

}
