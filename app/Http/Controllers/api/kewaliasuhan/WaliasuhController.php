<?php

namespace App\Http\Controllers\api\kewaliasuhan;

use Illuminate\Http\Request;
use App\Models\Peserta_didik;
use App\Http\Resources\PdResource;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Kewaliasuhan\Wali_asuh;
use App\Models\Santri;
use Database\Seeders\PesertaDidikSeeder;
use Illuminate\Support\Facades\Validator;

class WaliasuhController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
         $waliAsuh = Wali_asuh::Active()->latest()->paginate(5);
        return new PdResource(true, 'list wali asuh', $waliAsuh);
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

        $waliAsuh = Wali_asuh::create($validator->validated());

        return new PdResource(true, 'Data berhasil ditambah', $waliAsuh);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $waliAsuh = Wali_asuh::findOrFail($id);
        return new PdResource(true, 'Detail data', $waliAsuh);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $waliAsuh = Wali_asuh::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'id_peserta_didik' => 'required|exists:peserta_didik,id',
            'id_grup_wali_asuh' => 'required|exists:grup_wali_asuh,id',
            'updated_by' => 'nullable',
            'status' => 'nullable'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $waliAsuh->update($request->validated());
        return new PdResource(true, 'Data berhasil diubah', $waliAsuh);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $waliAsuh = Wali_asuh::findOrFail($id);
        $waliAsuh->delete();
        return new PdResource(true,'Data berhasil dihapus',null);
    }

    public function waliAsuh() {
        $waliAsuh = Santri::join('wali_asuh','santri.nis','=','wali_asuh.nis')
        ->join('peserta_didik','santri.id_peserta_didik','=','peserta_didik.id')
        ->join('biodata','peserta_didik.id_biodata','=','biodata.id')
        ->join('grup_wali_asuh','grup_wali_asuh.id','=','wali_asuh.id_grup_wali_asuh')
        ->join('kamar','santri.id_kamar','=','kamar.id')
        ->join('blok','santri.id_blok','=','blok.id')
        ->join('wilayah','santri.id_wilayah','=','wilayah.id')
        // ->join('desa', 'biodata.id_desa', '=', 'desa.id')
        // ->join('kecamatan', 'desa.id_kecamatan', '=', 'kecamatan.id')
        ->leftjoin('berkas', 'biodata.id','=','berkas.id_biodata')
        ->leftjoin('jenis_berkas','berkas.id_jenis_berkas','=','jenis_berkas.id')
        ->join('kabupaten', 'biodata.id_kabupaten', '=', 'kabupaten.id')
        ->select(
            'wali_asuh.id as id_wali_asuh',
            'berkas.file_path as foto_profile',
            'biodata.nama',
            'santri.nis',
            'kamar.nama_kamar',
            'blok.nama_blok',
            'wilayah.nama_wilayah',
            'kabupaten.nama_kabupaten',
            DB::raw('YEAR(santri.tanggal_masuk) as angkatan'),
            'wali_asuh.updated_at as Tanggal_update',
            'wali_asuh.created_at as Tanggal_input'
        )
        ->get();

        return new PdResource(true, 'List data wali asuh', $waliAsuh);
    }
}
