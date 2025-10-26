<?php

namespace App\Services\PesertaDidik\Filters;

use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;

class FilterListDataLulusService
{
    /**
     * Panggil semua filter berurutan
     */
    public function listDataLulusFilters(Builder $query, Request $request): Builder
    {
        $query = $this->applyNamaFilter($query, $request);
        $query = $this->applyLembagaPendidikanFilter($query, $request);
        $query = $this->applySorting($query, $request);

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

    public function applyNamaFilter(Builder $query, Request $request): Builder
    {
        if (! $request->filled('nama')) {
            return $query;
        }

        // tambahkan tanda kutip ganda di awal‑akhir
        $phrase = '"'.trim($request->nama).'"';

        return $query->whereRaw(
            'MATCH(b.nama) AGAINST(? IN BOOLEAN MODE)',
            [$phrase]
        );
    }

    public function applyLembagaPendidikanFilter(Builder $query, Request $request): Builder
    {
        if (! $request->filled('lembaga')) {
            return $query;
        }

        $query->where('l.nama_lembaga', $request->lembaga);

        if ($request->filled('jurusan')) {
            $query->join('jurusan AS j', 'rp.jurusan_id', '=', 'j.id')
                ->where('j.nama_jurusan', $request->jurusan);

            if ($request->filled('kelas')) {
                $query->join('kelas AS kls', 'rp.kelas_id', '=', 'kls.id')
                    ->where('kls.nama_kelas', $request->kelas);

                if ($request->filled('rombel')) {
                    $query->join('rombel AS r', 'rp.rombel_id', '=', 'r.id')
                        ->where('r.nama_rombel', $request->rombel);
                }
            }
        }

        return $query;
    }
}
