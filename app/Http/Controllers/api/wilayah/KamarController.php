<?php

namespace App\Http\Controllers\api\wilayah;

use Illuminate\Http\Request;
use App\Models\Kewilayahan\Kamar;
use App\Http\Resources\PdResource;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class KamarController extends Controller
{
    public function index()
    {
        $kamar = Kamar::Active()->get();
        return new PdResource(true, 'List data kamar', $kamar);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_kamar' => 'required|string|max:100',
            'id_blok' => 'required|integer',
            'created_by' => 'required|integer',
            'status' => 'required|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $kamar = Kamar::create($validator->validated());

        return new PdResource(true, 'Data Berhasil Ditambah', $kamar);
    }

    public function show($id)
    {
        $kamar = Kamar::findOrFail($id);

        return new PdResource(true, 'Detail data', $kamar);
    }

    public function update(Request $request, $id)
    {

        $kamar = Kamar::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nama_kamar' => 'required|string|max:100',
            'id_blok' => 'required|integer',
            'updated_by' => 'required|integer',
            'status' => 'required|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $kamar->update($request->validated());

        return new PdResource(true, 'Data Berhasil Diubah', $kamar);
    }

    public function destroy($id)
    {
        $kamar = Kamar::findOrFail($id);

        $kamar->delete();
        return new PdResource(true, 'Data Berhasil Dihapus', null);
    }
}
