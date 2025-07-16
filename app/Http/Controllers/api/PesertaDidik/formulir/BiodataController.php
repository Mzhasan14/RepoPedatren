<?php

namespace App\Http\Controllers\api\PesertaDidik\formulir;

use App\Http\Controllers\Controller;
use App\Http\Requests\PesertaDidik\formulir\BiodataRequest;
use App\Services\PesertaDidik\Formulir\BiodataService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class BiodataController extends Controller
{
    private BiodataService $biodata;

    public function __construct(BiodataService $biodata)
    {
        $this->biodata = $biodata;
    }

    // public function store(BiodataRequest $request)
    // {
    //     try {
    //         $result = $this->biodata->store($request->validated());
    //         if (!$result['status']) {
    //             return response()->json([
    //                 'message' => $result['message']
    //             ], 200);
    //         }
    //         return response()->json([
    //             'message' => 'Data berhasil ditambah',
    //             'data' => $result['data']
    //         ]);
    //     } catch (\Exception $e) {
    //         Log::error('Gagal tambah biodata: ' . $e->getMessage());
    //         return response()->json([
    //             'message' => 'Terjadi kesalahan saat memproses data',
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }

    public function show($id)
    {
        try {
            $result = $this->biodata->show($id);
            if (! $result['status']) {
                return response()->json([
                    'message' => $result['message'] ?? 'Data tidak ditemukan.',
                ], 200);
            }

            return response()->json([
                'message' => 'Detail data berhasil ditampilkan',
                'data' => $result['data'],
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal ambil detail domisili: '.$e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat menampilkan data.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(BiodataRequest $request, $id)
    {
        try {
            $result = $this->biodata->update($request->validated(), $id);
            if (! $result['status']) {
                return response()->json([
                    'message' => $result['message'] ?? 'Data tidak ditemukan.',
                ], 200);
            }

            return response()->json([
                'message' => 'Biodata berhasil diperbarui',
                'data' => $result['data'],
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal memperbarui biodata: '.$e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat memperbarui data.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
