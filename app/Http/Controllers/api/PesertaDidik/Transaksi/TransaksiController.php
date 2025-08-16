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

        $result = $this->service->scanCard($data['uid_kartu'], $data['pin'] ?? null, Auth::id());

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
            $data['uid_kartu'],
            $data['outlet_id'],
            $data['kategori_id'],
            $data['total_bayar'],
            $data['pin'] ?? null,
            Auth::id()
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

    // GET /api/transactions?page=1
    public function index(Request $request): JsonResponse
    {
        try {
            // ambil filter opsional dari query string
            $filters = $request->only(['santri_id', 'outlet_id', 'kategori_id', 'date_from', 'date_to', 'q']);
            $perPage = 25;

            $paginated = $this->service->listTransactions($filters, $perPage);

            return response()->json([
                'success' => true,
                'data' => $paginated
            ], 200);
        } catch (\Throwable $e) {
            Log::error('TransactionController@index error: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil daftar transaksi.'
            ], 500);
        }
    }
}
