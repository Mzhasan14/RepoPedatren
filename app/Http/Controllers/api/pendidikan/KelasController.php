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
        $kelases = Kelas::with(['jurusan.lembaga'])
            ->where('status', true)
            ->get(['id', 'nama_kelas', 'jurusan_id', 'status'])
            ->map(function ($kelas) {
                return [
                    'id' => $kelas->id,
                    'nama_kelas' => $kelas->nama_kelas,
                    'nama_jurusan' => $kelas->jurusan ? $kelas->jurusan->nama_jurusan : null,
                    'nama_lembaga' => $kelas->jurusan && $kelas->jurusan->lembaga ? $kelas->jurusan->lembaga->nama_lembaga : null,
                    'status' => $kelas->status,
                ];
            });

        return response()->json($kelases);
    }

    public function show($id)
    {
        $kelas = Kelas::with([
            'jurusan.lembaga',
            'rombel',
            'pendidikan'
        ])->findOrFail($id);

        $totalRombel = $kelas->rombel->count();
        $totalSiswa = $kelas->pendidikan->count();

        return response()->json([
            'id' => $kelas->id,
            'nama_kelas' => $kelas->nama_kelas,
            'status' => $kelas->status,
            'nama_jurusan' => $kelas->jurusan ? $kelas->jurusan->nama_jurusan : null,
            'nama_lembaga' => $kelas->jurusan && $kelas->jurusan->lembaga ? $kelas->jurusan->lembaga->nama_lembaga : null,
            'total_rombel' => $totalRombel,
            'total_siswa' => $totalSiswa,
        ]);
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
        $kelas->updated_by = Auth::id();
        $kelas->updated_at = now();
        $kelas->status = false;
        $kelas->save();
        $kelas->delete();

        return response()->json(null, 204);
    }
}
