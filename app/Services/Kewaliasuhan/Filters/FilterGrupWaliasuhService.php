<?php 

namespace App\Services\Kewaliasuhan\Filters;

use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;

class FilterGrupWaliasuhService {
    public function GrupWaliasuhFIlters(Builder $query, Request $request): Builder {
        $query = $this->applyJenisKelaminFilter($query, $request);
        $query = $this->applyWilayahFilter($query, $request);
        $query = $this->applyJenisGrupWaliAsuhFilter($query, $request);
        $query = $this->applyNamaFilter($query, $request);

        return $query;
    }


    public function applyJenisKelaminFilter(Builder $query, Request $request): Builder
    {
        if (! $request->filled('jenis_kelamin')) {
            return $query;
        }

        $jenis_kelamin = strtolower($request->jenis_kelamin);
        if ($jenis_kelamin === 'laki-laki' || $jenis_kelamin === 'ayah') {
            $query->where('b.jenis_kelamin', 'l');
        } elseif ($jenis_kelamin === 'perempuan' || $jenis_kelamin === 'ibu') {
            $query->where('b.jenis_kelamin', 'p');
        } else {
            // Jika nilai jenis_kelamin tidak valid, hasilkan query kosong
            $query->whereRaw('0 = 1');
        }

        return $query;
    }

    public function applyWilayahFilter(Builder $query, Request $request): Builder
    {
        if (! $request->filled('wilayah')) {
            return $query;
        }

        // Filter non domisili pesantren
        if ($request->wilayah === 'non domisili') {
            return $query->where(fn($q) => $q->whereNull('rd.id')->orWhere('rd.status', '!=', 'aktif'));
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

    public function applyJenisGrupWaliasuhFilter(Builder $query, Request $request): Builder
    {
        if (! $request->filled('grup_wali_asuh')) {
            return $query;
        }
        
        if ($request === 'tidak_ada_wali_dan_anak') {
            $query->whereNull('wali_asuh.id')->whereNull('anak_asuh.id');
        } elseif ($request === 'tidak_ada_wali') {
            $query->whereNull('wali_asuh.id');
        } elseif ($request === 'tidak_ada_anak') {
            $query->whereNull('anak_asuh.id');
        } elseif ($request === 'wali_ada_tapi_tidak_ada_anak') {
            $query->whereNotNull('wali_asuh.id')->whereNull('anak_asuh.id');
        } elseif ($request === 'anak_ada_tapi_tidak_ada_wali') {
            $query->whereNotNull('anak_asuh.id')->whereNull('wali_asuh.id');
        }


        return $query;
    }

    public function applyNamaFilter(Builder $query, Request $request): Builder
    {
        if (! $request->filled('nama')) {
            return $query;
        }

        // tambahkan tanda kutip ganda di awal‑akhir
        $phrase = '"' . trim($request->nama) . '"';

        return $query->whereRaw(
            "MATCH(gs.nama_grup) AGAINST(? IN BOOLEAN MODE)",
            [$phrase]
        );
    }
}