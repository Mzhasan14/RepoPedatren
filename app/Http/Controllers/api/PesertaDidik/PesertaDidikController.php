<?php

namespace App\Http\Controllers\api\PesertaDidik;

use App\Models\JenisBerkas;
use Illuminate\Http\Request;
use App\Models\Peserta_didik;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use App\Http\Resources\PdResource;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\api\FilterController;

class PesertaDidikController extends Controller
{
    protected $filterController;

    public function __construct(FilterController $filterController)
    {
        $this->filterController = $filterController;
    }

    public function index()
    {
        $pesertaDidik = Peserta_didik::Active()->latest()->paginate(10);
        return new PdResource(true, 'List Peserta Didik', $pesertaDidik);
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_biodata' => [
                'required',
                'integer',
                Rule::unique('peserta_didik', 'id_biodata')
            ],
            'created_by' => 'required|integer',
            'status' => 'required|boolean'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $pesertaDidik = Peserta_didik::create($validator->validated());

        return new PdResource(true, 'Data berhasil ditambah', $pesertaDidik);
    }

    public function show($id)
    {
        $pesertaDidik = Peserta_didik::findOrFail($id);

        $query = Peserta_didik::join('biodata', 'peserta_didik.id_biodata', '=', 'biodata.id')
           ;
        
        return new PdResource(true, 'Detail Peserta Didik', $pesertaDidik);
    }

    public function update(Request $request, $id)
    {

        $pesertaDidik = Peserta_didik::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'updated_by' => 'required|integer',
            'status' => 'required|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $pesertaDidik->update($validator->validated());

        return new PdResource(true, 'Data berhasil diubah', $pesertaDidik);
    }

    public function destroy($id)
    {
        $pesertaDidik = Peserta_didik::findOrFail($id);

        $pesertaDidik->delete();
        return new PdResource(true, 'Data berhasil dihapus', null);
    }

    public function pesertaDidik(Request $request)
    {
        $query = Peserta_didik::Active()
            ->join('biodata', 'peserta_didik.id_biodata', '=', 'biodata.id')
            ->leftJoin('kabupaten', 'kabupaten.id', '=', 'biodata.id_kabupaten')
            ->leftJoin('berkas', 'berkas.id_biodata', '=', 'biodata.id')
            ->leftJoin('jenis_berkas', 'berkas.id_jenis_berkas', '=', 'jenis_berkas.id')
            ->leftJoin('pelajar', 'peserta_didik.id', '=', 'pelajar.id_peserta_didik')
            ->leftJoin('lembaga', 'pelajar.id_lembaga', '=', 'lembaga.id')
            ->leftJoin('santri', 'peserta_didik.id', '=', 'santri.id_peserta_didik')
            ->leftjoin('wilayah', 'santri.id_wilayah', '=', 'wilayah.id')
            ->select(
                'peserta_didik.id',
                DB::raw("COALESCE(biodata.nik, biodata.no_passport) as identitas"),
                'biodata.nama',
                'biodata.niup',
                'lembaga.nama_lembaga',
                'wilayah.nama_wilayah',
                DB::raw("CONCAT('Kab. ', kabupaten.nama_kabupaten) as kota_asal"),
                'biodata.created_at',
                'biodata.updated_at',
                DB::raw("COALESCE(MAX(berkas.file_path), 'default.jpg') as foto_profil")
            )
            ->groupBy(
                'peserta_didik.id',
                'biodata.nik',
                'biodata.no_passport',
                'biodata.nama',
                'biodata.niup',
                'wilayah.nama_wilayah',
                'lembaga.nama_lembaga',
                'kabupaten.nama_kabupaten',
                'biodata.created_at',
                'biodata.updated_at',
            );

        // Filter Umum (Alamat dan Jenis Kelamin)
        $query = $this->filterController->applyCommonFilters($query, $request);

        // Filter Wilayah
        if ($request->filled('wilayah')) {
            $wilayah = strtolower($request->wilayah);
            $query->leftjoin('blok', 'santri.id_blok', '=', 'blok.id')
                ->leftjoin('kamar', 'santri.id_kamar', '=', 'kamar.id')
                ->where('wilayah.nama_wilayah', $wilayah);
            if ($request->filled('blok')) {
                $blok = strtolower($request->blok);
                $query->where('blok.nama_blok', $blok);
                if ($request->filled('kamar')) {
                    $kamar = strtolower($request->kamar);
                    $query->where('kamar.nama_kamar', $kamar);
                }
            }
        }

        // Filter Lembaga
        if ($request->filled('lembaga')) {
            $query->where('lembaga.nama_lembaga', $request->lembaga);
            if ($request->filled('jurusan')) {
                $query->leftJoin('jurusan', 'pelajar.id_jurusan', '=', 'jurusan.id')
                    ->leftJoin('kelas', 'pelajar.id_kelas', '=', 'kelas.id')
                    ->leftJoin('rombel', 'pelajar.id_rombel', '=', 'rombel.id');
                $query->where('jurusan.nama_jurusan', $request->jurusan);
                if ($request->filled('kelas')) {
                    $query->where('kelas.nama_kelas', $request->kelas);
                    if ($request->filled('rombel')) {
                        $query->where('rombel.nama_rombel', $request->rombel);
                    }
                }
            }
        }

        // Filter Status
        if ($request->filled('status')) {
            $status = strtolower($request->status);
            if ($status == 'santri') {
                $query->whereNotNull('santri.id');
            } else if ($status == 'santri non pelajar') {
                $query->whereNotNull('santri.id')->whereNull('pelajar.id');
            } else if ($status == 'pelajar') {
                $query->whereNotNull('pelajar.id');
            } else if ($status == 'pelajar non santri') {
                $query->whereNotNull('pelajar.id')->whereNull('santri.id');
            } else if ($status == 'santri-pelajar' || $status == 'pelajar-santri') {
                $query->whereNotNull('pelajar.id')->whereNotNull('santri.id');
            }
        }

        // Filter Angkatan Pelajar
        if ($request->filled('angkatan_pelajar')) {
            $query->where('pelajar.angkatan', $request->angkatan_pelajar);
        }

        // Filter Angkatan Santri
        if ($request->filled('angkatan_santri')) {
            $query->where('santri.angkatan', $request->angkatan_santri);
        }

        // Filter Status Warga Pesantren
        if ($request->filled('warga_pesantren')) {
            $warga_pesantren = strtolower($request->warga_pesantren);
            if ($warga_pesantren == 'memiliki niup') {
                $query->whereNotNull('biodata.niup');
            } else if ($warga_pesantren == 'tanpa niup') {
                $query->whereNull('biodata.niup');
            }
        }

        // Filter Smartcard
        if ($request->filled('smartcard')) {
            $smartcard = strtolower($request->smartcard);
            if ($smartcard == 'memiliki smartcard') {
                $query->whereNotNull('biodata.smartcard');
            } else if ($smartcard == 'tanpa smartcard') {
                $query->whereNull('biodata.smartcard');
            }
        }

        // Filter No Telepon
        if ($request->filled('phone_number')) {
            $phone_number = strtolower($request->phone_number);
            if ($phone_number == 'memiliki phone number') {
                $query->whereNotNull('biodata.no_telepon')
                    ->where('biodata.no_telepon', '!=', '');
            } else if ($phone_number == 'tidak ada phone number') {
                $query->whereNull('biodata.no_telepon')
                    ->where('biodata.no_telepon', '=', '');
            }
        }

        // Filter Pemberkasan (Lengkap / Tidak Lengkap)
        if ($request->filled('pemberkasan')) {
            $pemberkasan = strtolower($request->pemberkasan);
            if ($pemberkasan == 'tidak ada berkas') {
                $query->whereNull('berkas.id_biodata');
            } else if ($pemberkasan == 'tidak ada foto diri') {
                $query->where('berkas.id_jenis_berkas', 4) // ID untuk Foto Diri (sesuaikan dengan yang Anda punya)
                    ->whereNull('berkas.file_path');
            } else if ($pemberkasan == 'memiliki foto diri') {
                $query->where('berkas.id_jenis_berkas', 4)
                    ->whereNotNull('berkas.file_path');
            } else if ($pemberkasan == 'tidak ada kk') {
                $query->where('berkas.id_jenis_berkas', 1) // ID untuk Kartu Keluarga (sesuaikan)
                    ->whereNull('berkas.file_path');
            } else if ($pemberkasan == 'tidak ada akta kelahiran') {
                $query->where('berkas.id_jenis_berkas', 3) // ID untuk Akta Kelahiran (sesuaikan)
                    ->whereNull('berkas.file_path');
            } else if ($pemberkasan == 'tidak ada ijazah') {
                $query->where('berkas.id_jenis_berkas', 5) // ID untuk Ijazah (sesuaikan)
                    ->whereNull('berkas.file_path');
            }
        }

        // Filter Sort By
        if ($request->filled('sort_by')) {
            $sort_by = strtolower($request->sort_by);
            $allowedSorts = ['nama', 'niup', 'angkatan', 'jenis kelamin', 'tempat lahir'];
            if (in_array($sort_by, $allowedSorts)) {
                $query->orderBy($sort_by, 'asc'); // Default ascending
            }
        }

        // Filter Sort Order
        if ($request->filled('sort_order')) {
            $sortOrder = strtolower($request->sort_order) == 'desc' ? 'desc' : 'asc';
            $query->orderBy('peserta_didik.id', $sortOrder);
        }

        // Ambil jumlah data per halaman (default 10 jika tidak diisi)
        $perPage = $request->input('limit', 25);

        // Ambil halaman saat ini (jika ada)
        $currentPage = $request->input('page', 1);

        // Menerapkan pagination ke hasil
        $hasil = $query->paginate($perPage, ['*'], 'page', $currentPage);


        // Jika Data Kosong
        if ($hasil->isEmpty()) {
            return response()->json([
                "status" => "error",
                "message" => "Data tidak ditemukan",
                "code" => 404
            ], 404);
        }

        return response()->json([
            "total_data" => $hasil->total(),
            "current_page" => $hasil->currentPage(),
            "per_page" => $hasil->perPage(),
            "total_pages" => $hasil->lastPage(),
            "data" => $hasil->map(function ($item) {
                return [
                    "id" => $item->id,
                    "nik/nopassport" => $item->identitas,
                    "nama" => $item->nama,
                    "niup" => $item->niup,
                    "lembaga" => $item->nama_lembaga,
                    "wilayah" => $item->nama_wilayah,
                    "kota_asal" => $item->kota_asal,
                    "tgl_update" => Carbon::parse($item->updated_at)->translatedFormat('d F Y H:i:s'),
                    "tgl_input" =>  Carbon::parse($item->created_at)->translatedFormat('d F Y H:i:s'),
                    "foto_profil" => url($item->foto_profil)
                ];
            })
        ]);
    }

    public function bersaudaraKandung(Request $request)
    {
        $query = Peserta_didik::Active()
            ->join('biodata', 'peserta_didik.id_biodata', '=', 'biodata.id')
            ->leftjoin('keluarga', 'keluarga.id_biodata', '=', 'biodata.id') // Perbaikan: Join berdasarkan id_biodata
            ->leftJoin('keluarga as k_ayah', function ($join) {
                $join->on('keluarga.no_kk', '=', 'k_ayah.no_kk')
                    ->where('k_ayah.id_status_keluarga', function ($query) {
                        $query->select('id')
                            ->from('status_keluarga')
                            ->where('nama_status', 'ayah');
                    });
            })
            ->leftJoin('keluarga as k_ibu', function ($join) {
                $join->on('keluarga.no_kk', '=', 'k_ibu.no_kk')
                    ->where('k_ibu.id_status_keluarga', function ($query) {
                        $query->select('id')
                            ->from('status_keluarga')
                            ->where('nama_status', 'ibu');
                    });
            })
            ->leftJoin('biodata as ayah', 'k_ayah.id_biodata', '=', 'ayah.id')
            ->leftJoin('biodata as ibu', 'k_ibu.id_biodata', '=', 'ibu.id')
            ->leftjoin('kabupaten', 'kabupaten.id', '=', 'biodata.id_kabupaten')
            ->leftJoin('orang_tua', 'orang_tua.id_biodata', '=', 'biodata.id')
            ->leftJoin('berkas', 'berkas.id_biodata', '=', 'biodata.id')
            ->leftJoin('jenis_berkas', 'berkas.id_jenis_berkas', '=', 'jenis_berkas.id')
            ->leftJoin('pelajar', 'peserta_didik.id', '=', 'pelajar.id_peserta_didik')
            ->leftJoin('lembaga', 'pelajar.id_lembaga', '=', 'lembaga.id')
            ->leftJoin('santri', 'peserta_didik.id', '=', 'santri.id_peserta_didik')
            ->leftJoin('wilayah', 'santri.id_wilayah', '=', 'wilayah.id')
            ->select(
                'peserta_didik.id',
                DB::raw("COALESCE(biodata.nik, biodata.no_passport) as identitas"),
                'keluarga.no_kk',
                'biodata.nama',
                'biodata.niup',
                'lembaga.nama_lembaga',
                'wilayah.nama_wilayah',
                DB::raw("COALESCE(ibu.nama, 'Tidak Diketahui') as nama_ibu"),
                DB::raw("COALESCE(ayah.nama, 'Tidak Diketahui') as nama_ayah"),
                DB::raw("CONCAT('Kab. ', kabupaten.nama_kabupaten) as kota_asal"),
                DB::raw("COALESCE(MAX(berkas.file_path), 'default.jpg') as foto_profil"),
                'biodata.created_at',
                'biodata.updated_at'
            )
            ->groupBy(
                'peserta_didik.id',
                'biodata.nik',
                'biodata.no_passport',
                'keluarga.no_kk',
                'biodata.nama',
                'biodata.niup',
                'lembaga.nama_lembaga',
                'wilayah.nama_wilayah',
                'kabupaten.nama_kabupaten',
                'ibu.nama',
                'ayah.nama',
                'biodata.created_at',
                'biodata.updated_at'
            );

        // Filter Umum (Alamat dan Jenis Kelamin)
        $query = $this->filterController->applyCommonFilters($query, $request);

        // Filter Wilayah
        if ($request->filled('wilayah')) {
            $wilayah = strtolower($request->wilayah);
            $query->leftjoin('blok', 'santri.id_blok', '=', 'blok.id')
                ->leftjoin('kamar', 'santri.id_kamar', '=', 'kamar.id')
                ->where('wilayah.nama_wilayah', $wilayah);
            if ($request->filled('blok')) {
                $blok = strtolower($request->blok);
                $query->where('blok.nama_blok', $blok);
                if ($request->filled('kamar')) {
                    $kamar = strtolower($request->kamar);
                    $query->where('kamar.nama_kamar', $kamar);
                }
            }
        }

        // Filter Lembaga
        if ($request->filled('lembaga')) {
            $query->where('lembaga.nama_lembaga', $request->lembaga);
            if ($request->filled('jurusan')) {
                $query->leftJoin('jurusan', 'pelajar.id_jurusan', '=', 'jurusan.id')
                    ->leftJoin('kelas', 'pelajar.id_kelas', '=', 'kelas.id')
                    ->leftJoin('rombel', 'pelajar.id_rombel', '=', 'rombel.id');
                $query->where('jurusan.nama_jurusan', $request->jurusan);
                if ($request->filled('kelas')) {
                    $query->where('kelas.nama_kelas', $request->kelas);
                    if ($request->filled('rombel')) {
                        $query->where('rombel.nama_rombel', $request->rombel);
                    }
                }
            }
        }

        // Filter Status
        if ($request->filled('status')) {
            $status = strtolower($request->status);
            if ($status == 'santri') {
                $query->whereNotNull('santri.id');
            } else if ($status == 'santri non pelajar') {
                $query->whereNotNull('santri.id')->whereNull('pelajar.id');
            } else if ($status == 'pelajar') {
                $query->whereNotNull('pelajar.id');
            } else if ($status == 'pelajar non santri') {
                $query->whereNotNull('pelajar.id')->whereNull('santri.id');
            } else if ($status == 'santri-pelajar' || $status == 'pelajar-santri') {
                $query->whereNotNull('pelajar.id')->whereNotNull('santri.id');
            }
        }

        // Filter Angkatan Pelajar
        if ($request->filled('angkatan_pelajar')) {
            $query->where('pelajar.angkatan', $request->angkatan_pelajar);
        }

        // Filter Angkatan Santri
        if ($request->filled('angkatan_santri')) {
            $query->where('santri.angkatan', $request->angkatan_santri);
        }

        // Filter Status Warga Pesantren
        if ($request->filled('warga_pesantren')) {
            $warga_pesantren = strtolower($request->warga_pesantren);
            if ($warga_pesantren == 'memiliki niup') {
                $query->whereNotNull('biodata.niup');
            } else if ($warga_pesantren == 'tanpa niup') {
                $query->whereNull('biodata.niup');
            }
        }

        // Filter Smartcard
        if ($request->filled('smartcard')) {
            $smartcard = strtolower($request->smartcard);
            if ($smartcard == 'memiliki smartcard') {
                $query->whereNotNull('biodata.smartcard');
            } else if ($smartcard == 'tanpa smartcard') {
                $query->whereNull('biodata.smartcard');
            }
        }

        // Filter No Telepon
        if ($request->filled('phone_number')) {
            $phone_number = strtolower($request->phone_number);
            if ($phone_number == 'memiliki phone number') {
                $query->whereNotNull('biodata.no_telepon')
                    ->where('biodata.no_telepon', '!=', '');
            } else if ($phone_number == 'tidak ada phone number') {
                $query->whereNull('biodata.no_telepon')
                    ->where('biodata.no_telepon', '=', '');
            }
        }

        // Filter Pemberkasan (Lengkap / Tidak Lengkap)
        if ($request->filled('pemberkasan')) {
            $pemberkasan = strtolower($request->pemberkasan);
            if ($pemberkasan == 'tidak ada berkas') {
                $query->whereNull('berkas.id_biodata');
            } else if ($pemberkasan == 'tidak ada foto diri') {
                $query->where('berkas.id_jenis_berkas', 4) // ID untuk Foto Diri (sesuaikan dengan yang Anda punya)
                    ->whereNull('berkas.file_path');
            } else if ($pemberkasan == 'memiliki foto diri') {
                $query->where('berkas.id_jenis_berkas', 4)
                    ->whereNotNull('berkas.file_path');
            } else if ($pemberkasan == 'tidak ada kk') {
                $query->where('berkas.id_jenis_berkas', 1) // ID untuk Kartu Keluarga (sesuaikan)
                    ->whereNull('berkas.file_path');
            } else if ($pemberkasan == 'tidak ada akta kelahiran') {
                $query->where('berkas.id_jenis_berkas', 3) // ID untuk Akta Kelahiran (sesuaikan)
                    ->whereNull('berkas.file_path');
            } else if ($pemberkasan == 'tidak ada ijazah') {
                $query->where('berkas.id_jenis_berkas', 5) // ID untuk Ijazah (sesuaikan)
                    ->whereNull('berkas.file_path');
            }
        }

        // Filter Sort By
        if ($request->filled('sort_by')) {
            $sort_by = strtolower($request->sort_by);
            $allowedSorts = ['nama', 'niup', 'angkatan', 'jenis kelamin', 'tempat lahir'];
            if (in_array($sort_by, $allowedSorts)) {
                $query->orderBy($sort_by, 'asc'); // Default ascending
            }
        }

        // Filter Sort Order
        if ($request->filled('sort_order')) {
            $sortOrder = strtolower($request->sort_order) == 'desc' ? 'desc' : 'asc';
            $query->orderBy('peserta_didik.id', $sortOrder);
        }

        // Ambil jumlah data per halaman (default 10 jika tidak diisi)
        $perPage = $request->input('limit', 25);

        // Ambil halaman saat ini (jika ada)
        $currentPage = $request->input('page', 1);

        // Menerapkan pagination ke hasil
        $hasil = $query->paginate($perPage, ['*'], 'page', $currentPage);


        // Jika Data Kosong
        if ($hasil->isEmpty()) {
            return response()->json([
                "status" => "error",
                "message" => "Data tidak ditemukan",
                "code" => 404
            ], 404);
        }

        return response()->json([
            "total_data" => $hasil->total(),
            "current_page" => $hasil->currentPage(),
            "per_page" => $hasil->perPage(),
            "total_pages" => $hasil->lastPage(),
            "data" => $hasil->map(function ($item) {
                return [
                    "id" => $item->id,
                    "nik/nopassport" => $item->identitas,
                    "nokk" => $item->no_kk,
                    "nama" => $item->nama,
                    "niup" => $item->niup,
                    "lembaga" => $item->nama_lembaga,
                    "wilayah" => $item->nama_wilayah,
                    "kota_asal" => $item->kota_asal,
                    "ibu_kandung" => $item->nama_ibu,
                    "ayah_kandung" => $item->nama_ayah,
                    "tgl_update" => Carbon::parse($item->updated_at)->translatedFormat('d F Y H:i:s'),
                    "tgl_input" =>  Carbon::parse($item->created_at)->translatedFormat('d F Y H:i:s'),
                    "foto_profil" => url($item->foto_profil)
                ];
            })
        ]);
    }
}
