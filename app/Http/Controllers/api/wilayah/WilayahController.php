<?php

namespace App\Http\Controllers\api\wilayah;

use App\Http\Controllers\Controller;
use App\Models\Kewilayahan\Wilayah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WilayahController extends Controller
{
    public function index(Request $request)
    {
        // Default 10 per halaman, bisa override via query param ?per_page=20
        $perPage = $request->get('per_page', 10);

        $wilayah = Wilayah::where('status', true)
            ->select('id', 'nama_wilayah', 'kategori', 'status')
            ->paginate($perPage);

        return response()->json($wilayah);
    }


    public function show($id)
    {
        $w = Wilayah::with(['blok.kamar' => function ($q) {
            $q->where('status', true);
        }])->findOrFail($id);

        $totalBlok = $w->blok->count();
        $totalKamar = $w->blok->flatMap->kamar->count();
        $totalSlot = $w->blok->flatMap->kamar->sum('kapasitas');
        $totalPenghuni = $w->blok->flatMap->kamar->sum(function ($kamar) {
            return \App\Models\DomisiliSantri::where('kamar_id', $kamar->id)
                ->where('status', 'aktif')->count();
        });

        $result = [
            'id' => $w->id,
            'nama_wilayah' => $w->nama_wilayah,
            'kategori' => $w->kategori,
            'status' => $w->status,
            'total_blok' => $totalBlok,
            'total_kamar' => $totalKamar,
            'total_slot' => $totalSlot,
            'total_penghuni' => $totalPenghuni,
        ];

        return response()->json($result);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_wilayah' => 'required|string|max:255|unique:wilayah,nama_wilayah',
            'kategori'     => 'nullable|in:putra,putri',
        ], [
            'nama_wilayah.unique' => 'Nama wilayah sudah digunakan.',
            'kategori.in' => 'Kategori hanya boleh putra atau putri.',
        ]);

        $wilayah = Wilayah::create([
            'nama_wilayah' => $request->nama_wilayah,
            'kategori'     => $request->kategori,
            'status'       => true,
            'created_by'   => Auth::id(),
        ]);

        return response()->json($wilayah, 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_wilayah' => 'sometimes|required|string|max:255|unique:wilayah,nama_wilayah,' . $id,
            'kategori'     => 'sometimes|nullable|in:putra,putri',
            'status'       => 'sometimes|required|boolean',
        ], [
            'nama_wilayah.unique' => 'Nama wilayah sudah digunakan.',
            'kategori.in' => 'Kategori hanya boleh putra atau putri.',
        ]);

        $wilayah = Wilayah::findOrFail($id);
        $wilayah->fill($request->only('nama_wilayah', 'kategori', 'status'));
        $wilayah->updated_by = Auth::id();
        $wilayah->save();

        return response()->json($wilayah);
    }

    public function destroy($id)
    {
        $wilayah = Wilayah::findOrFail($id);

        $jumlahDomisiliAktif = $wilayah->domisiliSantri()->where('status', 'aktif')->count();

        if ($jumlahDomisiliAktif > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Wilayah tidak dapat dinonaktifkan karena masih terdapat ' . $jumlahDomisiliAktif . ' santri aktif yang menempati wilayah ini.',
            ], 400);
        }

        $wilayah->updated_by = Auth::id();
        $wilayah->updated_at = now();
        $wilayah->status = false;
        $wilayah->save();

        return response()->json([
            'success' => true,
            'message' => 'Data wilayah berhasil dinonaktifkan.',
            'data' => $wilayah
        ], 200);
    }

    public function activate($id)
    {
        $wilayah = Wilayah::findOrFail($id);

        $wilayah->updated_by = Auth::id();
        $wilayah->updated_at = now();
        $wilayah->status = true;
        $wilayah->save();

        return response()->json([
            'success' => true,
            'message' => 'Data wilayah berhasil diaktifkan kembali.',
            'data' => $wilayah
        ], 200);
    }
}
