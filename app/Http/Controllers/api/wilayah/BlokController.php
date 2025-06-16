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
        $perPage = $request->get('per_page', 10);

        $bloks = Blok::with('wilayah')
            ->where('status', true)
            ->select('id', 'wilayah_id', 'nama_blok', 'status')
            ->paginate($perPage);

        $bloks->getCollection()->transform(function ($blok) {
            return [
                'id' => $blok->id,
                'wilayah_id' => $blok->wilayah_id,
                'nama_blok' => $blok->nama_blok,
                'status' => $blok->status,
                'wilayah' => [
                    'id' => $blok->wilayah->id ?? null,
                    'nama_wilayah' => $blok->wilayah->nama_wilayah ?? null,
                    'kategori' => $blok->wilayah->kategori ?? null,
                ]
            ];
        });

        return response()->json($bloks);
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
            'nama_blok' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('blok')->ignore($blok->id)->where(function ($q) use ($request, $blok) {
                    // Jika wilayah_id diinput, pakai yang diinput. Jika tidak, pakai yang lama.
                    $wilayahId = $request->wilayah_id ?? $blok->wilayah_id;
                    return $q->where('wilayah_id', $wilayahId);
                }),
            ],
            'status' => 'sometimes|required|boolean',
        ]);

        $blok->fill($request->only('wilayah_id', 'nama_blok', 'status'));
        $blok->updated_by = Auth::id();
        $blok->save();

        return response()->json($blok->fresh('wilayah'));
    }

    public function destroy($id)
    {
        $blok = Blok::findOrFail($id);
        $blok->deleted_by = Auth::id();
        $blok->updated_by = Auth::id();
        $blok->updated_at = now();
        $blok->status = false;
        $blok->save();
        $blok->delete();

        return response()->json(null, 204);
    }
}
