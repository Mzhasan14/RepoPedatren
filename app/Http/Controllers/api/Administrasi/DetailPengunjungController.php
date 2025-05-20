<?php

namespace App\Http\Controllers\api\Administrasi;

use App\Models\PengunjungMahrom;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Services\Administrasi\DetailPengunjungMahromService;

class DetailPengunjungController extends Controller
{
    private DetailPengunjungMahromService $detailPengunjung;

    public function __construct(DetailPengunjungMahromService $detailPengunjung)
    {
        $this->detailPengunjung = $detailPengunjung;
    }

    public function getDetailPengunjung($id)
    {
        try {
            $perizinan = PengunjungMahrom::find($id);
            if (!$perizinan) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'ID Pengunjung tidak ditemukan',
                    'data' => []
                ], 404);
            }

            $data = $this->detailPengunjung->getDetailPengunjung($id);

            return response()->json([
                'status' => true,
                'data'    => $data,
            ], 200);
        } catch (\Exception $e) {
            Log::error("Error DetailPengunjung: " . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan pada server',
            ], 500);
        }
    }
}
