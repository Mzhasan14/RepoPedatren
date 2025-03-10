<?php

namespace App\Http\Controllers\api;

use App\Models\Pelajar;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Resources\PdResource;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class PelajarController extends Controller
{
    public function index()
    {
        $pelajar = Pelajar::Active()->latest()->paginate(10);
        return new PdResource(true, 'Data Pelajar', $pelajar);
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_peserta_didik' => [
                'required',
                'integer',
                Rule::unique('pelajar', 'id_peserta_didik')
            ],
            'id_lembaga' => ['required', 'integer', Rule::exists('lembaga', 'id')],
            'id_jurusan' => [
                'nullable',
                'integer',
                Rule::exists('jurusan', 'id')->where(function ($query) use ($request) {
                    if ($request->filled('id_lembaga')) {
                        $query->where('id_lembaga', $request->id_lembaga);
                    }
                }),
            ],
            'id_kelas' => [
                'nullable',
                'integer',
                Rule::exists('kelas', 'id')->where(function ($query) use ($request) {
                    if ($request->filled('id_jurusan')) {
                        $query->where('id_jurusan', $request->id_jurusan);
                    }
                }),
            ],
            'id_rombel' => [
                'nullable',
                'integer',
                Rule::exists('rombel', 'id')->where(function ($query) use ($request) {
                    if ($request->filled('id_kelas')) {
                        $query->where('id_kelas', $request->id_kelas);
                    }
                }),
            ],
            'no_induk' => 'nullable|string',
            'tanggal_masuk' => 'required|date',
            'tanggal_keluar' => 'nullable|date',
            'created_by' => 'required|integer',
            'status' => 'required|boolean'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $pelajar = Pelajar::create($validator->validated());

        return new PdResource(true, 'Data berhasil ditambah', $pelajar);
    }

    public function show($id)
    {
        $pelajar = Pelajar::findOrFail($id);
        return new PdResource(true, 'Detail Peserta Didik', $pelajar);
    }

    public function update(Request $request, $id)
    {

        $pelajar = Pelajar::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'id_lembaga' => ['required', 'integer', Rule::exists('lembaga', 'id')],
            'id_jurusan' => [
                'nullable',
                'integer',
                Rule::exists('jurusan', 'id')->where(function ($query) use ($request) {
                    if ($request->filled('id_lembaga')) {
                        $query->where('id_lembaga', $request->id_lembaga);
                    }
                }),
            ],
            'id_kelas' => [
                'nullable',
                'integer',
                Rule::exists('kelas', 'id')->where(function ($query) use ($request) {
                    if ($request->filled('id_jurusan')) {
                        $query->where('id_jurusan', $request->id_jurusan);
                    }
                }),
            ],
            'id_rombel' => [
                'nullable',
                'integer',
                Rule::exists('rombel', 'id')->where(function ($query) use ($request) {
                    if ($request->filled('id_kelas')) {
                        $query->where('id_kelas', $request->id_kelas);
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

        $pelajar->update($validator->validated());

        return new PdResource(true, 'Data berhasil diubah', $pelajar);
    }

    public function destroy($id)
    {
        $pelajar = Pelajar::findOrFail($id);

        $pelajar->delete();
        return new PdResource(true, 'Data berhasil dihapus', null);
    }
}
