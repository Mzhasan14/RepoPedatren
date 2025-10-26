<?php

namespace App\Http\Controllers\api\PesertaDidik\Transaksi;

use Exception;
use Illuminate\Http\Request;
use App\Models\DetailUserOutlet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Requests\PesertaDidik\Transaksi\StoreDetailUserOutletRequest;
use App\Http\Requests\PesertaDidik\Transaksi\UpdateDetailUserOutletRequest;

class DetailUserOutletController extends Controller
{
    public function index()
    {
        $data = DetailUserOutlet::with(['user', 'outlet'])->paginate(25);
        return response()->json([
            'status' => true,
            'message' => 'Daftar detail user outlet berhasil diambil',
            'data' => $data
        ]);
    }

    public function store(StoreDetailUserOutletRequest $request)
    {
        DB::beginTransaction();
        try {
            $detail = DetailUserOutlet::create($request->validated());
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Detail user outlet berhasil ditambahkan',
                'data' => $detail
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Store DetailUserOutlet error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Gagal menambahkan detail user outlet'
            ], 500);
        }
    }

    public function show(DetailUserOutlet $detailUserOutlet)
    {
        $detailUserOutlet->load(['user', 'outlet']);
        return response()->json([
            'status' => true,
            'message' => 'Detail user outlet',
            'data' => $detailUserOutlet
        ]);
    }

    public function update(UpdateDetailUserOutletRequest $request, DetailUserOutlet $detailUserOutlet)
    {
        DB::beginTransaction();
        try {
            $detailUserOutlet->update($request->validated());
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Detail user outlet berhasil diperbarui',
                'data' => $detailUserOutlet
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Update DetailUserOutlet error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Gagal memperbarui detail user outlet'
            ], 500);
        }
    }

    public function destroy(DetailUserOutlet $detailUserOutlet)
    {
        DB::beginTransaction();
        try {
            $detailUserOutlet->delete();
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Detail user outlet berhasil dihapus'
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Delete DetailUserOutlet error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Gagal menghapus detail user outlet'
            ], 500);
        }
    }
}
