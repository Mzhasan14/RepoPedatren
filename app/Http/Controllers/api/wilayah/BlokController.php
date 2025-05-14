<?php

namespace App\Http\Controllers\api\wilayah;

use Illuminate\Http\Request;
use App\Models\Kewilayahan\Blok;
use App\Http\Resources\PdResource;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class BlokController extends Controller
{
    public function index()
    {
        $bloks = Blok::with('wilayah')->where('status', true)->get();
        return response()->json($bloks);
    }

    public function show($id)
    {
        $blok = Blok::with('wilayah')->findOrFail($id);
        return response()->json($blok);
    }

    public function store(Request $request)
    {
        $request->validate([
            'wilayah_id' => 'required|exists:wilayah,id',
            'nama_blok'  => 'required|string|max:255',
        ]);

        $blok = Blok::create([
            'wilayah_id' => $request->wilayah_id,
            'nama_blok'  => $request->nama_blok,
            'created_by' => Auth::id(),
        ]);

        return response()->json($blok, 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'wilayah_id' => 'sometimes|required|exists:wilayah,id',
            'nama_blok'  => 'sometimes|required|string|max:255',
            'status'     => 'sometimes|required|boolean',
        ]);

        $blok = Blok::findOrFail($id);
        $blok->fill($request->only('wilayah_id', 'nama_blok', 'status'));
        $blok->updated_by = Auth::id();
        $blok->save();

        return response()->json($blok);
    }

    public function destroy($id)
    {
        $blok = Blok::findOrFail($id);
        $blok->deleted_by = Auth::id();
        $blok->save();
        $blok->delete();

        return response()->json(null, 204);
    }
}
