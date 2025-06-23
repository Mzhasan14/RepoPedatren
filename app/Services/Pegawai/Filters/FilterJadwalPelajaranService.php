<?php

namespace App\Services\Pegawai\Filters;

use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FilterJadwalPelajaranService
{
    public function applyJadwalFilters(Builder $query, Request $request): Builder
    {
        $query = $this->filterSearch($query, $request);
        $query = $this->filterLembaga($query, $request);
        $query = $this->filterJurusan($query, $request);
        $query = $this->filterKelas($query, $request);
        $query = $this->filterJamMulai($query, $request);
        $query = $this->filterJamSelesai($query, $request);
        $query = $this->filterHari($query, $request);

        return $query;
    }

    private function filterSearch(Builder $query, Request $request): Builder
    {
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('mp.nama_mapel', 'like', '%' . $search . '%');
        }
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

    private function filterJamMulai(Builder $query, Request $request): Builder
    {
        if ($request->filled('jam_mulai')) {
            $query->where('jam.jam_mulai', $request->input('jam_mulai'));
        }
        return $query;
    }

    private function filterJamSelesai(Builder $query, Request $request): Builder
    {
        if ($request->filled('jam_selesai')) {
            $query->where('jam.jam_selesai', $request->input('jam_selesai'));
        }
        return $query;
    }

    private function filterHari(Builder $query, Request $request): Builder
    {
        if ($request->filled('hari')) {
            $query->where('jp.hari', $request->input('hari'));
        }
        return $query;
    }
}