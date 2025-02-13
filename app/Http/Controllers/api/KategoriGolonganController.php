<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PdResource;
use App\Models\Pegawai\KategoriGolongan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class KategoriGolonganController extends Controller
{

    public function index()
    {
        $kategori = KategoriGolongan::all();
        return new PdResource(true,'Data berhasil ditampilkan',$kategori);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'nama_kategori_golongan' => 'required|string|max:255|unique:kategori_golongan,nama_kategori_golongan',
            'created_by' => 'required|integer',
            'status' => 'required|boolean',
        ]);

        if ($validator->fails()){
            return response()->json([
                'status' => false,
                'message' => 'Data gagal di tambahkan',
                'data' => $validator->errors()
            ]);
        }

        $kategori = KategoriGolongan::create($validator->validated());
        return new PdResource(true,'Data berehasil ditambahkan',$kategori);
    }

    public function show(string $id)
    {
        $kategori = KategoriGolongan::findOrFail($id);
        return new PdResource(true,'Data berhasil Ditampilkan',$kategori);
    }

    public function update(Request $request, string $id)
    {
        $kategori = KategoriGolongan::findOrFail($id);
        $validator = Validator::make($request->all(),[
            'nama_kategori_golongan' => 'required|string|max:255|unique:kategori_golongan,nama_kategori_golongan',
            'created_by' => 'required|integer',
            'status' => 'required|boolean',
        ]);

        if ($validator->fails()){
            return response()->json([
                'status' => false,
                'message' => 'Data gagal di tambahkan',
                'data' => $validator->errors()
            ]);
        }
        $kategori->update($validator->validated());
        return new PdResource(true,'Data berhasil di update',$kategori);
    }

    public function destroy(string $id)
    {
        $kategori = KategoriGolongan::findOrFail($id);
        $kategori->delete();
        return new PdResource(true,'Data berhasil dihapus',$kategori);
    }
}
