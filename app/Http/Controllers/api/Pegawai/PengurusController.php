<?php

namespace App\Http\Controllers\Api\Pegawai;

use App\Http\Controllers\Controller;
use App\Http\Resources\PdResource;
use App\Models\Pegawai\Pengurus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PengurusController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $pengurus = Pengurus::all();
        return new PdResource(true,'Data berhasil ditampilkan',$pengurus);
    }
    public function store(Request $request)
    {
        $validator =Validator::make($request->all(),[
            'id_pegawai' => ['required', 'exists:pegawai,id'],
            'id_golongan' => ['required', 'exists:golongan,id'],
            'satuan_kerja' => ['required', 'string', 'max:255'],
            'jabatan' => ['required', 'string', 'max:255'],
            'created_by' => ['required', 'integer'],
            'status' => ['required', 'boolean'],
        ]);
        if ($validator->fails())
        {
            return response()->json([
                'status' => false,
                'message' => 'Data gagal ditambahkan',
                'data' => $validator->errors()
            ]);
        }

        $pengurus = Pengurus::create($validator->validated());
        return new PdResource(true,'Data berhasil diitambahkan',$pengurus);
    }


    public function show(string $id)
    {
        $pengurus = Pengurus::findOrFail($id);
        return new PdResource(true,'Data berhasil ditampilkan',$pengurus);
    }

    public function update(Request $request, string $id)
    {
        $pengurus = Pengurus::findOrFail($id);
        $validator =Validator::make($request->all(),[
            'id_pegawai' =>'required', 'exists:pegawai,id',
            'id_golongan' => 'required', 'exists:golongan,id',
            'satuan_kerja' => 'required', 'string', 'max:255',
            'jabatan' => 'required', 'string', 'max:255',
            'updated_by' => 'nullable', 'integer',
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
        $pengurus->update($validator->validated());
        return new PdResource(true,'Data berhasil ditampilkan',$pengurus);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $pengurus = Pengurus::findOrFail($id);
        $pengurus->delete();
        return new PdResource(true,'Data berhasil ditampilkan',$pengurus);

    }
}
