<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PdResource;
use App\Models\Khadam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class KhadamController extends Controller
{
    public function index()
    {
        $khadam = Khadam::all();
        return new PdResource(true,'Data berhasil ditampilkan',$khadam);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'id_peserta_didik' => ['required', 'integer', Rule::unique('khadam', 'id_peserta_didik')],
            'keterangan' => 'required|string|max:255',
            'status' => 'required|boolean',
            'created_by' => 'required|integer',
        ]);

        if ($validator->fails()){
            return response()->json([
                'status' => false,
                'message' => 'Data gagal ditambahkan',
                'data' => $validator->errors()
            ]);
        }

        $khadam =Khadam::create($validator->validated());
        return new PdResource(true,'Data berhasil ditambahkan',$khadam);
    }
    public function show(string $id)
    {
        $khadam = Khadam::findOrFail($id);
        return new PdResource(true,'Data berhasil di tampilkan', $khadam);
    }
    public function update(Request $request, string $id)
    {
        $khadam = Khadam::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'id_peserta_didik' => [
                'required', 
                'integer', 
                Rule::unique('khadam', 'id_peserta_didik')->ignore($khadam->id) 
            ],
            'keterangan' => 'required|string|max:255',
            'status' => 'required|boolean',
            'updated_by' => 'nullable|integer',
        ]);
        

        if ($validator->fails()){
            return response()->json([
                'status' => false,
                'message' => 'Data gagal di update',
                'data' => $validator->errors()
            ]);
        }

        $khadam->update($validator->validated());
        return new PdResource(true,'Data berhasil diupdate', $khadam);
    }

    public function destroy(string $id)
    {
        $khadam = Khadam::findOrFail($id);
        $khadam->delete();
        return new PdResource(true,'Data berhasil dihapus', $khadam);
    }
}
