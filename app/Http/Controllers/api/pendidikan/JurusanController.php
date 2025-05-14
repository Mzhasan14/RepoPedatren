<?php

namespace App\Http\Controllers\api\pendidikan;

use Illuminate\Http\Request;
use App\Http\Resources\PdResource;
use App\Models\Pendidikan\Jurusan;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class JurusanController extends Controller
{
    public function index()
    {
        $jurusans = Jurusan::with('lembaga')->where('status', true)->get();
        return response()->json($jurusans);
    }

    public function show($id)
    {
        $jurusan = Jurusan::with('lembaga')->findOrFail($id);
        return response()->json($jurusan);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_jurusan' => 'required|string|max:255',
            'lembaga_id'   => 'required|exists:lembaga,id',
        ]);

        $jurusan = Jurusan::create([
            'nama_jurusan' => $request->nama_jurusan,
            'lembaga_id'   => $request->lembaga_id,
            'created_by'   => Auth::id(),
        ]);

        return response()->json($jurusan, 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_jurusan' => 'sometimes|required|string|max:255',
            'lembaga_id'   => 'sometimes|required|exists:lembaga,id',
            'status'       => 'sometimes|required|boolean',
        ]);

        $jurusan = Jurusan::findOrFail($id);
        $jurusan->fill($request->only('nama_jurusan', 'lembaga_id', 'status'));
        $jurusan->updated_by = Auth::id();
        $jurusan->save();

        return response()->json($jurusan);
    }

    public function destroy($id)
    {
        $jurusan = Jurusan::findOrFail($id);
        $jurusan->deleted_by = Auth::id();
        $jurusan->save();
        $jurusan->delete();

        return response()->json(null, 204);
    }
}
