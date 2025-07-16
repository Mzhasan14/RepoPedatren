<?php

namespace App\Http\Controllers\api\PesertaDidik;

use App\Models\Semester;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Requests\PesertaDidik\SemesterRequest;

class SemesterController extends Controller
{
    public function index()
    {
        try {
            $data = Semester::with('tahunAjaran')->orderByDesc('id')->get();
            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            Log::error('[Semester][Index] ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data.',
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $data = Semester::with('tahunAjaran')->findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            Log::error('[Semester][Show] ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan.',
            ], 404);
        }
    }

    public function store(SemesterRequest $request)
    {
        DB::beginTransaction();
        try {
            if ($request->boolean('status')) {
                Semester::where('tahun_ajaran_id', $request->tahun_ajaran_id)
                    ->where('status', true)
                    ->update(['status' => false]);
            }

            $semester = Semester::create($request->validated());
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Semester berhasil ditambahkan.',
                'data' => $semester->load('tahunAjaran'),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('[Semester][Store] ' . $e->getMessage(), [
                'exception' => $e,
                'input' => $request->all(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambah semester.',
            ], 500);
        }
    }

    public function update(SemesterRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $semester = Semester::findOrFail($id);

            if ($request->boolean('status')) {
                Semester::where('tahun_ajaran_id', $request->tahun_ajaran_id)
                    ->where('id', '!=', $semester->id)
                    ->where('status', true)
                    ->update(['status' => false]);
            }

            $semester->update($request->validated());
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Semester berhasil diperbarui.',
                'data' => $semester->load('tahunAjaran'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('[Semester][Update] ' . $e->getMessage(), [
                'exception' => $e,
                'input' => $request->all(),
                'semester_id' => $id,
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui semester.',
            ], 500);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $semester = Semester::findOrFail($id);

            if ($semester->status) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat menghapus semester yang sedang aktif.',
                ], 403);
            }

            $semester->delete();
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Semester berhasil dihapus.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('[Semester][Destroy] ' . $e->getMessage(), [
                'exception' => $e,
                'semester_id' => $id,
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus semester.',
            ], 500);
        }
    }
}
