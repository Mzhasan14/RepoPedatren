<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FilterController extends Controller
{
    public function applyCommonFilters($query, Request $request)
    {
        // Filter berdasarkan lokasi (negara, provinsi, kabupaten, kecamatan, desa)
        if ($request->filled('id_negara')) {
            $query->join('desa', 'biodata.id_desa', '=', 'desa.id')
                ->join('kecamatan', 'desa.id_kecamatan', '=', 'kecamatan.id')
                ->join('kabupaten', 'kecamatan.id_kabupaten', '=', 'kabupaten.id')
                ->join('provinsi', 'kabupaten.id_provinsi', '=', 'provinsi.id')
                ->join('negara', 'provinsi.id_negara', '=', 'negara.id')
                ->where('negara.id', $request->id_negara);
            if ($request->filled('id_provinsi')) {
                $query->where('provinsi.id', $request->id_provinsi);
                if ($request->filled('id_kabupaten')) {
                    $query->where('kabupaten.id', $request->id_kabupaten);
                    if ($request->filled('id_kecamatan')) {
                        $query->where('kecamatan.id', $request->id_kecamatan);
                        if ($request->filled('id_desa')) {
                            $query->where('desa.id', $request->id_desa);
                        }
                    }
                }
            }
        }

        // 🔹 Filter jenis kelamin (dari biodata)
        if ($request->filled('jenis_kelamin')) {
            $query->where('biodata.jenis_kelamin', $request->jenis_kelamin);
        }

        if ($request->filled('warga_pesantren')) {
            $query->where('rencana_pendidikan.mondok', $request->warga_pesantren);
        }

        if ($request->filled('smartcard')) {
            $query->where('rencana_pendidikan.mondok', $request->warga_pesantren);
        }


        return $query;
    }
}
