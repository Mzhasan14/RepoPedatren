<?php

namespace App\Http\Controllers\api\PesertaDidik\Transaksi;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Services\PesertaDidik\Transaksi\TransaksiService;
use App\Http\Requests\PesertaDidik\Transaksi\ScanKartuRequest;
use App\Http\Requests\PesertaDidik\Transaksi\TransaksiRequest;

class TransaksiController extends Controller
{
    protected TransaksiService $service;

    public function __construct(TransaksiService $service)
    {
        $this->service = $service;
    }

    // GET /api/transactions/scan?uid_kartu=xxx
    public function scan(ScanKartuRequest $request): JsonResponse
    {
        $data = $request->validated();

        $result = $this->service->scanCard($data['santri_id'], $data['pin'] ?? null);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'Kartu ditemukan.',
                'data' => $result['data']
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message']
        ], $result['status'] ?? 400);
    }

    // POST /api/transactions
    public function store(TransaksiRequest $request): JsonResponse
    {
        $data = $request->validated();

        $result = $this->service->createTransaction(
            $data['outlet_id'],
            $data['uid_kartu'],
            $data['kategori_id'],
            $data['total_bayar'],
            $data['pin'] ?? null
        );

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil.',
                'data' => $result['data']
            ], 201);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message']
        ], $result['status'] ?? 400);
    }

    public function index(Request $request): JsonResponse
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

            $result = $this->service->listTransactions($filters, $perPage);

            return response()->json($result, $result['status']);
        } catch (\Throwable $e) {
            Log::error('TransactionController@index error: ' . $e->getMessage(), ['exception' => $e]);

            return response()->json([
                'success' => false,
                'status'  => 500,
                'message' => 'Terjadi kesalahan saat mengambil daftar transaksi.',
                'data'    => []
            ], 500);
        }
    }

    public function transaksiToko(Request $request): JsonResponse
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

            $result = $this->service->transaksiToko($filters, $perPage);

            return response()->json($result, $result['status']);
        } catch (\Throwable $e) {
            Log::error('TransactionController@transaksiToko error: ' . $e->getMessage(), ['exception' => $e]);

            return response()->json([
                'success' => false,
                'status'  => 500,
                'message' => 'Terjadi kesalahan saat mengambil daftar transaksi.',
                'data'    => []
            ], 500);
        }
    }
}
