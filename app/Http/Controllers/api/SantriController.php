<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Models\Peserta_didik;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use App\Http\Resources\PdResource;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\SantriRequest;

class SantriController extends Controller
{
    public function index()
    {
        $santri = Peserta_didik::Santri()->Active()->latest()->paginate(5);

        return new PdResource(true, 'List data santri', $santri);
    }

    public function store(SantriRequest $request)
    {
        $validator = $request->validated();

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

        $validator = $request->validated();

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
