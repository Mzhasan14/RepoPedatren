<?php

namespace App\Http\Controllers\api\wilayah;

use App\Http\Controllers\Controller;
use App\Models\Kewilayahan\Kamar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KamarController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);

        $kamars = Kamar::with('blok.wilayah')
            ->where('status', true)
            ->select('id', 'blok_id', 'nama_kamar', 'kapasitas', 'status')
            ->paginate($perPage);

        $kamars->getCollection()->transform(function ($kamar) {
            return [
                'id' => $kamar->id,
                'nama_kamar' => $kamar->nama_kamar,
                'blok_id' => $kamar->blok_id,
                'kapasitas' => $kamar->kapasitas,
                'status' => $kamar->status,
                'blok' => [
                    'id' => $kamar->blok->id ?? null,
                    'nama_blok' => $kamar->blok->nama_blok ?? null,
                ],
                'wilayah' => [
                    'id' => $kamar->blok->wilayah->id ?? null,
                    'nama_wilayah' => $kamar->blok->wilayah->nama_wilayah ?? null,
                    'kategori' => $kamar->blok->wilayah->kategori ?? null,
                ],
            ];
        });

        return response()->json($kamars);
    }

    public function show($id)
    {
        $kamar = Kamar::with(['blok.wilayah'])->findOrFail($id);

        $jumlahPenghuni = \App\Models\DomisiliSantri::where('kamar_id', $kamar->id)
            ->where('status', 'aktif')
            ->count();
        $slotTersisa = $kamar->kapasitas !== null
            ? max($kamar->kapasitas - $jumlahPenghuni, 0)
            : null;

        $result = [
            'id' => $kamar->id,
            'nama_kamar' => $kamar->nama_kamar,
            'blok_id' => $kamar->blok_id,
            'kapasitas' => $kamar->kapasitas,
            'penghuni' => $jumlahPenghuni,
            'slot' => $slotTersisa,
            'status' => $kamar->status,
            'blok' => [
                'id' => $kamar->blok->id ?? null,
                'nama_blok' => $kamar->blok->nama_blok ?? null,
            ],
            'wilayah' => [
                'id' => $kamar->blok->wilayah->id ?? null,
                'nama_wilayah' => $kamar->blok->wilayah->nama_wilayah ?? null,
                'kategori' => $kamar->blok->wilayah->kategori ?? null,
            ],
        ];

        return response()->json($result);
    }


    public function store(Request $request)
    {
        $request->validate([
            'blok_id' => 'required|exists:blok,id',
            'nama_kamar' => 'required|string|max:255',
            'kapasitas' => 'nullable|integer|min:1',
        ]);

        $kamar = Kamar::create([
            'blok_id' => $request->blok_id,
            'nama_kamar' => $request->nama_kamar,
            'kapasitas' => $request->kapasitas,
            'created_by' => Auth::id(),
            'status' => true,
        ]);

        return response()->json($kamar->fresh('blok.wilayah'), 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'blok_id' => 'sometimes|required|exists:blok,id',
            'nama_kamar' => 'sometimes|required|string|max:255',
            'kapasitas' => 'sometimes|nullable|integer|min:1',
            'status' => 'sometimes|required|boolean',
        ]);

        $kamar = Kamar::findOrFail($id);
        $kamar->fill($request->only('blok_id', 'nama_kamar', 'kapasitas', 'status'));
        $kamar->updated_by = Auth::id();
        $kamar->save();

        return response()->json($kamar->fresh('blok.wilayah'));
    }

    public function destroy($id)
    {
        $kamar = Kamar::findOrFail($id);

        $kamar->updated_by = Auth::id();
        $kamar->updated_at = now();
        $kamar->status = false; // Menonaktifkan kamar
        $kamar->save();

        return response()->json([
            'success' => true,
            'message' => 'Data kamar berhasil dinonaktifkan.',
            'data' => $kamar
        ], 200);
    }

    public function activate($id)
    {
        $kamar = Kamar::findOrFail($id);

        $kamar->updated_by = Auth::id();
        $kamar->updated_at = now();
        $kamar->status = true;
        $kamar->save();

        return response()->json([
            'success' => true,
            'message' => 'Data kamar berhasil diaktifkan kembali.',
            'data' => $kamar
        ], 200);
    }
}
