<?php

namespace App\Services\Pegawai\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FilterWaliKelasService
{
    public function applyAllFilters($query, Request $request)
    {
        $query = $this->applyAlamatFilter($query, $request);
        $query = $this->applyPhoneFilter($query, $request);
        $query = $this->applyNamaFilter($query, $request);
        $query = $this->applyGenderRomble($query, $request);
        $query = $this->applyLembagaFilter($query, $request);

        
        return $query;
    }
    private function applyAlamatFilter(Builder $query, Request $request): Builder
    {
                // Filter berdasarkan lokasi (negara, provinsi, kabupaten, kecamatan, desa)
                if ($request->filled('negara')) {
                    $negara = strtolower($request->negara);
                    $query->join('negara', 'b.negara_id', '=', 'negara.id')
                        ->whereRaw('LOWER(negara.nama_negara) = ?', [$negara]);
                }

                if ($request->filled('provinsi')) {
                    $provinsi = strtolower($request->provinsi);
                    $query->leftJoin('provinsi', 'b.provinsi_id', '=', 'provinsi.id')
                        ->whereRaw('LOWER(provinsi.nama_provinsi) = ?', [$provinsi]);
                }

                if ($request->filled('kabupaten')) {
                    $kabupaten = strtolower($request->kabupaten);
                    $query->leftJoin('kabupaten', 'b.kabupaten_id', '=', 'kabupaten.id')
                        ->whereRaw('LOWER(kabupaten.nama_kabupaten) = ?', [$kabupaten]);
                }

                if ($request->filled('kecamatan')) {
                    $kecamatan = strtolower($request->kecamatan);
                    $query->leftJoin('kecamatan', 'b.kecamatan_id', '=', 'kecamatan.id')
                        ->whereRaw('LOWER(kecamatan.nama_kecamatan) = ?', [$kecamatan]);
                }

        return $query;
    }
    private function applyPhoneFilter(Builder $query, Request $request): Builder
    {
        if ($request->filled('phone_number')) {
            $phone = strtolower($request->phone_number);
    
            if ($phone === 'memiliki phone number') {
                // Salah satu dari no_telepon atau no_telepon_2 harus terisi
                $query->where(function ($q) {
                    $q->whereNotNull('b.no_telepon')->where('b.no_telepon', '!=', '')
                      ->orWhere(function ($q2) {
                          $q2->whereNotNull('b.no_telepon_2')->where('b.no_telepon_2', '!=', '');
                      });
                });
            } elseif ($phone === 'tidak ada phone number') {
                // Keduanya harus kosong atau null
                $query->where(function ($q) {
                    $q->where(function ($q1) {
                        $q1->whereNull('b.no_telepon')->orWhere('b.no_telepon', '');
                    })->where(function ($q2) {
                        $q2->whereNull('b.no_telepon_2')->orWhere('b.no_telepon_2', '');
                    });
                });
            } else {
                $query->whereRaw('0 = 1'); // default error fallback
            }
        }
    
        return $query;
    }
    private function applyNamaFilter(Builder $query, Request $request): Builder
    {
        if ($request->filled('nama')) {
            $query->whereRaw("MATCH(nama) AGAINST(? IN BOOLEAN MODE)", [$request->nama]);
        }
        return $query;
    }
    private function applyGenderRomble(Builder $query, Request $request): Builder
    {
        if ($request->filled('gender_rombel')) {
            if (strtolower($request->gender_rombel) === 'putra') {
                $query->where('r.gender_rombel', 'putra');
            } elseif (strtolower($request->gender_rombel) === 'putri') {
                $query->where('r.gender_rombel', 'putri');
            }
    }
            return $query;
    }
    private function applyLembagaFilter(Builder $query, Request $request): Builder
    {
        if ($request->filled('lembaga')) {
            $query->whereRaw('LOWER(l.nama_lembaga) = ?', [strtolower($request->lembaga)]);
    
            if ($request->filled('jurusan')) {
                $query->whereRaw('LOWER(j.nama_jurusan) = ?', [strtolower($request->jurusan)]);
    
                if ($request->filled('kelas')) {
                    $query->whereRaw('LOWER(k.nama_kelas) = ?', [strtolower($request->kelas)]);
    
                    if ($request->filled('rombel')) {
                        $query->whereRaw('LOWER(r.nama_rombel) = ?', [strtolower($request->rombel)]);
                    }
                }
            }
        }
    
        return $query;
    }
}