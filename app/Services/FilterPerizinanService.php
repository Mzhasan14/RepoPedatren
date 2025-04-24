<?php

namespace App\Services;

use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;

class FilterPerizinanService
{
    /**
     * Panggil semua filter berurutan
     */
    public function perizinanFilters(Builder $query, Request $request): Builder
    {
        $query = $this->applyAlamatFilter($query, $request);
        $query = $this->applyJenisKelaminFilter($query, $request);
        $query = $this->applyNamaFilter($query, $request);
        $query = $this->applyWilayahFilter($query, $request);
        $query = $this->applyLembagaPendidikanFilter($query, $request);
        $query = $this->applyStatusIzin($query, $request);
        $query = $this->applyJenisIzinFilter($query, $request);
        $query = $this->applyBermalamFilter($query, $request);
        $query = $this->applyMasaTelatFilter($query, $request);
        
        return $query;
    }

    public function applyAlamatFilter(Builder $query, Request $request): Builder
    {
        if (! $request->filled('negara')) {
            return $query;
        }

        // Filter berdasarkan lokasi (negara, provinsi, kabupaten, kecamatan, desa)
        if ($request->filled('negara')) {
            $query->join('negara', 'b.negara_id', '=', 'negara.id')
                ->where('negara.nama_negara', $request->negara);

            if ($request->filled('provinsi')) {
                $query->leftJoin('provinsi', 'b.provinsi_id', '=', 'provinsi.id')
                    ->where('provinsi.nama_provinsi', $request->provinsi);

                if ($request->filled('kabupaten')) {
                    // Pastikan join ke tabel kabupaten dilakukan sebelum pemakaian filter
                    $query->join('kabupaten as kb', 'b.kabupaten_id', '=', 'kb.id')
                        ->where('kb.nama_kabupaten', $request->kabupaten);

                    if ($request->filled('kecamatan')) {
                        $query->leftJoin('kecamatan', 'b.kecamatan_id', '=', 'kecamatan.id')
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

    public function applyStatusIzin(Builder $query, Request $request): Builder
    {
        if (! $request->filled('status_izin')) {
            return $query;
        }

        $query->where('pr.status_izin', $request->status_izin);

        return $query;
    }

    public function applyJenisIzinFilter(Builder $query, Request $request): Builder
    {
        if (! $request->filled('jenis_izin')) {
            return $query;
        }

        $query->where('pr.jenis_izin', $request->jenis_izin);

        return $query;
    }

   public function applyBermalamFilter(Builder $query, Request $request): Builder
    {
        if (! $request->filled('bermalam')) {
            return $query;
        }

        if ($request->bermalam === 'bermalam') {
            return $query->whereRaw("TIMESTAMPDIFF(HOUR, pr.tanggal_mulai, pr.tanggal_akhir) > 24");
        } elseif ($request->bermalam === 'tidak bermalam') {
            return $query->whereRaw("TIMESTAMPDIFF(HOUR, pr.tanggal_mulai, pr.tanggal_akhir) <= 24");
        }

        return $query;
    }

    public function applyMasaTelatFilter(Builder $query, Request $request): Builder
    {
        if (! $request->filled('masa_telat')) {
            return $query;
        }

        if ($request->masa_telat === 'lebih  dari seminggu') {
            return $query->whereRaw("DATEDIFF(NOW(), pr.tanggal_akhir) > 7");
        } elseif ($request->masa_telat === 'lebih dari 2 minggu') {
            return $query->whereRaw("DATEDIFF(NOW(), pr.tanggal_akhir) > 14");
        } elseif ($request->masa_telat === 'lebih dari satu bulan') {
            return $query->whereRaw("DATEDIFF(NOW(), pr.tanggal_akhir) > 30");
        }

        return $query;
    }

   
}
