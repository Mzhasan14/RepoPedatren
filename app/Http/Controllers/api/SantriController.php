<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Models\Peserta_didik;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use App\Http\Resources\PdResource;
use Illuminate\Support\Facades\Validator;

class SantriController extends Controller
{
    public function index()
    {
        $santri = Peserta_didik::Santri()->Active()->latest()->paginate(5);

        return new PdResource(true, 'List data santri', $santri);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_domisili' => 'required|integer',
            'id_biodata' => 'required|integer',
            'nis' => 'nullable|unique:peserta_didik,nis|string|size:11',
            'anak_keberapa' => 'required|numeric|min:1',
            'dari_saudara' => 'required|numeric|min:1|gte:anak_keberapa',
            'tinggal_bersama' => 'required|string|max:50',
            'smartcard' => 'required|unique:peserta_didik,smartcard|string|max:255',
            'tahun_masuk' => 'required|date',
            'tahun_keluar' => 'nullable|date',
            'created_by' => 'required|integer',
            'status' => 'required|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $santri = Peserta_didik::create($validator->validated());

        return new PdResource(true, 'Data berhasil ditambah', $santri);
    }

    public function show($id)
    {
        $santri = Peserta_didik::findOrFail($id);
        return new PdResource(true, 'Detail data', $santri);
    }

    public function update(Request $request, $id)
    {

        $santri = Peserta_didik::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'id_domisili' => 'required|integer',
            'anak_keberapa' => 'required|numeric|min:1',
            'dari_saudara' => 'required|numeric|min:1|gte:anak_keberapa',
            'tinggal_bersama' => 'required|string|max:50',
            'smartcard' => [
                'required',
                'string',
                'max:255',
                Rule::unique('peserta_didik', 'smartcard')->ignore($id)
            ],
            'tahun_masuk' => 'required|date',
            'tahun_keluar' => 'nullable|date',
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
        $santri = Peserta_didik::findOrFail($id);

        $santri->delete();
        return new PdResource(true, 'Data berhasil dihapus', null);
    }
}
