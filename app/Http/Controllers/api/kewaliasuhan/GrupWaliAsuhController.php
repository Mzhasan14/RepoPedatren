<?php

namespace App\Http\Controllers\api\kewaliasuhan;

use App\Http\Controllers\Controller;
use App\Http\Requests\Kewaliasuhan\grupWaliasuhRequest;
use App\Http\Resources\PdResource;
use App\Models\Biodata;
use App\Models\Kewaliasuhan\Grup_WaliAsuh;
use App\Models\Kewaliasuhan\Wali_asuh;
use App\Services\Kewaliasuhan\Filters\FilterGrupWaliasuhService;
use App\Services\Kewaliasuhan\GrupWaliasuhService;
use id;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class GrupWaliAsuhController extends Controller
{
    private GrupWaliasuhService $grupWaliasuhService;

    private FilterGrupWaliasuhService $filterGrupWaliasuhService;

    public function __construct(GrupWaliasuhService $grupWaliasuhService, FilterGrupWaliasuhService $filterGrupWaliasuhService)
    {
        $this->grupWaliasuhService = $grupWaliasuhService;
        $this->filterGrupWaliasuhService = $filterGrupWaliasuhService;
    }

    public function getAllGrupWaliasuh(Request $request)
    {
        $query = $this->grupWaliasuhService->getAllGrupWaliasuh($request);
        $query = $this->filterGrupWaliasuhService->GrupWaliasuhFIlters($query, $request);

        $perPage = (int) $request->input('limit', 25);
        $currentPage = (int) $request->input('page', 1);
        $results = $query->paginate($perPage, ['*'], 'page', $currentPage);

        if ($results->isEmpty()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Data kosong',
                'data' => [],
            ], 200);
        }

        $formatted = $this->grupWaliasuhService->formatData($results);

        return response()->json([
            'total_data' => $results->total(),
            'current_page' => $results->currentPage(),
            'per_page' => $results->perPage(),
            'total_pages' => $results->lastPage(),
            'data' => $formatted,
        ]);
    }

    public function index(): JsonResponse
    {
        try {
            $result = $this->grupWaliasuhService->index();
            if (! $result['status']) {
                return response()->json([
                    'message' => $result['message'],
                ], 404);
            }

            return response()->json([
                'status' => true,
                'data' => $result['data'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show($id): JsonResponse
    {
        $result = $this->grupWaliasuhService->show($id);

        if (! $result['status']) {
            return response()->json(['message' => $result['message']], 404);
        }

        return response()->json(['data' => $result['data']]);
    }

    public function store(grupWaliasuhRequest $request)
    {
        try {
            $validated = $request->validated();
            $result = $this->grupWaliasuhService->store($validated);
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
            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Update grup
    public function update(grupWaliasuhRequest $request, $id)
    {
        $result = $this->grupWaliasuhService->update($request->validated(), $id);
        if (! $result['status']) {
            return response()->json([
                'message' => $result['message'] ??
                    'Data tidak ditemukan.',
            ], 200);
        }

        return response()->json([
            'message' => 'Grup waliasuh berhasil diperbarui',
            'data' => $result['data'],
        ]);
    }

    public function getGrup()
    {
        return response()->json(
            Grup_WaliAsuh::select('id', 'nama_grup')->get()
        );
    }

    public function destroy($id)
    {
        return DB::transaction(function () use ($id) {
            if (!Auth::id()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Pengguna tidak terautentikasi',
                ], 401);
            }

            $grup = Grup_WaliAsuh::withTrashed()->find($id);

            if (!$grup) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data grup wali asuh tidak ditemukan',
                ], 404);
            }

            if ($grup->trashed()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data grup sudah dihapus sebelumnya',
                ], 410);
            }

            // Cek apakah grup masih memiliki anggota aktif
            $hasActiveMembers = Wali_asuh::where('id_grup_wali_asuh', $id)
                ->where('status', true)
                ->exists();

            if ($hasActiveMembers) {
                return response()->json([
                    'status' => false,
                    'message' => 'Tidak dapat menghapus grup yang masih memiliki anggota aktif',
                ], 400);
            }

            // Ubah status menjadi non aktif, isi kolom deleted_by dan deleted_at
            $grup->status = false;
            $grup->deleted_by = Auth::id();
            $grup->deleted_at = now();
            $grup->save();

            // Log activity
            activity('grup_wali_asuh_nonaktifkan')
                ->performedOn($grup)
                ->withProperties([
                    'deleted_at' => $grup->deleted_at,
                    'deleted_by' => $grup->deleted_by,
                ])
                ->event('nonaktif_grup_wali_asuh')
                ->log('Grup wali asuh dinonaktifkan tanpa dihapus (soft update)');

            return response()->json([
                'status' => true,
                'message' => 'Grup wali asuh berhasil dinonaktifkan',
                'data' => [
                    'deleted_at' => $grup->deleted_at,
                ],
            ]);
        });
    }

    public function activate($id)
    {
        return DB::transaction(function () use ($id) {
            if (!Auth::id()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Pengguna tidak terautentikasi',
                ], 401);
            }

            $grup = Grup_WaliAsuh::withTrashed()->find($id);

            if (!$grup) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data grup wali asuh tidak ditemukan',
                ], 404);
            }

            // Jika status sudah aktif
            if ($grup->status) {
                return response()->json([
                    'status' => false,
                    'message' => 'Grup wali asuh sudah dalam keadaan aktif',
                ], 400);
            }

            // Aktifkan kembali
            $grup->status = true;
            $grup->deleted_by = null;
            $grup->deleted_at = null;
            $grup->updated_by = Auth::id();
            $grup->updated_at = now();
            $grup->save();

            // Log activity
            activity('grup_wali_asuh_restore')
                ->performedOn($grup)
                ->event('restore_grup_wali_asuh')
                ->log('Grup wali asuh berhasil diaktifkan kembali');

            return response()->json([
                'status' => true,
                'message' => 'Grup wali asuh berhasil diaktifkan kembali',
            ]);
        });
    }
}
