<?php

namespace App\Http\Controllers\api\wilayah;


use Illuminate\Http\Request;
use App\Models\Peserta_didik;
use App\Http\Resources\PdResource;
use App\Models\Kewilayahan\Wilayah;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class WilayahController extends Controller
{
    public function index()
    {
        $wilayah = Wilayah::where('status', true)->get();
        return response()->json($wilayah);
    }

    public function show($id)
    {
        $wilayah = Wilayah::findOrFail($id);
        return response()->json($wilayah);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_wilayah' => 'required|string|max:255',
        ]);

        $wilayah = Wilayah::create([
            'nama_wilayah' => $request->nama_wilayah,
            'created_by'  => Auth::id(),
        ]);

        return response()->json($wilayah, 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_wilayah' => 'sometimes|required|string|max:255',
            'status'       => 'sometimes|required|boolean',
        ]);

        $wilayah = Wilayah::findOrFail($id);
        $wilayah->fill($request->only('nama_wilayah', 'status'));
        $wilayah->updated_by = Auth::id();
        $wilayah->save();

        return response()->json($wilayah);
    }

    public function destroy($id)
    {
        $wilayah = Wilayah::findOrFail($id);
        $wilayah->deleted_by = Auth::id();
        $wilayah->save();
        $wilayah->delete();

        return response()->json(null, 204);
    }
}
