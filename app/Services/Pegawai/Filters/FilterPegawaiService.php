<?php

namespace App\Services\Pegawai\Filters;

use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FilterPegawaiService
{
    public function applyAllFilters($query, Request $request)
    {
        $query = $this->applyAlamatFilter($query, $request);
        $query = $this->applyEntitasPegawaiFilter($query, $request);
        $query = $this->applyLembagaFilter($query, $request);
        $query = $this->applyWargaPesantrenFilter($query, $request);
        $query = $this->applyNamaFilter($query, $request);
        $query = $this->applyPemberkasanFilter($query, $request);
        $query = $this->applyUmurFilter($query, $request);
        $query = $this->applyPhoneFilter($query, $request);
        $query = $this->applyJenisKelaminFilter($query, $request);

        // $query = $this->applyStatusFilter($query, $request);

        return $query;
    }

    private function applyLembagaFilter(Builder $query, Request $request): Builder
    {
        // Lembaga filter: hanya pegawai yang memiliki lembaga yang sesuai
        if ($request->filled('lembaga')) {
            $query
                ->leftJoin('lembaga as lk', 'lk.id', '=', 'karyawan.lembaga_id')
                ->leftJoin('lembaga as lp', 'lp.id', '=', 'pengajar.lembaga_id')
                ->leftJoin('lembaga as lw', 'lw.id', '=', 'wali_kelas.lembaga_id')
                ->where(function ($q) use ($request) {
                    $nama = strtolower($request->lembaga);
                    $q->whereRaw('LOWER(lk.nama_lembaga) = ?', [$nama])
                        ->orWhereRaw('LOWER(lp.nama_lembaga) = ?', [$nama])
                        ->orWhereRaw('LOWER(lw.nama_lembaga) = ?', [$nama]);
                });
        }

        // Jurusan filter: hanya untuk wali_kelas
        if ($request->filled('jurusan')) {
            $query->leftJoin('jurusan as j', 'j.id', '=', 'wali_kelas.jurusan_id')
                ->whereRaw('LOWER(j.nama_jurusan) = ?', [strtolower($request->jurusan)]);
        }

        // Kelas filter: hanya untuk wali_kelas
        if ($request->filled('kelas')) {
            $query->leftJoin('kelas as k', 'k.id', '=', 'wali_kelas.kelas_id')
                ->whereRaw('LOWER(k.nama_kelas) = ?', [strtolower($request->kelas)]);
        }

        // Rombel filter: hanya untuk wali_kelas
        if ($request->filled('rombel')) {
            $query->leftJoin('rombel as r', 'r.id', '=', 'wali_kelas.rombel_id')
                ->whereRaw('LOWER(r.nama_rombel) = ?', [strtolower($request->rombel)]);
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
    // private function applyEntitasPegawaiFilter(Builder $query, Request $request): Builder
    // {
    //     if ($request->filled('entitas')) {
    //         $entitas = strtolower($request->entitas);

    //         switch ($entitas) {
    //             case 'pengajar':
    //                 $query->whereNotNull('pengajar.id');
    //                 break;
    //             case 'pengurus':
    //                 $query->whereNotNull('pengurus.id');
    //                 break;
    //             case 'karyawan':
    //                 $query->whereNotNull('karyawan.id');
    //                 break;
    //             case 'wali kelas':
    //                 $query->whereNotNull('wali_kelas.id');
    //                 break;
    //         }
    //     }

    //     return $query;
    // }
    private function applyEntitasPegawaiFilter(Builder $query, Request $request): Builder
    {
        if ($request->filled('entitas')) {
            $entitas = strtolower($request->entitas);

            if ($entitas == 'pengajar') {
                $query->whereNotNull('pengajar.id');
            } elseif ($entitas == 'pengurus') {
                $query->whereNotNull('pengurus.id');
            } elseif ($entitas == 'karyawan') {
                $query->whereNotNull('karyawan.id');
            } elseif ($entitas == 'pengajar pengurus') {
                $query->whereNotNull('pengajar.id')
                    ->WhereNotNull('pengurus.id');
            } elseif ($entitas == 'pengajar karyawan') {
                $query->whereNotNull('pengajar.id')
                    ->WhereNotNull('karyawan.id');
            } elseif ($entitas == 'pengurus karyawan') {
                $query->whereNotNull('pengurus.id')
                    ->WhereNotNull('karyawan.id');
            } elseif ($entitas == 'pengajar pengurus karyawan') {
                $query->whereNotNull('pengajar.id')
                    ->WhereNotNull('pengurus.id')
                    ->WhereNotNull('karyawan.id');
            }
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

    public function applyNamaFilter(Builder $query, Request $request): Builder
    {
        if (! $request->filled('nama')) {
            return $query;
        }

        $nama = trim($request->nama);

        return $query->whereRaw('LOWER(b.nama) LIKE ?', ['%' . strtolower($nama) . '%']);
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
                $query->where(function ($q) {
                    $q->where('br.jenis_berkas_id', 4)->whereNull('br.file_path')
                        ->orWhereNull('br.jenis_berkas_id');
                });
                break;

            case 'memiliki foto diri':
                $query->where('br.jenis_berkas_id', 4)->whereNotNull('br.file_path');
                break;

            case 'memiliki kk':
                $query->where('br.jenis_berkas_id', 1)->whereNotNull('br.file_path');
                break;

            case 'memiliki akta kelahiran':
                $query->where('br.jenis_berkas_id', 3)->whereNotNull('br.file_path');
                break;

            case 'memiliki ijazah':
                $query->where('br.jenis_berkas_id', 5)->whereNotNull('br.file_path');
                break;

            default:
                $query->whereRaw('0 = 1'); // fallback: tidak ada hasil
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

    private function applySmartcardFilter(Builder $query, Request $request): Builder
    {
        if ($request->filled('smartcard')) {
            $smartcard = strtolower($request->smartcard);
            if ($smartcard == 'memiliki smartcard') {
                $query->whereNotNull('b.smartcard');
            } elseif ($smartcard == 'tanpa smartcard') {
                $query->whereNull('b.smartcard');
            } else {
                $query->whereRaw('0 = 1');
            }
        }

        return $query;
    }
    // private function applyStatusFilter(Builder $query, Request $request): Builder
    // {
    //     if ($request->filled('status')) {
    //         $status = $request->get('status');

    //         if ($status === 'aktif') {
    //             $query->where('pegawai.status_aktif', '=', 'aktif');
    //         } elseif ($status === 'tidak_aktif') {
    //             $query->where('pegawai.status_aktif', '=', 'tidak aktif');
    //         }
    //     } else {
    //         // Jika tidak ada filter status, default filter ke 'aktif'
    //         $query->where('pegawai.status_aktif', '=', 'aktif');
    //     }

    //     return $query;
    // }

}
