<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Models\Peserta_didik;
use Illuminate\Validation\Rule;
use App\Http\Resources\PdResource;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class PesertaDidikController extends Controller
{
    public function index()
    {
        $pesertaDidik = Peserta_didik::Active()->latest()->paginate(10);
        return new PdResource(true, 'List Peserta Didik', $pesertaDidik);
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_domisili' => 'required|integer',
            'id_biodata' => [
                'required',
                'integer',
                Rule::unique('peserta_didik', 'id_biodata')
            ],
            'nis' => [
                'nullable',
                'string',
                'size:11',
                Rule::unique('peserta_didik', 'nis')
            ],
            'anak_keberapa' => 'required|numeric|min:1',
            'dari_saudara' => 'required|numeric|min:1|gte:anak_keberapa',
            'tinggal_bersama' => 'required|string|max:50',
            'smartcard' => [
                'required',
                'string',
                'max:255',
                Rule::unique('peserta_didik', 'smartcard')
            ],
            'tahun_masuk' => 'required|date',
            'tahun_keluar' => 'nullable|date',
            'created_by' => 'required|integer',
            'status' => 'required|boolean'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $pesertaDidik = Peserta_didik::create($validator->validated());

        return new PdResource(true, 'Data berhasil ditambah', $pesertaDidik);
    }

    public function show($id)
    {
        $pesertaDidik = Peserta_didik::findOrFail($id);
        return new PdResource(true, 'Detail Peserta Didik', $pesertaDidik);
    }

    public function update(Request $request, $id)
    {

        $pesertaDidik = Peserta_didik::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'id_domisili' => 'required|integer',
            'id_biodata' => [
                'required',
                'integer',
                Rule::unique('peserta_didik', 'id_biodata')->ignore($id)
            ],
            'nis' => [
                'nullable',
                'string',
                'size:11',
                Rule::unique('peserta_didik', 'nis')->ignore($id)
            ],
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

        $pesertaDidik->update($validator->validated());

        return new PdResource(true, 'Data berhasil diubah', $pesertaDidik);
    }

    public function destroy($id)
    {
        $pesertaDidik = Peserta_didik::findOrFail($id);

        $pesertaDidik->delete();
        return new PdResource(true, 'Data berhasil dihapus', null);
    }

    public function santri()
    {
        $santri = Peserta_didik::Santri()->Active()->latest()->paginate(5);

        return new PdResource(true, 'List data santri', $santri);
    }

    public function pesertaDidik()
    {
        $pesertaDidik = Peserta_Didik::join('biodata', 'peserta_didik.id_biodata', '=', 'biodata.id')
            ->join('rencana_pendidikan', 'peserta_didik.id', '=', 'rencana_pendidikan.id_peserta_didik')
            ->join('lembaga', 'rencana_pendidikan.id_lembaga', '=', 'lembaga.id')
            ->select('biodata.nama', 'biodata.niup', 'lembaga.nama_lembaga as lembaga')
            ->get();

        return new PdResource(true, 'Data peserta didik', $pesertaDidik);
    }
}
