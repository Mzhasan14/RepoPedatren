<?php

namespace App\Http\Controllers\api\pendidikan;

use App\Http\Controllers\Controller;
use App\Models\Pendidikan\Kelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KelasController extends Controller
{
    public function index()
    {
        $kelases = Kelas::with('jurusan')->where('status', true)->get();

        return response()->json($kelases);
    }

    public function show($id)
    {
        $kelas = Kelas::with('jurusan')->findOrFail($id);

        return response()->json($kelas);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_kelas' => 'required|string|max:255',
            'jurusan_id' => 'required|exists:jurusan,id',
        ]);

        $kelas = Kelas::create([
            'nama_kelas' => $request->nama_kelas,
            'jurusan_id' => $request->jurusan_id,
            'created_by' => Auth::id(),
        ]);

        return response()->json($kelas, 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_kelas' => 'sometimes|required|string|max:255',
            'jurusan_id' => 'sometimes|required|exists:jurusan,id',
            'status' => 'sometimes|required|boolean',
        ]);

        $kelas = Kelas::findOrFail($id);
        $kelas->fill($request->only('nama_kelas', 'jurusan_id', 'status'));
        $kelas->updated_by = Auth::id();
        $kelas->save();

        return response()->json($kelas);
    }

    public function destroy($id)
    {
        $kelas = Kelas::findOrFail($id);
        $kelas->deleted_by = Auth::id();
        $kelas->save();
        $kelas->delete();

        return response()->json(null, 204);
    }
}
