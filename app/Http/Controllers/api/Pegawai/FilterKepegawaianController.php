<?php

namespace App\Http\Controllers\Api\Pegawai;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FilterKepegawaianController extends Controller
{
    public function applySearchFilter($query, Request $request) {
        if ($request->filled('search')) {
            $search = strtolower($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('b.nik', 'LIKE', "%$search%")
                    ->orWhere('b.no_passport', 'LIKE', "%$search%")
                    ->orWhere('b.nama', 'LIKE', "%$search%")
                    ->orWhere('wp.niup', 'LIKE', "%$search%")
                    ->orWhere('l.nama_lembaga', 'LIKE', "%$search%")
                    ->orWhere('w.nama_wilayah', 'LIKE', "%$search%")
                    ->orWhere('kb.nama_kabupaten', 'LIKE', "%$search%");
            });
        }
        return $query;
    }
    
    public function applyWilayahFilter($query, Request $request) {
        if ($request->filled('wilayah')) {
            $query->where('w.nama_wilayah', strtolower($request->wilayah));
            if ($request->filled('blok')) {
                $query->where('bl.nama_blok', strtolower($request->blok));
                if ($request->filled('kamar')) {
                    $query->where('km.nama_kamar', strtolower($request->kamar));
                }
            }
        }
        return $query;
    }
    
    public function applyLembagaFilter($query, Request $request)
    {
        if ($request->filled('lembaga')) {
            $query->leftJoin('lembaga as l', 'l.id', '=', 'pegawai.lembaga_id')
                  ->whereRaw('LOWER(l.nama_lembaga) = ?', [strtolower($request->lembaga)]);
    
            if ($request->filled('jurusan')) {
                $query->leftJoin('jurusan as j', 'j.id', '=', 'pegawai.jurusan_id')
                      ->whereRaw('LOWER(j.nama_jurusan) = ?', [strtolower($request->jurusan)]);
    
                if ($request->filled('kelas')) {
                    $query->leftJoin('kelas as k', 'k.id', '=', 'pegawai.kelas_id')
                          ->whereRaw('LOWER(k.nama_kelas) = ?', [strtolower($request->kelas)]);
    
                    if ($request->filled('rombel')) {
                        $query->leftJoin('rombel as r', 'r.id', '=', 'pegawai.rombel_id')
                              ->whereRaw('LOWER(r.nama_rombel) = ?', [strtolower($request->rombel)]);
                    }
                }
            }
        }
    
        return $query;
    }
    
    
    public function applyWargaPesantrenFilter($query, Request $request) {
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
    
    public function applyStatusFilter($query, Request $request) {
        if ($request->filled('semua_status')) {
            $entitas = strtolower($request->semua_status);
            if ($entitas === 'pelajar') {
                $query->whereNotNull('pl.id');
            } elseif ($entitas === 'santri') {
                $query->whereNotNull('s.id');
            } elseif ($entitas === 'pelajar dan santri') {
                $query->whereNotNull('pl.id')->whereNotNull('s.id');
            }
        }
        return $query;
    }
    
    public function applyAngkatanFilter($query, Request $request) {
        if ($request->filled('angkatan_pelajar')) {
            $query->where('pl.angkatan_pelajar', strtolower($request->angkatan_pelajar));
        }
        if ($request->filled('angkatan_santri')) {
            $query->where('s.angkatan_santri', strtolower($request->angkatan_santri));
        }
        return $query;
    }
    
    public function applyPhoneFilter($query, Request $request)
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
    
    
    public function applySortFilter($query, Request $request) {
        if ($request->filled('sort_by')) {
            $sortBy = strtolower($request->sort_by);
            $sortOrder = $request->filled('sort_order') && strtolower($request->sort_order) === 'desc' ? 'desc' : 'asc';
    
            switch ($sortBy) {
                case 'nama':
                    $query->orderBy('b.nama', $sortOrder);
                    break;
                case 'niup':
                    $query->orderBy('wp.niup', $sortOrder);
                    break;
                case 'jenis kelamin':
                    $query->orderBy('b.jenis_kelamin', $sortOrder);
                    break;
                case 'tempat lahir':
                    $query->orderBy('b.tempat_lahir', $sortOrder);
                    break;
                case 'angkatan':
                    $query->orderBy('pl.angkatan_pelajar', $sortOrder);
                    break;
                default:
                    $query->whereRaw('0 = 1');
                    break;
            }
        } else {
            $query->orderBy('pd.id', 'asc');
        }
        return $query;
    }
    public function applyUmurFilter($query, Request $request)
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
    public function applyPemberkasanFilter($query, Request $request) {
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
    public function applylembagaKaryawanFilter($query, Request $request) {
                // Filter Lembaga
        if ($request->filled('lembaga')) {
            $query->where('l.nama_lembaga', strtolower($request->lembaga));
        }
        return $query;
    }
 
    public function applyjabatanKaryawanFilter($query, Request $request) {
        if ($request->filled('jabatan')){
            $query->where('karyawan.jabatan',strtolower($request->jabatan));
        }
    return $query;
    }
    public function applyGolonganJabatanFilter($query, Request $request) {
                if ($request->filled('golongan_jabatan')){
                    $query->where('kg.nama_kategori_golongan',strtolower($request->golongan_jabatan));
                }
    return $query;
    }
    public function applyEntitasPegawaiFilter($query, Request $request) {
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
        public function applyGerderRombelFilter($query, Request $request) {
            if ($request->filled('gender_rombel')) {
                if (strtolower($request->gender_rombel) === 'putra') {
                    $query->where('r.gender_rombel', 'putra');
                } elseif (strtolower($request->gender_rombel) === 'putri') {
                    $query->where('r.gender_rombel', 'putri');
                }
        }
                return $query;
        }
        public function applyGolonganFilter($query, Request $request) {
            if ($request->filled('golongan')){
                $query->where('g.nama_golongan',strtolower($request->golongan));
            }
                 return $query;
        }
        public function applyMateriAjarFilter($query, Request $request) {
            if ($request->has('materi_ajar')) {
                if (strtolower($request->materi_ajar) === 'materi ajar 1') {
                    // Hanya pengajar yang memiliki 1 materi ajar
                    $query->havingRaw('COUNT(DISTINCT materi_ajar.id) = 1');
                } elseif (strtolower($request->materi_ajar) === 'materi ajar lebih dari 1') {
                    // Hanya pengajar yang memiliki lebih dari 1 materi ajar
                    $query->havingRaw('COUNT(DISTINCT materi_ajar.id) > 1');
                }
            }
                 return $query;
        }
        public function applyMasaKerjaFilter($query, Request $request) {
            $masaKerja = $request->input('masa_kerja'); // Mengambil input dari request
            $today = now(); // Menggunakan tanggal saat ini
            
            if (preg_match('/^(\d+)-(\d+)$/', $masaKerja, $matches)) {
                // Jika input dalam format "min-max" (contoh: "1-5")
                $min = (int) $matches[1];
                $max = (int) $matches[2];
            
                $query->whereRaw("
                    TIMESTAMPDIFF(YEAR, entitas_pegawai.tanggal_masuk, COALESCE(entitas_pegawai.tanggal_keluar, ?)) BETWEEN ? AND ?
                ", [$today, $min, $max]);
            } elseif (is_numeric($masaKerja)) {
                // Jika input hanya angka (contoh: "1" untuk kurang dari 1 tahun)
                $query->whereRaw("
                    TIMESTAMPDIFF(YEAR, entitas_pegawai.tanggal_masuk, COALESCE(entitas_pegawai.tanggal_keluar, ?)) < ?
                ", [$today, (int) $masaKerja]);
            }
                 return $query;
        }
        public function applyJabatanPengajarFilter($query, Request $request) {
            if ($request->has('jabatan')) {
                $query->where('pengajar.jabatan', strtolower($request->jabatan));
            }     
                 return $query;
        }
        public function applyJabatanPengurusFilter($query, Request $request) {
                if ($request->filled('jabatan')) {
                    $query->where('pengurus.jabatan', strtolower($request->jabatan));
                }  
                 return $query;
        }
        public function applySatuanKerjaPengurusFilter($query, Request $request) {
               if ($request->filled('satuan_kerja')) {
                $query->where('pengurus.satuan_kerja', strtolower($request->satuan_kerja));
            }    
     
             return $query;
    }
}

