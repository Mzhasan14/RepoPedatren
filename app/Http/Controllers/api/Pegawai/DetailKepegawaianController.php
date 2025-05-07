<?php

namespace App\Http\Controllers\Api\Pegawai;

use App\Http\Controllers\Controller;
use App\Models\Pegawai\Pegawai;
use App\Services\Pegawai\GetDetailKepegawaianService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DetailKepegawaianController extends Controller
{
    private GetDetailKepegawaianService $getDetailKepegawaianService;
    public function __construct(GetDetailKepegawaianService $getDetailKepegawaianService)
    {
        $this->getDetailKepegawaianService = $getDetailKepegawaianService;
    }
    public function getAllKepegawaian(string $id)
    {
        // try {
            $Pegawai = Pegawai::find($id);
            if (!$Pegawai) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'ID Santri tidak ditemukan',
                    'data' => []
                ], 404);
            }

            $data = $this->getDetailKepegawaianService->getAllKepegawaian($id);
            
            return response()->json([
                'status' => true,
                'data'    => $data,
            ], 200);
        // } catch (\Exception $e) {
        //     Log::error("Error DetailKepegawaian: " . $e->getMessage());
        //     return response()->json([
        //         'status'  => 'error',
        //         'message' => 'Terjadi kesalahan pada server',
        //     ], 500);
        // }
    }
}
