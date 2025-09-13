<?php

namespace App\Http\Controllers\api\PesertaDidik\Fitur;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Requests\PesertaDidik\OrangTua\CatatanAfektifRequest;
use App\Http\Requests\PesertaDidik\OrangTua\CatatanKognitifRequest;
use Illuminate\Support\Facades\Auth;
use App\Services\PesertaDidik\OrangTua\PerizinanService;
use App\Services\PesertaDidik\OrangTua\HafalanAnakService;
use App\Services\PesertaDidik\OrangTua\TransaksiAnakService;
use App\Http\Requests\PesertaDidik\OrangTua\PerizinanRequest;
use App\Http\Requests\PesertaDidik\OrangTua\PelanggaranRequest;
use App\Http\Requests\PesertaDidik\OrangTua\ViewHafalanRequest;
use App\Http\Requests\PesertaDidik\OrangTua\ViewTransaksiRequest;
use App\Services\PesertaDidik\OrangTua\PresensiJamaahAnakService;
use App\Http\Requests\PesertaDidik\OrangTua\PresensiJamaahAnakRequest;
use App\Http\Requests\PesertaDidik\OrangTua\PresensiJamaahTodayRequest;
use App\Services\PesertaDidik\OrangTua\CatatanAfektifService;
use App\Services\PesertaDidik\OrangTua\CatatanKognitifService;
use App\Services\PesertaDidik\OrangTua\PelanggaranService;

class ViewOrangTuaController extends Controller
{
    protected $viewOrangTuaService;
    protected $viewHafalanService;
    protected $viewPresensiJamaahAnakService;
    protected $viewPerizinanService;
    protected $viewPelanggaranService;
    protected $CatatanAfektifService;
    protected $CatatanKognitifService;

    public function __construct(
        TransaksiAnakService $viewOrangTuaService,
        HafalanAnakService $viewHafalanService,
        PresensiJamaahAnakService $viewPresensiJamaahAnakService,
        PerizinanService $viewPerizinanService,
        PelanggaranService $viewPelanggaranService,
        CatatanAfektifService $CatatanAfektifService,
        CatatanKognitifService $CatatanKognitifService
    ) {
        $this->viewOrangTuaService = $viewOrangTuaService;
        $this->viewHafalanService = $viewHafalanService;
        $this->viewPresensiJamaahAnakService = $viewPresensiJamaahAnakService;
        $this->viewPerizinanService = $viewPerizinanService;
        $this->viewPelanggaranService = $viewPelanggaranService;
        $this->CatatanAfektifService = $CatatanAfektifService;
        $this->CatatanKognitifService = $CatatanKognitifService;
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

    public function getPresensiJamaahAnak(PresensiJamaahAnakRequest $request): JsonResponse
    {
        try {

            $result = $this->viewPresensiJamaahAnakService->getPresensiJamaahAnak($request->validated());

            return response()->json($result, 200);
        } catch (Exception $e) {
            Log::error('ViewOrangTuaController@getPresensiJamaahAnak error: ' . $e->getMessage(), [
                'exception' => $e,
                'user_id'   => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data presensi jamaah anak.',
                'status'  => 500,
                'data'    => []
            ], 500);
        }
    }

    public function getPresensiToday(PresensiJamaahTodayRequest $request): JsonResponse
    {
        try {
            $result = $this->viewPresensiJamaahAnakService->getPresensiToday($request->validated());

            return response()->json($result, 200);
        } catch (Exception $e) {
            Log::error('ViewOrangTuaController@getPresensiToday error: ' . $e->getMessage(), [
                'exception' => $e,
                'user_id'   => Auth::id(),
                'santri_id' => request()->get('santri_id'),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data presensi hari ini.',
                'status'  => 500,
                'data'    => []
            ], 500);
        }
    }

    public function perizinan(PerizinanRequest $request)
    {
        try {
            $result = $this->viewPerizinanService->perizinan($request->validated());

            return response()->json($result, 200);
        } catch (Exception $e) {
            Log::error('ViewOrangTuaController@ perizinan error : ', $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data presensi hari ini.',
                'status'  => 500,
                'data'    => []
            ], 500);
        }
    }

    public function pelanggaran(PelanggaranRequest $request)
    {
        try {
            $result = $this->viewPelanggaranService->pelanggaran($request->validated());

            return response()->json($result, 200);
        } catch (Exception $e) {
            Log::error('ViewOrangTuaController@ pelanggaran error : ', $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data presensi hari ini.',
                'status'  => 500,
                'data'    => []
            ], 500);
        }
    }
    public function catatanAfektif(CatatanAfektifRequest $request)
    {
        try {
            $result = $this->CatatanAfektifService->catatanAfektif($request->validated());

            return response()->json($result, 200);
        } catch (Exception $e) {
            Log::error('ViewOrangTuaController@ Catatan Afektif error : ', $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data Catatan Afektif.',
                'status'  => 500,
                'data'    => []
            ], 500);
        }
    }

    public function catatanKognitif(CatatanKognitifRequest $request)
    {
        try {
            $result = $this->CatatanKognitifService->catatanKognitif($request->validated());

            return response()->json($result, 200);
        } catch (Exception $e) {
            Log::error('ViewOrangTuaController@ Catatan Afektif error : ', $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data Catatan Afektif.',
                'status'  => 500,
                'data'    => []
            ], 500);
        }
    }
}
