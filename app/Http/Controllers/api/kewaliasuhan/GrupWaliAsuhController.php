<?php

namespace App\Http\Controllers\api\kewaliasuhan;

use App\Models\Biodata;
use Illuminate\Http\Request;
use App\Http\Resources\PdResource;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Models\Kewaliasuhan\Grup_WaliAsuh;

class GrupWaliAsuhController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
         $grupWaliAsuh = Grup_WaliAsuh::Active()->latest()->paginate(5);
        return new PdResource(true, 'list grup wali asuh', $grupWaliAsuh);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_grup' => 'required',
            'created_by' => 'required',
            'status' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $grupWaliAsuh = Grup_WaliAsuh::create($validator->validated());

        return new PdResource(true, 'Data berhasil ditambah', $grupWaliAsuh);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $grupWaliAsuh = Grup_WaliAsuh::findOrFail($id);
        return new PdResource(true, 'Detail data', $grupWaliAsuh);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $grupWaliAsuh = Grup_WaliAsuh::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nama_grup' => 'required',
            'updated_by' => 'nullable',
            'status' => 'nullable'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $grupWaliAsuh->update($request->validated());
        return new PdResource(true, 'Data berhasil diubah', $grupWaliAsuh);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $grupWaliAsuh = Grup_WaliAsuh::findOrFail($id);
        $grupWaliAsuh->delete();
        return new PdResource(true,'Data berhasil dihapus',null);
    }


    public function kewaliasuhan() {

         $grupKewaliasuhan = Grup_WaliAsuh::join('wali_asuh as wa1','grup_wali_asuh.id','=','wa1.id_grup_wali_asuh')
         ->join('wilayah','grup_wali_asuh.id_wilayah','=','wilayah.id')
         ->join('santri','santri.nis','=','wa1.nis')
         ->join('peserta_didik','santri.id_peserta_didik','=','peserta_didik.id')
         ->join('biodata','peserta_didik.id_biodata','=','biodata.id')
         
         ->select(
            'grup_wali_asuh.id',
            'grup_wali_asuh.nama_grup',
            'santri.nis as Nis_WaliAsuh',
            'biodata.nama as Nama_WaliAsuh',
            'wilayah.nama_wilayah',
            'grup_wali_asuh.updated_by as Tanggal Update Group'

         )->get();

        return new PdResource(true, 'list grup kewaliasuhan', $grupKewaliasuhan);
    }
}
