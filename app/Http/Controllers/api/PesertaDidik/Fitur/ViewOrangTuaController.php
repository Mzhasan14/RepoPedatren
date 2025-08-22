<?php

namespace App\Http\Controllers\api\PesertaDidik\Fitur;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
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
            $filters = array_filter($request->only([
                'santri_id',
                'outlet_id',
                'kategori_id',
                'date_from',
                'date_to',
                'q'
            ]));

            $perPage = $request->get('per_page', 25);

            $result = $this->viewOrangTuaService->getTransaksiAnak($filters, $perPage);

            $status = $result['status'] ?? 200;

            return response()->json($result, $status);
        } catch (\Throwable $e) {
            Log::error('ViewOrangTuaController@getTransaksiAnak error: ' . $e->getMessage(), [
                'exception' => $e,
                'user_id'   => Auth::id(),
                'filters'   => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'status'  => 500,
                'message' => 'Terjadi kesalahan saat mengambil daftar transaksi.',
                'data'    => []
            ], 500);
        }
    }
}
