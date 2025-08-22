<?php

namespace App\Http\Controllers\api\PesertaDidik\Fitur;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Services\PesertaDidik\OrangTua\HafalanAnakService;
use App\Http\Requests\PesertaDidik\OrangTua\ViewHafalanRequest;
use Illuminate\Support\Facades\Auth;
use App\Services\PesertaDidik\OrangTua\TransaksiAnakService;
use App\Http\Requests\PesertaDidik\OrangTua\ViewTransaksiRequest;

class ViewOrangTuaController extends Controller
{
    protected $viewOrangTuaService;
    protected $viewHafalanService;

    public function __construct(
        TransaksiAnakService $viewOrangTuaService,
        HafalanAnakService $viewHafalanService
        )
    {
        $this->viewOrangTuaService = $viewOrangTuaService;
        $this->viewHafalanService = $viewHafalanService;
    }

    public function getTransaksiAnak(ViewTransaksiRequest $request): JsonResponse
    {
        try {
            $filters = array_filter($request->only([
                'santri_id',
                'outlet_id',
                'kategori_id',
                'date_from',
                'date_to',
                'q'
            ]));

            $perPage = $request->get('per_page', 25);

            $result = $this->viewOrangTuaService->getTransaksiAnak($filters, $perPage);

            $status = $result['status'] ?? 200;

            return response()->json($result, $status);
        } catch (\Throwable $e) {
            Log::error('ViewOrangTuaController@getTransaksiAnak error: ' . $e->getMessage(), [
                'exception' => $e,
                'user_id'   => Auth::id(),
                'filters'   => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'status'  => 500,
                'message' => 'Terjadi kesalahan saat mengambil daftar transaksi.',
                'data'    => []
            ], 500);
        }
    }

    public function getTahfidzAnak(ViewHafalanRequest $request)
    {
        try {
            $dataAnak = $request->validated();

            $result = $this->viewHafalanService->getTahfidzAnak($dataAnak);

            return response()->json($result, 200);
        } catch (Exception $e) {
            Log::error('ViewOrangTuaController@getTahfidzAnak error: ' . $e->getMessage(), [
                'exception' => $e,
                'user_id'   => Auth::id(),
                'santri_id' => $request->santri_id ?? null,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data tahfidz anak.',
                'status'  => 500,
                'data'    => []
            ], 500);
        }
    }

    public function getNadhomanAnak(ViewHafalanRequest $request)
    {
        try {
            $dataAnak = $request->validated();

            $result = $this->viewHafalanService->getNadhomanAnak($dataAnak);

            return response()->json($result, 200);
        } catch (Exception $e) {
            Log::error('ViewOrangTuaController@getNadhomanAnak error: ' . $e->getMessage(), [
                'exception' => $e,
                'user_id'   => Auth::id(),
                'santri_id' => $request->santri_id ?? null,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data nadhoman anak.',
                'status'  => 500,
                'data'    => []
            ], 500);
        }
    }
}
