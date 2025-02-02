<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Models\Peserta_didik;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use App\Http\Resources\SantriResource;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\SantriRequest;

class SantriController extends Controller
{
    public function index()
    {
        $santri = Peserta_didik::Santri()->Active()->latest()->paginate(5);

        return new SantriResource(true, 'List data santri', $santri);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_biodata' => 'required', // |exists:biodatas,id
            'nis' => 'required|numeric|unique:peserta_didik,nis|digits_between:5,10',
            'anak_keberapa' => 'required|numeric|min:1',
            'dari_saudara' => 'required|numeric|min:1|gte:anak_keberapa',
            'tinggal_bersama' => 'required|string|max:50',
            'jenjang_pendidikan_terakhir' => 'required',
            'nama_pendidikan_terakhir' => 'required|string|max:100',
            'smartcard' => 'required|string|max:255',
            'tahun_masuk' => 'required|date|before_or_equal:tahun_keluar',
            'tahun_keluar' => 'required|date|after_or_equal:tahun_masuk',
            'created_by' => 'required', // |exists:users,id
            'status' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $santri = Peserta_didik::create($validator->validated());

        return new SantriResource(true, 'Data berhasil ditambah', $santri);
    }

    public function show($id)
    {
        $santri = Peserta_didik::findOrFail($id);
        return new SantriResource(true, 'Detail data', $santri);
    }

    public function update(Request $request, $id)
    {

        $santri = Peserta_didik::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'id_biodata' => 'required', // |exists:biodatas,id
            'nis' => [
                'required',
                'numeric',
                Rule::unique('peserta_didik', 'nis')->ignore($santri->id),
                'digits_between:5,10',
            ],
            'anak_keberapa' => 'required|numeric|min:1',
            'dari_saudara' => 'required|numeric|min:1|gte:anak_keberapa',
            'tinggal_bersama' => 'required|string|max:50',
            'jenjang_pendidikan_terakhir' => 'required',
            'nama_pendidikan_terakhir' => 'required|string|max:100',
            'smartcard' => 'required|string|max:255',
            'tahun_masuk' => 'required|date|before_or_equal:tahun_keluar',
            'tahun_keluar' => 'required|date|after_or_equal:tahun_masuk',
            'created_by' => 'required', // |exists:users,id
            'status' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $santri->update($validator->validated());

        return new SantriResource(true, 'Data berhasil diubah', $santri);
    }

    public function destroy($id)
    {
        $santri = Peserta_didik::findOrFail($id);

        $santri->delete();
        return new SantriResource(true, 'Data berhasil dihapus', null);
    }
}
