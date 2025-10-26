<?php

namespace App\Services\Pegawai\Filters;

use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FilterJadwalPelajaranService
{
    public function applyJadwalFilters(Builder $query, Request $request): Builder
    {
        $query = $this->filterLembaga($query, $request);
        $query = $this->filterJurusan($query, $request);
        $query = $this->filterKelas($query, $request);

        return $query;
    }

    private function filterLembaga(Builder $query, Request $request): Builder
    {
        if ($request->filled('nama_lembaga')) {
            $query->where('l.nama_lembaga', $request->input('nama_lembaga'));
        }
        return $query;
    }

    private function filterJurusan(Builder $query, Request $request): Builder
    {
        if ($request->filled('nama_jurusan')) {
            $query->where('j.nama_jurusan', $request->input('nama_jurusan'));
        }
        return $query;
    }

    private function filterKelas(Builder $query, Request $request): Builder
    {
        if ($request->filled('nama_kelas')) {
            $query->where('k.nama_kelas', $request->input('nama_kelas'));
        }
        return $query;
    }
}