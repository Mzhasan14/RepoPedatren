<?php

namespace App\Http\Controllers\api\PesertaDidik\Pembayaran;

use App\Models\Bank;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\PesertaDidik\Pembayaran\BankRequest;

class BankController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Bank::query();

            if ($request->filled('q')) {
                $q = $request->q;
                $query->where(function ($w) use ($q) {
                    $w->where('kode_bank', 'like', "%{$q}%")
                        ->orWhere('nama_bank', 'like', "%{$q}%");
                });
            }

            if ($request->filled('status')) {
                $status = filter_var($request->status, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                if (!is_null($status)) {
                    $query->where('status', $status);
                }
            }

            $data = $query->orderBy('kode_bank')->paginate(25);

            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Throwable $e) {
            Log::error('Banks Index Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['success' => false, 'message' => 'Gagal mengambil data'], 500);
        }
    }

    public function store(BankRequest $request)
    {
        DB::beginTransaction();
        try {
            $bank = Bank::create([
                'kode_bank' => $request->kode_bank,
                'nama_bank' => $request->nama_bank,
                'status'    => $request->boolean('status', true),
                'created_by' => Auth::id(),
            ]);

            DB::commit();
            return response()->json(['success' => true, 'data' => $bank], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Banks Store Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'payload' => $request->only(['kode_bank', 'nama_bank', 'status']),
            ]);
            return response()->json(['success' => false, 'message' => 'Gagal membuat bank'], 500);
        }
    }

    public function show($id)
    {
        try {
            $bank = Bank::findOrFail($id);
            return response()->json(['success' => true, 'data' => $bank]);
        } catch (\Throwable $e) {
            Log::error('Banks Show Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'id' => $id,
            ]);
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan'], 404);
        }
    }

    public function update(BankRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $bank = Bank::findOrFail($id);

            $bank->update([
                'kode_bank' => $request->kode_bank,
                'nama_bank' => $request->nama_bank,
                'status'    => $request->has('status') ? $request->boolean('status') : $bank->status,
                'updated_by' => Auth::id(),
            ]);

            DB::commit();
            return response()->json(['success' => true, 'data' => $bank]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Banks Update Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'id' => $id,
                'payload' => $request->only(['kode_bank', 'nama_bank', 'status']),
            ]);
            return response()->json(['success' => false, 'message' => 'Gagal memperbarui bank'], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $bank = Bank::findOrFail($id);
            $bank->update(['deleted_by' => Auth::id()]);
            $bank->delete();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Data berhasil dihapus']);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Banks Delete Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'id' => $id,
            ]);
            return response()->json(['success' => false, 'message' => 'Gagal menghapus data'], 500);
        }
    }
}
