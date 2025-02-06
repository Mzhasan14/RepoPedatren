<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PdResource;
use App\Models\Alamat\Desa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DesaController extends Controller
{

    public function index()
    {
        $desa = Desa::Active()->get();
        return new PdResource(true,'Data berhasil di tampilkan',$desa);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'nama_desa' => 'required|string|max:100',
            'id_kecamatan' => 'required|integer',
            'status' => 'required|boolean',
            'created_by' => 'nullable|integer',
            'updated_by' => 'nullable|integer',
            'deleted_by' => 'nullable|integer',
        ]);

        if ($validator->fails()){
            return response()->json([
                'status' => false,
                'message' => 'Data Gagal ditambahkan',
                'data' => $validator->errors()
            ]);
        }
        $desa = Desa::create($validator->validated());
        return new PdResource(true,'Data Berhasil ditambahkan',$desa);
    }

    public function show(string $id)
    {
        $desa = Desa::findOrFail($id);
        return new PdResource(true,'Data berhasil di tampilkan',$desa);
    }

    public function update(Request $request, string $id)
    {
        $desa = Desa::findOrFail($id);
        $validator = Validator::make($request->all(),[
            'nama_desa' => 'required|string|max:100',
            'id_kecamatan' => 'required|integer',
            'status' => 'required|boolean',
            'created_by' => 'required|integer',
            'updated_by' => 'required|integer',
        ]);

        if ($validator->fails()){
            return response()->json([
                'status' => false,
                'message' => 'Data Gagal ditambahkan',
                'data' => $validator->errors()
            ]);
        }
        $desa->update($validator->validated());
        return new PdResource(true,'Data berhasil diupdate',$desa);
    }

    public function destroy(string $id)
    {
        $desa = Desa::findOrFail($id);
        $desa->delete();
        return new PdResource(true,'Data berhasil dihapus',$desa);
    }
}
