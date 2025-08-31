<?php

namespace App\Http\Controllers\api\PesertaDidik\formulir;

use App\Http\Controllers\Controller;
use App\Http\Requests\PesertaDidik\formulir\CreateDomisiliRequest;
use App\Http\Requests\PesertaDidik\formulir\KeluarDomisiliRequest;
use App\Http\Requests\PesertaDidik\formulir\PindahDomisiliRequest;
use App\Http\Requests\PesertaDidik\formulir\UpdateDomisiliRequest;
use App\Services\PesertaDidik\Formulir\DomisiliService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class DomisiliController extends Controller
{
    private DomisiliService $domisili;

    public function __construct(DomisiliService $domisili)
    {
        $this->domisili = $domisili;
    }

    /**
     * Menampilkan daftar data domisili berdasarkan ID.
     */
    public function index($id): JsonResponse
    {
        try {
            $result = $this->domisili->index($id);

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
            Log::error('Gagal ambil data domisili: ' . $e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat menampilkan data.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Menyimpan data domisili baru.
     */
    public function store(CreateDomisiliRequest $request, $bioId): JsonResponse
    {
        try {
            $validated = $request->validated();
            $result = $this->domisili->store($validated, $bioId);

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
            Log::error('Gagal tambah domisili: ' . $e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Menampilkan detail domisili berdasarkan ID.
     */
    public function show($id): JsonResponse
    {
        try {
            $result = $this->domisili->show($id);

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
            Log::error('Gagal ambil detail domisili: ' . $e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat menampilkan data.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Memperbarui data domisili berdasarkan ID.
     */
    public function update(UpdateDomisiliRequest $request, $id): JsonResponse
    {
        try {
            $validated = $request->validated();
            $result = $this->domisili->update($validated, $id);

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
            Log::error('Gagal update domisili: ' . $e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Memproses perpindahan domisili.
     */
    public function pindahDomisili(PindahDomisiliRequest $request, $id): JsonResponse
    {
        try {
            $validated = $request->validated();
            $result = $this->domisili->pindahDomisili($validated, $id);

            if (! $result['status']) {
                return response()->json([
                    'message' => $result['message'],
                ], 200);
            }

            return response()->json([
                'message' => 'Domisili baru berhasil dibuat',
                'data' => $result['data'],
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal pindah domisili: ' . $e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Memproses penghapusan/keluar dari domisili.
     */
    public function keluarDomisili(KeluarDomisiliRequest $request, $id): JsonResponse
    {
        try {
            $validated = $request->validated();
            $result = $this->domisili->keluarDomisili($validated, $id);

            return response()->json([
                'status'  => $result['status'],
                'message' => $result['message'],
            ], $result['status'] ? 200 : 200);
        } catch (\Exception $e) {
            Log::error('Gagal keluar domisili: ' . $e->getMessage());

            return response()->json([
                'status'  => false,
                'message' => 'Terjadi kesalahan saat memproses data',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
