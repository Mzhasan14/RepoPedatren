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
                ->where('negara.nama_negara', $request->negara);
            if ($request->filled('provinsi')) {
                $query->leftjoin('provinsi', 'biodata.id_provinsi', '=', 'provinsi.id');
                $query->where('provinsi.nama_provinsi', $request->provinsi);
                if ($request->filled('kabupaten')) {
                    $query->where('kabupaten.nama_kabupaten', $request->kabupaten);
                    if ($request->filled('kecamatan')) {
                        $query->leftjoin('kecamatan', 'biodata.id_kecamatan', '=', 'kecamatan.id');
                        $query->where('kecamatan.nama_kecamatan', $request->kecamatan);
                    }
                }
            }
        }

        // 🔹 Filter jenis kelamin (dari biodata)
        if ($request->filled('jenis_kelamin')) {
            $jenis_kelamin = strtolower($request->jenis_kelamin);
            if ($jenis_kelamin == 'laki-laki' || $jenis_kelamin == 'ayah') {
                $query->where('biodata.jenis_kelamin', 'l');
            } else if ($jenis_kelamin == 'perempuan' || $jenis_kelamin == 'ibu') {
               $query->where('biodata.jenis_kelamin', 'p');
            }
        } 

        if ($request->filled('smartcard')) {
            if (strtolower($request->smartcard) === 'mempunyai') {
                // Hanya tampilkan data yang memiliki smartcard
                $query->whereNotNull('biodata.smartcard')->where('biodata.smartcard', '!=', '');
            } elseif (strtolower($request->smartcard) === 'tidak mempunyai') {
                // Hanya tampilkan data yang tidak memiliki smartcard
                $query->whereNull('biodata.smartcard')->orWhere('biodata.smartcard', '');
            }
        }
        

        if ($request->filled('nama')) {
            $query->whereRaw("MATCH(nama) AGAINST(? IN BOOLEAN MODE)", [$request->nama]);
        }

        return $query;
    }
}
