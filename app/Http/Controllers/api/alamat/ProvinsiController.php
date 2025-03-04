<?php

namespace App\Http\Controllers\api\alamat;

use App\Http\Controllers\Controller;
use App\Http\Resources\PdResource;
use App\Models\Alamat\Provinsi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProvinsiController extends Controller
{
    public function index() 
    {
        $provinsi = Provinsi::Active()->get();
        return new PdResource(true,'Data Berhasil Ditampilkan', $provinsi);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'nama_provinsi' => 'required|string|max:255',
            'created_by' => 'nullable|integer', 
            'status' => 'required|boolean',
            'created_at' => 'nullable|date_format:Y-m-d',
            'updated_at' => 'nullable|date_format:Y-m-d',
        ]);
        if ($validator->fails()){
            return response()->json([
                'status' => false,
                'message' => 'Data gagal di tambahkan',
                'data' => $validator->errors()
            ],402);
        }
        $provinsi = Provinsi::create($validator->validated());
        return new PdResource(true,'Data berhasil ditambahkan',$provinsi);
    }
    public function show(string $id)
    {
        $provinsi = Provinsi::findOrFail($id);
        return new PdResource(true,'Data berhasil ditampilkan',$provinsi);
    }
    public function update(Request $request, string $id)
    {
        $provinsi = Provinsi::findOrFail($id);
        $validator = Validator::make($request->all(),[
            'nama_provinsi' => 'required|string|max:255',
            'updated_by' => 'nullable|integer', 
            'status' => 'required|boolean',
            'updated_at' => 'nullable|date_format:Y-m-d',
        ]);
        if ($validator->fails()){
            return response()->json([
                'status' => false,
                'message' => 'Data gagal di update',
                'data' => $validator->errors()
            ],402);
        }
        $provinsi->update($validator->validated());
        return new PdResource(true,'Data berhasil di update',$provinsi);
    }

    public function destroy(string $id)
    {
        $provinsi = Provinsi::findOrFail($id);
        $provinsi->delete();
        return new PdResource(true,'Data berhasil dihapus',$provinsi);
    }
}