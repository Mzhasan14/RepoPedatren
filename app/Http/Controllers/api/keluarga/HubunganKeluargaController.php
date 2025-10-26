<?php

namespace App\Http\Controllers\api\keluarga;

use App\Http\Controllers\Controller;
use App\Http\Requests\Keluarga\HubunganKeluargaRequest;
use App\Http\Requests\Keluarga\SetWaliRequest;
use App\Models\HubunganKeluarga;
use App\Services\Keluarga\HubunganKeluargaService;
use Illuminate\Http\JsonResponse;

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

    public function getHubungan()
    {
        $hk = HubunganKeluarga::select('id', 'nama_status')->get();

        return response()->json($hk);
    }

    public function index(): JsonResponse
    {
        $result = $this->service->index();

        return response()->json([
            'status' => true,
            'data' => $result['data'],
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
            if (! $result['status']) {
                return response()->json([
                    'message' => $result['message'],
                ], 404);
            }

            return response()->json([
                'message' => 'Data berhasil disimpan',
                'data' => $result['data'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id): JsonResponse
    {
        $result = $this->service->show($id);

        if (! $result['status']) {
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

        if (! $result['status']) {
            return response()->json(['message' => $result['message']], 404);
        }

        return response()->json([
            'message' => 'Data berhasil diperbarui',
            'data' => $result['data'],
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id): JsonResponse
    {
        $result = $this->service->delete($id);

        if (! $result['status']) {
            return response()->json([
                'message' => $result['message'],
            ], 404);
        }

        return response()->json([
            'message' => $result['message'],
        ]);
    }

    public function setWali(SetWaliRequest $request): JsonResponse
    {
        $biodataId = $request->validated()['biodata_id'];

        try {
            $result = $this->service->setwali($biodataId);

            return response()->json($result, $result['status'] ? 200 : 422);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan internal: ' . $e->getMessage(),
            ], 500);
        }
    }
}
