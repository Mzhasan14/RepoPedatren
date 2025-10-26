<?php

namespace App\Services\Kewaliasuhan\Filters;

use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;

class FilterGrupWaliasuhService
{
    public function GrupWaliasuhFIlters(Builder $query, Request $request): Builder
    {
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

        $jenis = $request->grup_wali_asuh;

        switch ($jenis) {
            case 'tidak_ada_wali_dan_anak':
                // Grup tidak punya wali & anak
                $query->whereNull('gs.wali_asuh_id')
                    ->havingRaw('COUNT(aa.id) = 0');
                break;

            case 'tidak_ada_wali':
                // Grup tidak punya wali
                $query->whereNull('gs.wali_asuh_id');
                break;

            case 'tidak_ada_anak':
                // Grup ada wali tapi anak kosong
                $query->havingRaw('COUNT(aa.id) = 0');
                break;

            case 'wali_ada_tapi_tidak_ada_anak':
                // Grup punya wali tapi anak kosong
                $query->whereNotNull('gs.wali_asuh_id')
                    ->havingRaw('COUNT(aa.id) = 0');
                break;

            case 'anak_ada_tapi_tidak_ada_wali':
                // Grup punya anak tapi tidak ada wali
                $query->whereNull('gs.wali_asuh_id')
                    ->havingRaw('COUNT(aa.id) > 0');
                break;
        }

        return $query;
    }

    public function applyNamaFilter(Builder $query, Request $request): Builder
    {
        if (! $request->filled('nama')) {
            return $query;
        }

        $nama = trim($request->nama);

        return $query->whereRaw('LOWER(gs.nama_grup) LIKE ?', ['%' . strtolower($nama) . '%']);
    }
}
