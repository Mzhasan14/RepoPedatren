<?php

namespace App\Services\Administrasi\Filters;

use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;

class FilterPengunjungMahromService
{
    /**
     * Panggil semua filter berurutan
     */
    public function pengunjungFilters(Builder $query, Request $request): Builder
    {
        $query = $this->applyAlamatFilter($query, $request);
        $query = $this->applyNamaFilter($query, $request);
        $query = $this->applyWilayahFilter($query, $request);

        return $query;
    }

    public function applyAlamatFilter(Builder $query, Request $request): Builder
    {
        if (! $request->filled('negara')) {
            return $query;
        }

        // Filter berdasarkan lokasi (negara, provinsi, kabupaten, kecamatan, desa)
        if ($request->filled('negara')) {
            $query->join('negara', 'bs.negara_id', '=', 'negara.id')
                ->where('negara.nama_negara', $request->negara);

            if ($request->filled('provinsi')) {
                $query->leftJoin('provinsi', 'bs.provinsi_id', '=', 'provinsi.id')
                    ->where('provinsi.nama_provinsi', $request->provinsi);

                if ($request->filled('kabupaten')) {
                    // Pastikan join ke tabel kabupaten dilakukan sebelum pemakaian filter
                    $query
                        ->leftJoin('kabupaten', 'bs.kabupaten_id', '=', 'kabupaten.id')
                        ->where('kb.nama_kabupaten', $request->kabupaten);

                    if ($request->filled('kecamatan')) {
                        $query->leftJoin('kecamatan', 'bs.kecamatan_id', '=', 'kecamatan.id')
                            ->where('kecamatan.nama_kecamatan', $request->kecamatan);
                    }
                } else {
                    // Jika nilai kabupaten tidak valid, hasilkan query kosong
                    $query->whereRaw('0 = 1');
                }
            }
        }

        return $query;
    }

    public function applyNamaFilter(Builder $query, Request $request): Builder
    {
        if (! $request->filled('nama')) {
            return $query;
        }

        // tambahkan tanda kutip ganda di awal‑akhir
        $phrase = '"'.trim($request->nama).'"';

        return $query->whereRaw(
            'MATCH(bp.nama) AGAINST(? IN BOOLEAN MODE)',
            [$phrase]
        );
    }

    public function applyWilayahFilter(Builder $query, Request $request): Builder
    {
        if (! $request->filled('wilayah')) {
            return $query;
        }

        // Filter non domisili pesantren
        if ($request->wilayah === 'non domisili') {
            return $query->where(fn ($q) => $q->whereNull('rd.id')->orWhere('rd.status', '!=', 'aktif'));
        }

        $query->where('w.nama_wilayah', $request->wilayah);

        if ($request->filled('blok')) {
            $query->where('bl.nama_blok', $request->blok);

            if ($request->filled('kamar')) {
                $query->where('km.nama_kamar', $request->kamar);
            }
        }

        return $query;
    }
}
