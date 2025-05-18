<?php

namespace App\Http\Controllers\Api\Pegawai;

use App\Exports\Pegawai\KaryawanExport;
use App\Http\Controllers\api\FilterController;
use App\Http\Controllers\Controller;
use App\Http\Requests\Pegawai\CreateKaryawanRequest;
use App\Http\Requests\Pegawai\KaryawanFormulirRequest;
use App\Http\Requests\Pegawai\KeluarKaryawanRequest;
use App\Http\Requests\Pegawai\PindahKaryawanRequest;
use App\Http\Resources\PdResource;
use App\Models\JenisBerkas;
use App\Models\Pegawai\Karyawan;
use App\Services\FilterKaryawanService;
use App\Services\Karyawan\KaryawanService;
use App\Services\Pegawai\Filters\FilterKaryawanService as FiltersFilterKaryawanService;
use App\Services\Pegawai\Filters\Formulir\KaryawanService as FormulirKaryawanService;
use App\Services\Pegawai\KaryawanService as PegawaiKaryawanService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;



class KaryawanController extends Controller
{
    private PegawaiKaryawanService $karyawanService;
    private FiltersFilterKaryawanService $filterController;
    private FormulirKaryawanService $formulirKaryawanService;

    public function __construct(FormulirKaryawanService $formulirKaryawanService,PegawaiKaryawanService $karyawanService, FiltersFilterKaryawanService $filterController,)
    {
        $this->karyawanService = $karyawanService;
        $this->filterController = $filterController;
        $this->formulirKaryawanService = $formulirKaryawanService;
    }

    /**
     * Display a listing of the resource.
     */

    public function index($id)
    {
        try {
            $result = $this->formulirKaryawanService->index($id);
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
            Log::error('Gagal ambil data Karyawan: ' . $e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat menampilkan data.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(KaryawanFormulirRequest $request, $bioId)
    {
        try {
            $result = $this->formulirKaryawanService->store($request->validated(), $bioId);
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
            Log::error('Gagal tambah Karyawan: ' . $e->getMessage());
            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function edit($id)
    {
        try {
            $result = $this->formulirKaryawanService->show($id);
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
            Log::error('Gagal ambil detail Karyawan: ' . $e->getMessage());
            return response()->json([
                'message' => 'Terjadi kesalahan saat menampilkan data.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(KaryawanFormulirRequest $request, $id)
    {
        try {
            $result = $this->formulirKaryawanService->update($request->validated(), $id);

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
            Log::error('Gagal update Karyawan: ' . $e->getMessage());
            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function dataKaryawan(Request $request)
    {
        try {
            $query = $this->karyawanService->getAllKaryawan($request);
            $query = $this->filterController->applyAllFilters($query, $request);

            $perPage     = (int) $request->input('limit', 25);
            $currentPage = (int) $request->input('page', 1);
            $results     = $query->paginate($perPage, ['*'], 'page', $currentPage);
        } catch (\Throwable $e) {
            Log::error("[PelajarController] Error: {$e->getMessage()}");
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

        $formatted = $this->karyawanService->formatData($results);

        return response()->json([
            "total_data"   => $results->total(),
            "current_page" => $results->currentPage(),
            "per_page"     => $results->perPage(),
            "total_pages"  => $results->lastPage(),
            "data"         => $formatted
        ]);
    }
    public function pindahKaryawan(PindahKaryawanRequest $request, $id)
    {
        try {
            $validated = $request->validated();
            $result = $this->formulirKaryawanService->pindahKaryawan($validated, $id);

            if (!$result['status']) {
                return response()->json([
                    'message' => $result['message']
                ], 200);
            }

            return response()->json([
                'message' => 'Karyawan baru berhasil dibuat',
                'data' => $result['data']
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal pindah Karyawan: ' . $e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses data',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function keluarKaryawan(KeluarKaryawanRequest $request, $id)
    {
        try {
            $validated = $request->validated();
            $result = $this->formulirKaryawanService->keluarKaryawan($validated, $id);

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
            Log::error('Gagal keluar Karyawan: ' . $e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function karyawanExport()
    {
        return Excel::download(new KaryawanExport, 'data_karyawan.xlsx');
    }     
}
