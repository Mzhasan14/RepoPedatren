<?php

namespace App\Http\Controllers\api;

use App\Models\Santri;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Resources\PdResource;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class SantriController extends Controller
{
    public function index()
    {
        $santri = Santri::Active()->latest()->paginate(10);
        return new PdResource(true, 'Data Santri', $santri);
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_peserta_didik' => [
                'required',
                'integer',
                Rule::unique('santri', 'id_peserta_didik')
            ],
            'id_wilayah' => ['required', 'integer', Rule::exists('wilayah', 'id')],
            'id_blok' => [
                'nullable',
                'integer',
                Rule::exists('blok', 'id')->where(function ($query) use ($request) {
                    if ($request->filled('id_wilayah')) {
                        $query->where('id_wilayah', $request->id_wilayah);
                    }
                }),
            ],
            'id_kamar' => [
                'nullable',
                'integer',
                Rule::exists('kamar', 'id')->where(function ($query) use ($request) {
                    if ($request->filled('id_blok')) {
                        $query->where('id_blok', $request->id_blok);
                    }
                }),
            ],
            'id_domisili' => [
                'nullable',
                'integer',
                Rule::exists('domisili', 'id')->where(function ($query) use ($request) {
                    if ($request->filled('id_kamar')) {
                        $query->where('id_kamar', $request->id_kamar);
                    }
                }),
            ],
            'nis' => [
                'nullable',
                'string',
                'size:11',
                Rule::unique('santri', 'nis')
            ],
            'tanggal_masuk' => 'required|date',
            'tanggal_keluar' => 'nullable|date',
            'created_by' => 'required|integer',
            'status' => 'required|boolean'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $santri = Santri::create($validator->validated());

        return new PdResource(true, 'Data berhasil ditambah', $santri);
    }

    public function show($id)
    {
        $santri = Santri::findOrFail($id);
        return new PdResource(true, 'Detail Peserta Didik', $santri);
    }

    public function update(Request $request, $id)
    {
        $santri = Santri::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'id_wilayah' => ['required', 'integer', Rule::exists('wilayah', 'id')],
            'id_blok' => [
                'nullable',
                'integer',
                Rule::exists('blok', 'id')->where(function ($query) use ($request) {
                    if ($request->filled('id_wilayah')) {
                        $query->where('id_wilayah', $request->id_wilayah);
                    }
                }),
            ],
            'id_kamar' => [
                'nullable',
                'integer',
                Rule::exists('kamar', 'id')->where(function ($query) use ($request) {
                    if ($request->filled('id_blok')) {
                        $query->where('id_blok', $request->id_blok);
                    }
                }),
            ],
            'id_domisili' => [
                'nullable',
                'integer',
                Rule::exists('domisili', 'id')->where(function ($query) use ($request) {
                    if ($request->filled('id_kamar')) {
                        $query->where('id_kamar', $request->id_kamar);
                    }
                }),
            ],
            'tanggal_keluar' => 'nullable|date',
            'updated_by' => 'required|integer',
            'status' => 'required|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $santri->update($validator->validated());

        return new PdResource(true, 'Data berhasil diubah', $santri);
    }

    public function destroy($id)
    {
        $santri = Santri::findOrFail($id);

        $santri->delete();
        return new PdResource(true, 'Data berhasil dihapus', null);
    }
}
