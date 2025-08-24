<?php

namespace App\Http\Controllers\api\PesertaDidik\Transaksi;

use Exception;
use App\Models\Kategori;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\PesertaDidik\Transaksi\StoreKategoriRequest;
use App\Http\Requests\PesertaDidik\Transaksi\UpdateKategoriRequest;
use App\Models\DetailUserOutlet;

class KategoriController extends Controller
{
    public function index()
    {
        $data = Kategori::paginate(25);
        return response()->json([
            'status' => true,
            'message' => 'Daftar kategori berhasil diambil',
            'data' => $data
        ]);
    }

    public function store(StoreKategoriRequest $request)
    {
        DB::beginTransaction();
        try {
            $kategori = Kategori::create($request->validated());
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Kategori berhasil ditambahkan',
                'data' => $kategori
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Store Kategori error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Gagal menambahkan kategori'
            ], 500);
        }
    }

    public function show(Kategori $kategori)
    {
        return response()->json([
            'status' => true,
            'message' => 'Detail kategori',
            'data' => $kategori
        ]);
    }

    public function update(UpdateKategoriRequest $request, Kategori $kategori)
    {
        DB::beginTransaction();
        try {
            $kategori->update($request->validated());
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Kategori berhasil diperbarui',
                'data' => $kategori
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Update Kategori error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Gagal memperbarui kategori'
            ], 500);
        }
    }

    public function destroy(Kategori $kategori)
    {
        DB::beginTransaction();
        try {
            $kategori->delete();
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Kategori berhasil dihapus'
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Delete Kategori error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Gagal menghapus kategori'
            ], 500);
        }
    }

    public function kategoriById()
    {
        $user = Auth::user();

        if (! $user->hasRole('superadmin')) {
            $kategori = DB::table('detail_user_outlet')
                ->join('outlets', 'detail_user_outlet.outlet_id', '=', 'outlets.id')
                ->join('outlet_kategori', 'outlet_kategori.outlet_id', '=', 'outlets.id')
                ->join('kategori', 'outlet_kategori.kategori_id', '=', 'kategori.id')
                ->where('detail_user_outlet.user_id', $user->id)
                ->select('kategori.id as kategori_id', 'kategori.nama_kategori', 'outlets.nama_outlet')
                ->get();
        } else {
            $kategori = DB::table('detail_user_outlet')
                ->join('outlets', 'detail_user_outlet.outlet_id', '=', 'outlets.id')
                ->join('outlet_kategori', 'outlet_kategori.outlet_id', '=', 'outlets.id')
                ->join('kategori', 'outlet_kategori.kategori_id', '=', 'kategori.id')
                ->select('kategori.id as kategori_id', 'kategori.nama_kategori', 'outlets.nama_outlet')
                ->get();
        }



        return response()->json([
            'success' => true,
            'message' => 'Daftar kategori berhasil diambil',
            'data' => $kategori
        ]);
    }
}
