<?php

namespace App\Http\Controllers\api\wilayah;

use Illuminate\Http\Request;
use App\Models\Kewilayahan\Kamar;
use App\Http\Resources\PdResource;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class KamarController extends Controller
{
    public function index()
    {
        $kamars = Kamar::with('blok')->where('status', true)->get();
        return response()->json($kamars);
    }

    public function show($id)
    {
        $kamar = Kamar::with('blok')->findOrFail($id);
        return response()->json($kamar);
    }

    public function store(Request $request)
    {
        $request->validate([
            'blok_id'    => 'required|exists:blok,id',
            'nama_kamar' => 'required|string|max:255',
        ]);

        $kamar = Kamar::create([
            'blok_id'    => $request->blok_id,
            'nama_kamar' => $request->nama_kamar,
            'created_by' => Auth::id(),
        ]);

        return response()->json($kamar, 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'blok_id'    => 'sometimes|required|exists:blok,id',
            'nama_kamar' => 'sometimes|required|string|max:255',
            'status'     => 'sometimes|required|boolean',
        ]);

        $kamar = Kamar::findOrFail($id);
        $kamar->fill($request->only('blok_id', 'nama_kamar', 'status'));
        $kamar->updated_by = Auth::id();
        $kamar->save();

        return response()->json($kamar);
    }

    public function destroy($id)
    {
        $kamar = Kamar::findOrFail($id);
        $kamar->deleted_by = Auth::id();
        $kamar->save();
        $kamar->delete();

        return response()->json(null, 204);
    }
}
