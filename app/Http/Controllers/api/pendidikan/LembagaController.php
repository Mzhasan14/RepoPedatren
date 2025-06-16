<?php

namespace App\Http\Controllers\api\pendidikan;

use App\Http\Controllers\Controller;
use App\Models\Pendidikan\Lembaga;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LembagaController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 25);
        $status = $request->get('status', 'aktif');

        $lembaga = Lembaga::where('status', $status === 'aktif')
            ->select('id', 'nama_lembaga', 'status')
            ->paginate($perPage);

        $lembaga->getCollection()->transform(function ($item) {
            return [
                'id' => $item->id,
                'nama_lembaga' => $item->nama_lembaga,
                'status' => $item->status,
            ];
        });

        if ($lembaga->total() == 0) {
            return response()->json([
                'status' => 'success',
                'message' => 'Data kosong',
                'data' => [],
            ]);
        }

        return response()->json($lembaga);
    }

    public function show($id)
    {
        $lembaga = Lembaga::with([
            'jurusan.kelas.rombel',
            'pendidikan',
        ])->findOrFail($id);

        // Hitung total jurusan
        $totalJurusan = $lembaga->jurusan->count();

        // Hitung total kelas
        $totalKelas = $lembaga->jurusan->sum(function ($jurusan) {
            return $jurusan->kelas->count();
        });

        // Hitung total rombel
        $totalRombel = $lembaga->jurusan->sum(function ($jurusan) {
            return $jurusan->kelas->sum(function ($kelas) {
                return $kelas->rombel->count();
            });
        });

        // Hitung total siswa
        $totalSiswa = $lembaga->pendidikan->count();

        return response()->json([
            'id' => $lembaga->id,
            'nama_lembaga' => $lembaga->nama_lembaga,
            'status' => $lembaga->status,
            'total_jurusan' => $totalJurusan,
            'total_kelas' => $totalKelas,
            'total_rombel' => $totalRombel,
            'total_siswa' => $totalSiswa,
        ]);
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

        // Hitung jumlah data pelajar aktif
        $jumlahPelajarAktif = $lembaga->pendidikan()->where('status', 'aktif')->count();

        if ($jumlahPelajarAktif > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Lembaga tidak dapat dinonaktifkan karena masih ada ' . $jumlahPelajarAktif . ' data pelajar aktif.',
            ], 400);
        }

        $lembaga->updated_by = Auth::id();
        $lembaga->updated_at = now();
        $lembaga->status = false;
        $lembaga->save();

        return response()->json([
            'success' => true,
            'message' => 'Data lembaga berhasil dinonaktifkan.',
            'data' => $lembaga
        ], 200);
    }

    public function activate($id)
    {
        $lembaga = Lembaga::findOrFail($id);

        $lembaga->updated_by = Auth::id();
        $lembaga->updated_at = now();
        $lembaga->status = true;
        $lembaga->save();

        return response()->json([
            'success' => true,
            'message' => 'Data lembaga berhasil diaktifkan kembali.',
            'data' => $lembaga
        ], 200);
    }
}
