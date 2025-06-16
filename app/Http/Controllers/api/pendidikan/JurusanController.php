<?php

namespace App\Http\Controllers\api\pendidikan;

use App\Http\Controllers\Controller;
use App\Models\Pendidikan\Jurusan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JurusanController extends Controller
{
    public function index()
    {
        $jurusans = Jurusan::with('lembaga:id,nama_lembaga')->where('status', true)
            ->get(['id', 'nama_jurusan', 'status', 'lembaga_id'])
            ->map(function ($jurusan) {
                return [
                    'id' => $jurusan->id,
                    'nama_jurusan' => $jurusan->nama_jurusan,
                    'status'       => $jurusan->status,
                    'nama_lembaga' => $jurusan->lembaga ? $jurusan->lembaga->nama_lembaga : null
                ];
            });

        return response()->json($jurusans);
    }

    public function show($id)
    {
        $jurusan = Jurusan::with([
            'lembaga',
            'kelas.rombel',
            'kelas.pendidikan', // relasi siswa
        ])->findOrFail($id);

        // Hitung total kelas
        $totalKelas = $jurusan->kelas->count();

        // Hitung total rombel
        $totalRombel = $jurusan->kelas->sum(function ($kelas) {
            return $kelas->rombel->count();
        });

        // Hitung total siswa (dari relasi pendidikan di kelas)
        $totalSiswa = $jurusan->kelas->sum(function ($kelas) {
            return $kelas->pendidikan->count();
        });

        return response()->json([
            'id' => $jurusan->id,
            'nama_jurusan' => $jurusan->nama_jurusan,
            'status' => $jurusan->status,
            'nama_lembaga' => $jurusan->lembaga ? $jurusan->lembaga->nama_lembaga : null,
            'total_kelas' => $totalKelas,
            'total_rombel' => $totalRombel,
            'total_siswa' => $totalSiswa,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_jurusan' => 'required|string|max:255',
            'lembaga_id' => 'required|exists:lembaga,id',
        ]);

        $jurusan = Jurusan::create([
            'nama_jurusan' => $request->nama_jurusan,
            'lembaga_id' => $request->lembaga_id,
            'created_by' => Auth::id(),
        ]);

        return response()->json($jurusan, 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_jurusan' => 'sometimes|required|string|max:255',
            'lembaga_id' => 'sometimes|required|exists:lembaga,id',
            'status' => 'sometimes|required|boolean',
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

        $jumlahPelajarAktif = $jurusan->pendidikan()->where('status', 'aktif')->count();

        if ($jumlahPelajarAktif > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Jurusan tidak dapat dinonaktifkan karena masih ada ' . $jumlahPelajarAktif . ' data pelajar aktif.',
            ], 400);
        }

        $jurusan->updated_by = Auth::id();
        $jurusan->updated_at = now();
        $jurusan->status = false;
        $jurusan->save();

        return response()->json([
            'success' => true,
            'message' => 'Data jurusan berhasil dinonaktifkan.',
            'data' => $jurusan
        ], 200);
    }

    public function activate($id)
    {
        $jurusan = Jurusan::findOrFail($id);

        $jurusan->updated_by = Auth::id();
        $jurusan->updated_at = now();
        $jurusan->status = true;
        $jurusan->save();

        return response()->json([
            'success' => true,
            'message' => 'Data jurusan berhasil diaktifkan kembali.',
            'data' => $jurusan
        ], 200);
    }
}
