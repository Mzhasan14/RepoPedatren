<?php

namespace App\Http\Controllers\api\pendidikan;

use App\Http\Controllers\Controller;
use App\Models\Pendidikan\Jurusan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JurusanController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 25);
        $status = $request->get('status', 'aktif');

        $jurusan = Jurusan::with('lembaga:id,nama_lembaga')
            ->where('status', $status === 'aktif')
            ->select('id', 'nama_jurusan', 'lembaga_id', 'status')
            ->paginate($perPage);

        $jurusan->getCollection()->transform(function ($item) {
            return [
                'id' => $item->id,
                'nama_jurusan' => $item->nama_jurusan,
                'lembaga' => $item->lembaga ? $item->lembaga->nama_lembaga : null,
                'status' => $item->status,
            ];
        });

        // Cek jika data kosong
        if ($jurusan->total() == 0) {
            return response()->json([
                'status' => 'success',
                'message' => 'Data kosong',
                'data' => [],
            ]);
        }

        // Jika data ada, tetap pakai format paginasi
        return response()->json($jurusan);
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
        ]);

        $jurusan = Jurusan::findOrFail($id);
        $jurusan->fill($request->only('nama_jurusan'));
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
