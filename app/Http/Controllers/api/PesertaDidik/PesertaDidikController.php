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
            ->leftJoin('pendidikan_pelajar', function ($join) {
                $join->on('pendidikan_pelajar.id_peserta_didik', '=', 'peserta_didik.id')
                    ->where('pendidikan_pelajar.status', true);
            })
            ->leftjoin('domisili_santri', function ($join) {
                $join->on('domisili_santri.id_peserta_didik', '=', 'peserta_didik.id')
                    ->where('domisili_santri.status', 'aktif');
            })
            ->leftJoin('lembaga', 'pendidikan_pelajar.id_lembaga', '=', 'lembaga.id')
            ->leftJoin('wilayah', 'domisili_santri.id_wilayah', '=', 'wilayah.id')
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
            $query->leftjoin('blok', 'domisili_santri.id_blok', '=', 'blok.id')
                ->leftjoin('kamar', 'domisili_santri.id_kamar', '=', 'kamar.id')
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
                $query->leftJoin('jurusan', 'pendidikan_pelajar.id_jurusan', '=', 'jurusan.id')
                    ->leftJoin('kelas', 'pendidikan_pelajar.id_kelas', '=', 'kelas.id')
                    ->leftJoin('rombel', 'pendidikan_pelajar.id_rombel', '=', 'rombel.id');
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
            $query->leftjoin('pelajar', 'pelajar.id_peserta_didik', '=', 'peserta_didik.id')
                ->leftjoin('santri', 'santri.id_peserta_didik', '=', 'peserta_didik.id');
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
            $query->where('pelajar.angkatan_pelajar', $request->angkatan_pelajar);
        }

        // Filter Angkatan Santri
        if ($request->filled('angkatan_santri')) {
            $query->where('santri.angkatan_santri', $request->angkatan_santri);
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
            $allowedSorts = ['nama', 'niup', 'jenis_kelamin'];
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
                "data" => []
            ], 200);
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
            ->leftJoin('keluarga', 'keluarga.id_biodata', '=', 'biodata.id')
            ->leftJoin('orang_tua_wali as otw_ayah', function ($join) {
                $join->on('biodata.id', '=', 'otw_ayah.id_biodata')
                    ->whereRaw('otw_ayah.id_hubungan_keluarga = (SELECT id FROM hubungan_keluarga WHERE nama_status = "ayah")');
            })

            ->leftJoin('biodata as b_ayah', 'otw_ayah.id_biodata', '=', 'b_ayah.id')
            ->leftJoin('orang_tua_wali as otw_ibu', function ($join) {
                $join->on('biodata.id', '=', 'otw_ibu.id_biodata')
                    ->whereRaw('otw_ibu.id_hubungan_keluarga = (SELECT id FROM hubungan_keluarga WHERE nama_status = "ibu")');
            })

            ->leftJoin('biodata as b_ibu', 'otw_ibu.id_biodata', '=', 'b_ibu.id')
            ->leftJoin('kabupaten', 'kabupaten.id', '=', 'biodata.id_kabupaten')
            ->leftJoin(
                DB::raw('(SELECT id_biodata, MAX(file_path) as file_path FROM berkas GROUP BY id_biodata) as berkas'),
                'biodata.id',
                '=',
                'berkas.id_biodata'
            )

            ->leftJoin('pendidikan_pelajar', function ($join) {
                $join->on('pendidikan_pelajar.id_peserta_didik', '=', 'peserta_didik.id')
                    ->where('pendidikan_pelajar.status', true);
            })
            ->leftjoin('domisili_santri', function ($join) {
                $join->on('domisili_santri.id_peserta_didik', '=', 'peserta_didik.id')
                    ->where('domisili_santri.status', 'aktif');
            })
            ->leftJoin('lembaga', 'pendidikan_pelajar.id_lembaga', '=', 'lembaga.id')
            ->leftJoin('wilayah', 'domisili_santri.id_wilayah', '=', 'wilayah.id')
            ->select(
                'peserta_didik.id',
                DB::raw("COALESCE(biodata.nik, biodata.no_passport) as identitas"),
                'keluarga.no_kk',
                'biodata.nama',
                'biodata.niup',
                'lembaga.nama_lembaga',
                'wilayah.nama_wilayah',
                DB::raw("COALESCE(b_ibu.nama, 'Tidak Diketahui') as nama_ibu"),
                DB::raw("COALESCE(b_ayah.nama, 'Tidak Diketahui') as nama_ayah"),
                DB::raw("CONCAT('Kab. ', kabupaten.nama_kabupaten) as kota_asal"),
                DB::raw("COALESCE(ANY_VALUE(berkas.file_path), 'default.jpg') as foto_profil"),
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
                'biodata.created_at',
                'biodata.updated_at'
            );

        // Filter Umum (Alamat dan Jenis Kelamin)
        $query = $this->filterController->applyCommonFilters($query, $request);

        // Filter Wilayah
        if ($request->filled('wilayah')) {
            $wilayah = strtolower($request->wilayah);
            $query->leftjoin('blok', 'domisili_santri.id_blok', '=', 'blok.id')
                ->leftjoin('kamar', 'domisili_santri.id_kamar', '=', 'kamar.id')
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
                $query->leftJoin('jurusan', 'pendidikan_pelajar.id_jurusan', '=', 'jurusan.id')
                    ->leftJoin('kelas', 'pendidikan_pelajar.id_kelas', '=', 'kelas.id')
                    ->leftJoin('rombel', 'pendidikan_pelajar.id_rombel', '=', 'rombel.id');
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
                $query->leftjoin('santri', 'peserta_didik.id', '=', 'santri.id_peserta_didik')
                    ->leftjoin('pelajar', 'peserta_didik.id', '=', 'pelajar.id_peserta_didik')
                    ->whereNotNull('santri.id');
            } else if ($status == 'santri non pelajar') {
                $query->join('santri', 'peserta_didik.id', '=', 'santri.id_peserta_didik');
            } else if ($status == 'pelajar') {
                $query->leftjoin('santri', 'peserta_didik.id', '=', 'santri.id_peserta_didik')
                    ->leftjoin('pelajar', 'peserta_didik.id', '=', 'pelajar.id_peserta_didik')
                    ->whereNotNull('pelajar.id');
            } else if ($status == 'pelajar non santri') {
                $query->join('pelajar', 'peserta_didik.id', '=', 'pelajar.id_peserta_didik');
            } else if ($status == 'santri-pelajar' || $status == 'pelajar-santri') {
                $query->join('santri', 'peserta_didik.id', '=', 'santri.id_peserta_didik')
                    ->join('pelajar', 'peserta_didik.id', '=', 'pelajar.id_peserta_didik');
            }
        }

        // Filter Angkatan Pelajar
        if ($request->filled('angkatan_pelajar')) {
            $query->where('pelajar.angkatan_pelajar', $request->angkatan_pelajar);
        }

        // Filter Angkatan Santri
        if ($request->filled('angkatan_santri')) {
            $query->where('santri.angkatan_santri', $request->angkatan_santri);
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
            $allowedSorts = ['nama', 'niup', 'jenis_kelamin'];
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
                "data" => []
            ], 200);
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

    // public function getBiodata(Request $request, $id)
    // {
    //     $pesertaDidik = Peserta_didik::join('biodata', 'peserta_didik.id_biodata', '=', 'biodata.id')
    //         ->leftjoin('keluarga', 'biodata.id', '=', 'keluarga.id_biodata')
    //         ->leftjoin('kecamatan', 'biodata.id_kecamatan', '=', 'kecamatan.id')
    //         ->leftjoin('kabupaten', 'biodata.id_kabupaten', '=', 'kabupaten.id')
    //         ->leftjoin('provinsi', 'biodata.id_provinsi', '=', 'provinsi.id')
    //         ->leftjoin('negara', 'biodata.id_negara', '=', 'negara.id')
    //         ->select(
    //             'keluarga.no_kk',
    //             DB::raw("COALESCE(biodata.nik, biodata.no_passport) as identitas"),
    //             'biodata.niup',
    //             'biodata.nama',
    //             'biodata.jenis_kelamin',
    //             DB::raw("CONCAT(biodata.tempat_lahir, ', ', DATE_FORMAT(biodata.tanggal_lahir, '%e %M %Y')) as tempat_tanggal_lahir"),
    //             DB::raw("CONCAT(biodata.anak_keberapa, ' dari ', biodata.dari_saudara, ' Bersaudara') as anak_dari"),
    //             DB::raw("CONCAT(TIMESTAMPDIFF(YEAR, biodata.tanggal_lahir, CURDATE()), ' tahun') as umur"),
    //             'kecamatan.nama_kecamatan',
    //             'kabupaten.nama_kabupaten',
    //             'provinsi.nama_provinsi',
    //             'negara.nama_negara',
    //         )->where('peserta_didik.id', $id);

    //     // Ambil jumlah data per halaman (default 10 jika tidak diisi)
    //     $perPage = $request->input('limit', 25);

    //     // Ambil halaman saat ini (jika ada)
    //     $currentPage = $request->input('page', 1);

    //     // Menerapkan pagination ke hasil
    //     $hasil = $pesertaDidik->paginate($perPage, ['*'], 'page', $currentPage);

    //     // Jika Data Kosong
    //     if ($hasil->isEmpty()) {
    //         return response()->json([
    //             "status" => "error",
    //             "message" => "Data tidak ditemukan",
    //             "data" => []
    //         ], 200);
    //     }

    //     return response()->json([
    //         "total_data" => $hasil->total(),
    //         "current_page" => $hasil->currentPage(),
    //         "per_page" => $hasil->perPage(),
    //         "total_pages" => $hasil->lastPage(),
    //         "data" => $hasil->map(function ($item) {
    //             return [
    //                 "nokk" => $item->no_kk,
    //                 "nik/nopassport" => $item->identitas,
    //                 "niup" => $item->niup,
    //                 "nama" => $item->nama,
    //                 "jenis_kelamin" => $item->jenis_kelamin,
    //                 "Tempat, Tanggal Lahir" => $item->tempat_tanggal_lahir,
    //                 "Anak Ke" => $item->anak_dari,
    //                 "umur" => $item->umur,
    //                 "Kecamatan" => $item->nama_kecamatan,
    //                 "Kabupaten" => $item->nama_kabupaten,
    //                 "Provinsi" => $item->nama_provinsi,
    //                 "Warganegara" => $item->nama_negara,
    //                 "foto_profil" => url($item->foto_profil)
    //             ];
    //         })
    //     ]);
    // }


    private function formTampilanAwal($perPage, $currentPage)
    {
        return Peserta_didik::Active()
            ->join('biodata', 'peserta_didik.id_biodata', '=', 'biodata.id')
            ->leftJoin('kabupaten', 'kabupaten.id', '=', 'biodata.id_kabupaten')
            ->leftJoin('berkas', 'berkas.id_biodata', '=', 'biodata.id')
            ->leftJoin('jenis_berkas', 'berkas.id_jenis_berkas', '=', 'jenis_berkas.id')
            ->leftJoin('pendidikan_pelajar', function ($join) {
                $join->on('pendidikan_pelajar.id_peserta_didik', '=', 'peserta_didik.id')
                    ->where('pendidikan_pelajar.status', true);
            })
            ->leftJoin('domisili_santri', function ($join) {
                $join->on('domisili_santri.id_peserta_didik', '=', 'peserta_didik.id')
                    ->where('domisili_santri.status', 'aktif');
            })
            ->leftJoin('lembaga', 'pendidikan_pelajar.id_lembaga', '=', 'lembaga.id')
            ->leftJoin('wilayah', 'domisili_santri.id_wilayah', '=', 'wilayah.id')
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
                'biodata.updated_at'
            )
            ->distinct() // Menghindari duplikasi data
            ->paginate($perPage, ['*'], 'page', $currentPage);
    }

    // **Query untuk Detail Peserta Didik**
    private function formDetail($idPesertaDidik)
    {
        $biodata = Peserta_didik::where('peserta_didik.id', $idPesertaDidik)
            ->join('biodata', 'peserta_didik.id_biodata', '=', 'biodata.id')
            ->leftJoin('berkas', 'biodata.id', '=', 'berkas.id_biodata')
            ->leftJoin('jenis_berkas', 'berkas.id_jenis_berkas', '=', 'jenis_berkas.id')
            ->leftJoin('keluarga', 'biodata.id', '=', 'keluarga.id_biodata')
            ->leftJoin('kecamatan', 'biodata.id_kecamatan', '=', 'kecamatan.id')
            ->leftJoin('kabupaten', 'biodata.id_kabupaten', '=', 'kabupaten.id')
            ->leftJoin('provinsi', 'biodata.id_provinsi', '=', 'provinsi.id')
            ->leftJoin('negara', 'biodata.id_negara', '=', 'negara.id')
            ->select(
                'keluarga.no_kk',
                DB::raw("COALESCE(biodata.nik, biodata.no_passport) as identitas"),
                'biodata.niup',
                'biodata.nama',
                'biodata.jenis_kelamin',
                DB::raw("CONCAT(biodata.tempat_lahir, ', ', DATE_FORMAT(biodata.tanggal_lahir, '%e %M %Y')) as tempat_tanggal_lahir"),
                DB::raw("CONCAT(biodata.anak_keberapa, ' dari ', biodata.dari_saudara, ' Bersaudara') as anak_dari"),
                DB::raw("CONCAT(TIMESTAMPDIFF(YEAR, biodata.tanggal_lahir, CURDATE()), ' tahun') as umur"),
                'kecamatan.nama_kecamatan',
                'kabupaten.nama_kabupaten',
                'provinsi.nama_provinsi',
                'negara.nama_negara',
                DB::raw("COALESCE(MAX(berkas.file_path), 'default.jpg') as foto_profil")
            )
            ->groupBy(
                'keluarga.no_kk',
                'biodata.nik',
                'biodata.no_passport',
                'biodata.niup',
                'biodata.nama',
                'biodata.jenis_kelamin',
                'biodata.tempat_lahir',
                'biodata.tanggal_lahir',
                'biodata.anak_keberapa',
                'biodata.dari_saudara',
                'kecamatan.nama_kecamatan',
                'kabupaten.nama_kabupaten',
                'provinsi.nama_provinsi',
                'negara.nama_negara'
            )
            ->first();

        if ($biodata) {
            $data['biodata'] = [
                "nokk" => $biodata->no_kk,
                "nik/nopassport" => $biodata->identitas,
                "niup" => $biodata->niup,
                "nama" => $biodata->nama,
                "jenis_kelamin" => $biodata->jenis_kelamin,
                "Tempat, Tanggal Lahir" => $biodata->tempat_tanggal_lahir,
                "Anak Ke" => $biodata->anak_dari,
                "umur" => $biodata->umur,
                "Kecamatan" => $biodata->nama_kecamatan,
                "Kabupaten" => $biodata->nama_kabupaten,
                "Provinsi" => $biodata->nama_provinsi,
                "Warganegara" => $biodata->nama_negara,
                "foto_profil" => url($biodata->foto_profil)
            ];
        }

        // **2. DATA KELUARGA (Jika Ada)**

        $keluarga = Peserta_didik::where('peserta_didik.id', $idPesertaDidik)
            ->join('biodata as b_anak', 'peserta_didik.id_biodata', '=', 'b_anak.id')
            ->join('keluarga as k_anak', 'b_anak.id', '=', 'k_anak.id_biodata') // Cari No KK anak
            ->leftjoin('keluarga as k_ortu', 'k_anak.no_kk', '=', 'k_ortu.no_kk') // Cari anggota keluarga lain dengan No KK yang sama
            ->join('orang_tua_wali as otw', 'k_ortu.id_biodata', '=', 'otw.id_biodata')
            ->join('biodata as b_ortu', 'otw.id_biodata', '=', 'b_ortu.id') // Hubungkan orang tua ke biodata mereka
            ->join('hubungan_keluarga as hk', 'otw.id_hubungan_keluarga', '=', 'hk.id') // Status hubungan keluarga
            ->select(
                'b_ortu.nama',
                'b_ortu.nik',
                'hk.nama_status',
                'otw.wali'
            )
            ->get();

        if ($keluarga->isNotEmpty()) {
            $data['keluarga'] = $keluarga->map(function ($item) {
                return [
                    "nama" => $item->nama,
                    "nik" => $item->nik,
                    "status" => $item->nama_status,
                    "wali" => $item->wali,
                ];
            })->toArray();
        }

        // // **3. STATUS SANTRI (Jika Ada)**
        // $statusSantri = DB::table('status_santri')
        //     ->where('peserta_didik_id', $pesertaDidik->id)
        //     ->first();`

        // if ($statusSantri) {
        //     $data['status_santri'] = [
        //         'status' => $statusSantri->status,
        //         'lembaga' => $statusSantri->lembaga,
        //     ];
        // }

        // // **4. DOMISILI (Jika Ada)**
        // $domisili = DB::table('domisili')
        //     ->where('peserta_didik_id', $pesertaDidik->id)
        //     ->first();

        // if ($domisili) {
        //     $data['domisili'] = [
        //         'alamat' => $domisili->alamat,
        //         'kecamatan' => $domisili->kecamatan,
        //         'kabupaten' => $domisili->kabupaten,
        //         'provinsi' => $domisili->provinsi,
        //     ];
        // }

        // // **5. PENDIDIKAN (Jika Ada)**
        // $pendidikan = DB::table('pendidikan')
        //     ->where('peserta_didik_id', $pesertaDidik->id)
        //     ->first();

        // if ($pendidikan) {
        //     $data['pendidikan'] = [
        //         'jenjang' => $pendidikan->jenjang,
        //         'sekolah' => $pendidikan->sekolah,
        //         'kelas' => $pendidikan->kelas,
        //         'tahun_masuk' => $pendidikan->tahun_masuk,
        //     ];
        // }

        return $data;
    }

    // **Mengambil Data Peserta Didik (Tampilan Awal + Detail)**
    public function getPesertaDidik(Request $request)
    {
        $perPage = $request->input('limit', 25);
        $currentPage = $request->input('page', 1);

        $hasil = $this->formTampilanAwal($perPage, $currentPage);
        $result = $hasil->items(); // Ambil data tanpa metadata pagination

        $data = collect($result)->map(function ($item) {
            return [
                'tampilan_awal' => [
                    "id" => $item->id,
                    "nik/nopassport" => $item->identitas,
                    "nama" => $item->nama,
                    "niup" => $item->niup,
                    "lembaga" => $item->nama_lembaga,
                    "wilayah" => $item->nama_wilayah,
                    "kota_asal" => $item->kota_asal,
                    "tgl_update" => Carbon::parse($item->updated_at)->translatedFormat('d F Y H:i:s'),
                    "tgl_input" => Carbon::parse($item->created_at)->translatedFormat('d F Y H:i:s'),
                    "foto_profil" => url($item->foto_profil)
                ],
                'detail' => $this->formDetail($item->id),
            ];
        });

        return response()->json([
            "total_data" => $hasil->total(),
            "current_page" => $hasil->currentPage(),
            "per_page" => $hasil->perPage(),
            "total_pages" => $hasil->lastPage(),
            "data" => $data->values(),
        ]);
    }
}
