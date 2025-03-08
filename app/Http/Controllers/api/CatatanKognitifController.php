<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PdResource;
use App\Models\Catatan_kognitif;
use Database\Seeders\CatatanKognitifSeeder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CatatanKognitifController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $CatatanKognitif = Catatan_kognitif::all();
        return new PdResource(true,'Data Berhasil Ditampilkan',$CatatanKognitif);
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'id_peserta_didik' => 'required|exists:peserta_didik,id',
            'id_wali_asuh' => 'required|exists:wali_asuh,id',
            'kebahasaan_nilai' => 'required|in:A,B,C,D,E',
            'kebahasaan_tindak_lanjut' => 'nullable|string',
            'baca_kitab_kuning_nilai' => 'required|in:A,B,C,D,E',
            'baca_kitab_kuning_tindak_lanjut' => 'nullable|string',
            'hafalan_tahfidz_nilai' => 'required|in:A,B,C,D,E',
            'hafalan_tahfidz_tindak_lanjut' => 'nullable|string',
            'furudul_ainiyah_nilai' => 'required|in:A,B,C,D,E',
            'furudul_ainiyah_tindak_lanjut' => 'nullable|string',
            'tulis_alquran_nilai' => 'required|in:A,B,C,D,E',
            'tulis_alquran_tindak_lanjut' => 'nullable|string',
            'baca_alquran_nilai' => 'required|in:A,B,C,D,E',
            'baca_alquran_tindak_lanjut' => 'nullable|string',
        ]);
        if ($validator->fails()){
            return response()->json([
                'status' => false,
                'message' => 'Data Gagal Ditambahkan',
                'data' => $validator->errors()
            ]);
        }
        $CatatanKognitif = Catatan_kognitif::create($validator->validated());
        return new PdResource(true,'Data berhasil ditambahkan',$CatatanKognitif);
    }

    public function show(string $id)
    {
        $CatatanKognitif = Catatan_kognitif::findOrFail($id);
        return new PdResource(true, 'Detail data', $CatatanKognitif);
    }

    public function update(Request $request, string $id)
    {
        $CatatanKognitif = Catatan_kognitif::findOrFail($id);

        
        $validator = Validator::make($request->all(),[
            'id_peserta_didik' => 'required|exists:peserta_didik,id',
            'id_wali_asuh' => 'required|exists:wali_asuh,id',
            'kebahasaan_nilai' => 'required|in:A,B,C,D,E',
            'kebahasaan_tindak_lanjut' => 'nullable|string',
            'baca_kitab_kuning_nilai' => 'required|in:A,B,C,D,E',
            'baca_kitab_kuning_tindak_lanjut' => 'nullable|string',
            'hafalan_tahfidz_nilai' => 'required|in:A,B,C,D,E',
            'hafalan_tahfidz_tindak_lanjut' => 'nullable|string',
            'furudul_ainiyah_nilai' => 'required|in:A,B,C,D,E',
            'furudul_ainiyah_tindak_lanjut' => 'nullable|string',
            'tulis_alquran_nilai' => 'required|in:A,B,C,D,E',
            'tulis_alquran_tindak_lanjut' => 'nullable|string',
            'baca_alquran_nilai' => 'required|in:A,B,C,D,E',
            'baca_alquran_tindak_lanjut' => 'nullable|string',
        ]);
        if ($validator->fails()){
            return response()->json([
                'status' => false,
                'message' => 'Data Gagal Ditambahkan',
                'data' => $validator->errors()
            ]);
        }
        $CatatanKognitif->update($validator->validated());
        return new PdResource(true, 'Data berhasil diupdate', $CatatanKognitif);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $CatatanKognitif = Catatan_kognitif::findOrFail($id);
        $CatatanKognitif->delete();
        return new PdResource(true, 'Data berhasil di hapus', $CatatanKognitif);

    }
}
