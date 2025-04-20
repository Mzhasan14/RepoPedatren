<?php

namespace App\Services;

use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;

class FilterKhadamService
{
    /**
     * Panggil semua filter berurutan
     */
    public function applyAllFilters(Builder $query, Request $request): Builder
    {
        $query = $this->applyAlamatFilter($query, $request);
        $query = $this->applyJenisKelaminFilter($query, $request);
        $query = $this->applySmartcardFilter($query, $request);
        $query = $this->applyNamaFilter($query, $request);
        $query = $this->applyWilayahFilter($query, $request);
        $query = $this->applyLembagaPendidikanFilter($query, $request);
        $query = $this->applyStatusWargaPesantrenFilter($query, $request);
        $query = $this->applyPhoneNumber($query, $request);
        $query = $this->applyPemberkasan($query, $request);

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
                    $query->where('kabupaten.nama_kabupaten', $request->kabupaten);

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

    public function applySmartcardFilter(Builder $query, Request $request): Builder
    {
        if (! $request->filled('smartcard')) {
            return $query;
        }
        
        if ($request->filled('smartcard')) {
            $smartcard = strtolower($request->smartcard);
            if ($smartcard === 'memiliki smartcard') {
                $query->whereNotNull('b.smartcard');
            } elseif ($smartcard === 'tanpa smartcard') {
                $query->whereNull('b.smartcard');
            } else {
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

    public function applyWilayahFilter(Builder $query, Request $request): Builder
    {
        if (! $request->filled('wilayah')) {
            return $query;
        }
        
        if ($request->filled('wilayah')) {
            $query->join('peserta_didik AS pd', 'pd.id_biodata', '=', 'b.id')
                ->join('riwayat_domisili AS rd', 'rd.id_peserta_didik', '=', 'pd.id')
                ->join('wilayah AS w', 'rd.id_wilayah', '=', 'w.id')
                ->where('w.nama_wilayah', $request->wilayah);

            if ($request->filled('blok')) {
                $query->join('blok AS bl', 'rd.id_blok', '=', 'bl.id')
                    ->where('bl.nama_blok', $request->blok);

                if ($request->filled('kamar')) {
                    $query->join('kamar AS km', 'rd.id_kamar', '=', 'km.id')
                        ->where('km.nama_kamar', $request->kamar);
                }
            }
        } else {
            $query->whereRaw('0 = 1');
        }

        return $query;
    }

    public function applyLembagaPendidikanFilter(Builder $query, Request $request): Builder
    {
        if (! $request->filled('lembaga')) {
            return $query;
        }

        if ($request->filled('lembaga')) {
            $query->join('peserta_didik AS pd', 'pd.id_biodata', '=', 'b.id')
                ->join('riwayat_pendidikan AS rp', 'rp.id_peserta_didik', '=', 'pd.id')
                ->join('lembaga AS l', 'rp.id_lembaga', '=', 'l.id')
                ->where('l.nama_lembaga', $request->lembaga);

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

    public function applyStatusWargaPesantrenFilter(Builder $query, Request $request): Builder
    {
        if (! $request->filled('warga_pesantren')) {
            return $query;
        }

        if ($request->filled('warga_pesantren')) {
            $flag = strtolower($request->warga_pesantren);
            if ($flag === 'memiliki niup') {
                $query->whereNotNull('wp.niup');
            } elseif ($flag === 'tanpa niup') {
                $query->whereNull('wp.niup');
            } else {
                $query->whereRaw('0 = 1');
            }
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

    public function applyPemberkasan(Builder $query, Request $request): Builder
    {
        if (! $request->filled('pemberkasan')) {
            return $query;
        }
        if ($request->filled('pemberkasan')) {
            switch (strtolower($request->pemberkasan)) {
                case 'tidak ada berkas':
                    $query->whereNull('br.id_biodata');
                    break;
                case 'tidak ada foto diri':
                    $query->where('br.id_jenis_berkas', 4)->whereNull('br.file_path');
                    break;
                case 'memiliki foto diri':
                    $query->where('br.id_jenis_berkas', 4)->whereNotNull('br.file_path');
                    break;
                case 'tidak ada kk':
                    $query->where('br.id_jenis_berkas', 1)->whereNull('br.file_path');
                    break;
                case 'tidak ada akta kelahiran':
                    $query->where('br.id_jenis_berkas', 3)->whereNull('br.file_path');
                    break;
                case 'tidak ada ijazah':
                    $query->where('br.id_jenis_berkas', 5)->whereNull('br.file_path');
                    break;
                default:
                    $query->whereRaw('0 = 1');
            }
        }
        return $query;
    }
}
