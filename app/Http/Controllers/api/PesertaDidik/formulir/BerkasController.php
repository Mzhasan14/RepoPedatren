<?php

namespace App\Http\Controllers\api\PesertaDidik\formulir;

use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Requests\PesertaDidik\BerkasRequest;
use App\Services\PesertaDidik\Formulir\BerkasService;

class BerkasController extends Controller
{
    private BerkasService $berkas;

    public function __construct(BerkasService $berkas)
    {
        $this->berkas = $berkas;
    }

    /**
     * Tampilkan semua berkas berdasarkan ID bio.
     */
    public function index(int $bioId): JsonResponse
    {
        try {
            $result = $this->berkas->index($bioId);

            if (!$result['status']) {
                return response()->json([
                    'message' => $result['message'] ?? 'Data tidak ditemukan.'
                ], Response::HTTP_OK);
            }

            return response()->json([
                'message' => 'Data berhasil ditampilkan',
                'data'    => $result['data'],
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error("Gagal ambil data berkas: {$e->getMessage()}");

            return response()->json([
                'message' => 'Terjadi kesalahan saat menampilkan data.',
                'error'   => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Simpan berkas baru untuk ID bio.
     */
    public function store(BerkasRequest $request, int $bioId): JsonResponse
    {
        try {
            $data   = $request->validated();
            $result = $this->berkas->store($data, $bioId);

            if (!$result['status']) {
                return response()->json([
                    'message' => $result['message']
                ], Response::HTTP_OK);
            }

            return response()->json([
                'message' => 'Data berhasil ditambah',
                'data'    => $result['data'],
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            Log::error("Gagal tambah berkas: {$e->getMessage()}");

            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses data.',
                'error'   => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Tampilkan detail berkas berdasarkan ID.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $result = $this->berkas->show($id);

            if (!$result['status']) {
                return response()->json([
                    'message' => $result['message'] ?? 'Data tidak ditemukan.'
                ], Response::HTTP_OK);
            }

            return response()->json([
                'message' => 'Detail data berhasil ditampilkan',
                'data'    => $result['data'],
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error("Gagal ambil detail berkas: {$e->getMessage()}");

            return response()->json([
                'message' => 'Terjadi kesalahan saat menampilkan data.',
                'error'   => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Perbarui berkas berdasarkan ID.
     */
    public function update(BerkasRequest $request, int $id): JsonResponse
    {
        try {
            $data   = $request->validated();
            $result = $this->berkas->update($data, $id);

            if (!$result['status']) {
                return response()->json([
                    'message' => $result['message']
                ], Response::HTTP_OK);
            }

            return response()->json([
                'message' => 'Data berhasil diperbarui',
                'data'    => $result['data'],
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error("Gagal memperbarui berkas: {$e->getMessage()}");

            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses data.',
                'error'   => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
