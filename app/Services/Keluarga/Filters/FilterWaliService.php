<?php

namespace App\Services\Keluarga\Filters;

use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;

class FilterWaliService
{
    public function waliFilters(Builder $query, Request $request): Builder
    {
        $query = $this->applyAlamatFilter($query, $request);
        $query = $this->applyJenisKelaminFilter($query, $request);
        $this->applyJenisKelaminAnakFilter($query, $request);

        $query = $this->applyNamaFilter($query, $request);
        $query = $this->applyPhoneNumber($query, $request);
        $query = $this->applyWafatFilter($query, $request);

        return $query;
    }

    public function applyAlamatFilter(Builder $query, Request $request): Builder
    {
        // Cek pertama kali hanya untuk negara
        if (! $request->filled('negara')) {
            return $query; // Jika negara tidak diisi, tidak perlu filter lebih lanjut
        }

        // Filter berdasarkan negara
        $query->join('negara', 'b.negara_id', '=', 'negara.id')
            ->where('negara.nama_negara', $request->negara);

        // Filter berdasarkan provinsi (hanya jika diisi)
        if ($request->filled('provinsi')) {
            $query->leftJoin('provinsi', 'b.provinsi_id', '=', 'provinsi.id')
                ->where('provinsi.nama_provinsi', $request->provinsi);

            if ($request->filled('kabupaten')) {
                $query->where('kb.nama_kabupaten', $request->kabupaten);
            }

            if ($request->filled('kecamatan')) {
                $query->leftJoin('kecamatan', 'b.kecamatan_id', '=', 'kecamatan.id')
                    ->where('kecamatan.nama_kecamatan', $request->kecamatan);
            }
        }
        // Jika provinsi tidak diisi, maka filter kabupaten, kecamatan, desa tidak akan diterapkan.
        // Ini adalah perilaku yang benar.

        return $query;
    }

    public function applyJenisKelaminFilter(Builder $query, Request $request): Builder
    {
        if (! $request->filled('jenis_kelamin')) {
            return $query;
        }

        $jenis_kelamin = strtolower($request->jenis_kelamin);
        // if ($jenis_kelamin === 'laki-laki' || $jenis_kelamin === 'ayah') {
        //     $query->where('b.jenis_kelamin', 'l');
        // } elseif ($jenis_kelamin === 'perempuan' || $jenis_kelamin === 'ibu') {
        //     $query->where('b.jenis_kelamin', 'p');
        // } else {
        //     // Jika nilai jenis_kelamin tidak valid, hasilkan query kosong
        //     $query->whereRaw('0 = 1');
        // }
        if (in_array($jenis_kelamin, ['laki-laki', 'l', 'ayah'])) {
            $query->where('b.jenis_kelamin', 'l');
        } elseif (in_array($jenis_kelamin, ['perempuan', 'p', 'ibu'])) {
            $query->where('b.jenis_kelamin', 'p');
        } else {
            // Jika nilai jenis_kelamin tidak valid, hasilkan query kosong
            $query->whereRaw('0 = 1');
        }

        return $query;
    }

    public function applyJenisKelaminAnakFilter(Builder $query, Request $request): Builder
    {
        if (! $request->filled('jenis_kelamin_anak')) {
            return $query;
        }

        $jenis_kelamin = strtolower($request->jenis_kelamin_anak);
        if ($jenis_kelamin === 'laki-laki' || $jenis_kelamin === 'ayah') {
            $query->where('ba.jenis_kelamin', 'l');
        } elseif ($jenis_kelamin === 'perempuan' || $jenis_kelamin === 'ibu') {
            $query->where('ba.jenis_kelamin', 'p');
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

        $nama = trim($request->nama);

        return $query->whereRaw('LOWER(b.nama) LIKE ?', ['%' . strtolower($nama) . '%']);
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
            $query->where(fn($q) => $q->whereNull('b.no_telepon')->orWhere('b.no_telepon', '=', ''));
        } else {
            $query->whereRaw('0 = 1');
        }

        return $query;
    }

    public function applyWafatFilter(Builder $query, Request $request): Builder
    {
        if (! $request->filled('wafat')) {
            return $query;
        }

        $wafat = strtolower($request->wafat);

        if ($wafat === 'sudah wafat') {
            $query->where('b.wafat', true);
        } elseif ($wafat === 'masih hidup') {
            $query->where('b.wafat', false);
        } else {
            // Jika nilai tidak valid, tidak menampilkan data apapun
            $query->whereRaw('0 = 1');
        }

        return $query;
    }
}
