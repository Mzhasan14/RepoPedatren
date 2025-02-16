<?php

namespace App\Http\Controllers\api\kewaliasuhan;

use Illuminate\Http\Request;
use App\Http\Resources\PdResource;
use App\Http\Controllers\Controller;
use App\Models\Kewaliasuhan\Anak_asuh;
use Illuminate\Support\Facades\Validator;

class AnakasuhController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
         $anakAsuh = Anak_asuh::Active()->latest()->paginate(5);
        return new PdResource(true, 'list anak asuh', $anakAsuh);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nis' => 'required|exists:peserta_didik,nis',
            'id_grup_wali_asuh' => 'required|exists:grup_wali_asuh,id',
            'created_by' => 'required',
            'updated_by' => 'nullable',
            'status' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $anakAsuh = Anak_asuh::create($validator->validated());

        return new PdResource(true, 'Data berhasil ditambah', $anakAsuh);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $anakAsuh = Anak_asuh::findOrFail($id);
        return new PdResource(true, 'Detail data', $anakAsuh);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $anakAsuh = Anak_asuh::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nis' => 'required|exists:peserta_didik,nis',
            'id_grup_wali_asuh' => 'required|exists:grup_wali_asuh,id',
            'created_by' => 'nullable',
            'updated_by' => 'nullable',
            'status' => 'nullable'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $anakAsuh->update($request->validated());
        return new PdResource(true, 'Data berhasil diubah', $anakAsuh);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $anakAsuh = Anak_asuh::findOrFail($id);
        $anakAsuh->delete();
        return new PdResource(true,'Data berhasil dihapus',null);
    }
}
