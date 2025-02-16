<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PdResource;
use App\Models\Alamat\Kecamatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class KecamatanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $kecamatan = Kecamatan::Active()->get();
        return new PdResource(true,'Data berhasil ditampilkan',$kecamatan);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'nama_kecamatan' => 'required|string|max:255', 
            'id_kabupaten' => 'required|integer|exists:kabupaten,id',
            'updated_by' => 'nullable|integer', 
            'created_by' => 'required|integer', 
            'status' => 'required|boolean'
        ]);
        if ($validator->fails())
        {
            return response()->json([
                'status' => false,
                'message' => 'data gagal dibuat',
                'data' => $validator->errors()
            ]);
        }
        $kecamatan = Kecamatan::create($validator->validated());
        return new PdResource(true,'data berhasil dibuat',$kecamatan);
    }

    public function show(string $id)
    {
        $kecamatan = Kecamatan::findOrFail($id);
        return new PdResource(true,'Data berhasil di tampilkan',$kecamatan);
    }
    public function update(Request $request, string $id)
    {
        $kecamatan = Kecamatan::findOrFail($id);
        $validator = Validator::make($request->all(),[
            'nama_kecamatan' => 'required|string|max:255', 
            'id_kabupaten' => 'required|integer|exists:kabupaten,id',
            'updated_by' => 'nullable|integer',
            'created_by' => 'nullable|integer', 
            'status' => 'required|boolean'
        ]);
        if ($validator->fails())
        {
            return response()->json([
                'status' => false,
                'message' => 'data gagal dibuat',
                'data' => $validator->errors()
            ]);
        }
        $kecamatan->update($validator->validated());
        return new PdResource(true,'Data berhasil diupdate',$kecamatan);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $kecamatan = Kecamatan::findOrFail($id);
        $kecamatan->delete();
        return new PdResource(true,'Data berhasil dihapus',$kecamatan);
    }
}
