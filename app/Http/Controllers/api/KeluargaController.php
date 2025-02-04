<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\KeluargaRequest;
use App\Http\Resources\PdResource;
use App\Models\Keluarga;
use Illuminate\Support\Facades\Validator;

class KeluargaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $keluarga = Keluarga::Active()->latest()->paginate(5);
        return new PdResource(true, 'List Keluarga', $keluarga);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(KeluargaRequest $request)
    {
        $validator = $request->validated();

        $keluarga = Keluarga::create($validator);
        return new PdResource(true,'Data berhasil Ditambah', $keluarga);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $keluarga = Keluarga::findOrFail($id);
        return new PdResource(true,'detail data',$keluarga);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $keluarga = Keluarga::findOrFail($id);

        $validator = $request->validated();

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $keluarga->update($validator->validated());
        return new PdResource(true, 'data berhasil diubah', $keluarga);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $keluarga = Keluarga::findOrFail($id);

        $keluarga->delete();
        return new PdResource(true, 'Data berhasil dihapus', null);
    }
}
