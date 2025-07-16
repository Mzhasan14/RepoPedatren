<?php

namespace App\Http\Controllers\api\PesertaDidik;

use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Requests\PesertaDidik\TahunAjaranRequest;

class TahunAjaranController extends Controller
{
    public function index()
    {
        try {
            $data = TahunAjaran::orderByDesc('tahun_ajaran')->get();
            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            Log::error('[TahunAjaran][Index] ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data.',
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $data = TahunAjaran::findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            Log::error('[TahunAjaran][Show] ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan.',
            ], 404);
        }
    }

    public function store(TahunAjaranRequest $request)
    {
        DB::beginTransaction();
        try {
            if ($request->boolean('status')) {
                TahunAjaran::where('status', true)->update(['status' => false]);
            }

            $tahunAjaran = TahunAjaran::create($request->validated());
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Tahun ajaran berhasil ditambahkan.',
                'data' => $tahunAjaran,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('[TahunAjaran][Store] ' . $e->getMessage(), ['exception' => $e, 'input' => $request->all()]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambah tahun ajaran.',
            ], 500);
        }
    }

    public function update(TahunAjaranRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $tahunAjaran = TahunAjaran::findOrFail($id);
            $newStatus = $request->boolean('status');
            $oldStatus = $tahunAjaran->status;

            // Cek: Jika sebelumnya aktif dan mau di-nonaktifkan
            if (!$newStatus && $oldStatus) {
                $cekLainAktif = TahunAjaran::where('status', true)
                    ->where('id', '!=', $tahunAjaran->id)
                    ->exists();

                if (!$cekLainAktif) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Setidaknya harus ada satu tahun ajaran yang aktif.',
                        'data' => [
                            'status' => ['Setidaknya harus ada satu tahun ajaran yang aktif.']
                        ],
                    ], 422);
                }
            }

            // Jika status akan di-set aktif, nonaktifkan yang lain
            if ($newStatus) {
                TahunAjaran::where('status', true)
                    ->where('id', '!=', $tahunAjaran->id)
                    ->update(['status' => false]);
            }

            $tahunAjaran->update($request->validated());
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tahun ajaran berhasil diperbarui.',
                'data' => $tahunAjaran,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('[TahunAjaran][Update] ' . $e->getMessage(), [
                'exception' => $e,
                'input' => $request->all(),
                'tahun_ajaran_id' => $id,
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui tahun ajaran.',
            ], 500);
        }
    }


    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $tahunAjaran = TahunAjaran::findOrFail($id);

            if ($tahunAjaran->status) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat menghapus tahun ajaran yang sedang aktif.',
                ], 403);
            }

            $tahunAjaran->delete();
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Tahun ajaran berhasil dihapus.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('[TahunAjaran][Destroy] ' . $e->getMessage(), [
                'exception' => $e,
                'tahun_ajaran_id' => $id,
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus tahun ajaran.',
            ], 500);
        }
    }
}
