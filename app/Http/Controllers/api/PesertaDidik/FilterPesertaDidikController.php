<?php

namespace App\Http\Controllers\api\PesertaDidik;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FilterPesertaDidikController extends Controller
{
    // Filter Wilayah (termasuk blok dan kamar)
    public function applyWilayahFilter($query, Request $request)
    {
        if ($request->filled('wilayah')) {
            $query->whereRaw('LOWER(w.nama_wilayah) = ?', [strtolower($request->wilayah)]);

            if ($request->filled('blok')) {
                $query->whereRaw('LOWER(bl.nama_blok) = ?', [strtolower($request->blok)]);
            }
            if ($request->filled('kamar')) {
                $query->whereRaw('LOWER(km.nama_kamar) = ?', [strtolower($request->kamar)]);
            }

            // Jika tidak ada data yang cocok, buat query kosong
            if (!$query->exists()) {
                $query->whereRaw('0 = 1');
            }
        }
        return $query;
    }

    // Filter Lembaga dan Pendidikan
    public function applyLembagaPendidikanFilter($query, Request $request)
    {
        if ($request->filled('lembaga')) {
            $query->where('l.nama_lembaga', $request->lembaga)
                ->when($request->filled('jurusan'), function ($q) use ($request) {
                    $q->where('j.nama_jurusan', $request->jurusan);
                    if ($request->filled('kelas')) {
                        $q->where('k.nama_kelas', $request->kelas);
                    }
                    if ($request->filled('rombel')) {
                        $q->where('r.nama_rombel', $request->rombel);
                    }
                });

            // Jika tidak ada data yang cocok, buat query kosong
            if (!$query->exists()) {
                $query->whereRaw('0 = 1');
            }
        }
        return $query;
    }

    // Filter Status Peserta (santri, pelajar, kombinasi)
    public function applyStatusPesertaFilter($query, Request $request)
    {
        if ($request->filled('status')) {
            switch (strtolower($request->status)) {
                case 'santri':
                    $query->whereNotNull('s.id')
                        ->where('s.status_santri', 'aktif');
                    break;
                case 'santri non pelajar':
                    $query->whereNotNull('s.id')->whereNull('p.id')
                        ->where('s.status_santri', 'aktif');
                    break;
                case 'pelajar':
                    $query->whereNotNull('p.id')->where('p.status_pelajar', 'aktif');
                    break;
                case 'pelajar non santri':
                    $query->whereNotNull('p.id')->whereNull('s.id')
                        ->where('p.status_pelajar', 'aktif');
                    break;
                case 'santri-pelajar':
                case 'pelajar-santri':
                    $query->whereNotNull('p.id')->whereNotNull('s.id')
                        ->where('p.status_pelajar', 'aktif')
                        ->where('s.status_santri', 'aktif');
                    break;
                default:
                    $query->whereRaw('0 = 1');
                    break;
            }
        }
        return $query;
    }

    public function applyStatusAlumniFilter($query, Request $request)
    {
        if ($request->filled('status_alumni')) {
            switch (strtolower($request->status_alumni)) {
                case 'alumni santri':
                    $query->whereNotNull('s.id');
                    break;
                case 'alumni santri non pelajar':
                    $query->whereNotNull('s.id')->whereNull('p.id');
                    break;
                case 'alumni santri tetapi masih pelajar aktif':
                    $query->join('pelajar as p', 'p.id_peserta_didik', '=', 'pd.id')
                        ->whereNotNull('s.id')->whereNotNull('p.id');
                    break;
                case 'alumni pelajar':
                    $query->whereNotNull('p.id');
                    break;
                case 'alumni pelajar non santri':
                    $query->whereNotNull('p.id')->whereNull('s.id');
                    break;
                case 'alumni pelajar tetapi masih santri aktif':
                    $query->join('santri as s', 's.id_peserta_didik', '=', 'pd.id')
                        ->whereNotNull('p.id')->whereNotNull('s.id');
                    break;
                case 'alumni pelajar sekaligus santri':
                case 'alumni santri sekaligus pelajar':
                    $query->whereNotNull('p.id')->whereNotNull('s.id');
                    break;
                default:
                    $query->whereRaw('0 = 1');
                    break;
            }
        }
        return $query;
    }

    // Filter Status Warga Pesantren (berdasarkan NIUP)
    public function applyStatusWargaPesantrenFilter($query, Request $request)
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

