<?php

namespace App\Http\Controllers\api\wilayah;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Kewilayahan\Blok;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class BlokController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 25);
        $status = $request->get('status', 'aktif');

        $blok = Blok::with('wilayah:id,nama_wilayah')
            ->where('status', $status === 'aktif')
            ->select('id', 'nama_blok', 'wilayah_id', 'status')
            ->paginate($perPage);

        $blok->getCollection()->transform(function ($item) {
            return [
                'id' => $item->id,
                'nama_blok' => $item->nama_blok,
                'wilayah' => $item->wilayah ? $item->wilayah->nama_wilayah : null,
                'status' => $item->status,
            ];
        });

        if ($blok->total() == 0) {
            return response()->json([
                'status' => 'success',
                'message' => 'Data kosong',
                'data' => [],
            ]);
        }

        return response()->json($blok);
    }

    public function show($id)
    {
        $blok = Blok::with('wilayah', 'kamar')->findOrFail($id);

        $kamarAktif = $blok->kamar->where('status', true);
        $totalKamar = $kamarAktif->count();
        $totalKapasitas = $kamarAktif->sum('kapasitas');
        $totalPenghuni = 0;
        foreach ($kamarAktif as $kamar) {
            $totalPenghuni += \App\Models\DomisiliSantri::where('kamar_id', $kamar->id)
                ->where('status', 'aktif')
                ->count();
        }
        $slotTersisa = $totalKapasitas > 0 ? max($totalKapasitas - $totalPenghuni, 0) : null;

        $result = [
            'id' => $blok->id,
            'wilayah_id' => $blok->wilayah_id,
            'nama_blok' => $blok->nama_blok,
            'status' => $blok->status,
            'wilayah' => [
                'id' => $blok->wilayah->id ?? null,
                'nama_wilayah' => $blok->wilayah->nama_wilayah ?? null,
                'kategori' => $blok->wilayah->kategori ?? null,
            ],
            'total_kamar' => $totalKamar,
            'total_kapasitas' => $totalKapasitas,
            'penghuni' => $totalPenghuni,
            'slot' => $slotTersisa,
        ];

        return response()->json($result);
    }


    public function store(Request $request)
    {
        $request->validate([
            'wilayah_id' => 'required|exists:wilayah,id',
            'nama_blok' => [
                'required',
                'string',
                'max:255',
                // Blok unik dalam satu wilayah
                Rule::unique('blok')->where(function ($q) use ($request) {
                    return $q->where('wilayah_id', $request->wilayah_id);
                }),
            ],
        ]);

        $blok = Blok::create([
            'wilayah_id' => $request->wilayah_id,
            'nama_blok' => $request->nama_blok,
            'created_by' => Auth::id(),
            'status' => true,
        ]);

        return response()->json($blok->fresh('wilayah'), 201);
    }

    public function update(Request $request, $id)
    {
        $blok = Blok::findOrFail($id);

        $request->validate([
            'wilayah_id' => 'sometimes|required|exists:wilayah,id',
            'kategoti'   => 'required|string|in:putra,putri'
        ]);

        $blok->fill($request->only('wilayah_id', 'status'));
        $blok->updated_by = Auth::id();
        $blok->save();

        return response()->json($blok->fresh('wilayah'));
    }

    public function destroy($id)
    {
        $blok = Blok::findOrFail($id);

        $jumlahDomisiliAktif = $blok->domisiliSantri()->where('status', 'aktif')->count();

        if ($jumlahDomisiliAktif > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Blok tidak dapat dinonaktifkan karena masih terdapat ' . $jumlahDomisiliAktif . ' santri aktif yang menempati blok ini.',
            ], 400);
        }

        $blok->updated_by = Auth::id();
        $blok->updated_at = now();
        $blok->status = false;
        $blok->save();

        return response()->json([
            'success' => true,
            'message' => 'Data blok berhasil dinonaktifkan.',
            'data' => $blok
        ], 200);
    }

    public function activate($id)
    {
        $blok = Blok::findOrFail($id);

        $blok->updated_by = Auth::id();
        $blok->updated_at = now();
        $blok->status = true;
        $blok->save();

        return response()->json([
            'success' => true,
            'message' => 'Data blok berhasil diaktifkan kembali.',
            'data' => $blok
        ], 200);
    }
}
