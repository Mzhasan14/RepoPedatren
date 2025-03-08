<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PdResource;
use App\Models\Catatan_afektif;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CatatanAfektifController extends Controller
{

    public function index()
    {
        $CatatanAfektif = Catatan_afektif::all();
        return new PdResource(true,'Data Berhasil Ditampilkan',$CatatanAfektif);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'id_peserta_didik' => 'required|exists:peserta_didik,id',
            'id_wali_asuh' => 'required|exists:wali_asuh,id',
            'kepedulian_nilai' => 'required|in:A,B,C,D,E',
            'kepedulian_tindak_lanjut' => 'required|string',
            'kebersihan_nilai' => 'required|in:A,B,C,D,E',
            'kebersihan_tindak_lanjut' => 'required|string',
            'akhlak_nilai' => 'required|in:A,B,C,D,E',
            'akhlak_tindak_lanjut' => 'required|string',
            'created_by' => 'required|integer',
            'status' => 'required|boolean',
        ]);
        if ($validator->fails()){
            return response()->json([
                'status' => false,
                'message' => 'Data Gagal Ditambahkan',
                'data' => $validator->errors()
            ]);
        }
        $CatatanAfektif = Catatan_afektif::create($validator->validated());
        return new PdResource(true,'Data berhasil ditambahkan',$CatatanAfektif);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $CatatanAfektif = Catatan_afektif::findOrFail($id);
        return new PdResource(true, 'Detail data', $CatatanAfektif);
    }

    public function update(Request $request, string $id)
    {
        $CatatanAfektif = Catatan_afektif::findOrFail($id);

        $validator = Validator::make($request->all(),[
            'id_peserta_didik' => 'required|exists:peserta_didik,id',
            'id_wali_asuh' => 'required|exists:wali_asuh,id',
            'kepedulian_nilai' => 'required|in:A,B,C,D,E',
            'kepedulian_tindak_lanjut' => 'required|string',
            'kebersihan_nilai' => 'required|in:A,B,C,D,E',
            'kebersihan_tindak_lanjut' => 'required|string',
            'akhlak_nilai' => 'required|in:A,B,C,D,E',
            'akhlak_tindak_lanjut' => 'required|string',
            'updated_by' => 'nullable|integer',
            'status' => 'required|boolean',
        ]);
        if ($validator->fails()){
            return response()->json([
                'status' => false,
                'message' => 'Data Gagal Ditambahkan',
                'data' => $validator->errors()
            ]);
        }
        $CatatanAfektif->update($validator->validated());
        return new PdResource(true, 'Data berhasil di Update', $CatatanAfektif);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $CatatanAfektif = Catatan_afektif::findOrFail($id);
        $CatatanAfektif->delete();
        return new PdResource(true, 'Data berhasil di hapus', $CatatanAfektif);

    }
}
