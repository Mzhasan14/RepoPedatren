<?php

namespace App\Services;

use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;

class FilterPelanggaranService
{
    /**
     * Panggil semua filter berurutan
     */
    public function pelanggaranFilters(Builder $query, Request $request): Builder
    {
        $query = $this->applyJenisKelaminFilter($query, $request);
        $query = $this->applyNamaFilter($query, $request);
        $query = $this->applyWilayahFilter($query, $request);
        $query = $this->applyLembagaPendidikanFilter($query, $request);
        $query = $this->applyJenisPutusanFilter($query, $request);
        $query = $this->applyJenisPelanggaranFilter($query, $request);
        $query = $this->applyStatusPelanggaranFilter($query, $request);
        
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
            // Jika nilai tidak valid, hasilkan query kosong
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
        $phrase = '"' . trim($request->nama) . '"';

        return $query->whereRaw(
            "MATCH(nama) AGAINST(? IN BOOLEAN MODE)",
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

    public function applyLembagaPendidikanFilter(Builder $query, Request $request): Builder
    {
        if (! $request->filled('lembaga')) {
            return $query;
        }

        $query->where('l.nama_lembaga', $request->lembaga);

        if ($request->filled('jurusan')) {
            $query->join('jurusan as j', 'rp.jurusan_id', '=', 'j.id')
                ->where('j.nama_jurusan', $request->jurusan);

            if ($request->filled('kelas')) {
                $query->join('kelas as kls', 'rp.kelas_id', '=', 'kls.id')
                    ->where('kls.nama_kelas', $request->kelas);

                if ($request->filled('rombel')) {
                    $query->join('rombel as r', 'rp.rombel_id', '=', 'r.id')
                        ->where('r.nama_rombel', $request->rombel);
                }
            }
        }

        return $query;
    }

    public function applyJenisPutusanFilter(Builder $query, Request $request): Builder
    {
        if (! $request->filled('jenis_putusan')) {
            return $query;
        }

        $query->where('pl.jenis_putusan', $request->jenis_putusan);

        return $query;
    }

    public function applyJenisPelanggaranFilter(Builder $query, Request $request): Builder
    {
        if (! $request->filled('jenis_pelanggaran')) {
            return $query;
        }

        $query->where('pl.jenis_pelanggaran', $request->jenis_pelanggaran);

        return $query;
    }

    public function applyStatusPelanggaranFilter(Builder $query, Request $request): Builder
    {
        if (! $request->filled('status_pelanggaran')) {
            return $query;
        }

        $query->where('pl.status_pelanggaran', $request->status_pelanggaran);

        return $query;
    }



   
}
