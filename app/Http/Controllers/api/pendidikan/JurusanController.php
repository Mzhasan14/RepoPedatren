<?php

namespace App\Http\Controllers\api\pendidikan;

use Illuminate\Http\Request;
use App\Http\Resources\PdResource;
use App\Models\Pendidikan\Jurusan;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class JurusanController extends Controller
{
    public function index()
    {
        $jurusan = Jurusan::Active()->get();
        return new PdResource(true, 'Data Jurusan', $jurusan);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_jurusan' => 'required|string|max:100',
            'id_lembaga' => 'required|integer',
            'created_by' => 'required|integer',
            'status' => 'required|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $jurusan = Jurusan::create($validator->validated());
        return new PdResource(true, 'Jurusan Berhasil Ditambah', $jurusan);
    }

    public function show($id)
    {
        $jurusan = Jurusan::findOrFail($id);
        return new PdResource(true, 'Detail Jurusan', $jurusan);
    }

    public function update(Request $request, $id)
    {
        $jurusan = Jurusan::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nama_jurusan' => 'required|string|max:100',
            'id_lembaga' => 'required|integer',
            'updated_by' => 'required|integer',
            'status' => 'required|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $jurusan->update($validator->validated());
        return new PdResource(true, 'Jurusan Berhasil Diubah', $jurusan);
    }

    public function destroy($id)
    {
        $jurusan = Jurusan::findOrFail($id);
        $jurusan->delete();
        return new PdResource(true, 'Jurusan Berhasil Dihapus', null);
    }
}
