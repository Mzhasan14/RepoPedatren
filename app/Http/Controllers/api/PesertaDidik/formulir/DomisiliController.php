<?php

namespace App\Http\Controllers\api\PesertaDidik\formulir;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Requests\PesertaDidik\CreateDomisiliRequest;
use App\Http\Requests\PesertaDidik\DomisiliRequest;
use App\Http\Requests\PesertaDidik\KeluarDomisiliRequest;
use App\Http\Requests\PesertaDidik\PindahDomisiliRequest;
use App\Http\Requests\PesertaDidik\UpdateDomisiliRequest;
use App\Services\PesertaDidik\Formulir\DomisiliService;

class DomisiliController extends Controller
{
    private DomisiliService $domisili;

    public function __construct(DomisiliService $domisili)
    {
        $this->domisili = $domisili;
    }

    public function index($id)
    {
        try {
            $result = $this->domisili->index($id);
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
            Log::error('Gagal ambil data domisili: ' . $e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat menampilkan data.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(CreateDomisiliRequest $request, $bioId)
    {
        try {
            $result = $this->domisili->store($request->validated(), $bioId);
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

    public function edit($id)
    {
        try {
            $result = $this->domisili->edit($id);
            if (!$result['status']) {
                return response()->json([
                    'message' => $result['message'] ?? 'Data tidak ditemukan.'
                ], 200);
            }
            return response()->json([
                'message' => 'Detail data berhasil ditampilkan',
                'data' => $result['data']
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal ambil detail domisili: ' . $e->getMessage());
            return response()->json([
                'message' => 'Terjadi kesalahan saat menampilkan data.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(UpdateDomisiliRequest $request, $id)
    {
        try {
            $result = $this->domisili->update($request->validated(), $id);

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
            Log::error('Gagal update domisili: ' . $e->getMessage());
            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function pindahDomisili(PindahDomisiliRequest $request, $id)
    {
        try {
            $result = $this->domisili->pindahDomisili($request->validated(), $id);

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
            Log::error('Gagal update domisili: ' . $e->getMessage());
            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses data',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function keluarDomisili(KeluarDomisiliRequest $request, $id)
    {
        try {
            $result = $this->domisili->keluarDomisili($request->validated(), $id);

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
            Log::error('Gagal update domisili: ' . $e->getMessage());
            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses data',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
