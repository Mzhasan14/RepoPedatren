<?php

namespace App\Http\Controllers\api\wilayah;

use App\Http\Controllers\Controller;
use App\Models\Kewilayahan\Blok;
use App\Models\Kewilayahan\Kamar;
use App\Models\Kewilayahan\Wilayah;

class DropdownWilayahController extends Controller
{
    public function getWilayah()
    {
        return response()->json(
            Wilayah::select('id', 'nama_wilayah')->get()
        );
    }

    public function getBlok(Wilayah $wilayah)
    {
        $blok = Blok::where('wilayah_id', $wilayah->id)
            ->select('id', 'nama_blok')
            ->get();

        return response()->json($blok);
    }

    public function getKamar(Blok $blok)
    {
        $kamar = Kamar::where('blok_id', $blok->id)
            ->select('id', 'nama_kamar')
            ->get();

        return response()->json($kamar);
    }
}
