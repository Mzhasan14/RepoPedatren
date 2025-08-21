<?php

namespace App\Http\Controllers\api\PesertaDidik\Pembayaran;

use Throwable;
use Illuminate\Http\Request;
use App\Models\TagihanKhusus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Requests\PesertaDidik\Pembayaran\TagihanKhususRequest;

class TagihanKhususController extends Controller
{
    /** LIST DATA */
    public function index(Request $request)
    {
        try {
            $query = TagihanKhusus::with(['tagihan', 'angkatan', 'lembaga', 'jurusan'])
                ->latest();

            if ($request->filled('tagihan_id')) {
                $query->where('tagihan_id', $request->tagihan_id);
            }

            $data = $query->paginate(20);

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (Throwable $e) {
            Log::error('TagihanKhusus Index Error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan'], 500);
        }
    }

    /** CREATE DATA */
    public function store(TagihanKhususRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = TagihanKhusus::create($request->validated());

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Tagihan khusus berhasil ditambahkan',
                'data' => $data
            ]);
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('TagihanKhusus Store Error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Gagal menambahkan data'], 500);
        }
    }

    /** DETAIL DATA */
    public function show($id)
    {
        try {
            $data = TagihanKhusus::with(['tagihan', 'angkatan', 'lembaga', 'jurusan'])->findOrFail($id);

            return response()->json(['success' => true, 'data' => $data]);
        } catch (Throwable $e) {
            Log::error('TagihanKhusus Show Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan'], 404);
        }
    }

    /** UPDATE DATA */
    public function update(TagihanKhususRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $data = TagihanKhusus::findOrFail($id);
            $data->update($request->validated());

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Tagihan khusus berhasil diperbarui',
                'data' => $data
            ]);
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('TagihanKhusus Update Error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Gagal memperbarui data'], 500);
        }
    }

    /** HAPUS DATA */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $data = TagihanKhusus::findOrFail($id);
            $data->delete();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Tagihan khusus berhasil dihapus']);
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('TagihanKhusus Delete Error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Gagal menghapus data'], 500);
        }
    }
}
