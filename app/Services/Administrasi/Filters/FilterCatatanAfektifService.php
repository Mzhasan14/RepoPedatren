<?php

namespace App\Services\Administrasi\Filters;

use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;

class FilterCatatanAfektifService
{
    /**
     * Panggil semua filter berurutan
     */
    public function applyAllFilters(Builder $query, Request $request): Builder
    {
        $query = $this->applyJenisKelaminFilter($query, $request);
        $query = $this->applyNamaFilter($query, $request);
        $query = $this->applyWilayahFilter($query, $request);
        $query = $this->applyNegaraFilter($query, $request);
        $query = $this->applyLembagaFilter($query, $request);
        $query = $this->applyPhoneNumberFilter($query, $request);
        $query = $this->applyPeriodeFilter($query, $request);
        $query = $this->applyKategoriFilter($query, $request);
        $query = $this->applyScoreFilter($query, $request);

        return $query;
    }

    private function applyNamaFilter(Builder $query, Request $request): Builder
    {
        // Filter Search
        if ($request->filled('nama')) {
            $query->whereRaw('MATCH(bs.nama) AGAINST(? IN BOOLEAN MODE)', [$request->nama]);
        }

        return $query;

    }

    private function applyNegaraFilter(Builder $query, Request $request): Builder
    {
        // Filter berdasarkan lokasi (negara, provinsi, kabupaten, kecamatan, desa)
        if ($request->filled('negara')) {
            $query->leftJoin('negara', 'bs.negara_id', '=', 'negara.id')
                ->where('negara.nama_negara', $request->negara);
            if ($request->filled('provinsi')) {
                $query->leftjoin('provinsi', 'bs.provinsi_id', '=', 'provinsi.id');
                $query->where('provinsi.nama_provinsi', $request->provinsi);
                if ($request->filled('kabupaten')) {
                    $query->leftjoin('kabupaten', 'bs.kabupaten_id', '=', 'kabupaten.id');
                    $query->where('kabupaten.nama_kabupaten', $request->kabupaten);
                    if ($request->filled('kecamatan')) {
                        $query->leftjoin('kecamatan', 'bs.kecamatan_id', '=', 'kecamatan.id');
                        $query->where('kecamatan.nama_kecamatan', $request->kecamatan);
                    }
                }
            }
        }

        return $query;

    }

    private function applyLembagaFilter(Builder $query, Request $request): Builder
    {
        // Filter Lembaga
        if ($request->filled('lembaga')) {
            $query->where('lembaga.nama_lembaga', strtolower($request->lembaga));
            if ($request->filled('jurusan')) {
                $query->where('jurusan.nama_jurusan', strtolower($request->jurusan));
                if ($request->filled('kelas')) {
                    $query->where('kelas.nama_kelas', strtolower($request->kelas));
                    if ($request->filled('rombel')) {
                        $query->where('rombel.nama_rombel', strtolower($request->rombel));
                    }
                }
            }
        }

        return $query;

    }

    private function applywilayahFilter(Builder $query, Request $request): Builder
    {
        // Filter Wilayah
        if ($request->filled('wilayah')) {
            $wilayah = strtolower($request->wilayah);
            $query->where('wilayah.nama_wilayah', $wilayah);
            if ($request->filled('blok')) {
                $blok = strtolower($request->blok);
                $query->where('blok.nama_blok', $blok);
                if ($request->filled('kamar')) {
                    $kamar = strtolower($request->kamar);
                    $query->where('kamar.nama_kamar', $kamar);
                }
            }
        }

        return $query;

    }

    private function applyJenisKelaminFilter(Builder $query, Request $request): Builder
    {
        // Filter jenis kelamin
        if ($request->filled('jenis_kelamin')) {
            $jenis_kelamin = strtolower($request->jenis_kelamin);
            if ($jenis_kelamin == 'laki-laki') {
                $query->where('bs.jenis_kelamin', 'l');
            } elseif ($jenis_kelamin == 'perempuan') {
                $query->where('bs.jenis_kelamin', 'p');
            }
        }

        return $query;

    }

    private function applyPhoneNumberFilter(Builder $query, Request $request): Builder
    {            // Filter No Telepon
        if ($request->filled('phone_number')) {
            $query->where(function ($q) use ($request) {
                if (strtolower($request->phone_number) === 'mempunyai') {
                    $q->whereNotNull('bs.no_telepon')
                        ->where('bs.no_telepon', '!=', '');
                } elseif (strtolower($request->phone_number) === 'tidak mempunyai') {
                    $q->where(function ($q2) {
                        $q2->whereNull('bs.no_telepon')
                            ->orWhere('bs.no_telepon', '');
                    });
                }
            });
        }

        return $query;

    }

    private function applyPeriodeFilter(Builder $query, Request $request): Builder
    {
        if ($request->filled('periode')) {
            try {
                $date = Carbon::parse($request->periode);
                $query->whereYear('catatan_afektif.tanggal_buat', $date->year)
                    ->whereMonth('catatan_afektif.tanggal_buat', $date->month);
            } catch (\Exception $e) {
                // Handle error jika format tanggal salah, misal ignore filter atau log error
            }
        }

        return $query;
    }

    private function applyKategoriFilter(Builder $query, Request $request): Builder
    {
        if ($request->filled('kategori')) {
            $kategori = strtolower($request->kategori);

            if (in_array($kategori, ['akhlak', 'kebersihan', 'kepedulian'])) {
                // Ganti whereNotNull jadi where nilai kategori != null
                $column = "catatan_afektif.{$kategori}_nilai";
                $query->whereNotNull($column);
            }
        }

        return $query;
    }

    private function applyScoreFilter(Builder $query, Request $request): Builder
    {
        if ($request->filled('score') && in_array($request->score, ['A', 'B', 'C', 'D', 'E']) && $request->filled('kategori')) {
            $score = $request->score;
            $kategori = strtolower($request->kategori); // konsisten lowercase

            if (in_array($kategori, ['akhlak', 'kebersihan', 'kepedulian'])) {
                $column = "catatan_afektif.{$kategori}_nilai";
                $query->where($column, $score);
            }
        }

        return $query;
    }
}
