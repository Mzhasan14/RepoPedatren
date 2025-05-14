<?php

namespace App\Http\Controllers\api\PesertaDidik\formulir;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Requests\PesertaDidik\PendidikanRequest;
use App\Services\PesertaDidik\Formulir\PendidikanService;
use App\Http\Requests\PesertaDidik\CreatePendidikanRequest;
use App\Http\Requests\PesertaDidik\KeluarPendidikanRequest;
use App\Http\Requests\PesertaDidik\PindahPendidikanRequest;
use App\Http\Requests\PesertaDidik\UpdatePendidikanRequest;

class PendidikanController extends Controller
{
    private PendidikanService $pendidikan;

    public function __construct(PendidikanService $pendidikan)
    {
        $this->pendidikan = $pendidikan;
    }

    /**
     * List semua data pendidikan berdasarkan ID bio.
     */
    public function index($bioId): JsonResponse
    {
        try {
            $result = $this->pendidikan->index($bioId);

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
            Log::error("Gagal ambil data pendidikan: {$e->getMessage()}");

            return response()->json([
                'message' => 'Terjadi kesalahan saat menampilkan data.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Simpan data pendidikan baru untuk ID bio.
     */
    public function store(CreatePendidikanRequest $request, $bioId): JsonResponse
    {
        try {
            $data   = $request->validated();
            $result = $this->pendidikan->store($data, $bioId);

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
            Log::error("Gagal tambah pendidikan: {$e->getMessage()}");

            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses data.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Tampilkan detail pendidikan berdasarkan ID.
     */
    public function show($id): JsonResponse
    {
        try {
            $result = $this->pendidikan->show($id);

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
            Log::error("Gagal ambil detail pendidikan: {$e->getMessage()}");

            return response()->json([
                'message' => 'Terjadi kesalahan saat menampilkan data.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Perbarui data pendidikan berdasarkan ID.
     */
    public function update(UpdatePendidikanRequest $request, $id): JsonResponse
    {
        try {
            $data   = $request->validated();
            $result = $this->pendidikan->update($data, $id);

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
            Log::error("Gagal update pendidikan: {$e->getMessage()}");

            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses data.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Proses perpindahan pendidikan.
     */
    public function pindahPendidikan(PindahPendidikanRequest $request, $id): JsonResponse
    {
        try {
            $data   = $request->validated();
            $result = $this->pendidikan->pindahPendidikan($data, $id);

            if (!$result['status']) {
                return response()->json([
                    'message' => $result['message']
                ], 200);
            }

            return response()->json([
                'message' => 'Pendidikan baru berhasil dibuat',
                'data'    => $result['data'],
            ]);
        } catch (\Exception $e) {
            Log::error("Gagal pindah pendidikan: {$e->getMessage()}");

            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses data.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Proses keluar dari pendidikan.
     */
    public function keluarPendidikan(KeluarPendidikanRequest $request, $id): JsonResponse
    {
        try {
            $data   = $request->validated();
            $result = $this->pendidikan->keluarPendidikan($data, $id);

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
            Log::error("Gagal keluar pendidikan: {$e->getMessage()}");

            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses data.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
