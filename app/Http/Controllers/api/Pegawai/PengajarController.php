<?php

namespace App\Http\Controllers\Api\Pegawai;

use App\Http\Controllers\Controller;
use App\Http\Resources\PdResource;
use App\Models\Biodata;
use App\Models\Pegawai\Pengajar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PengajarController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $pengajar = Pengajar::all();
        return new PdResource(true,'Data berhasil ditampilkan',$pengajar);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'id_pegawai'   => 'required|integer',
            'id_golongan'  => 'required|integer',
            'id_lembaga'   => 'required|integer',
            'mapel'        => 'required|string|max:255',
            'created_by'   => 'required|integer',
            'updated_by'   => 'nullable|integer',
            'status'       => 'required|boolean',
        ]);

        if ($validator->fails()){
            return response()->json([
                'status' => false,
                'message' => 'Data gagal ditambahkan',
                'data' => $validator->errors()
            ]);
        }

        $pengajar = Pengajar::create($validator->validated());
        return new PdResource(true,'Data berhasil ditambahkan',$pengajar);
    }

    public function show(string $id)
    {
        $pengajar = Pengajar::findOrFail($id);
        return new PdResource(true,'Data berhasil ditampilkan',$pengajar);
    }
    public function update(Request $request, string $id)
    {
        $pengajar = Pengajar::findOrFail($id);
        $validator = Validator::make($request->all(),[
            'id_pegawai'   => 'required|integer',
            'id_golongan'  => 'required|integer',
            'id_lembaga'   => 'required|integer',
            'mapel'        => 'required|string|max:255',
            'created_by'   => 'required|integer',
            'updated_by'   => 'nullable|integer',
            'status'       => 'required|boolean',
        ]);

        if ($validator->fails()){
            return response()->json([
                'status' => false,
                'message' => 'Data gagal ditambahkan',
                'data' => $validator->errors()
            ]);
        }
        $pengajar->update($validator->validated());
        return new PdResource(true,'Data berhasil diupdate',$pengajar);
        
    }
    public function destroy(string $id)
    {
        $pengajar = Pengajar::findOrFail($id);
        $pengajar->delete();
        return new PdResource(true,'Data berhasil dihapus',$pengajar);
    }

    public function Pengajar()
    {
        $pengajar = Biodata::join('pegawai', 'biodata.id', '=', 'pegawai.id_biodata')
        ->join('pengajar', 'pegawai.id', '=', 'pengajar.id_pegawai')
        ->select(
            'pengajar.id as id_pengajar', 
            'biodata.nama', 
            'biodata.niup', 
            'biodata.nama_pendidikan_terakhir', 
            'biodata.image_url'
        )
        ->get();

    return response()->json([
        'status' => true,
        'message' => 'Data berhasil ditampilkan',
        'data' => $pengajar
    ]);

    }
}
