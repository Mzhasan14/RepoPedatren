<?php

namespace App\Services\PesertaDidik\Filters;

use Illuminate\Http\Request;
use Illuminate\Database\Query\Builder;

class FilterListDataLulusSantriService
{
    /**
     * Panggil semua filter berurutan
     */
    public function listDataLulusSantriFilters(Builder $query, Request $request): Builder
    {
        $query = $this->applyNamaFilter($query, $request);
        $query = $this->applyWilayahFilter($query, $request);
        $query = $this->applySorting($query, $request);

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
            'MATCH(b.nama) AGAINST(? IN BOOLEAN MODE)',
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

            return $query->where(fn($q) => $q->whereNull('rd.id')->orWhere('rd.status', '!=', 'aktif'));
        }

        $query->where('w.nama_wilayah', $request->wilayah);

        if ($request->filled('blok')) {
            $query->join('blok AS bl', 'rd.blok_id', '=', 'bl.id')
                ->where('bl.nama_blok', $request->blok);

            if ($request->filled('kamar')) {
                $query->join('kamar AS km', 'rd.kamar_id', '=', 'km.id')
                    ->where('km.nama_kamar', $request->kamar);
            }
        }

        return $query;
    }

    public function applySorting(Builder $query, Request $request): Builder
    {
        if (! $request->filled('sort_by')) {
            return $query;
        }

        $allowed = ['id', 'nama', 'niup', 'jenis_kelamin'];
        $by = strtolower($request->sort_by);
        $order = ($request->filled('sort_order') && strtolower($request->sort_order) === 'desc') ? 'desc' : 'asc';

        if (in_array($by, $allowed, true)) {
            $query->orderBy($by, $order);
        } else {
            $query->whereRaw('0 = 1');
        }

        return $query;
    }
}
