<?php

namespace App\Http\Controllers\api\pendidikan;

use Illuminate\Http\Request;
use App\Models\Pendidikan\Rombel;
use App\Http\Resources\PdResource;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class RombelController extends Controller
{
    public function index()
    {
        $rombels = Rombel::with('kelas')->where('status', true)->get();
        return response()->json($rombels);
    }

    public function show($id)
    {
        $rombel = Rombel::with('kelas')->findOrFail($id);
        return response()->json($rombel);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_rombel'   => 'required|string|max:255',
            'gender_rombel' => 'required|in:putra,putri',
            'kelas_id'      => 'required|exists:kelas,id',
        ]);

        $rombel = Rombel::create([
            'nama_rombel'   => $request->nama_rombel,
            'gender_rombel' => $request->gender_rombel,
            'kelas_id'      => $request->kelas_id,
            'created_by'    => Auth::id(),
        ]);

        return response()->json($rombel, 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_rombel'   => 'sometimes|required|string|max:255',
            'gender_rombel' => 'sometimes|required|in:putra,putri',
            'kelas_id'      => 'sometimes|required|exists:kelas,id',
            'status'        => 'sometimes|required|boolean',
        ]);

        $rombel = Rombel::findOrFail($id);
        $rombel->fill($request->only('nama_rombel', 'gender_rombel', 'kelas_id', 'status'));
        $rombel->updated_by = Auth::id();
        $rombel->save();

        return response()->json($rombel);
    }

    public function destroy($id)
    {
        $rombel = Rombel::findOrFail($id);
        $rombel->deleted_by = Auth::id();
        $rombel->save();
        $rombel->delete();

        return response()->json(null, 204);
    }
}
