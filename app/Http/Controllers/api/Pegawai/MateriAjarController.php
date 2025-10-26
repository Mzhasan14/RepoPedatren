<?php

namespace App\Http\Controllers\api\Pegawai;

use App\Http\Controllers\Controller;
use App\Http\Resources\PdResource;
use App\Models\Pegawai\MateriAjar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MateriAjarController extends Controller
{
    public function index()
    {
        $materiAjar = MateriAjar::all();

        return new PdResource(true, 'Data berhasil ditampilkan', $materiAjar);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_pengajar' => ['required', 'exists:pengajar,id'],
            'nama_materi' => ['required', 'string', 'max:255'],
            'jumlah_menit' => ['nullable', 'integer', 'min:1'],
            'created_by' => ['required', 'integer'],
            'status' => ['required', 'boolean'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Data gagal di tambahkan',
                'data' => $validator->errors(),
            ]);
        }

        $materiAjar = MateriAjar::create($validator->validated());

        return new PdResource(true, 'Data berehasil ditambahkan', $materiAjar);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $materiAjar = MateriAjar::findOrFail($id);

        return new PdResource(true, 'Data berhasil Ditampilkan', $materiAjar);
    }

    public function update(Request $request, string $id)
    {
        $materiAjar = MateriAjar::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'id_pengajar' => ['required', 'exists:pengajar,id'],
            'nama_materi' => ['required', 'string', 'max:255'],
            'jumlah_menit' => ['nullable', 'integer', 'min:1'],
            'updated_by' => ['nullable', 'integer'],
            'status' => ['required', 'boolean'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Data gagal di tambahkan',
                'data' => $validator->errors(),
            ]);
        }
        $materiAjar->update($validator->validated());

        return new PdResource(true, 'Data berhasil di update', $materiAjar);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $materiAjar = MateriAjar::findOrFail($id);
        $materiAjar->delete();

        return new PdResource(true, 'Data berhasil dihapus', $materiAjar);
    }
}
