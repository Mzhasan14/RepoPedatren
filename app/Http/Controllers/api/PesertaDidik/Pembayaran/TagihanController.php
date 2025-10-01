<?php

namespace App\Http\Controllers\api\PesertaDidik\Pembayaran;

use App\Models\Tagihan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\PesertaDidik\Pembayaran\TagihanRequest;

class TagihanController extends Controller
{
    public function index()
    {
        try {
            $data = Tagihan::with('potongans')->paginate(25);

            // sembunyikan pivot
            $data->getCollection()->transform(function ($item) {
                $item->potongans->makeHidden('pivot');
                return $item;
            });

            return response()->json([
                'success' => true,
                'data'    => $data
            ]);
        } catch (\Exception $e) {
            Log::error('Tagihan Index Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data'
            ], 500);
        }
    }

    public function store(TagihanRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = Tagihan::create([
                'kode_tagihan' => $request->kode_tagihan,
                'tipe'         => $request->tipe,
                'nama_tagihan' => $request->nama_tagihan,
                'nominal'      => $request->nominal,
                'jatuh_tempo'  => $request->jatuh_tempo,
                'status'       => $request->status ?? true,
                'created_by'   => Auth::id(),
            ]);

            DB::commit();
            return response()->json(['success' => true, 'data' => $data], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Tagihan Store Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['success' => false, 'message' => 'Gagal membuat Tagihan'], 500);
        }
    }

    public function show($id)
    {
        try {
            $data = Tagihan::with('potongans')->findOrFail($id);

            // sembunyikan pivot
            $data->potongans->makeHidden('pivot');

            return response()->json([
                'success' => true,
                'data'    => $data
            ]);
        } catch (\Exception $e) {
            Log::error('Tagihan Show Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }
    }

    public function update(TagihanRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $data = Tagihan::findOrFail($id);
            $data->update([
                'tipe'        => $request->tipe,
                'nama_tagihan' => $request->nama_tagihan,
                'nominal'     => $request->nominal,
                'jatuh_tempo' => $request->jatuh_tempo,
                'status'      => $request->status ?? $data->status,
                'updated_by'  => Auth::id(),
            ]);

            DB::commit();
            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Tagihan Update Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['success' => false, 'message' => 'Gagal memperbarui Tagihan'], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $data = Tagihan::findOrFail($id);
            $data->update(['deleted_by' => Auth::id()]);
            $data->delete();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Data berhasil dihapus']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Tagihan Delete Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['success' => false, 'message' => 'Gagal menghapus data'], 500);
        }
    }
}
