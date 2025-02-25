<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PdResource;
use Illuminate\Http\Request;
use App\Models\Peserta_didik;

class PesertaDidikController extends Controller
{
    public function getPesertaDidik()
    {
        $data = Peserta_Didik::join('biodata', 'peserta_didik.id_biodata', '=', 'biodata.id')
        ->join('rencana_pendidikan', 'peserta_didik.id', '=', 'rencana_pendidikan.id_peserta_didik')
        ->join('lembaga', 'rencana_pendidikan.id_lembaga', '=', 'lembaga.id')
        ->select('biodata.nama', 'biodata.niup', 'lembaga.nama_lembaga as lembaga')
        ->get();

        return new PdResource(true, 'Data peserta didik', $data);
    }
}
