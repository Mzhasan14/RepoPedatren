<?php

namespace App\Http\Controllers\api\keluarga;

use App\Models\Biodata;
use App\Models\Keluarga;
use Illuminate\Http\Request;
use App\Http\Resources\PdResource;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
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
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'no_kk' => 'required|max:16',
            'status_wali' => 'nullable',
            'id_status_keluarga' => 'required',
            'created_by' => 'required',
            'status' => 'nullable'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $keluarga = Keluarga::create($validator->validated());
        return new PdResource(true, 'Data berhasil Ditambah', $keluarga);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $keluarga = Keluarga::findOrFail($id);
        return new PdResource(true, 'detail data', $keluarga);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $keluarga = Keluarga::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'no_kk' => 'required|max:16',
            'status_wali' => 'nullable',
            'id_status_keluarga' => 'required',
            'updated_by' => 'nullable',
            'status' => 'nullable'
        ]);

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

    public function dataWali()
    {
        $wali = Biodata::join('keluarga', 'biodata.no_kk', '=', 'keluarga.no_kk')
            ->select('biodata.nama', 'biodata.nik', 'biodata.no_telepon')
            ->where('status_wali', true)->get();
        return new PdResource(true, 'List Data Wali', $wali);
    }

    public function keluarga() {
        $keluarga = Keluarga::join('status_keluarga','keluarga.id_status_keluarga','=','status_keluarga.id')
        ->join('biodata','keluarga.no_kk','=','biodata.no_kk')
        ->select(
           'keluarga.id',
           'keluarga.no_kk',
           'biodata.nama',
           'keluarga.status_wali',
           'status_keluarga.nama_status',
            'biodata.image_url'
        )->get();
        return new PdResource(true, 'Data berhasil ditampilkan', $keluarga);
    }
    
    
}
