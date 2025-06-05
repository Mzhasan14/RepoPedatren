<?php

namespace App\Http\Controllers\api\PesertaDidik\Fitur;

use App\Http\Controllers\Controller;
use App\Http\Requests\PesertaDidik\ProsesLulusPendidikanRequest;
use App\Services\PesertaDidik\Fitur\ProsesLulusPendidikanService;

class ProsesLulusPendidikanController extends Controller
{
    private ProsesLulusPendidikanService $data;
    public function __construct(ProsesLulusPendidikanService $data)
    {
        $this->data = $data;
    }

    public function prosesLulus(ProsesLulusPendidikanRequest $request)
    {

        try {
            $validated = $request->validated();
            $result = $this->data->prosesLulus($validated);

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => [
                    'berhasil' => $result['data_berhasil'],
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
