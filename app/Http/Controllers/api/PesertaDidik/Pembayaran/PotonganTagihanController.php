<?php

namespace App\Http\Controllers\api\PesertaDidik\Pembayaran;

use Illuminate\Http\Request;
use App\Models\PotonganTagihan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Requests\PesertaDidik\Pembayaran\PotonganTagihanRequest;

class PotonganTagihanController extends Controller
{
    public function index()
    {
        try {
            $data = PotonganTagihan::with(['potongan', 'tagihan'])->latest()->paginate(10);
            return response()->json($data);
        } catch (\Throwable $e) {
            Log::error("Gagal mengambil data potongan_tagihan: " . $e->getMessage());
            return response()->json(['message' => 'Gagal mengambil data'], 500);
        }
    }

    public function store(PotonganTagihanRequest $request)
    {
        DB::beginTransaction();
        try {
            $exists = PotonganTagihan::where('potongan_id', $request->potongan_id)
                ->where('tagihan_id', $request->tagihan_id)
                ->exists();

            if ($exists) {
                return response()->json([
                    'message' => 'Potongan sudah terhubung dengan tagihan ini'
                ], 422);
            }

            $data = PotonganTagihan::create($request->validated());
            DB::commit();

            return response()->json([
                'message' => 'Potongan berhasil ditautkan ke tagihan',
                'data'    => $data
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Gagal menambahkan potongan_tagihan: " . $e->getMessage());
            return response()->json(['message' => 'Gagal menambahkan potongan_tagihan'], 500);
        }
    }

    public function update(PotonganTagihanRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $data = PotonganTagihan::findOrFail($id);

            // Cek duplikasi sebelum update
            $exists = PotonganTagihan::where('potongan_id', $request->potongan_id)
                ->where('tagihan_id', $request->tagihan_id)
                ->where('id', '!=', $id)
                ->exists();

            if ($exists) {
                return response()->json([
                    'message' => 'Potongan sudah terhubung dengan tagihan ini'
                ], 422);
            }

            $data->update($request->validated());
            DB::commit();

            return response()->json([
                'message' => 'Potongan_tagihan berhasil diperbarui',
                'data'    => $data
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Gagal update potongan_tagihan ID {$id}: " . $e->getMessage());
            return response()->json(['message' => 'Gagal update potongan_tagihan'], 500);
        }
    }


    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $data = PotonganTagihan::findOrFail($id);
            $data->delete();
            DB::commit();

            return response()->json(['message' => 'Potongan_tagihan berhasil dihapus']);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Gagal hapus potongan_tagihan ID {$id}: " . $e->getMessage());
            return response()->json(['message' => 'Gagal hapus potongan_tagihan'], 500);
        }
    }
}