    // Filter Angkatan
    public function applyAngkatanPelajar($query, Request $request)
    {
        if ($request->filled('angkatan_pelajar')) {
            $query->where('p.angkatan_pelajar', $request->angkatan_pelajar);
        }
        if ($request->filled('angkatan_santri')) {
            $query->where('s.angkatan_santri', $request->angkatan_santri);
        }

        if (!$query->exists()) {
            $query->whereRaw('0 = 1');
        }

        return $query;
    }

    // Filter No Telepon
    public function applyPhoneNumber($query, Request $request)
    {
        if ($request->filled('phone_number')) {
            $phone_number = strtolower($request->phone_number);
            if ($phone_number == 'memiliki phone number') {
                $query->whereNotNull('b.no_telepon')->where('b.no_telepon', '!=', '');
            } else if ($phone_number == 'tidak ada phone number') {
                $query->whereNull('b.no_telepon')->orWhere('b.no_telepon', '=', '');
            } else {
                $query->whereRaw('0 = 1');
            }
        }
        return $query;
    }

    // Filter Pemberkasan
    public function applyPemberkasan($query, Request $request)
    {
        if ($request->filled('pemberkasan')) {
            $pemberkasan = strtolower($request->pemberkasan);
            switch ($pemberkasan) {
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
                    break;
            }
        }
        return $query;
    }

    // Sorting
    public function applySorting($query, Request $request)
    {
        if ($request->filled('sort_by')) {
            $allowedSorts = ['id', 'nama', 'niup', 'jenis_kelamin'];
            $sortBy = strtolower($request->sort_by);
            if (in_array($sortBy, $allowedSorts)) {
                $sortOrder = ($request->filled('sort_order') && strtolower($request->sort_order) === 'desc') ? 'desc' : 'asc';
                $query->orderBy($sortBy, $sortOrder);
            } else {
                $query->whereRaw('0 = 1'); // Jika parameter sort tidak valid, hasilkan query kosong
            }
        } else {
            $query->orderBy('pd.id', 'asc'); // Default sorting
        }
        return $query;
    }

    public function applyKewaliasuhan($query, Request $request)
    {
        if ($request->filled('kewaliasuhan')) {
            $kewaliasuhan = strtolower($request->kewaliasuhan);
            if ($kewaliasuhan == 'waliasuh or anakasuh') {
                $query->leftjoin('wali_asuh as wa', 'wa.id_santri', '=', 's.id')
                    ->leftjoin('anak_asuh as aa', 'aa.id_santri', '=', 's.id');
                $query->whereNotNull('wa.id')
                    ->orWhereNotNull('aa.id');
            } else {
                $query->whereRaw('0 = 1');
            }
            if ($kewaliasuhan == 'non kewaliasuhan') {
                $query->leftjoin('wali_asuh as wa', 'wa.id_santri', '=', 's.id')
                    ->leftjoin('anak_asuh as aa', 'aa.id_santri', '=', 's.id');
                $query->whereNull('wa.id')
                    ->WhereNull('aa.id');
            } else {
                $query->whereRaw('0 = 1');
            }
        }
        return $query;
    }

    public function applyStatusSaudara($query, Request $request)
    {
        if ($request->filled('status_saudara')) {
            $status_saudara = strtolower($request->status_saudara);
            switch ($status_saudara) {
                case 'ibu kandung terisi':
                    $query->whereNotNull('parents.nama_ibu');
                    break;

                case 'ibu kandung tidak terisi':
                    $query->where(function ($sub) {
                        $sub->whereNull('parents.nama_ibu')
                            ->orWhere('parents.nama_ibu', 'Tidak Diketahui');
                    });
                    break;

                case 'kk sama dgn ibu kandung':
                    // Pastikan sudah join subquery ibu_info di atas
                    $query->whereColumn('keluarga.no_kk', '=', 'ibu_info.kk_ibu');
                    break;

                case 'kk berbeda dgn ibu kandung':
                    // Pastikan sudah join subquery ibu_info di atas
                    $query->whereColumn('keluarga.no_kk', '!=', 'ibu_info.kk_ibu');
                    break;
                default:
                    $query->whereRaw('0 = 1');
                    break;
            }
        }
        return $query;
    }

    public function applyWafat($query, Request $request)
    {
        // Filter Wafat atau Hidup
        if ($request->filled('wafat')) {
            $wafat = strtolower($request->wafat);
            if ($wafat == 'sudah wafat') {
                $query->where('b.wafat', true);
            } else if ($wafat == 'masih hidup') {
                $query->where('b.wafat', false);
            } else {
                $query->whereRaw('0 = 1');
            }
        }
        return $query;
    }
}
