<?php

namespace App\Http\Controllers\api\kewaliasuhan;

use App\Http\Controllers\Controller;
use App\Http\Requests\Kewaliasuhan\KewaliasuhanRequest;
use App\Services\Kewaliasuhan\KewaliasuhanService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class KewaliasuhanController extends Controller
{
    private KewaliasuhanService $service;

    public function __construct(KewaliasuhanService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(KewaliasuhanRequest $request): JsonResponse
    {
        try {
            // Validasi request
            $validated = $request->validated();

            // Panggil service untuk buat grup wali asuh + wali asuh + anak asuh + kewaliasuhan
            $result = $this->service->createGrup($validated);

            // Jika service mengembalikan status false
            if (! $result['status']) {
                return response()->json([
                    'message' => $result['message'],
                ], 200);
            }

            // Sukses
            return response()->json([
                'message' => 'Data berhasil ditambah',
                'data' => [
                    'grup' => $result['grup'],
                    'wali_asuh' => $result['wali_asuh'],
                    'anak_asuh' => $result['anak_asuh'],
                ],
            ]);
        } catch (\Exception $e) {
            // Log error agar bisa dilacak
            Log::error('Gagal tambah waliasuh: '.$e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
