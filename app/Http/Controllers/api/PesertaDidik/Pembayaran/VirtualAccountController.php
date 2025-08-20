<?php

namespace App\Http\Controllers\api\PesertaDidik\Pembayaran;

use Illuminate\Http\Request;
use App\Models\VirtualAccount;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\PesertaDidik\Pembayaran\VirtualAccountRequest;

class VirtualAccountController extends Controller
{
    public function index()
    {
        try {
            $data = DB::table('virtual_accounts as va')
                ->join('banks as ba', 'ba.id', 'va.bank_id')
                ->join('santri as s', 's.id', '=', 'va.santri_id')
                ->join('biodata as b', 'b.id', '=', 's.biodata_id')
                ->select(
                    'va.id',
                    'ba.nama_bank',
                    'va.va_number',
                    'b.nama',
                    's.nis',
                    'va.status',
                    'va.created_at'
                )
                ->paginate(25);
            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            Log::error('VA Index Error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Gagal mengambil data'], 500);
        }
    }

    public function store(VirtualAccountRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = VirtualAccount::create([
                'santri_id'  => $request->santri_id,
                'bank_id'  => $request->bank_id,
                'va_number'  => $request->va_number,
                'status'     => $request->status ?? true,
                'created_by' => Auth::id(),
            ]);

            DB::commit();
            return response()->json(['success' => true, 'data' => $data], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('VA Store Error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Gagal membuat Virtual Account'], 500);
        }
    }

    public function show($id)
    {
        try {
            $data = DB::table('virtual_accounts as va')
                ->join('banks as ba', 'ba.id', 'va.bank_id')
                ->join('santri as s', 's.id', '=', 'va.santri_id')
                ->join('biodata as b', 'b.id', '=', 's.biodata_id')
                ->select(
                    'va.id',
                    'ba.nama_bank',
                    'ba.kode_bank',
                    'va.va_number',
                    'b.nama',
                    's.nis',
                    'va.status',
                    'va.created_at'
                )->findOrFail($id);
            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            Log::error('VA Show Error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan'], 404);
        }
    }

    public function update(VirtualAccountRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $data = VirtualAccount::findOrFail($id);
            $data->update([
                'santri_id'  => $request->santri_id,
                'bank_id'  => $request->bank_id,
                'va_number'  => $request->va_number,
                'status'     => $request->status ?? $data->status,
                'updated_by' => Auth::id(),
            ]);

            DB::commit();
            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('VA Update Error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Gagal memperbarui Virtual Account'], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $data = VirtualAccount::findOrFail($id);
            $data->update(['deleted_by' => Auth::id()]);
            $data->delete();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Data berhasil dihapus']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('VA Delete Error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Gagal menghapus data'], 500);
        }
    }
}
