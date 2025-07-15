<?php

namespace App\Http\Controllers\api\PesertaDidik;

use App\Models\Angkatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Requests\PesertaDidik\AngkatanRequest;

class AngkatanController extends Controller
{
    public function index()
    {
        try {
            $data = Angkatan::with('tahunAjaran')->orderByDesc('angkatan')->get();
            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            Log::error('[Angkatan][Index] ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data.',
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $data = Angkatan::with('tahunAjaran')->findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            Log::error('[Angkatan][Show] ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan.',
            ], 404);
        }
    }

    public function store(AngkatanRequest $request)
    {
        DB::beginTransaction();
        try {
            $angkatan = Angkatan::create($request->validated());
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Angkatan berhasil ditambahkan.',
                'data' => $angkatan->load('tahunAjaran'),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('[Angkatan][Store] ' . $e->getMessage(), ['exception' => $e, 'input' => $request->all()]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambah angkatan.',
            ], 500);
        }
    }

    public function update(AngkatanRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $angkatan = Angkatan::findOrFail($id);
            $angkatan->update($request->validated());
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Angkatan berhasil diperbarui.',
                'data' => $angkatan->load('tahunAjaran'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('[Angkatan][Update] ' . $e->getMessage(), [
                'exception' => $e,
                'input' => $request->all(),
                'angkatan_id' => $id,
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui angkatan.',
            ], 500);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $angkatan = Angkatan::findOrFail($id);
            $angkatan->delete();
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Angkatan berhasil dihapus.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('[Angkatan][Destroy] ' . $e->getMessage(), [
                'exception' => $e,
                'angkatan_id' => $id,
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus angkatan.',
            ], 500);
        }
    }
}
