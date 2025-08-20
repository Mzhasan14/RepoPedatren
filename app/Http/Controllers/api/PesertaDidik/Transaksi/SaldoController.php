<?php

namespace App\Http\Controllers\api\PesertaDidik\Transaksi;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Services\PesertaDidik\Transaksi\SaldoService;
use App\Http\Requests\PesertaDidik\Transaksi\SaldoRequest;
use App\Http\Requests\PesertaDidik\Transaksi\TopUpRequest;

class SaldoController extends Controller
{
    protected $service;

    public function __construct(SaldoService $service)
    {
        $this->service = $service;
    }

    public function topup(SaldoRequest $request): JsonResponse
    {
        $result = $this->service->topup(
            $request->metode,
            $request->santri_id,
            $request->jumlah,
            $request->pin,
            Auth::id()
        );

        return response()->json($result, $result['status'] ? 200 : 400);
    }

    public function tarik(SaldoRequest $request): JsonResponse
    {
        $result = $this->service->tarik(
            $request->metode,
            $request->santri_id,
            $request->jumlah,
            $request->pin,
            Auth::id()
        );

        return response()->json($result, $result['status'] ? 200 : 400);
    }

    // public function requestTopUp(TopUpRequest $request, string $santriId): JsonResponse
    // {
    //     try {
    //         $transaksi = $this->service->requestTopUp(
    //             $santriId,
    //             $request->nominal,
    //             $request->file('bukti_transfer')
    //         );

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Top-up berhasil diajukan, menunggu verifikasi admin.',
    //             'data' => $transaksi
    //         ]);
    //     } catch (Exception $e) {
    //         return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
    //     }
    // }

    // public function approveTopUp(int $transaksiId): JsonResponse
    // {
    //     try {
    //         $transaksi = $this->service->approveTopUp($transaksiId);

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Top-up berhasil disetujui.',
    //             'data' => $transaksi
    //         ]);
    //     } catch (Exception $e) {
    //         return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
    //     }
    // }
}
