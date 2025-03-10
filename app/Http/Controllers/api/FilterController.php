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
            $query->join('negara', 'biodata.id_negara', '=', 'negara.id')
                ->leftjoin('provinsi', 'biodata.id_provinsi', '=', 'provinsi.id')
                ->leftjoin('kabupaten', 'biodata.id_kabupaten', '=', 'kabupaten.id')
                ->leftjoin('kecamatan', 'biodata.id_kecamatan', '=', 'kecamatan.id')
                ->leftjoin('desa', 'biodata.id_desa', '=', 'desa.id')
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
            $jenis_kelamin = strtolower($request->jenis_kelamin);
            if ($jenis_kelamin == 'laki-laki') {
                $query->where('biodata.jenis_kelamin', 'l');
            } else if ($jenis_kelamin == 'perempuan') {
                $query->where('biodata.jenis_kelamin', 'p');
            }
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
