<?php

namespace App\Http\Controllers\api\PesertaDidik\formulir;

use App\Http\Controllers\Controller;
use App\Http\Requests\PesertaDidik\formulir\CreateKhadamRequest;
use App\Http\Requests\PesertaDidik\formulir\KeluarKhadamRequest;
use App\Http\Requests\PesertaDidik\formulir\PindahKhadamRequest;
use App\Http\Requests\PesertaDidik\formulir\UpdateKhadamRequest;
use App\Services\PesertaDidik\Formulir\KhadamFormService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class KhadamFormController extends Controller
{
    private KhadamFormService $khadam;

    public function __construct(KhadamFormService $khadam)
    {
        $this->khadam = $khadam;
    }

    /**
     * Menampilkan daftar data khadam berdasarkan ID.
     */
    public function index($id): JsonResponse
    {
        try {
            $result = $this->khadam->index($id);

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
            Log::error('Gagal ambil data khadam: '.$e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat menampilkan data.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Menyimpan data khadam baru.
     */
    public function store(CreateKhadamRequest $request, $bioId): JsonResponse
    {
        try {
            $validated = $request->validated();
            $result = $this->khadam->store($validated, $bioId);

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
            Log::error('Gagal tambah khadam: '.$e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Menampilkan detail khadam berdasarkan ID.
     */
    public function show($id): JsonResponse
    {
        try {
            $result = $this->khadam->show($id);

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
            Log::error('Gagal ambil detail khadam: '.$e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat menampilkan data.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Memperbarui data khadam berdasarkan ID.
     */
    public function update(UpdateKhadamRequest $request, $id): JsonResponse
    {
        try {
            $validated = $request->validated();
            $result = $this->khadam->update($validated, $id);

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
            Log::error('Gagal update khadam: '.$e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Memproses perpindahan khadam.
     */
    public function pindahKhadam(PindahKhadamRequest $request, $id): JsonResponse
    {
        try {
            $validated = $request->validated();
            $result = $this->khadam->pindahKhadam($validated, $id);

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
            Log::error('Gagal pindah khadam: '.$e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Memproses penghapusan/keluar dari khadam.
     */
    public function keluarKhadam(KeluarKhadamRequest $request, $id): JsonResponse
    {
        try {
            $validated = $request->validated();
            $result = $this->khadam->keluarKhadam($validated, $id);

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
            Log::error('Gagal keluar khadam: '.$e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
