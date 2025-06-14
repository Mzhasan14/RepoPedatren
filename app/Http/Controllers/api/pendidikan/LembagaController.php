<?php

namespace App\Http\Controllers\api\pendidikan;

use App\Http\Controllers\Controller;
use App\Models\Pendidikan\Lembaga;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LembagaController extends Controller
{
    public function index()
    {
        $lembagas = Lembaga::where('status', true)->get();

        return response()->json($lembagas);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_lembaga' => 'required|string|max:255',
        ]);

        $lembaga = Lembaga::create([
            'nama_lembaga' => $request->nama_lembaga,
            'created_by' => Auth::id(),
        ]);

        return response()->json($lembaga, 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_lembaga' => 'sometimes|required|string|max:255',
            'status' => 'sometimes|required|boolean',
        ]);

        $lembaga = Lembaga::findOrFail($id);
        $lembaga->fill($request->only('nama_lembaga', 'status'));
        $lembaga->updated_by = Auth::id();
        $lembaga->save();

        return response()->json($lembaga);
    }

    public function destroy($id)
    {
        $lembaga = Lembaga::findOrFail($id);
        $lembaga->deleted_by = Auth::id();
        $lembaga->save();
        $lembaga->delete();

        return response()->json(null, 204);
    }
}
