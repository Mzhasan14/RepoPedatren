<?php

namespace App\Http\Controllers\api\PesertaDidik\Pembayaran;

use Exception;
use Illuminate\Http\Request;
use App\Models\SantriPotongan;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Services\PesertaDidik\Pembayaran\SantriPotonganService;
use App\Http\Requests\PesertaDidik\Pembayaran\SantriPotonganRequest;

class SantriPotonganController extends Controller
{
    protected SantriPotonganService $service;

    public function __construct(SantriPotonganService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        try {
            $data = $this->service->list($request->all());
            return response()->json([
                'message' => 'Daftar santri potongan',
                'data'    => $data
            ]);
        } catch (\Throwable $e) {
            Log::error('SantriPotonganController index error', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Terjadi kesalahan saat mengambil data.'], 500);
        }
    }

    public function show(int $id)
    {
        try {
            $data = $this->service->find($id);
            if (!$data) {
                return response()->json(['message' => 'Data tidak ditemukan.'], 404);
            }

            return response()->json([
                'message' => 'Detail santri potongan',
                'data'    => $data
            ]);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Gagal mengambil detail data.'], 500);
        }
    }


    public function store(SantriPotonganRequest $request)
    {
        try {
            $result = $this->service->assign($request->validated());
            return response()->json([
                'message' => 'Potongan berhasil ditetapkan ke santri.',
                'result'  => $result
            ], 201);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Gagal menyimpan potongan santri.'], 500);
        }
    }

    public function update(SantriPotonganRequest $request, int $id)
    {
        try {
            $updated = $this->service->update($id, $request->validated());
            if (!$updated) {
                return response()->json(['message' => 'Data tidak ditemukan.'], 404);
            }
            return response()->json(['message' => 'Data berhasil diperbarui.']);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Gagal memperbarui data.'], 500);
        }
    }

    public function destroy(int $id)
    {
        try {
            $deleted = $this->service->delete($id);
            if (!$deleted) {
                return response()->json(['message' => 'Data tidak ditemukan.'], 404);
            }
            return response()->json(['message' => 'Data berhasil dihapus.']);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Gagal menghapus data.'], 500);
        }
    }
}
