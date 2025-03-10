<?php

namespace App\Http\Controllers\api\kewaliasuhan;

use Illuminate\Http\Request;
use App\Models\Peserta_didik;
use App\Http\Resources\PdResource;
use Illuminate\Support\Facades\DB;
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
            'id_peserta_didik' => 'required|exists:peserta_didik,id',
            'id_grup_wali_asuh' => 'required|exists:grup_wali_asuh,id',
            'created_by' => 'required',
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
            'id_peserta_didik' => 'required|exists:peserta_didik,id',
            'id_grup_wali_asuh' => 'required|exists:grup_wali_asuh,id',
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

    public function anakAsuh() {
        $anakAsuh = Peserta_didik::join('biodata','peserta_didik.id_biodata','=','biodata.id')
        ->join('anak_asuh','peserta_didik.id','=','anak_asuh.id_peserta_didik')
        ->join('grup_wali_asuh','anak_asuh.id_grup_wali_asuh','=','grup_wali_asuh.id')
        ->join('desa','biodata.id_desa','=','desa.id')
        ->join('kecamatan','desa.id_kecamatan','=','kecamatan.id')
        ->join('kabupaten','kecamatan.id_kabupaten','=','kabupaten.id')
        ->select(
            'anak_asuh.id as id_anak_asuh',
            'biodata.nama',
            'peserta_didik.nis',
            DB::raw('YEAR(peserta_didik.tahun_masuk) as angkatan'),
            'kabupaten.nama_kabupaten',
            'anak_asuh.status',
            'biodata.image_url'
        )
        ->get();

        return response()->json([
            'status' => true,
            'message' => 'Data berhasil ditampilkan',
            'data' => $anakAsuh
        ]);
    }
}
