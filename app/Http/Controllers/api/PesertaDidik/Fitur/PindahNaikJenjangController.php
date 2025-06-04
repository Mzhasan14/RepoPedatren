<?php

namespace App\Http\Controllers\api\PesertaDidik\fitur;

use App\Http\Controllers\Controller;
use App\Http\Requests\PesertaDidik\PindahNaikJenjangRequest;
use App\Services\PesertaDidik\Fitur\PindahNaikJenjangService;
use Illuminate\Http\Request;

class PindahNaikJenjangController extends Controller
{
    private PindahNaikJenjangService $pindah;
    public function __construct(PindahNaikJenjangService $pindah)
    {
        $this->pindah = $pindah;
    }

    public function pindah(PindahNaikJenjangRequest $request)
    {

        try {
            $validated = $request->validated();
            $result = $this->pindah->pindah($validated);

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => [
                    'berhasil' => $result['data_baru'],
                    'gagal' => $result['data_gagal'],
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses permintaan.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function naik(PindahNaikJenjangRequest $request)
    {
        try {
            $validated = $request->validated();
            $result = $this->pindah->naik($validated);

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => [
                    'berhasil' => $result['data_baru'],
                    'gagal' => $result['data_gagal'],
                ]
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
