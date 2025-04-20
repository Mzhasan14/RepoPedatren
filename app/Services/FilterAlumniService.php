<?php

namespace App\Services;

use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;

class FilterAlumniService
{
    /**
     * Panggil semua filter berurutan
     */
    public function applyAllFilters(Builder $query, Request $request): Builder
    {
        $query = $this->applyAlamatFilter($query, $request);
        $query = $this->applyJenisKelaminFilter($query, $request);
        $query = $this->applyNamaFilter($query, $request);
        $query = $this->applyLembagaPendidikanFilter($query, $request);
        $query = $this->applyStatusAlumniFilter($query, $request);
        $query = $this->applyAngkatanPelajar($query, $request);
        $query = $this->applyPhoneNumber($query, $request);
        $query = $this->applyWafat($query, $request);

        return $query;
    }

    public function applyAlamatFilter(Builder $query, Request $request): Builder
    {
        if (! $request->filled('negara')) {
            return $query;
        }

        // Filter berdasarkan lokasi (negara, provinsi, kabupaten, kecamatan, desa)
        if ($request->filled('negara')) {
            $query->join('negara', 'b.id_negara', '=', 'negara.id')
                ->where('negara.nama_negara', $request->negara);

            if ($request->filled('provinsi')) {
                $query->leftJoin('provinsi', 'b.id_provinsi', '=', 'provinsi.id')
                    ->where('provinsi.nama_provinsi', $request->provinsi);

                if ($request->filled('kabupaten')) {
                    // Pastikan join ke tabel kabupaten dilakukan sebelum pemakaian filter
                    $query->where('kb.nama_kabupaten', $request->kabupaten);

                    if ($request->filled('kecamatan')) {
                        $query->leftJoin('kecamatan', 'b.id_kecamatan', '=', 'kecamatan.id')
                            ->where('kecamatan.nama_kecamatan', $request->kecamatan);
                    }
                } else {
                    // Jika nilai jenis_kelamin tidak valid, hasilkan query kosong
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

        if ($request->filled('jenis_kelamin')) {
            $jenis_kelamin = strtolower($request->jenis_kelamin);
            if ($jenis_kelamin === 'laki-laki' || $jenis_kelamin === 'ayah') {
                $query->where('b.jenis_kelamin', 'l');
            } elseif ($jenis_kelamin === 'perempuan' || $jenis_kelamin === 'ibu') {
                $query->where('b.jenis_kelamin', 'p');
            } else {
                // Jika nilai jenis_kelamin tidak valid, hasilkan query kosong
                $query->whereRaw('0 = 1');
            }
        }
        return $query;
    }

    public function applyNamaFilter(Builder $query, Request $request): Builder
    {
        if (! $request->filled('nama')) {
            return $query;
        }

        if ($request->filled('nama')) {
            $query->whereRaw("MATCH(nama) AGAINST(? IN BOOLEAN MODE)", [$request->nama]);
        }
        return $query;
    }

    public function applyLembagaPendidikanFilter(Builder $query, Request $request): Builder
    {
        if (! $request->filled('lembaga')) {
            return $query;
        }

        if ($request->filled('lembaga')) {
            $query->where('l.nama_lembaga', $request->lembaga);

            if ($request->filled('jurusan')) {
                $query->join('jurusan AS j', 'rp.id_jurusan', '=', 'j.id')
                    ->where('j.nama_jurusan', $request->jurusan);

                if ($request->filled('kelas')) {
                    $query->join('kelas AS kls', 'rp.id_kelas', '=', 'kls.id')
                        ->where('kls.nama_kelas', $request->kelas);

                    if ($request->filled('rombel')) {
                        $query->join('rombel AS r', 'rp.id_rombel', '=', 'r.id')
                            ->where('r.nama_rombel', $request->rombel);
                    }
                }
            }
        } else {
            $query->whereRaw('0 = 1');
        }
        return $query;
    }

    public function applyStatusAlumniFilter(Builder $query, Request $request): Builder
    {
        if (! $request->filled('status')) {
            return $query;
        }

        if ($request->filled('status')) {
            switch (strtolower($request->status)) {
                case 'alumni santri':
                    $query->where('s.status', 'alumni');
                    break;
                case 'alumni santri non pelajar':
                    $query->where('s.status', 'alumni')
                        ->where(fn($q) => $q->whereNull('p.id')->orWhere('p.status', '!=', 'aktif'));
                    break;
                case 'alumni santri tetapi masih pelajar aktif':
                    $query->where('s.status', 'alumni')
                        ->where(fn($q) => $q->whereNotNull('p.id')->orWhere('p.status', '=', 'aktif'));
                    break;
                case 'alumni pelajar':
                    $query->where('p.status', 'alumni');
                    break;
                case 'alumni pelajar non santri':
                    $query->where('p.status', 'alumni')
                        ->where(fn($q) => $q->whereNull('s.id')->orWhere('s.status', '!=', 'aktif'));
                    break;
                case 'alumni pelajar tetapi masih santri aktif':
                    $query->where('p.status', 'alumni')
                        ->where(fn($q) => $q->whereNotNull('s.id')->orWhere('s.status', '=', 'aktif'));
                    break;
                case 'alumni pelajar sekaligus santri':
                case 'alumni santri sekaligus pelajar':
                    $query->whereNotNull('p.id')
                        ->whereNotNull('s.id')
                        ->where('p.status', 'alumni')
                        ->where('s.status', 'alumni');
                    break;
                default:
                    $query->whereRaw('0 = 1');
            }
        }
        return $query;
    }

    public function applyAngkatanSantri(Builder $query, Request $request): Builder
    {
        if (! $request->filled('angkatan_santri')) {
            return $query;
        }

        if ($request->filled('angkatan_santri')) {
            $query->where('s.angkatan_santri', $request->angkatan_santri);
        } else {
            $query->whereRaw('0 = 1');
        }
        return $query;
    }

    public function applyAngkatanPelajar(Builder $query, Request $request): Builder
    {
        if (! $request->filled('angkatan_pelajar')) {
            return $query;
        }
        if ($request->filled('angkatan_pelajar')) {
            $query->where('p.angkatan_pelajar', $request->angkatan_pelajar);
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

        if ($request->filled('phone_number')) {
            $pn = strtolower($request->phone_number);
            if ($pn === 'memiliki phone number') {
                $query->whereNotNull('b.no_telepon')->where('b.no_telepon', '!=', '');
            } elseif ($pn === 'tidak ada phone number') {
                $query->where(fn($q) => $q->whereNull('b.no_telepon')->orWhere('b.no_telepon', '=', ''));
            } else {
                $query->whereRaw('0 = 1');
            }
        }
        return $query;
    }

    public function applyWafat(Builder $query, Request $request): Builder
    {
        if (! $request->filled('wafat')) {
            return $query;
        }

        if ($request->filled('wafat')) {
            $w = strtolower($request->wafat);
            if ($w === 'sudah wafat') {
                $query->where('b.wafat', true);
            } elseif ($w === 'masih hidup') {
                $query->where('b.wafat', false);
            } else {
                $query->whereRaw('0 = 1');
            }
        }
        return $query;
    }
}
