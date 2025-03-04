<?php

namespace App\Http\Controllers\api\keluarga;

use App\Models\OrangTua;
use Illuminate\Http\Request;
use App\Http\Resources\PdResource;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class OrangTuaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $ortu = OrangTua::Active()->latest()->paginate(5);
        return new PdResource(true, 'List Orang Tua', $ortu);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_biodata' => 'required|exists:biodata,id',
            'pekerjaan' => 'required|string',
            'penghasilan' => 'nullable|integer',
            'created_by' => 'required',
            'updated_by' => 'nullable',
            'status' => 'nullable'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $ortu = OrangTua::create($validator->validated());
        return new PdResource(true, 'Data berhasil Ditambah', $ortu);
    
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $ortu = OrangTua::findOrFail($id);
        return new PdResource(true, 'detail data', $ortu);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $ortu = OrangTua::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'id_biodata' => 'required|exists:biodata,id',
            'pekerjaan' => 'required|string',
            'penghasilan' => 'nullable|integer',
            'created_by' => 'nullable',
            'updated_by' => 'nullable',
            'status' => 'nullable'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $ortu->update($validator->validated());
        return new PdResource(true, 'data berhasil diubah', $ortu);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $ortu = OrangTua::findOrFail($id);

        $ortu->delete();
        return new PdResource(true, 'Data berhasil dihapus', null);
    }
}
