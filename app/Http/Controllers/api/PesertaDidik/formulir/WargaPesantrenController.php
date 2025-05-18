<?php

namespace App\Http\Controllers\api\PesertaDidik\formulir;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Requests\PesertaDidik\formulir\WargaPesantrenRequest;
use App\Services\PesertaDidik\Formulir\WargaPesantrenService;

class WargaPesantrenController extends Controller
{
    private WargaPesantrenService $wargaPesantren;

    public function __construct(WargaPesantrenService $wargaPesantren)
    {
        $this->wargaPesantren = $wargaPesantren;
    }

    /**
     * List semua warga pesantren berdasarkan ID bio.
     */
    public function index($bioId): JsonResponse
    {
        try {
            $result = $this->wargaPesantren->index($bioId);

            if (!$result['status']) {
                return response()->json([
                    'message' => $result['message'] ?? 'Data tidak ditemukan.'
                ], 200);
            }

            return response()->json([
                'message' => 'Data berhasil ditampilkan',
                'data'    => $result['data'],
            ]);
        } catch (\Exception $e) {
            Log::error("Gagal ambil data warga pesantren: {$e->getMessage()}");

            return response()->json([
                'message' => 'Terjadi kesalahan saat menampilkan data.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Simpan warga pesantren baru untuk ID bio.
     */
    public function store(WargaPesantrenRequest $request, $bioId): JsonResponse
    {
        try {
            $data   = $request->validated();
            $result = $this->wargaPesantren->store($data, $bioId);

            if (!$result['status']) {
                return response()->json([
                    'message' => $result['message']
                ], 200);
            }

            return response()->json([
                'message' => 'Data berhasil ditambah',
                'data'    => $result['data'],
            ]);
        } catch (\Exception $e) {
            Log::error("Gagal tambah warga pesantren: {$e->getMessage()}");

            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses data.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Tampilkan detail warga pesantren berdasarkan ID.
     */
    public function show($id): JsonResponse
    {
        try {
            $result = $this->wargaPesantren->show($id);

            if (!$result['status']) {
                return response()->json([
                    'message' => $result['message'] ?? 'Data tidak ditemukan.'
                ], 200);
            }

            return response()->json([
                'message' => 'Detail data berhasil ditampilkan',
                'data'    => $result['data'],
            ]);
        } catch (\Exception $e) {
            Log::error("Gagal ambil detail warga pesantren: {$e->getMessage()}");

            return response()->json([
                'message' => 'Terjadi kesalahan saat menampilkan data.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Perbarui data warga pesantren berdasarkan ID.
     */
    public function update(WargaPesantrenRequest $request, $id): JsonResponse
    {
        try {
            $data   = $request->validated();
            $result = $this->wargaPesantren->update($data, $id);

            if (!$result['status']) {
                return response()->json([
                    'message' => $result['message']
                ], 200);
            }

            return response()->json([
                'message' => 'Data berhasil diperbarui',
                'data'    => $result['data'],
            ]);
        } catch (\Exception $e) {
            Log::error("Gagal memperbarui data warga pesantren: {$e->getMessage()}");

            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses data.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
