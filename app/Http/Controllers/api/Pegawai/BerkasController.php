<?php

namespace App\Http\Controllers\Api\Pegawai;

use App\Http\Controllers\Controller;
use App\Http\Resources\PdResource;
use App\Models\Biodata;
use App\Models\Pegawai\Berkas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BerkasController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $berkas = Berkas::all();
        return new PdResource(true,'Data berhasil ditampilkan',$berkas);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'id_jenis_berkas' => 'required|exists:jenis_berkas,id',
            'file_path' => 'required|string',
            'created_by' => 'required|integer',
            'status' => 'required|boolean',
        ]);

        if ($validator->fails()){
            return response()->json([
                'status' => false,
                'message' => 'Data gagal ditammbahkan',
                'data' => $validator->errors()
            ]);
        }

        $berkas = Berkas::create($validator->validated());
        return new PdResource(true,'Data berhasil ditambahkan',$berkas);
    }

    public function show(string $id)
    {
        $berkas = Berkas::findOrFail($id);
        return new PdResource(true,'Data berhasil ditampilkan',$berkas);
    }

    public function update(Request $request, string $id)
    {
        $berkas = Berkas::findOrFail($id);
        $validator = Validator::make($request->all(),[
            'id_jenis_berkas' => 'required|exists:jenis_berkas,id',
            'file_path' => 'required|string',
            'created_by' => 'required|integer',
            'status' => 'required|boolean',
        ]);

        if ($validator->fails()){
            return response()->json([
                'status' => false,
                'message' => 'Data gagal ditammbahkan',
                'data' => $validator->errors()
            ]);
        }

        $berkas->update($validator->validated());
        return new PdResource(true,'Data berhasil diperbarui',$berkas);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $berkas = Berkas::findOrFail($id);
        $berkas->delete();
        return new PdResource(true,'Data berhasil dihapus',$berkas);
    }

    public function Berkas()
    {
        $biodata = Biodata::join('berkas as b1','biodata.id', '=' , 'b1.id_biodata')
                        ->join('jenis_berkas as jb','b1.id_jenis_berkas','=','jb.id')
                        ->select('biodata.id', 'jb.type_jenis_berkas as type','jb.nama_jenis_berkas as nama berkas','b1.file_path as foto')
                        ->get();

        return new PdResource(true,'data berhasil ditambahkan',$biodata);
    }
}
