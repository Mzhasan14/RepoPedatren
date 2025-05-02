<?php

namespace App\Http\Controllers\api\Administrasi;

use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\Perizinan;
use App\Services\Administrasi\PerizinanService;
use App\Services\Administrasi\DetailPerizinanService;

class DetailPerizinanController extends Controller
{
    private DetailPerizinanService $detailPerizinanService;

    public function __construct(DetailPerizinanService $detailPerizinanService)
    {
        $this->detailPerizinanService = $detailPerizinanService;
    }

    public function getDetailPerizinan($id)
    {
        try {
            $perizinan = Perizinan::find($id);
            if (!$perizinan) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'ID Perizinan tidak ditemukan',
                    'data' => []
                ], 404);
            }

            $data = $this->detailPerizinanService->getDetailPerizinan($id);

            return response()->json([
                'status' => true,
                'data'    => $data,
            ], 200);
        } catch (\Exception $e) {
            Log::error("Error DetailPerizinan: " . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan pada server',
            ], 500);
        }
    }
}
