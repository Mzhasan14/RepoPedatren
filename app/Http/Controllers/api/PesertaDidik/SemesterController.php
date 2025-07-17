<?php

namespace App\Http\Controllers\api\PesertaDidik;

use App\Models\Semester;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
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

            $data = $request->validated();
            $data['created_by'] = Auth::id();

            $semester = Semester::create($data);
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

            $newStatus = $request->boolean('status');
            $oldStatus = $semester->status;

            // Cegah jika akan menonaktifkan satu-satunya semester aktif di tahun ajaran tersebut
            if (!$newStatus && $oldStatus) {
                $cekLainAktif = Semester::where('tahun_ajaran_id', $request->tahun_ajaran_id)
                    ->where('id', '!=', $semester->id)
                    ->where('status', true)
                    ->exists();

                if (!$cekLainAktif) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Setidaknya harus ada satu semester aktif dalam tahun ajaran ini.',
                    ], 422);
                }
            }

            // Jika akan diaktifkan, nonaktifkan semester lain dalam tahun ajaran yang sama
            if ($newStatus) {
                Semester::where('tahun_ajaran_id', $request->tahun_ajaran_id)
                    ->where('id', '!=', $semester->id)
                    ->where('status', true)
                    ->update(['status' => false]);
            }

            $data = $request->validated();
            $data['updated_by'] = Auth::id();

            $semester->update($data);

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

            $semester->deleted_by = Auth::id();
            $semester->save();
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
