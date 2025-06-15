<?php

namespace App\Http\Controllers\api\Pegawai;

use App\Exports\BaseExport;
use App\Exports\Pegawai\KaryawanExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Pegawai\KaryawanFormulirRequest;
use App\Http\Requests\Pegawai\KeluarKaryawanRequest;
use App\Http\Requests\Pegawai\PindahKaryawanRequest;
use App\Services\Pegawai\Filters\FilterKaryawanService as FiltersFilterKaryawanService;
use App\Services\Pegawai\Filters\Formulir\KaryawanService as FormulirKaryawanService;
use App\Services\Pegawai\KaryawanService as PegawaiKaryawanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class KaryawanController extends Controller
{
    private PegawaiKaryawanService $karyawanService;

    private FiltersFilterKaryawanService $filterController;

    private FormulirKaryawanService $formulirKaryawanService;

    public function __construct(
        FormulirKaryawanService $formulirKaryawanService,
        PegawaiKaryawanService $karyawanService,
        FiltersFilterKaryawanService $filterController
    ) {
        $this->karyawanService = $karyawanService;
        $this->filterController = $filterController;
        $this->formulirKaryawanService = $formulirKaryawanService;
    }

    public function index($id)
    {
        try {
            $result = $this->formulirKaryawanService->index($id);
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
            Log::error('Gagal ambil data Karyawan: '.$e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat menampilkan data.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(KaryawanFormulirRequest $request, $bioId)
    {
        try {
            $result = $this->formulirKaryawanService->store($request->validated(), $bioId);
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
            Log::error('Gagal tambah Karyawan: '.$e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function edit($id)
    {
        try {
            $result = $this->formulirKaryawanService->show($id);
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
            Log::error('Gagal ambil detail Karyawan: '.$e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat menampilkan data.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(KaryawanFormulirRequest $request, $id)
    {
        try {
            $result = $this->formulirKaryawanService->update($request->validated(), $id);
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
            Log::error('Gagal update Karyawan: '.$e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function dataKaryawan(Request $request)
    {
        try {
            $query = $this->karyawanService->getAllKaryawan($request);
            $query = $this->filterController->applyAllFilters($query, $request);

            $perPage = (int) $request->input('limit', 25);
            $currentPage = (int) $request->input('page', 1);
            $results = $query->paginate($perPage, ['*'], 'page', $currentPage);
        } catch (\Throwable $e) {
            Log::error("[KaryawanController] Error: {$e->getMessage()}");

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

        $formatted = $this->karyawanService->formatData($results);

        return response()->json([
            'total_data' => $results->total(),
            'current_page' => $results->currentPage(),
            'per_page' => $results->perPage(),
            'total_pages' => $results->lastPage(),
            'data' => $formatted,
        ]);
    }

    public function pindahKaryawan(PindahKaryawanRequest $request, $id)
    {
        try {
            $validated = $request->validated();
            $result = $this->formulirKaryawanService->pindahKaryawan($validated, $id);
            if (! $result['status']) {
                return response()->json([
                    'message' => $result['message'],
                ], 200);
            }

            return response()->json([
                'message' => 'Karyawan baru berhasil dibuat',
                'data' => $result['data'],
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal pindah Karyawan: '.$e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function keluarKaryawan(KeluarKaryawanRequest $request, $id)
    {
        try {
            $validated = $request->validated();
            $result = $this->formulirKaryawanService->keluarKaryawan($validated, $id);
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
            Log::error('Gagal keluar Karyawan: '.$e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function KaryawanExcel(Request $request)
    {
        $defaultFields = ['nama_lengkap', 'jenis_kelamin', 'tanggal_mulai', 'tanggal_selesai', 'status_aktif'];

        $columnOrder = [
            'no_kk',
            'nik',
            'niup',
            'nama_lengkap',
            'tempat_tanggal_lahir',
            'jenis_kelamin',
            'alamat',
            'pendidikan_terakhir',
            'email',
            'no_telepon',
            'lembaga',
            'golongan_jabatan',
            'jabatan',
            'keterangan_jabatan',
            'tanggal_mulai',
            'tanggal_selesai',
            'status_aktif',
        ];

        $optionalFields = $request->input('fields', []);
        $fields = array_unique(array_merge($defaultFields, $optionalFields));
        $fields = array_values(array_intersect($columnOrder, $fields));

        $query = $this->karyawanService->getExportKaryawanQuery($fields, $request)->latest('b.created_at');

        $results = $request->input('all') === 'true'
            ? $query->get()
            : $query->limit((int) $request->input('limit', 100))->get();

        $addNumber = true;
        $formatted = $this->karyawanService->formatDataExport($results, $fields, $addNumber);
        $headings  = $this->karyawanService->getFieldExportHeadings($fields, $addNumber);

        $now = now()->format('Y-m-d_H-i-s');
        return Excel::download(new BaseExport($formatted, $headings), "karyawan_{$now}.xlsx");
    }
}
