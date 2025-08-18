<?php

namespace App\Services\PesertaDidik\Filters;

use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;

class FilterKhadamService
{
    /**
     * Panggil semua filter berurutan
     */
    public function khadamFilters(Builder $query, Request $request): Builder
    {
        $query = $this->applyAlamatFilter($query, $request);
        $query = $this->applyJenisKelaminFilter($query, $request);
         
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
            $query->join('negara', 'b.negara_id', '=', 'negara.id')
                ->where('negara.nama_negara', $request->negara);

            if ($request->filled('provinsi')) {
                $query->leftJoin('provinsi', 'b.provinsi_id', '=', 'provinsi.id')
                    ->where('provinsi.nama_provinsi', $request->provinsi);

                if ($request->filled('kabupaten')) {
                    // Pastikan join ke tabel kabupaten dilakukan sebelum pemakaian filter
                    $query->join('kabupaten', 'b.kabupaten_id', '=', 'kabupaten.id')
                        ->where('kabupaten.nama_kabupaten', $request->kabupaten);

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

            return $query->where(fn ($q) => $q->whereNull('ds.id')->orWhere('ds.status', '!=', 'aktif'));
        }

        if ($request->filled('wilayah')) {
            $query->join('domisili_santri AS ds', fn ($join) => $join->on('s.id', '=', 'ds.santri_id')->where('ds.status', 'aktif'))
                ->join('wilayah AS w', 'ds.wilayah_id', '=', 'w.id')
                ->where('w.nama_wilayah', $request->wilayah);

            if ($request->filled('blok')) {
                $query->join('blok AS bl', 'ds.blok_id', '=', 'bl.id')
                    ->where('bl.nama_blok', $request->blok);

                if ($request->filled('kamar')) {
                    $query->join('kamar AS km', 'ds.kamar_id', '=', 'km.id')
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
            $query->join('pendidikan AS pd', fn ($j) => $j->on('s.id', '=', 'pd.santri_id')->where('pd.status', 'aktif'))
                ->join('lembaga AS l', 'pd.lembaga_id', '=', 'l.id')
                ->where('l.nama_lembaga', $request->lembaga);

            if ($request->filled('jurusan')) {
                $query->join('jurusan AS j', 'pd.jurusan_id', '=', 'j.id')
                    ->where('j.nama_jurusan', $request->jurusan);

                if ($request->filled('kelas')) {
                    $query->join('kelas AS kls', 'pd.kelas_id', '=', 'kls.id')
                        ->where('kls.nama_kelas', $request->kelas);

                    if ($request->filled('rombel')) {
                        $query->join('rombel AS r', 'pd.rombel_id', '=', 'r.id')
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
            $query->whereNotNull('b.no_telepon')->where('b.no_telepon', '!=', '');
        } elseif ($pn === 'tidak ada phone number') {
            $query->where(fn ($q) => $q->whereNull('b.no_telepon')->orWhere('b.no_telepon', '=', ''));
        } else {
            $query->whereRaw('0 = 1');
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
                    $query->whereNull('br.biodata_id');
                    break;
                case 'tidak ada foto diri':
                    $query->where('br.jenis_berkas_id', 4)->whereNull('br.file_path');
                    break;
                case 'memiliki foto diri':
                    $query->where('br.jenis_berkas_id', 4)->whereNotNull('br.file_path');
                    break;
                case 'tidak ada kk':
                    $query->where('br.jenis_berkas_id', 1)->whereNull('br.file_path');
                    break;
                case 'tidak ada akta kelahiran':
                    $query->where('br.jenis_berkas_id', 3)->whereNull('br.file_path');
                    break;
                case 'tidak ada ijazah':
                    $query->where('br.jenis_berkas_id', 5)->whereNull('br.file_path');
                    break;
                default:
                    $query->whereRaw('0 = 1');
            }
        }

        return $query;
    }
}
