<?php

namespace App\Http\Controllers\api\PesertaDidik\Transaksi;

use Exception;
use App\Models\Outlet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Requests\PesertaDidik\Transaksi\StoreOutletRequest;
use App\Http\Requests\PesertaDidik\Transaksi\UpdateOutletRequest;

class OutletController extends Controller
{
    public function index()
    {
        $data = Outlet::paginate(25);

        return response()->json([
            'status' => true,
            'message' => 'Daftar outlet berhasil diambil',
            'data' => $data
        ]);
    }

    public function store(StoreOutletRequest $request)
    {
        DB::beginTransaction();
        try {
            $outlet = Outlet::create($request->validated());
            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Outlet berhasil ditambahkan',
                'data' => $outlet
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Store Outlet error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Gagal menambahkan outlet'
            ], 500);
        }
    }

    public function show(Outlet $outlet)
    {
        return response()->json([
            'status' => true,
            'message' => 'Detail outlet',
            'data' => $outlet
        ]);
    }

    public function update(UpdateOutletRequest $request, Outlet $outlet)
    {
        DB::beginTransaction();
        try {
            $outlet->update($request->validated());
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Outlet berhasil diperbarui',
                'data' => $outlet
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Update Outlet error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Gagal memperbarui outlet'
            ], 500);
        }
    }

    public function destroy(Outlet $outlet)
    {
        DB::beginTransaction();
        try {
            $outlet->delete();
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Outlet berhasil dihapus'
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Delete Outlet error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Gagal menghapus outlet'
            ], 500);
        }
    }
}

