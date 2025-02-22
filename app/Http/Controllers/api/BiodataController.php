<?php

namespace App\Http\Controllers\api;

use App\Models\Biodata;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Resources\PdResource;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class BiodataController extends Controller
{
    public function index()
    {
        $biodata = Biodata::Active()->latest()->paginate(5);
        return new PdResource(true, 'list biodata', $biodata);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_desa' => 'required|integer',
            'nama' => 'required|string|max:100',
            'niup' => 'required|unique:biodata,niup',
            'jenis_kelamin' => 'required|string',
            'tanggal_lahir' => 'required|date|before:today',
            'tempat_lahir' => 'required|string|max:50',
            'nik' => 'required|unique:biodata:nik|string',
            'no_kk' => 'required|string',
            'no_telepon' => [
                'required',
                'regex:/^(?:\+62|0)[0-9]{9,13}$/',
            ],
            'email' => 'required|email|unique:biodata,email',
            'jenjang_pendidikan_terakhir' => 'required|string|max:50',
            'nama_pendidikan_terakhir' => 'required|string|max:50',
            'status' => 'required|boolean',
            'created_by' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $biodata = Biodata::create($validator->validated());

        return new PdResource(true, 'Data berhasil ditambah', $biodata);
    }

    public function show($id)
    {
        $biodata = Biodata::findOrFail($id);
        return new PdResource(true, 'Detail data', $biodata);
    }

    public function update(Request $request, $id)
    {
        $biodata = Biodata::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'id_desa' => 'required|integer',
            'nama' => 'required|string|max:100',
            'no_kk' => 'required|string',
            'no_telepon' => [
                'required',
                'regex:/^(?:\+62|0)[0-9]{9,13}$/',
                Rule::unique('biodata', 'no_telepon')->ignore($id),
            ],
            'email' => [
                'required',
                'email',
                Rule::unique('biodata', 'email')->ignore($id),
            ],
            'jenjang_pendidikan_terakhir' => 'required|string|max:50',
            'nama_pendidikan_terakhir' => 'required|string|max:50',
            'status' => 'required|boolean',
            'updated_by' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $biodata->update($request->validated());

        return new PdResource(true, 'Data berhasil diubah', $biodata);
    }

    public function destroy($id)
    {
        $biodata = Biodata::findOrFail($id);
        $biodata->delete();

        return new PdResource(true, 'Data Berhasil Dihapus', null);
    }
    
}
