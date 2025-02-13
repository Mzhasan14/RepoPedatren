<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PdResource;
use App\Models\Pegawai\Karyawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class KaryawanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $Karyawan = Karyawan::all();
        return new PdResource(true,'Data berhasil ditampilkan',$Karyawan);
    }


    public function store(Request $request)
    {
        $validator =Validator::make($request->all(),[
            'id_pegawai' => 'required', 'exists:pegawai,id', 'unique:karyawan,id_pegawai',
            'id_golongan' => 'required', 'exists:golongan,id',
            'keterangan' => 'required', 'string',
            'created_by' => 'required', 'integer',
            'status' => 'required', 'boolean',
        ]);
        if ($validator->fails())
        {
            return response()->json([
                'status' => false,
                'message' => 'Data gagal ditambahkan',
                'data' => $validator->errors()
            ]);
        }

        $Karyawan = Karyawan::create($validator->validated());
        return new PdResource(true,'Data berhasil diitambahkan',$Karyawan);
    }

    public function show(string $id)
    {
        $Karyawan = Karyawan::findOrFail($id);
        return new PdResource(true,'Data berhasil ditampilkan',$Karyawan);
    }
    public function update(Request $request, string $id)
    {
        $Karyawan = Karyawan::findOrFail($id);
        $validator =Validator::make($request->all(),[
            'id_pegawai' => 'required', 'exists:pegawai,id', 'unique:karyawan,id_pegawai',
            'id_golongan' => 'required', 'exists:golongan,id',
            'keterangan' => 'required', 'string',
            'created_by' => 'required', 'integer',
            'status' => 'required', 'boolean',
        ]);
        if ($validator->fails())
        {
            return response()->json([
                'status' => false,
                'message' => 'Data gagal ditambahkan',
                'data' => $validator->errors()
            ]);
        }
        $Karyawan->update($validator->validated());
        return new PdResource(true,'Data berhasil ditampilkan',$Karyawan);

    }
    public function destroy(string $id)
    {
        $Karyawan = Karyawan::findOrFail($id);
        $Karyawan->delete();
        return new PdResource(true,'Data berhasil ditampilkan',$Karyawan);
    }
}
