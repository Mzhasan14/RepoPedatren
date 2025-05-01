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
            // Cek apakah ID santri ada di tabel
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
                'status' => 'success',
                'data'    => $data,
            ], 200);
        } catch (\Exception $e) {
            Log::error("Error DetailPesertaDidikSantri: " . $e->getMessage());
            return ['error' => 'Terjadi kesalahan pada server'];
        }
    }
}
