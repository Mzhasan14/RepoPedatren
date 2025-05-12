<?php

namespace App\Http\Controllers\api\PesertaDidik\formulir;

use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Requests\PesertaDidik\WargaPesantrenRequest;
use App\Services\PesertaDidik\Formulir\WargaPesantrenService;

class WargaPesantrenController extends Controller
{
    private WargaPesantrenService $wargaPesantren;

    public function __construct(WargaPesantrenService $wargaPesantren)
    {
        $this->wargaPesantren = $wargaPesantren;
    }

    public function index($id)
    {
        try {
            $result = $this->wargaPesantren->index($id);
            if (!$result['status']) {
                return response()->json([
                    'message' => $result['message'] ?? 'Data tidak ditemukan.'
                ], 200);
            }
            return response()->json([
                'message' => 'Data berhasil ditampilkan',
                'data' => $result['data']
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal ambil data wargapesantren: ' . $e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat menampilkan data.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(WargaPesantrenRequest $request, $bioId)
    {
        try {
            $result = $this->wargaPesantren->store($request->validated(), $bioId);
            if (!$result['status']) {
                return response()->json([
                    'message' => $result['message']
                ], 200);
            }
            return response()->json([
                'message' => 'Data berhasil ditambah',
                'data' => $result['data']
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal tambah domisili: ' . $e->getMessage());
            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function edit($bioId)
    {
        try {
            $result = $this->wargaPesantren->edit($bioId);
            if (!$result['status']) {
                return response()->json([
                    'data' => $result['data']
                ], 200);
            }
            return response()->json([
                'message' => 'Data berhasil ditampilkan',
                'data' => $result['data']
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal menampilkan data: ' . $e->getMessage());
            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(WargaPesantrenRequest $request, $id)
    {
        try {
            $result = $this->wargaPesantren->update($request->validated(), $id);
            if (!$result['status']) {
                return response()->json([
                    'message' => $result['message']
                ], 200);
            }
            return response()->json([
                'message' => 'Data berhasil diperbarui',
                'data' => $result['data']
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal memperbarui data: ' . $e->getMessage());
            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses data',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
