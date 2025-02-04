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
            'niup' => 'required|unique',
            'jenis_kelamin' => 'required',
            'tanggal_lahir' => 'required|date|before:today',
            'tempat_lahir' => 'required|string|max:50',
            'nik' => 'required|unique',
            'no_kk' => 'required',
            'no_telepon' => 'required',
            'email' => 'required|unique',
            'jenjang_pendidikan_terakhir' => 'required',
            'nama_pendidikan_terakhir' => 'required',
            'status' => 'required',
            'created_by' => 'required',
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
            'niup' => [
                'required',
                Rule::unique('biodata', 'niup')->ignore($id),
            ],
            'jenis_kelamin' => 'required',
            'tanggal_lahir' => 'required|date|before:today',
            'tempat_lahir' => 'required|string|max:50',
            'nik' => [
                'required',
                Rule::unique('biodata', 'nik')->ignore($id),
            ],
            'no_kk' => 'required',
            'no_telepon' => 'required',
            'email' => [
                'required',
                Rule::unique('biodata', 'email')->ignore($id),
            ],
            'jenjang_pendidikan_terakhir' => 'required',
            'nama_pendidikan_terakhir' => 'required',
            'status' => 'required',
            'created_by' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $biodata->update($request->validated());

        return new PdResource(true, 'Data berhasil diubah', $biodata);
    }
}
