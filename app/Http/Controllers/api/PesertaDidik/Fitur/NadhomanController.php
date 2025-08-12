<?php

namespace App\Http\Controllers\api\PesertaDidik\Fitur;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\PesertaDidik\NadhomanRequest;
use App\Services\PesertaDidik\Fitur\NadhomanService;

class NadhomanController extends Controller
{
    private NadhomanService $service;
    
    public function __construct(NadhomanService $service)
    {
        $this->service = $service;
    }

    public function store(NadhomanRequest $request)
    {
        try {
            $validated = $request->validated();
            $result = $this->service->setoranNadhoman($validated);

            return response()->json([
                'success' => true,
                'message' => 'Setoran nadhoman berhasil disimpan.',
                'data' => $result,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan setoran nadhoman.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function listSetoran(Request $request)
    {
        try {
            $filters = $request->all();
            $result = $this->service->listSetoran($filters);

            return response()->json([
                'success' => true,
                'data' => $result,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data setoran nadhoman.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function listRekap(Request $request)
    {
        try {
            $filters = $request->all();
            $result = $this->service->listRekap($filters);

            return response()->json([
                'success' => true,
                'data' => $result,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data rekap nadhoman.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
