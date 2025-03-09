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

         $grupKewaliasuhan = DB::table('grup_wali_asuh')
            // ->join('wali_asuh', 'grup_wali_asuh.id', '=', 'wali_asuh.id_grup_wali_asuh')
            // ->join('anak_asuh', 'grup_wali_asuh.id', '=', 'anak_asuh.id_grup_wali_asuh')
            ->select(
                'grup_wali_asuh.id as id_grup_wali_asuh',
                'grup_wali_asuh.nama_grup',
                // 'wali_asuh.id',
                // 'anak_asuh.id'
            )
            // ->groupBy('grup_wali_asuh.id', 'grup_wali_asuh.nama_grup')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Data berhasil ditampilkan',
            'data' => $grupKewaliasuhan
        ]);
    }
}
