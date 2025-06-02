<?php

namespace App\Http\Controllers\api\keluarga;

use Illuminate\Http\Request;
use App\Models\HubunganKeluarga;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Services\Keluarga\HubunganKeluargaService;
use App\Http\Requests\Keluarga\HubunganKeluargaRequest;



class HubunganKeluargaController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    private HubunganKeluargaService $service;

    public function __construct(HubunganKeluargaService $service)
    {
        $this->service = $service;
    }

    public function getHubungan() {
        $hk = HubunganKeluarga::select('id', 'nama_status')->get();
        return response()->json($hk);
    }
    public function index(): JsonResponse
    {
        $result = $this->service->index();

        return response()->json([
            'status' => true,
            'data'   => $result['data']
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(HubunganKeluargaRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $result = $this->service->store($validated);
            if (!$result['status']) {
                return response()->json([
                    'message' => $result['message']
                ], 404);
            }

            return response()->json([
                'message' => 'Data berhasil disimpan',
                'data'    => $result['data']
            ]);
        } catch (\Exception $e) {
                return response()->json([
                    'message' => 'Terjadi kesalahan saat memproses data',
                    'error' => $e->getMessage()
                ], 500);
            }
    }

    /**
     * Display the specified resource.
     */
    public function show($id): JsonResponse
    {
        $result = $this->service->show($id);

        if (!$result['status']) {
            return response()->json(['message' => $result['message']], 404);
        }

        return response()->json(['data' => $result['data']]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(HubunganKeluargaRequest $request, $id): JsonResponse
    {
        $validated = $request->validated();
        $result = $this->service->update($validated, $id);

        if (!$result['status']) {
            return response()->json(['message' => $result['message']], 404);
        }

        return response()->json([
            'message' => 'Data berhasil diperbarui',
            'data'    => $result['data'],
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id): JsonResponse
    {
        $result = $this->service->delete($id);

        if (!$result['status']) {
            return response()->json([
                'message' => $result['message']
            ], 404);
        }

        return response()->json([
            'message' => $result['message']
        ]);
    }
}
