<?php

namespace App\Http\Controllers\api\PesertaDidik;

use App\Http\Controllers\Controller;
use App\Services\PesertaDidik\DetailService;
use Illuminate\Support\Facades\Log;

class DetailController extends Controller
{
    private DetailService $detail;

    public function __construct(DetailService $detail)
    {
        $this->detail = $detail;
    }

    // Detail all data
    public function getDetail(string $bioId)
    {
        try {
            $data = $this->detail->getDetail($bioId);

            return response()->json([
                'status' => true,
                'data' => $data,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error Detail : '.$e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Data tidak ditemukan atau ID tidak valid.',
            ], 404);
        }
    }
}
