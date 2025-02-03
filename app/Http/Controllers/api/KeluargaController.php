<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
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
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'no_kk' => 'required|max:16',
            'status_wali' => 'nullable',
            'id_status_keluarga' => 'required',
            'created_by' => 'required',
            'updated_by' => 'nullable',
            'status' => 'nullable'
            ]);

        if($validator->fails()) {
            return response()->json($validator->errors(),422);
        }

        $keluarga = Keluarga::create($validator->validate());
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
