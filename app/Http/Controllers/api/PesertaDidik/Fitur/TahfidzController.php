<?php

namespace App\Http\Controllers\api\PesertaDidik\Fitur;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\PesertaDidik\TahfidzRequest;
use App\Services\PesertaDidik\Fitur\TahfidzService;

class TahfidzController extends Controller
{
    private TahfidzService $service;

    public function __construct(TahfidzService $service)
    {
        $this->service = $service;
    }

    public function store(TahfidzRequest $request)
    {
        try {
            $validated = $request->validated();
            $result = $this->service->setoranTahfidz($validated);

            return response()->json([
                'success' => true,
                'message' => 'Setoran tahfidz berhasil disimpan.',
                'data' => $result,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan setoran tahfidz.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function listSetoran(Request $request)
    {
        try {
            $result = $this->service->listSetoran($request);

            return response()->json([
                'success' => true,
                'data' => $result,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data setoran tahfidz.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function listRekap(Request $request)
    {
        try {
            $result = $this->service->listRekap($request);

            return response()->json([
                'success' => true,
                'data' => $result,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data rekap tahfidz.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getAllRekap(Request $request)
    {
        try {
            $result = $this->service->getAllRekap($request);

            return response()->json([
                'success' => true,
                'data' => $result,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data rekap tahfidz.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
