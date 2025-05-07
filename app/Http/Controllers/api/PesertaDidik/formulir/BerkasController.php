<?php

namespace App\Http\Controllers\api\PesertaDidik\formulir;

use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Http\Requests\PesertaDidik\BerkasRequest;
use App\Services\PesertaDidik\Formulir\BerkasService;

class BerkasController extends Controller
{
    private BerkasService $berkas;
    public function __construct(BerkasService $berkas)
    {
        $this->berkas = $berkas;
    }

    public function update(BerkasRequest $request, $id)
    {
        try {
            $berkas = $this->berkas->update($request->validated(), $id);

            return response()->json([
                'message' => 'Berkas berhasil di update.'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat memperbarui data.',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
