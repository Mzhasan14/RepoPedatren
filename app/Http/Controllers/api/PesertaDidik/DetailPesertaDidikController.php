<?php

namespace App\Http\Controllers\api\PesertaDidik;

use App\Models\Santri;
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

    public function getDetailPesertaDidik(string $idSantri)
    {
        try {
            $santri = Santri::find($idSantri);
            if (!$santri) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'ID Santri tidak ditemukan',
                    'data' => []
                ], 404);
            }

            $data = $this->detailPesertaDidikService->getDetailPesertaDidik($idSantri);
            
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
