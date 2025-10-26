<?php

namespace App\Http\Controllers\api\PesertaDidik\Pembayaran;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Services\PesertaDidik\Pembayaran\PembayaranService;
use App\Http\Requests\PesertaDidik\Pembayaran\PembayaranRequest;

class PembayaranController extends Controller
{
    private PembayaranService $service;

    public function __construct(PembayaranService $service)
    {
        $this->service = $service;
    }

    public function bayar(PembayaranRequest $request)
    {
        $result = $this->service->bayar($request->validated(), Auth::id());
        return response()->json($result, $result['success'] ? 200 : 500);
    }
}
