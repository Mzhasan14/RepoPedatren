<?php

namespace App\Services\Pegawai\Filters;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FilterKaryawanService
{
    public function applyAllFilters($query, Request $request)
    {
        $query = $this->applyAlamatFilter($query, $request);
        $query = $this->applyjabatanKaryawanFilter($query, $request);
        $query = $this->applyLembagaFilter($query, $request);
        $query = $this->applyWargaPesantrenFilter($query, $request);
        $query = $this->applyNamaFilter($query, $request);
        $query = $this->applyPemberkasanFilter($query, $request);
        $query = $this->applyUmurFilter($query, $request);
        $query = $this->applyPhoneFilter($query, $request);
        $query = $this->applyJenisKelaminFilter($query, $request);
        $query = $this->applySmartcardFilter($query, $request);
        $query = $this->applyGolonganJabatanFilter($query, $request);
        return $query;
    }
    private function applyNamaFilter(Builder $query, Request $request): Builder
    {
        if ($request->filled('nama')) {
            $query->whereRaw("MATCH(nama) AGAINST(? IN BOOLEAN MODE)", [$request->nama]);
        }
        return $query;
    }
    private function applyUmurFilter(Builder $query, Request $request): Builder
    {
        if ($request->filled('umur')) {
            $umurInput = $request->umur;
    
            if (strpos($umurInput, '-') !== false) {
                [$umurMin, $umurMax] = explode('-', $umurInput);
            } else {
                $umurMin = $umurInput;
                $umurMax = $umurInput;
            }
    
            $query->whereBetween(
                DB::raw('TIMESTAMPDIFF(YEAR, b.tanggal_lahir, CURDATE())'),
                [(int) $umurMin, (int) $umurMax]
            );
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
    private function applySmartcardFilter(Builder $query, Request $request): Builder
    {
        if ($request->filled('smartcard')) {
            $smartcard = strtolower($request->smartcard);
            if ($smartcard == 'memiliki smartcard') {
                $query->whereNotNull('b.smartcard');
            } else if ($smartcard == 'tanpa smartcard') {
                $query->whereNull('b.smartcard');
            } else {
                $query->whereRaw('0 = 1');
            }
        }
        return $query;
    }
    private function applyPemberkasanFilter(Builder $query, Request $request): Builder
    {
        if (! $request->filled('pemberkasan')) {
            return $query;
        }

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
        return $query;
    }
    private function applyWargaPesantrenFilter(Builder $query, Request $request): Builder
    {
        if ($request->filled('warga_pesantren')) {
            $status = strtolower($request->warga_pesantren);
            if ($status === 'memiliki niup') {
                $query->whereNotNull('wp.niup');
            } elseif ($status === 'tanpa niup') {
                $query->whereNull('wp.niup');
            } else {
                $query->whereRaw('0 = 1');
            }
        }
        return $query;
    }
    private function applyGolonganJabatanFilter(Builder $query, Request $request): Builder
    {

           if ($request->filled('golongan')){
               $query->where('g.nama_golongan_jabatan',strtolower($request->golongan));
           }
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
    private function applyJenisKelaminFilter(Builder $query, Request $request): Builder
    {

        // 🔹 Filter jenis kelamin (dari biodata)
        if ($request->filled('jenis_kelamin')) {
            // normalisasi input: lowercase, buang spasi dan strip
            $raw = $request->input('jenis_kelamin');
            $input = strtolower(str_replace([' ', '-'], '', $raw));

            // definisi map untuk male ('l') & female ('p')
            $mapping = [
                'l' => ['l', 'laki', 'lakilaki', 'pria', 'ayah'],
                'p' => ['p', 'perempuan', 'wanita', 'ibu'],
            ];

            if (in_array($input, $mapping['l'], true)) {
                $query->where('b.jenis_kelamin', 'l');
            } elseif (in_array($input, $mapping['p'], true)) {
                $query->where('b.jenis_kelamin', 'p');
            }
            // jika input tidak cocok, kita skip filter—hasil tidak akan di‐empty
        }
        return $query;
    }
    private function applyjabatanKaryawanFilter(Builder $query, Request $request): Builder
    {
        if ($request->filled('jabatan')) {
            $jabatan = strtolower($request->jabatan);
    
            // Mapping dari input user ke nilai yang sesuai di database
            $opsi = [
                'kultural' => 'kultural',
                'kontrak' => 'kontrak',
                'pengkaderan' => 'pengkaderan',
                'tetap' => 'tetap',
            ];
    
            if (array_key_exists($jabatan, $opsi)) {
                $query->where('karyawan.jabatan', $opsi[$jabatan]);
            }
        }
    
        return $query;
    }
    private function applyLembagaFilter(Builder $query, Request $request): Builder
    {
        if ($request->filled('lembaga')) {
            $query->whereRaw('LOWER(l.nama_lembaga) = ?', [strtolower($request->lembaga)]);
        }
    
        return $query;
    }
}