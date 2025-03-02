<?php

namespace App\Http\Controllers\api\pendidikan;

use Illuminate\Http\Request;
use App\Models\Pendidikan\Rombel;
use App\Http\Resources\PdResource;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class RombelController extends Controller
{
    public function index()
    {
        $rombel = Rombel::Active()->get();
        return new PdResource(true, 'Data Rombel', $rombel);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_rombel' => 'required|string|max:100',
            'id_kelas' => 'required|integer',
            'created_by' => 'required|integer',
            'status' => 'required|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $rombel = Rombel::create($validator->validated());
        return new PdResource(true, 'Rombel Berhasil Ditambah', $rombel);
    }

    public function show($id)
    {
        $rombel = Rombel::findOrFail($id);
        return new PdResource(true, 'Detail Rombel', $rombel);
    }

    public function update(Request $request, $id)
    {
        $rombel = Rombel::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nama_rombel' => 'required|string|max:100',
            'id_kelas' => 'required|integer',
            'updated_by' => 'required|integer',
            'status' => 'required|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $rombel->update($validator->validated());
        return new PdResource(true, 'Rombel Berhasil Diubah', $rombel);
    }

    public function destroy($id)
    {
        $rombel = Rombel::findOrFail($id);
        $rombel->delete();
        return new PdResource(true, 'Rombel Berhasil Dihapus', null);
    }
}
