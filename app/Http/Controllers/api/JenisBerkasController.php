<?php

namespace App\Http\Controllers\api;

use App\Models\JenisBerkas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\JenisBerkasRequest;

class JenisBerkasController extends Controller
{
    public function index()
    {
        try {
            $data = JenisBerkas::all();
            return response()->json($data);
        } catch (\Exception $e) {
            Log::error('JenisBerkas Index Error: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal mengambil data'], 500);
        }
    }

    public function store(JenisBerkasRequest $request)
    {
        try {
            DB::beginTransaction();

            $jenisBerkas = JenisBerkas::create([
                'nama_jenis_berkas' => $request->nama_jenis_berkas,
                'status' => $request->status,
                'created_by' => Auth::id(),
            ]);

            DB::commit();
            return response()->json($jenisBerkas, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('JenisBerkas Store Error: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal menyimpan data'], 500);
        }
    }

    public function show($id)
    {
        try {
            $data = JenisBerkas::findOrFail($id);
            return response()->json($data);
        } catch (\Exception $e) {
            Log::error('JenisBerkas Show Error: ' . $e->getMessage());
            return response()->json(['error' => 'Data tidak ditemukan'], 404);
        }
    }

    public function update(JenisBerkasRequest $request, $id)
    {
        try {
            DB::beginTransaction();

            $jenisBerkas = JenisBerkas::findOrFail($id);
            $jenisBerkas->update([
                'nama_jenis_berkas' => $request->nama_jenis_berkas,
                'status' => $request->status,
                'updated_by' => Auth::id(),
            ]);

            DB::commit();
            return response()->json($jenisBerkas);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('JenisBerkas Update Error: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal mengubah data'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $jenisBerkas = JenisBerkas::findOrFail($id);
            $jenisBerkas->update(['deleted_by' => Auth::id()]);
            $jenisBerkas->delete();

            DB::commit();
            return response()->json(['message' => 'Data berhasil dihapus']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('JenisBerkas Delete Error: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal menghapus data'], 500);
        }
    }
}
