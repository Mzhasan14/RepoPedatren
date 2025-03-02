<?php

namespace App\Http\Controllers\api\pendidikan;

use Illuminate\Http\Request;
use App\Models\Pendidikan\Kelas;
use App\Http\Resources\PdResource;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class KelasController extends Controller
{
    public function index()
    {
        $kelas = Kelas::Active()->get();
        return new PdResource(true, 'Data Kelas', $kelas);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_kelas' => 'required|string|max:100',
            'id_jurusan' => 'required|integer',
            'created_by' => 'required|integer',
            'status' => 'required|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $kelas = Kelas::create($validator->validated());
        return new PdResource(true, 'Kelas Berhasil Ditambah', $kelas);
    }

    public function show($id)
    {
        $kelas = Kelas::findOrFail($id);
        return new PdResource(true, 'Detail Kelas', $kelas);
    }

    public function update(Request $request, $id)
    {
        $kelas = Kelas::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nama_kelas' => 'required|string|max:100',
            'id_jurusan' => 'required|integer',
            'updated_by' => 'required|integer',
            'status' => 'required|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $kelas->update($validator->validated());
        return new PdResource(true, 'Kelas Berhasil Diubah', $kelas);
    }

    public function destroy($id)
    {
        $kelas = kelas::findOrFail($id);
        $kelas->delete();
        return new PdResource(true, 'Kelas Berhasil Dihapus', null);
    }
}
