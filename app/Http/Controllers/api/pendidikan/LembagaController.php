<?php

namespace App\Http\Controllers\api\pendidikan;

use Illuminate\Http\Request;
use App\Http\Resources\PdResource;
use App\Models\Pendidikan\Lembaga;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class LembagaController extends Controller
{
    public function index()
    {
        $lembaga = Lembaga::Active()->get();
        return new PdResource(true, 'Data Lembaga', $lembaga);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_lembaga' => 'required|string|max:100',
            'created_by' => 'required|integer',
            'status' => 'required|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $lembaga = Lembaga::create($validator->validated());
        return new PdResource(true, 'Lembaga Berhasil Ditambah', $lembaga);
    }

    public function show($id)
    {
        $lembaga = Lembaga::findOrFail($id);
        return new PdResource(true, 'Detail Lembaga', $lembaga);
    }

    public function update(Request $request, $id)
    {
        $lembaga = Lembaga::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nama_lembaga' => 'required|string|max:100',
            'updated_by' => 'required|integer',
            'status' => 'required|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $lembaga->update($validator->validated());
        return new PdResource(true, 'Lembaga Berhasil Diubah', $lembaga);
    }

    public function destroy($id)
    {
        $lembaga = Lembaga::findOrFail($id);
        $lembaga->delete();
        return new PdResource(true, 'Lembaga Berhasil Dihapus', null);
    }
}
