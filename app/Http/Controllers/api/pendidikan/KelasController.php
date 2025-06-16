<?php

namespace App\Http\Controllers\api\pendidikan;

use App\Http\Controllers\Controller;
use App\Models\Pendidikan\Kelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KelasController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 25);
        $status = $request->get('status', 'aktif');

        $kelas = Kelas::with('jurusan.lembaga:id,nama_lembaga')
            ->where('status', $status === 'aktif')
            ->select('id', 'nama_kelas', 'jurusan_id', 'status')
            ->paginate($perPage);

        $kelas->getCollection()->transform(function ($item) {
            return [
                'id' => $item->id,
                'nama_kelas' => $item->nama_kelas,
                'jurusan' => $item->jurusan ? $item->jurusan->nama_jurusan : null,
                'lembaga' => $item->jurusan && $item->jurusan->lembaga ? $item->jurusan->lembaga->nama_lembaga : null,
                'status' => $item->status,
            ];
        });

        if ($kelas->total() == 0) {
            return response()->json([
                'status' => 'success',
                'message' => 'Data kosong',
                'data' => [],
            ]);
        }

        return response()->json($kelas);
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

        $jumlahPelajarAktif = $kelas->pendidikan()->where('status', 'aktif')->count();

        if ($jumlahPelajarAktif > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Kelas tidak dapat dinonaktifkan karena masih ada ' . $jumlahPelajarAktif . ' data pelajar aktif.',
            ], 400);
        }

        $kelas->updated_by = Auth::id();
        $kelas->updated_at = now();
        $kelas->status = false;
        $kelas->save();

        return response()->json([
            'success' => true,
            'message' => 'Data kelas berhasil dinonaktifkan.',
            'data' => $kelas
        ], 200);
    }

    public function activate($id)
    {
        $kelas = Kelas::findOrFail($id);

        $kelas->updated_by = Auth::id();
        $kelas->updated_at = now();
        $kelas->status = true;
        $kelas->save();

        return response()->json([
            'success' => true,
            'message' => 'Data kelas berhasil diaktifkan kembali.',
            'data' => $kelas
        ], 200);
    }
}
