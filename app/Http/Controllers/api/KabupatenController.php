<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Models\Alamat\Kabupaten;
use App\Http\Resources\PdResource;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class KabupatenController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $kabupaten = Kabupaten::Active()->get();
        return new PdResource(true, 'Data berhasil ditampilkan', $kabupaten);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_provinsi' => 'required|integer|exists:provinsi,id',
            'nama_kabupaten' => 'required|string|max:255',
            'created_by' => 'required|integer',
            'updated_by' => 'nullable|integer',
            'status' => 'required|boolean'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'data gagal dibuat',
                'data' => $validator->errors()
            ]);
        }
        $kabupaten = Kabupaten::create($validator->validated());
        return new PdResource(true, 'data berhasil dibuat', $kabupaten);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $kabupaten = Kabupaten::findOrFail($id);
        return new PdResource(true, 'Data berhasil di tampilkan', $kabupaten);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $kabupaten = Kabupaten::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'id_provinsi' => 'required|integer|exists:provinsi,id',
            'nama_kabupaten' => 'required|string|max:255',
            'created_by' => 'nullable|integer',
            'updated_by' => 'nullable|integer',
            'status' => 'required|boolean'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'data gagal dibuat',
                'data' => $validator->errors()
            ]);
        }
        $kabupaten->update($validator->validated());
        return new PdResource(true, 'Data berhasil diupdate', $kabupaten);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $kabupaten = Kabupaten::findOrFail($id);
        $kabupaten->delete();
        return new PdResource(true, 'Data berhasil dihapus', $kabupaten);
    }
}
