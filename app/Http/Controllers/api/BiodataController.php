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
            'id_negara' => ['required', 'integer', Rule::exists('negara', 'id')],
            'id_provinsi' => [
                'nullable',
                'integer',
                Rule::exists('provinsi', 'id')->where(function ($query) use ($request) {
                    if ($request->filled('id_negara')) {
                        $query->where('id_negara', $request->id_negara);
                    }
                }),
            ],
            'id_kabupaten' => [
                'nullable',
                'integer',
                Rule::exists('kabupaten', 'id')->where(function ($query) use ($request) {
                    if ($request->filled('id_provinsi')) {
                        $query->where('id_provinsi', $request->id_provinsi);
                    }
                }),
            ],
            'id_kecamatan' => [
                'nullable',
                'integer',
                Rule::exists('kecamatan', 'id')->where(function ($query) use ($request) {
                    if ($request->filled('id_kabupaten')) {
                        $query->where('id_kabupaten', $request->id_kabupaten);
                    }
                }),
            ],
            'id_desa' => [
                'nullable',
                'integer',
                Rule::exists('desa', 'id')->where(function ($query) use ($request) {
                    if ($request->filled('id_kecamatan')) {
                        $query->where('id_kecamatan', $request->id_kecamatan);
                    }
                }),
            ],
            'no_passport' => 'nullable|string',
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
            'anak_keberapa' => 'required|numeric|min:1',
            'dari_saudara' => 'required|numeric|min:1|gte:anak_keberapa',
            'tinggal_bersama' => 'required|string|max:50',
            'smartcard' => [
                'required',
                'string',
                'max:255',
                Rule::unique('peserta_didik', 'smartcard')
            ],
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
            'id_negara' => ['required', 'integer', Rule::exists('negara', 'id')],
            'id_provinsi' => [
                'nullable',
                'integer',
                Rule::exists('provinsi', 'id')->where(function ($query) use ($request) {
                    if ($request->filled('id_negara')) {
                        $query->where('id_negara', $request->id_negara);
                    }
                }),
            ],
            'id_kabupaten' => [
                'nullable',
                'integer',
                Rule::exists('kabupaten', 'id')->where(function ($query) use ($request) {
                    if ($request->filled('id_provinsi')) {
                        $query->where('id_provinsi', $request->id_provinsi);
                    }
                }),
            ],
            'id_kecamatan' => [
                'nullable',
                'integer',
                Rule::exists('kecamatan', 'id')->where(function ($query) use ($request) {
                    if ($request->filled('id_kabupaten')) {
                        $query->where('id_kabupaten', $request->id_kabupaten);
                    }
                }),
            ],
            'id_desa' => [
                'nullable',
                'integer',
                Rule::exists('desa', 'id')->where(function ($query) use ($request) {
                    if ($request->filled('id_kecamatan')) {
                        $query->where('id_kecamatan', $request->id_kecamatan);
                    }
                }),
            ],
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

    public function wargaPesantren(string $id)
    {
        $biodata = Biodata::where('id', $id)
            ->select('id', 'niup', 'status as aktif')
            ->first();

        return new PdResource(true, 'data berhasil di tampilkan', $biodata);
    }
}
