<?php

namespace App\Http\Controllers\api\PesertaDidik\Pembayaran;

use Exception;
use App\Models\Potongan;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Services\PesertaDidik\Pembayaran\PotonganService;
use App\Http\Requests\PesertaDidik\Pembayaran\PotonganRequest;

class PotonganController extends Controller
{
    protected PotonganService $service;

    public function __construct(PotonganService $service)
    {
        $this->service = $service;
    }

    public function index(): JsonResponse
    {
        try {
            $potongan = Potongan::with('tagihans')->get();
            return response()->json(['success' => true, 'data' => $potongan], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil daftar potongan',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function show(Potongan $potongan): JsonResponse
    {
        try {
            $potongan->load('tagihans');
            return response()->json(['success' => true, 'data' => $potongan], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil detail potongan',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function store(PotonganRequest $request): JsonResponse
    {
        try {
            $potongan = $this->service->create($request->validated());
            return response()->json(['success' => true, 'data' => $potongan], 201);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal membuat potongan', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(PotonganRequest $request, Potongan $potongan): JsonResponse
    {
        try {
            $potongan = $this->service->update($potongan, $request->validated());
            return response()->json(['success' => true, 'data' => $potongan], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal mengupdate potongan', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy(Potongan $potongan): JsonResponse
    {
        try {
            $this->service->delete($potongan);
            return response()->json(['success' => true, 'message' => 'Potongan berhasil dihapus'], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal menghapus potongan', 'error' => $e->getMessage()], 500);
        }
    }
}
