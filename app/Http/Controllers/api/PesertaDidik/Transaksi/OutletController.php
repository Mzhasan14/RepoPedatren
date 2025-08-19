<?php

namespace App\Http\Controllers\api\PesertaDidik\Transaksi;

use Exception;
use App\Models\Outlet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\PesertaDidik\Transaksi\StoreOutletRequest;
use App\Http\Requests\PesertaDidik\Transaksi\UpdateOutletRequest;

class OutletController extends Controller
{
    public function index()
    {
        $data = Outlet::with('kategori')->get();

        return response()->json([
            'status'  => true,
            'message' => 'Daftar outlet berhasil diambil',
            'data'    => $data
        ]);
    }

    public function store(StoreOutletRequest $request)
    {
        DB::beginTransaction();
        try {
            $outlet = Outlet::create([
                'nama_outlet' => $request->nama_outlet,
                'status'      => $request->status ?? true,
                'created_by'  => Auth::id()
            ]);

            $outlet->kategori()->sync($request->kategori_ids);

            DB::commit();
            return response()->json([
                'status'  => true,
                'message' => 'Outlet berhasil ditambahkan',
                'data'    => $outlet->load('kategori')
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Store Outlet error: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Gagal menambahkan outlet'], 500);
        }
    }

    public function show(Outlet $outlet)
    {
        return response()->json([
            'status'  => true,
            'message' => 'Detail outlet',
            'data'    => $outlet->load('kategori')
        ]);
    }

    public function update(UpdateOutletRequest $request, Outlet $outlet)
    {
        DB::beginTransaction();
        try {
            $outlet->update([
                'nama_outlet' => $request->nama_outlet,
                'status'      => $request->status ?? $outlet->status,
                'updated_by'  => Auth::id()
            ]);

            $outlet->kategori()->sync($request->kategori_ids);

            DB::commit();
            return response()->json([
                'status'  => true,
                'message' => 'Outlet berhasil diperbarui',
                'data'    => $outlet->load('kategori')
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Update Outlet error: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Gagal memperbarui outlet'], 500);
        }
    }

    public function destroy(Outlet $outlet)
    {
        DB::beginTransaction();
        try {
            $outlet->update(['deleted_by' => Auth::id()]);
            $outlet->kategori()->detach();
            $outlet->delete();

            DB::commit();
            return response()->json([
                'status'  => true,
                'message' => 'Outlet berhasil dihapus'
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Delete Outlet error: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Gagal menghapus outlet'], 500);
        }
    }
}
