<?php

namespace App\Http\Controllers\Api\Pegawai;

use App\Http\Controllers\Controller;
use App\Http\Resources\PdResource;
use App\Models\Pegawai\WaliKelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WalikelasController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $walikelas = WaliKelas::all();
        return new PdResource(true,'Data berhasil ditampilkan',$walikelas);
    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'id_pengajar'  => 'required|integer',
            'id_rombel'    => 'required|integer',
            'jumlah_murid' => 'required|string|min:1',
            'created_by'   => 'required|integer',
            'status'       => 'required|boolean',
        ]);

        if ($validator->fails()){
            return response()->json([
                'status' => false,
                'message' => 'Data Gagal ditambahkan',
                'data'=> $validator->errors()
            ]);
        }
        $walikelas = WaliKelas::create($validator->validated());
        return new PdResource(true,'Data berhasil ditambahkan',$walikelas);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $walikelas = WaliKelas::findOrFail($id);
        return new PdResource(true,'Data berhasil ditampilkan',$walikelas);
    }
    public function update(Request $request, string $id)
    {
        $walikelas = WaliKelas::findOrFail($id);
        $validator = Validator::make($request->all(),[
            'id_pengajar'  => 'required|integer',
            'id_rombel'    => 'required|integer',
            'jumlah_murid' => 'required|string|min:1',
            'updated_by'   => 'nullable|integer',
            'status'       => 'required|boolean',
        ]);

        if ($validator->fails()){
            return response()->json([
                'status' => false,
                'message' => 'Data Gagal ditambahkan',
                'data'=> $validator->errors()
            ]);
        }
        $walikelas->update($validator->validated());
        return new PdResource(true,'Data berhasil diupdate',$walikelas);
    }


    public function destroy(string $id)
    {
        $walikelas = WaliKelas::findOrFail($id);
        $walikelas->delete();
        return new PdResource(true,'Data berhasil dihapus',$walikelas);
    }
}
