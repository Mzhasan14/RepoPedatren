<?php

namespace App\Http\Controllers\api\PesertaDidik\Fitur;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Requests\PesertaDidik\Fitur\ViewOrangTuaRequest;
use App\Services\PesertaDidik\Fitur\ViewOrangTuaService;

class ViewOrangTuaController extends Controller
{
    protected $viewOrangTuaService;

    public function __construct(ViewOrangTuaService $viewOrangTuaService)
    {
        $this->viewOrangTuaService = $viewOrangTuaService;
    }

    public function getAnak(ViewOrangTuaRequest $request)
    {
        try {
            return $this->viewOrangTuaService->getAnak($request->biodata_id_ortu);
        } catch (Exception $e) {
            Log::error('Error mengambil data anak:' . $e->getMessage(), [
                'biodata_id_ortu' => $request->biodata_id_ortu,
            ]);
            return response()->json([
                'message' => 'Terjadi kesalahan saat mengambil data anak.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
