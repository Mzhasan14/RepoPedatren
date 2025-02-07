<?php

namespace App\Http\Controllers\api\wilayah;

use App\Models\Kewilayahan\Blok;
use Illuminate\Http\Request;
use App\Http\Resources\PdResource;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class BlokController extends Controller
{
    public function index()
    {
        $blok = Blok::Active();
        return new PdResource(true, 'List data blok', $blok);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_blok' => 'required|string|max:100',
            'id_wilayah' => 'required|integer',
            'created_by' => 'required|integer',
            'updated_by' => 'required|integer',
            'deleted_by' => 'required|integer',
            'status' => 'required|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $blok = Blok::create($validator->validated());

        return new PdResource(true, 'Data Berhasil Ditambah', $blok);
    }

    public function show($id)
    {
        $blok = Blok::findOrFail($id);

        return new PdResource(true, 'Detail data', $blok);
    }

    public function update(Request $request, $id)
    {

        $blok = Blok::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nama_blok' => 'required|string|max:100',
            'id_wilayah' => 'required|integer',
            'created_by' => 'required|integer',
            'updated_by' => 'required|integer',
            'deleted_by' => 'required|integer',
            'status' => 'required|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $blok->update($request->validated());

        return new PdResource(true, 'Data Berhasil Diubah', $blok);
    }

    public function destroy($id)
    {
        $blok = Blok::findOrFail($id);

        $blok->delete();
        return new PdResource(true, 'Data Berhasil Dihapus', null);
    }
}
