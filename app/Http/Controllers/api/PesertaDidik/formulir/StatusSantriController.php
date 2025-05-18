<?php

namespace App\Http\Controllers\api\PesertaDidik\formulir;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Requests\PesertaDidik\formulir\StatusSantriRequest;
use App\Services\PesertaDidik\Formulir\StatusSantriService;

class StatusSantriController extends Controller
{
    private StatusSantriService $santri;

    public function __construct(StatusSantriService $santri)
    {
        $this->santri = $santri;
    }

    /**
     * List semua status santri berdasarkan ID bio.
     */
    public function index($bioId): JsonResponse
    {
        try {
            $result = $this->santri->index($bioId);

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
            Log::error("Gagal ambil data santri: {$e->getMessage()}");

            return response()->json([
                'message' => 'Terjadi kesalahan saat menampilkan data.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Simpan status santri baru untuk ID bio.
     */
    public function store(StatusSantriRequest $request, $bioId): JsonResponse
    {
        try {
            $data   = $request->validated();
            $result = $this->santri->store($data, $bioId);

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
            Log::error("Gagal tambah santri: {$e->getMessage()}");

            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses data.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Tampilkan detail status santri berdasarkan ID.
     */
    public function show($id): JsonResponse
    {
        try {
            $result = $this->santri->show($id);

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
            Log::error("Gagal ambil detail santri: {$e->getMessage()}");

            return response()->json([
                'message' => 'Terjadi kesalahan saat menampilkan data.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Perbarui status santri berdasarkan ID.
     */
    public function update(StatusSantriRequest $request, $id): JsonResponse
    {
        try {
            $data   = $request->validated();
            $result = $this->santri->update($data, $id);

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
            Log::error("Gagal update santri: {$e->getMessage()}");

            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses data.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
