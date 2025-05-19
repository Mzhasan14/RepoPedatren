<?php

namespace App\Http\Controllers\api\pendidikan;

use Illuminate\Http\Request;
use App\Models\Pendidikan\Kelas;
use App\Models\Pendidikan\Rombel;
use App\Models\Pendidikan\Jurusan;
use App\Models\Pendidikan\Lembaga;
use App\Http\Controllers\Controller;

class DropdownPendidikanController extends Controller
{
    public function getLembaga()
    {
        $lembaga = Lembaga::select('id', 'nama_lembaga')->get();
        return response()->json($lembaga);
    }

    public function getJurusan(Lembaga $lembaga)
    {
        $jurusan = Jurusan::where('lembaga_id', $lembaga->id)
            ->select('id', 'nama_jurusan')
            ->get();

        return response()->json($jurusan);
    }

    public function getKelas(Jurusan $jurusan)
    {
        $kelas = Kelas::where('jurusan_id', $jurusan->id)
            ->select('id', 'nama_kelas')
            ->get();

        return response()->json($kelas);
    }

    public function getRombel(Kelas $kelas)
    {
        $rombel = Rombel::where('kelas_id', $kelas->id)
            ->select('id', 'nama_rombel')
            ->get();

        return response()->json($rombel);
    }
}
