<?php

namespace App\Http\Controllers\api\PesertaDidik\Fitur;

use App\Http\Controllers\Controller;
use App\Http\Requests\PesertaDidik\PindahKamarRequest;
use App\Services\PesertaDidik\Fitur\PindahKamarService;

class PindahKamarController extends Controller
{
    private PindahKamarService $pindah;

    public function __construct(PindahKamarService $pindah)
    {
        $this->pindah = $pindah;
    }

    public function pindah(PindahKamarRequest $request)
    {

        try {
            $validated = $request->validated();
            $result = $this->pindah->pindah($validated);

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => [
                    'berhasil' => $result['data_baru'],
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses permintaan.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
