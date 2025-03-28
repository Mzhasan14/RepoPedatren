<?php

namespace App\Http\Controllers\Api;

use App\Models\JenisBerkas;
use Illuminate\Http\Request;
use App\Http\Resources\PdResource;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class JenisBerkasController extends Controller
{
    public function index()
    {
        $jenisberkas = JenisBerkas::all();
        return new PdResource(true,'Data berhasil ditampilkan',$jenisberkas);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'nama_jenis_berkas' => 'required|string|max:255',
            'created_by' => 'required|integer',
            'status' => 'required|boolean',
        ]);

        if ($validator->fails()){
            return response()->json([
                'status' => false,
                'message' => 'Data gagal ditammbahkan',
                'data' => $validator->errors()
            ]);
        }

        $jenisberkas = JenisBerkas::create($validator->validated());
        return new PdResource(true,'Data berhasil ditambahkan',$jenisberkas);
    }

    public function show(string $id)
    {
        $jenisberkas = JenisBerkas::findOrFail($id);
        return new PdResource(true,'Data berhasil ditampilkan',$jenisberkas);
    }

    public function update(Request $request, string $id)
    {
        $jenisberkas = JenisBerkas::findOrFail($id);
        $validator = Validator::make($request->all(),[
            'nama_jenis_berkas' => 'required|string|max:255',
            'updated_by' => 'nullable|integer',
            'status' => 'required|boolean',
        ]);

        if ($validator->fails()){
            return response()->json([
                'status' => false,
                'message' => 'Data gagal ditammbahkan',
                'data' => $validator->errors()
            ]);
        }

        $jenisberkas->update($validator->validated());
        return new PdResource(true,'Data berhasil diperbarui',$jenisberkas);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {   
        $jenisberkas = JenisBerkas::findOrFail($id);
        $jenisberkas->delete();
        return new PdResource(true,'Data berhasil dihapus',$jenisberkas);
    }
}
