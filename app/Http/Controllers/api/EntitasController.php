<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PdResource;
use App\Models\Pegawai\EntitasPegawai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EntitasController extends Controller
{
    public function index()
    {
        $entitas = EntitasPegawai::all();
        return new PdResource(true,'Data berhasil ditampilkan',$entitas);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'id_pegawai' => 'required|integer|exists:pegawai,id',
            'id_golongan' => 'required|integer|exists:golongan,id',
            'tanggal_masuk' => 'required|date|before_or_equal:today',
            'tanggal_keluar' => 'nullable|date|after:tanggal_masuk',
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

        $entitas = EntitasPegawai::create($validator->validated());
        return new PdResource(true,'Data berhasil ditambahkan',$entitas);
    }
    public function show(string $id)
    {
        $entitas = EntitasPegawai::findOrFail($id);
        return new PdResource(true,'Data berhasil ditampilkan',$entitas);
    }
    public function update(Request $request, string $id)
    {
        $entitas = EntitasPegawai::findOrFail($id);
        $validator = Validator::make($request->all(),[
            'id_pegawai' => 'required|integer|exists:pegawai,id',
            'id_golongan' => 'required|integer|exists:golongan,id',
            'tanggal_masuk' => 'required|date|before_or_equal:today',
            'tanggal_keluar' => 'nullable|date|after:tanggal_masuk',
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
        $entitas->update($validator->validated());
        return new PdResource(true,'Data berhasil diupdate',$entitas);
    }


    public function destroy(string $id)
    {
        $entitas = EntitasPegawai::findOrFail($id);
        $entitas->delete();
        return new PdResource(true,'Data berhasil dihapus',$entitas);
    }
}
