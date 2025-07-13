<?php

namespace App\Services\Pegawai\Filters;

use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FilterMataPelajaranService
{
    public function applyMapelFilters($query, Request $request)
    {
        $query = $this->applyMapellFilters($query, $request);
        $query = $this->applyLembagaFilter($query, $request);

        return $query;
    }

    public function applyMapellFilters(Builder $query, Request $request): Builder
    {
        if ($request->filled('search')) {
            $search = $request->input('search');

            $query->where(function ($q) use ($search) {
                $q->where('mp.nama_mapel', 'like', '%' . $search . '%')
                ->orWhere('mp.kode_mapel', 'like', '%' . $search . '%')
                ->orWhere('b.nama', 'like', '%' . $search . '%');
            });
        }

        return $query;
    }
    private function applyLembagaFilter(Builder $query, Request $request): Builder
    {
        if ($request->filled('lembaga')) {
            $query->whereRaw('LOWER(l.nama_lembaga) = ?', [strtolower($request->lembaga)]);
        }

        return $query;
    }

}