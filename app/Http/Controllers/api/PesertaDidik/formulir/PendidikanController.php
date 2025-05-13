<?php

namespace App\Http\Controllers\api\PesertaDidik\formulir;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Requests\PesertaDidik\CreatePendidikanRequest;
use App\Http\Requests\PesertaDidik\KeluarPendidikanRequest;
use App\Http\Requests\PesertaDidik\PendidikanRequest;
use App\Http\Requests\PesertaDidik\PindahPendidikanRequest;
use App\Http\Requests\PesertaDidik\UpdatePendidikanRequest;
use App\Services\PesertaDidik\Formulir\PendidikanService;

class PendidikanController extends Controller
{
    private PendidikanService $pendidikan;

    public function __construct(PendidikanService $pendidikan)
    {
        $this->pendidikan = $pendidikan;
    }

    public function index($id)
    {
        try {
            $result = $this->pendidikan->index($id);
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
            Log::error('Gagal ambil data pendidikan: ' . $e->getMessage());
            return response()->json([
                'message' => 'Terjadi kesalahan saat menampilkan data.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(CreatePendidikanRequest $request, $bioId)
    {
        try {
            $result = $this->pendidikan->store($request->validated(), $bioId);
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
            Log::error('Gagal tambah pendidikan: ' . $e->getMessage());
            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses data',
                'error' => $e->getMessage()
            ], 500);    
        }
    }

    public function edit($id)
    {
        try {
            $result = $this->pendidikan->edit($id);
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
            Log::error('Gagal ambil detail pendidikan: ' . $e->getMessage());
            return response()->json([
                'message' => 'Terjadi kesalahan saat menampilkan data.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(UpdatePendidikanRequest $request, $id)
    {
        try {
            $result = $this->pendidikan->update($request->validated(), $id);

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
            Log::error('Gagal update pendidikan: ' . $e->getMessage());
            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function pindahPendidikan(PindahPendidikanRequest $request, $id)
    {
        try {
            $result = $this->pendidikan->pindahPendidikan($request->validated(), $id);

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
            Log::error('Gagal update pendidikan: ' . $e->getMessage());
            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function keluarPendidikan(KeluarPendidikanRequest $request, $id)
    {
        try {
            $result = $this->pendidikan->keluarPendidikan($request->validated(), $id);

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
            Log::error('Gagal update pendidikan: ' . $e->getMessage());
            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses data',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
