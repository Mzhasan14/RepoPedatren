<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Models\Status_keluarga;
use App\Http\Resources\PdResource;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class StatusKeluargaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $statusKeluarga = Status_keluarga::Active()->latest()->paginate(5);
        return new PdResource(true, 'List Status Keluarga', $statusKeluarga);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_status' => 'required',
            'created_by' => 'required',
            'updated_by' => 'nullable',
            'status' => 'nullable'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $statusKeluarga = Status_keluarga::create($validator->validated());
        return new PdResource(true, 'Data berhasil Ditambah', $statusKeluarga);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $statusKeluarga = Status_keluarga::findOrFail($id);
        return new PdResource(true, 'detail data', $statusKeluarga);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $statusKeluarga = Status_keluarga::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nama_status' => 'required',
            'created_by' => 'nullable',
            'updated_by' => 'nullable',
            'status' => 'nullable'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $statusKeluarga->update($validator->validated());
        return new PdResource(true, 'data berhasil diubah', $statusKeluarga);
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $statusKeluarga = Status_keluarga::findOrFail($id);

        $statusKeluarga->delete();
        return new PdResource(true, 'Data berhasil dihapus', null);
    }
}
