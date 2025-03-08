<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FilterController extends Controller
{
    public function applyCommonFilters($query, Request $request)
    {
        // Filter berdasarkan lokasi (negara, provinsi, kabupaten, kecamatan, desa)
        if ($request->has('id_negara')) {
            $query->where('negara.id', $request->id_negara);
        }
        if ($request->has('id_provinsi')) {
            $query->where('provinsi.id', $request->id_provinsi);
        }
        if ($request->has('id_kabupaten')) {
            $query->where('kabupaten.id', $request->id_kabupaten);
        }
        if ($request->has('id_kecamatan')) {
            $query->where('kecamatan.id', $request->id_kecamatan);
        }
        if ($request->has('id_desa')) {
            $query->where('desa.id', $request->id_desa);
        }

        // 🔹 Filter jenis kelamin (dari biodata)
        if ($request->has('jenis_kelamin')) {
            $query->where('biodata.jenis_kelamin', $request->jenis_kelamin);
        }


        return $query;
    }
}
