<?php

namespace App\Services\Kewaliasuhan\Filters;

use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;

class FilterWaliasuhService
{
    /**
     * Panggil semua filter berurutan
     */
    public function WaliasuhFilters(Builder $query, Request $request): Builder
    {
        $query = $this->applyAlamatFilter($query, $request);
        $query = $this->applyJenisKelaminFilter($query, $request);
         
        $query = $this->applyNamaFilter($query, $request);
        $query = $this->applyWilayahFilter($query, $request);
        $query = $this->applyJenisWaliAsuhFilter($query, $request);
        $query = $this->applyLembagaPendidikanFilter($query, $request);
        $query = $this->applyStatusPesertaFilter($query, $request);
        $query = $this->applyStatusWargaPesantrenFilter($query, $request);
        $query = $this->applyAngkatanSantri($query, $request);
        $query = $this->applyPhoneNumber($query, $request);
        $query = $this->applySorting($query, $request);

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
                    $query
                        ->where('kb.nama_kabupaten', $request->kabupaten);

                    if ($request->filled('kecamatan')) {
                        $query->leftJoin('kecamatan', 'b.kecamatan_id', '=', 'kecamatan.id')
                            ->where('kecamatan.nama_kecamatan', $request->kecamatan);
                    }
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
        $phrase = '"'.trim($request->nama).'"';

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
            return $query->where(fn ($q) => $q->whereNull('rd.id')->orWhere('rd.status', '!=', 'aktif'));
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

    public function applyJenisWaliAsuhFilter(Builder $query, Request $request): Builder
    {
        if (! $request->filled('jenis_wali_asuh')) {
            return $query;
        }

        if ($request->filled('jenis_wali_asuh')) {
            if ($request->jenis_wali_asuh === 'dengan_grup') {
                $query->whereNotNull('ws.id_grup_wali_asuh');
            } elseif ($request->jenis_wali_asuh === 'tanpa_grup') {
                $query->whereNull('ws.id_grup_wali_asuh');
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
            $query->join('jurusan as j', 'pd.jurusan_id', '=', 'j.id')
                ->where('j.nama_jurusan', $request->jurusan);

            if ($request->filled('kelas')) {
                $query->join('kelas as kls', 'pd.kelas_id', '=', 'kls.id')
                    ->where('kls.nama_kelas', $request->kelas);

                if ($request->filled('rombel')) {
                    $query->join('rombel as r', 'pd.rombel_id', '=', 'r.id')
                        ->where('r.nama_rombel', $request->rombel);
                }
            }
        }

        return $query;
    }

    public function applyAngkatanSantri(Builder $query, Request $request): Builder
    {
        if (! $request->filled('angkatan_santri')) {
            return $query;
        }

        $query->whereYear('s.tanggal_masuk', $request->angkatan_santri);

        return $query;
    }

    public function applyStatusPesertaFilter(Builder $query, Request $request): Builder
    {
        if (! $request->filled('status')) {
            return $query;
        }

        switch (strtolower($request->status)) {
            case 'santri':
                $query->where('s.status', 'aktif');
                break;
            case 'santri non pelajar':
                $query->where('s.status', 'aktif')
                    ->where(fn ($q) => $q->whereNull('pd.id')->orWhere('pd.status', '!=', 'aktif'));
                break;
            case 'pelajar':
                $query->where('pd.status', 'aktif');
                break;
            case 'pelajar non santri':
                $query->where('pd.status', 'aktif')
                    ->where('s.status', '!=', 'aktif');
                break;
            case 'santri-pelajar':
            case 'pelajar-santri':
                $query->where('pd.status', 'aktif')
                    ->where('s.status', 'aktif');
                break;
            default:
                $query->whereRaw('0 = 1');
        }

        return $query;
    }

    public function applyStatusWargaPesantrenFilter(Builder $query, Request $request): Builder
    {
        if (! $request->filled('warga_pesantren')) {
            return $query;
        }

        $flag = strtolower($request->warga_pesantren);
        if ($flag === 'memiliki niup') {
            $query->whereNotNull('wp.niup');
        } elseif ($flag === 'tanpa niup') {
            $query->whereNull('wp.niup');
        } else {
            $query->whereRaw('0 = 1');
        }

        return $query;
    }

    public function applyPhoneNumber(Builder $query, Request $request): Builder
    {
        if (! $request->filled('phone_number')) {
            return $query;
        }

        $pn = strtolower($request->phone_number);
        if ($pn === 'memiliki phone number') {
            $query->whereNotNull('b.no_telepon')
                ->where('b.no_telepon', '!=', '');
        } elseif ($pn === 'tidak ada phone number') {
            $query->where(fn ($q) => $q->whereNull('b.no_telepon')->orWhere('b.no_telepon', '=', ''));
        } else {
            $query->whereRaw('0 = 1');
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
