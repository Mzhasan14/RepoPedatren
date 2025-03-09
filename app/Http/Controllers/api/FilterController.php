<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FilterController extends Controller
{
    public function applyCommonFilters($query, Request $request)
    {
        // Filter berdasarkan lokasi (negara, provinsi, kabupaten, kecamatan, desa)
        if ($request->filled('negara')) {
            $query->join('desa', 'biodata.id_desa', '=', 'desa.id')
                ->join('kecamatan', 'desa.id_kecamatan', '=', 'kecamatan.id')
                ->join('kabupaten', 'kecamatan.id_kabupaten', '=', 'kabupaten.id')
                ->join('provinsi', 'kabupaten.id_provinsi', '=', 'provinsi.id')
                ->join('negara', 'provinsi.id_negara', '=', 'negara.id')
                ->where('negara.nama_negara', $request->negara);
            if ($request->filled('provinsi')) {
                $query->where('provinsi.nama_provinsi', $request->provinsi);
                if ($request->filled('kabupaten')) {
                    $query->where('kabupaten.nama_kabupaten', $request->kabupaten);
                    if ($request->filled('kecamatan')) {
                        $query->where('kecamatan.nama_kecamatan', $request->kecamatan);
                    }
                }
            }
        }

        // 🔹 Filter jenis kelamin (dari biodata)
        if ($request->filled('jenis_kelamin')) {
            $query->where('biodata.jenis_kelamin', $request->jenis_kelamin);
        }

        if ($request->filled('smartcard')) {
            $query->where('biodata.smartcard', $request->smartcard);
        }

        if ($request->filled('nama')) {
            $query->whereRaw("MATCH(nama) AGAINST(? IN BOOLEAN MODE)", [$request->nama]);
        }


        return $query;
    }
}
