<?php

namespace App\Http\Controllers\api\PesertaDidik;

use App\Models\Santri;
use App\Models\Biodata;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Services\PesertaDidik\DetailPesertaDidikService;

class DetailPesertaDidikController extends Controller
{

    private DetailPesertaDidikService $detailPesertaDidikService;
    public function __construct(DetailPesertaDidikService $detailPesertaDidikService)
    {
        $this->detailPesertaDidikService = $detailPesertaDidikService;
    }

    public function getDetailPesertaDidik(string $bioId)
    {
        try {
            $data = $this->detailPesertaDidikService->getDetailPesertaDidik($bioId);
            return response()->json([
                'status' => true,
                'data'    => $data,
            ], 200);
        } catch (\Exception $e) {
            Log::error("Error DetailPesertaDidik: " . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan pada server',
            ], 500);
        }
    }
}
