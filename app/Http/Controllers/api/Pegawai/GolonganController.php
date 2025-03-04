<?php

namespace App\Http\Controllers\Api\Pegawai;

use App\Http\Controllers\Controller;
use App\Http\Resources\PdResource;
use App\Models\Pegawai\Golongan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GolonganController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $golonngan = Golongan::all();
        return new PdResource(true,'Data berhasil ditampilkan',$golonngan);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'nama_golongan' => 'required|string|max:255|unique:golongan,nama_golongan',
            'id_kategori_golongan' => 'required|integer|exists:kategori_golongan,id',
            'created_by' => 'required|integer',
            'status' => 'required|boolean',
        ]);
        if ($validator->fails()){
            return response()->json([
                'status' => false,
                'message' => 'Data gagal ditambahkan',
                'data' => $validator->errors()
            ]);
        }

        $golonngan =  Golongan::create($validator->validated());
        return new PdResource(true,'Data berhasil Ditambahkan', $golonngan);
    }

    public function show(string $id)
    {
        $golonngan = Golongan::findOrFail($id);
        return new PdResource(true,'Data berhasil ditampilkan',$golonngan);
    }

    public function update(Request $request, string $id)
    {
        $golonngan = Golongan::findOrFail($id);
        $validator = Validator::make($request->all(),[
            'nama_golongan' => 'required|string|max:255|unique:golongan,nama_golongan',
            'id_kategori_golongan' => 'required|integer|exists:kategori_golongan,id',
            'updated_by' => 'nullable |integer',
            'status' => 'required|boolean',
        ]);
        if ($validator->fails()){
            return response()->json([
                'status' => false,
                'message' => 'Data gagal ditambahkan',
                'data' => $validator->errors()
            ]);
        }
        $golonngan->update($validator->validated());
        return new PdResource(true,'Data berhasil diupdated',$golonngan);
    }


    public function destroy(string $id)
    {
        $golonngan = Golongan::findOrFail($id);
        $golonngan->delete();
        return new PdResource(true,'Data berhasil dihapus',$golonngan);
    }
}
