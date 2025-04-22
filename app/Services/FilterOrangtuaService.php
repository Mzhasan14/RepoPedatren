<?php

namespace App\Services;

use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;

class FilterOrangtuaService {

    public function applyAllFilters(Builder $query, Request $request): Builder {
        $query = $this->applyAlamatFilter($query,$request);
        $query = $this->applyJenisKelaminFilter($query, $request);
        $query = $this->applySmartcardFilter($query, $request);
        $query = $this->applyNamaFilter($query, $request);
        $query = $this->applyPhoneNumber($query, $request);
        $query = $this->applyWafatFilter($query, $request);

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

    public function applySmartcardFilter(Builder $query, Request $request): Builder
    {
        if (! $request->filled('smartcard')) {
            return $query;
        }

        $smartcard = strtolower($request->smartcard);
        if ($smartcard === 'memiliki smartcard') {
            $query->whereNotNull('b.smartcard');
        } elseif ($smartcard === 'tanpa smartcard') {
            $query->whereNull('b.smartcard');
        } else {
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
            $query->whereNotNull('orang_tua_wali.wafat');
        } elseif ($wafat === 'masih hidup') {
            $query->whereNull('orang_tua_wali.wafat');
        } else {
            $query->whereRaw('0 = 1');
        }

        return $query;
    }
}

