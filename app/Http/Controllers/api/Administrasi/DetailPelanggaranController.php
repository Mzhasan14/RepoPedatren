<?php

namespace App\Http\Controllers\api\Administrasi;

use App\Models\Pelanggaran;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Services\Administrasi\DetailPelanggaranService;

class DetailPelanggaranController extends Controller
{
    private DetailPelanggaranService $detailPelanggaranService;

    public function __construct(DetailPelanggaranService $detailPelanggaranService)
    {
        $this->detailPelanggaranService = $detailPelanggaranService;
    }

    public function getDetailPelanggaran($id)
    {
        try {
            $pelanggaran = Pelanggaran::find($id);
            if (!$pelanggaran) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'ID Pelanggaran tidak ditemukan',
                    'data' => []
                ], 404);
            }

            $data = $this->detailPelanggaranService->getDetailPelanggaran($id);

            return response()->json([
                'status' => true,
                'data'    => $data,
            ], 200);
        } catch (\Exception $e) {
            Log::error("Error DetailPelanggaran: " . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan pada server',
            ], 500);
        }
    }
}
