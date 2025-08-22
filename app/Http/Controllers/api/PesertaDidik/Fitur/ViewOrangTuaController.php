<?php

namespace App\Http\Controllers\api\PesertaDidik\Fitur;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Services\PesertaDidik\Fitur\ViewOrangTuaService;
use App\Http\Requests\PesertaDidik\Fitur\ViewOrangTuaRequest;

class ViewOrangTuaController extends Controller
{
    protected $viewOrangTuaService;

    public function __construct(ViewOrangTuaService $viewOrangTuaService)
    {
        $this->viewOrangTuaService = $viewOrangTuaService;
    }

    public function getTransaksiAnak(ViewOrangTuaRequest $request): JsonResponse
    {
        try {
            $filters = $request->only([
                'santri_id',
                'outlet_id',
                'kategori_id',
                'date_from',
                'date_to',
                'q'
            ]);
            $perPage = 25;

            $result = $this->viewOrangTuaService->getTransaksiAnak($filters, $perPage);

            return response()->json($result, $result['status']);
        } catch (\Throwable $e) {
            Log::error('ViewOrangTuaController@index error: ' . $e->getMessage(), ['exception' => $e]);

            return response()->json([
                'success' => false,
                'status'  => 500,
                'message' => 'Terjadi kesalahan saat mengambil daftar transaksi.',
                'data'    => []
            ], 500);
        }
    }
}
