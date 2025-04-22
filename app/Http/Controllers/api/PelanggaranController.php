<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PdResource;
use App\Models\Pelanggaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PelanggaranController extends Controller
{
    // public function index()
    // {
    //     $pelanggaran = Pelanggaran::all();
    //     return new PdResource(true,'Data berhasil ditampilkan',$pelanggaran);
    // }

    // public function store(Request $request)
    // {
    //     $validator = Validator::make($request->all(),[
    //         'id_peserta_didik' => ['required', 'integer', 'exists:peserta_didik,id'],
    //         'status_pelanggaran' => ['required', Rule::in(['Belum diproses', 'Sedang diproses', 'Sudah diproses'])],
    //         'jenis_putusan' => ['required', Rule::in(['Belum ada putusan', 'Disanksi', 'Dibebaskan'])],
    //         'jenis_pelanggaran' => ['required', Rule::in(['Ringan', 'Sedang', 'Berat'])],
    //         'keterangan' => 'required|string|max:1000',
    //         'created_by' => 'required|integer',
    //         'status' => 'required|boolean',
    //     ]);

    //     if ($validator->fails()){
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Data Gagal ditambahkan',
    //             'data' => $validator->errors()
    //         ]);
    //     }

    //     $pelanggaran = Pelanggaran::create($validator->validated());
    //     return new PdResource(true,'Data berhasil di tampilkan',$pelanggaran);
    // }

    // public function show(string $id)
    // {
    //     $pelanggaran = Pelanggaran::findOrFail($id);
    //     return new PdResource(true,'Data berhasil ditampilkan',$pelanggaran);
    // }
    // public function update(Request $request, string $id)
    // {
    //     $pelanggaran = Pelanggaran::findOrFail($id);
    //     $validator = Validator::make($request->all(),[
    //         'id_peserta_didik' => [
    //             'required', 
    //             'integer', 
    //             Rule::exists('peserta_didik', 'id'),
    //         ],
    //     'status_pelanggaran' => ['required', Rule::in(['Belum diproses', 'Sedang diproses', 'Sudah diproses'])],
    //     'jenis_putusan' => ['required', Rule::in(['Belum ada putusan', 'Disanksi', 'Dibebaskan'])],
    //     'jenis_pelanggaran' => ['required', Rule::in(['Ringan', 'Sedang', 'Berat'])],
    //     'keterangan' => 'required|string|max:1000',
    //     'status' => 'required|boolean',
    //     'updated_by' => 'nullable|integer',
    //     ]);

    //     if ($validator->fails()){
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Data Gagal ditambahkan',
    //             'data' => $validator->errors()
    //         ]);
    //     }
    //     $pelanggaran->update($validator->validated());
    //     return new PdResource(true,'Data Berhasil Di update',$pelanggaran);
        
    // }

    // public function destroy(string $id)
    // {
    //     $pelanggaran = Pelanggaran::findOrFail($id);
    //     $pelanggaran->delete();
    //     return new PdResource(true,'Data berhasil dihapus',$pelanggaran);
    // }

    
}
