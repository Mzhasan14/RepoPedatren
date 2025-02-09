<?php

namespace App\Http\Controllers\api\wilayah;

use Illuminate\Http\Request;
use App\Http\Resources\PdResource;
use App\Http\Controllers\Controller;
use App\Models\Kewilayahan\Domisili;
use Illuminate\Support\Facades\Validator;

class DomisiliController extends Controller
{
    public function index()
    {
        $domisili = Domisili::Active();
        return new PdResource(true, 'List data domisili', $domisili);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_domisili' => 'required|string|max:100',
            'id_kamar' => 'required|integer',
            'id_peserta_didik' => 'required|integer',
            'created_by' => 'required|integer',
            'updated_by' => 'required|integer',
            'deleted_by' => 'required|integer',
            'status' => 'required|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $domisili = Domisili::create($validator->validated());

        return new PdResource(true, 'Data Berhasil Ditambah', $domisili);
    }

    public function show($id)
    {
        $domisili = Domisili::findOrFail($id);

        return new PdResource(true, 'Detail data', $domisili);
    }

    public function update(Request $request, $id)
    {

        $domisili = Domisili::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nama_domisili' => 'required|string|max:100',
            'id_kamar' => 'required|integer',
            'id_peserta_didik' => 'required|integer',
            'created_by' => 'required|integer',
            'updated_by' => 'required|integer',
            'deleted_by' => 'required|integer',
            'status' => 'required|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $domisili->update($request->validated());

        return new PdResource(true, 'Data Berhasil Diubah', $domisili);
    }

    public function destroy($id)
    {
        $domisili = Domisili::findOrFail($id);

        $domisili->delete();
        return new PdResource(true, 'Data Berhasil Dihapus', null);
    }
}
