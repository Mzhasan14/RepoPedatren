<?php

namespace App\Http\Controllers\api\wilayah;


use App\Models\Kewilayahan\Wilayah;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\PdResource;
use App\Models\Peserta_didik;
use Illuminate\Support\Facades\Validator;

class WilayahController extends Controller
{
    public function index()
    {
        $wilayah = Wilayah::Active();
        return new PdResource(true, 'List data Wilayah', $wilayah);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_wilayah' => 'required|string|max:100',
            'created_by' => 'required|integer',
            'status' => 'required|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $wilayah = Wilayah::create($validator->validated());

        return new PdResource(true, 'Data Berhasil Ditambah', $wilayah);
    }

    public function show($id)
    {
        $wilayah = Wilayah::findOrFail($id);

        return new PdResource(true, 'Detail data', $wilayah);
    }

    public function update(Request $request, $id)
    {

        $wilayah = Wilayah::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nama_wilayah' => 'required|string|max:100',
            'updated_by' => 'required|integer',
            'status' => 'required|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $wilayah->update($request->validated());

        return new PdResource(true, 'Data Berhasil Diubah', $wilayah);
    }

    public function destroy($id)
    {
        $wilayah = Wilayah::findOrFail($id);

        $wilayah->delete();
        return new PdResource(true, 'Data Berhasil Dihapus', null);
    }
}
