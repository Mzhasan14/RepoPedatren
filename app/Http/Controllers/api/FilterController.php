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
            $query->join('negara', 'b.id_negara', '=', 'negara.id')
                ->where('negara.nama_negara', $request->negara);

            if ($request->filled('provinsi')) {
                $query->leftJoin('provinsi', 'b.id_provinsi', '=', 'provinsi.id')
                    ->where('provinsi.nama_provinsi', $request->provinsi);

                if ($request->filled('kabupaten')) {
                    // Pastikan join ke tabel kabupaten dilakukan sebelum pemakaian filter
                    $query->leftJoin('kabupaten', 'b.id_kabupaten', '=', 'kabupaten.id')
                        ->where('kabupaten.nama_kabupaten', $request->kabupaten);

                    if ($request->filled('kecamatan')) {
                        $query->leftJoin('kecamatan', 'b.id_kecamatan', '=', 'kecamatan.id')
                            ->where('kecamatan.nama_kecamatan', $request->kecamatan);
                    }
                }
            }
            // Jika ada parameter lokasi tetapi tidak sesuai (misalnya negara tidak valid),
            // kondisi where akan menghasilkan query kosong saat dieksekusi.
        }

        // 🔹 Filter jenis kelamin (dari biodata)
        if ($request->filled('jenis_kelamin')) {
            $jenis_kelamin = strtolower($request->jenis_kelamin);
            if ($jenis_kelamin == 'laki-laki' || $jenis_kelamin == 'ayah') {
                $query->where('b.jenis_kelamin', 'l');
            } elseif ($jenis_kelamin == 'perempuan' || $jenis_kelamin == 'ibu') {
                $query->where('b.jenis_kelamin', 'p');
            } else {
                // Jika nilai jenis_kelamin tidak valid, hasilkan query kosong
                $query->whereRaw('0 = 1');
            }
        }

        if ($request->filled('smartcard')) {
            $smartcard = strtolower($request->smartcard);
            if ($smartcard == 'memiliki smartcard') {
                $query->whereNotNull('b.smartcard');
            } else if ($smartcard == 'tanpa smartcard') {
                $query->whereNull('b.smartcard');
            } else {
                $query->whereRaw('0 = 1');
            }
        }

        // 🔹 Filter pencarian berdasarkan nama (fulltext search)
        if ($request->filled('nama')) {
            $query->whereRaw("MATCH(nama) AGAINST(? IN BOOLEAN MODE)", [$request->nama]);
        }

        return $query;
    }
}
