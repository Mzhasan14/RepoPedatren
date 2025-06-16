<?php

namespace App\Http\Controllers\api\pendidikan;

use App\Http\Controllers\Controller;
use App\Models\Pendidikan\Rombel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RombelController extends Controller
{
    public function index()
    {
        $rombels = Rombel::with(['kelas.jurusan.lembaga'])
            ->where('status', true)
            ->get(['id', 'nama_rombel', 'gender_rombel', 'kelas_id', 'status'])
            ->map(function ($rombel) {
                return [
                    'id' => $rombel->id,
                    'nama_rombel' => $rombel->nama_rombel,
                    'gender_rombel' => $rombel->gender_rombel,
                    'nama_kelas' => $rombel->kelas ? $rombel->kelas->nama_kelas : null,
                    'nama_jurusan' => $rombel->kelas && $rombel->kelas->jurusan ? $rombel->kelas->jurusan->nama_jurusan : null,
                    'nama_lembaga' => $rombel->kelas && $rombel->kelas->jurusan && $rombel->kelas->jurusan->lembaga ? $rombel->kelas->jurusan->lembaga->nama_lembaga : null,
                    'status' => $rombel->status,
                ];
            });

        return response()->json($rombels);
    }

    public function show($id)
    {
        $rombel = Rombel::with([
            'kelas.jurusan.lembaga',
            'pendidikan'
        ])->findOrFail($id);

        $totalSiswa = $rombel->pendidikan->count();

        return response()->json([
            'id' => $rombel->id,
            'nama_rombel' => $rombel->nama_rombel,
            'gender_rombel' => $rombel->gender_rombel,
            'status' => $rombel->status,
            'nama_kelas' => $rombel->kelas ? $rombel->kelas->nama_kelas : null,
            'nama_jurusan' => $rombel->kelas && $rombel->kelas->jurusan ? $rombel->kelas->jurusan->nama_jurusan : null,
            'nama_lembaga' => $rombel->kelas && $rombel->kelas->jurusan && $rombel->kelas->jurusan->lembaga ? $rombel->kelas->jurusan->lembaga->nama_lembaga : null,
            'total_siswa' => $totalSiswa,
        ]);
    }


    public function store(Request $request)
    {
        $request->validate([
            'nama_rombel' => 'required|string|max:255',
            'gender_rombel' => 'required|in:putra,putri',
            'kelas_id' => 'required|exists:kelas,id',
        ]);

        $rombel = Rombel::create([
            'nama_rombel' => $request->nama_rombel,
            'gender_rombel' => $request->gender_rombel,
            'kelas_id' => $request->kelas_id,
            'created_by' => Auth::id(),
        ]);

        return response()->json($rombel, 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_rombel' => 'sometimes|required|string|max:255',
            'gender_rombel' => 'sometimes|required|in:putra,putri',
            'kelas_id' => 'sometimes|required|exists:kelas,id',
            'status' => 'sometimes|required|boolean',
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

        $jumlahPelajarAktif = $rombel->pendidikan()->where('status', 'aktif')->count();

        if ($jumlahPelajarAktif > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Rombel tidak dapat dinonaktifkan karena masih ada ' . $jumlahPelajarAktif . ' data pelajar aktif.',
            ], 400);
        }

        $rombel->updated_by = Auth::id();
        $rombel->updated_at = now();
        $rombel->status = false;
        $rombel->save();

        return response()->json([
            'success' => true,
            'message' => 'Data rombel berhasil dinonaktifkan.',
            'data' => $rombel
        ], 200);
    }

    public function activate($id)
    {
        $rombel = Rombel::findOrFail($id);

        $rombel->updated_by = Auth::id();
        $rombel->updated_at = now();
        $rombel->status = true;
        $rombel->save();

        return response()->json([
            'success' => true,
            'message' => 'Data rombel berhasil diaktifkan kembali.',
            'data' => $rombel
        ], 200);
    }
}
