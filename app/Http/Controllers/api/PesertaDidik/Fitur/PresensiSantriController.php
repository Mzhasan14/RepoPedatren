<?php

namespace App\Http\Controllers\api\PesertaDidik\Fitur;

use Exception;
use Illuminate\Http\Request;
use App\Models\PresensiSantri;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\PesertaDidik\PresensiSantriRequest;
use App\Services\PesertaDidik\Fitur\PresensiSantriService;

class PresensiSantriController extends Controller
{
    protected $service;

    public function __construct(PresensiSantriService $service)
    {
        $this->service = $service;
    }

    public function getAllPresensiSantri(Request $request)
    {
        try {
            $query = $this->service->getAllPresensiSantri($request);
            $query = $query->latest('ps.id');

            $perPage     = (int) $request->input('limit', 25);
            $currentPage = (int) $request->input('page', 1);

            $results     = $query->paginate($perPage, ['*'], 'page', $currentPage);
        } catch (Exception $e) {
            Log::error('Error Presensi getAllPresensiSantri', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        if ($results->isEmpty()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Data kosong',
                'data'    => [],
            ], 200);
        }

        $formatted = $this->service->formatData($results);

        return response()->json([
            'total_data'   => $results->total(),
            'current_page' => $results->currentPage(),
            'per_page'     => $results->perPage(),
            'total_pages'  => $results->lastPage(),
            'data'         => $formatted,
        ]);
    }
    public function store(PresensiSantriRequest $request)
    {
        try {
            $presensi = $this->service->store($request->validated(), Auth::id());

            return response()->json([
                'success' => true,
                'message' => 'Presensi berhasil dicatat.',
                'data'    => $presensi
            ], 201);
        } catch (Exception $e) {
            Log::error('Error Presensi Santri STORE', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function update(PresensiSantriRequest $request, PresensiSantri $presensi)
    {
        try {
            $presensi = $this->service->update($presensi, $request->validated(), Auth::id());

            return response()->json([
                'success' => true,
                'message' => 'Presensi berhasil diupdate.',
                'data'    => $presensi
            ], 200);
        } catch (Exception $e) {
            Log::error('Error Presensi Santri UPDATE', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function destroy(PresensiSantri $presensi)
    {
        try {
            $this->service->delete($presensi, Auth::id());

            return response()->json([
                'success' => true,
                'message' => 'Presensi berhasil dihapus.',
            ], 200);
        } catch (Exception $e) {
            Log::error('Error Presensi Santri DESTROY', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
