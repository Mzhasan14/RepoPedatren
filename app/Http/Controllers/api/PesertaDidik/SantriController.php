<?php

namespace App\Http\Controllers\api\PesertaDidik;

use App\Models\Santri;
use App\Models\JenisBerkas;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use App\Http\Resources\PdResource;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\api\FilterController;

class SantriController extends Controller
{
    protected $filterController;

    public function __construct(FilterController $filterController)
    {
        $this->filterController = $filterController;
    }

    public function index()
    {
        $santri = Santri::Active()->latest()->paginate(10);
        return new PdResource(true, 'Data Santri', $santri);
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_peserta_didik' => [
                'required',
                'integer',
                Rule::unique('santri', 'id_peserta_didik')
            ],
            'id_wilayah' => ['required', 'integer', Rule::exists('wilayah', 'id')],
            'id_blok' => [
                'nullable',
                'integer',
                Rule::exists('blok', 'id')->where(function ($query) use ($request) {
                    if ($request->filled('id_wilayah')) {
                        $query->where('id_wilayah', $request->id_wilayah);
                    }
                }),
            ],
            'id_kamar' => [
                'nullable',
                'integer',
                Rule::exists('kamar', 'id')->where(function ($query) use ($request) {
                    if ($request->filled('id_blok')) {
                        $query->where('id_blok', $request->id_blok);
                    }
                }),
            ],
            'id_domisili' => [
                'nullable',
                'integer',
                Rule::exists('domisili', 'id')->where(function ($query) use ($request) {
                    if ($request->filled('id_kamar')) {
                        $query->where('id_kamar', $request->id_kamar);
                    }
                }),
            ],
            'nis' => [
                'nullable',
                'string',
                'size:11',
                Rule::unique('santri', 'nis')
            ],
            'tanggal_masuk' => 'required|date',
            'tanggal_keluar' => 'nullable|date',
            'created_by' => 'required|integer',
            'status' => 'required|boolean'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $santri = Santri::create($validator->validated());

        return new PdResource(true, 'Data berhasil ditambah', $santri);
    }

    public function show($id)
    {
        $santri = Santri::findOrFail($id);
        return new PdResource(true, 'Detail Peserta Didik', $santri);
    }

    public function update(Request $request, $id)
    {
        $santri = Santri::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'id_wilayah' => ['required', 'integer', Rule::exists('wilayah', 'id')],
            'id_blok' => [
                'nullable',
                'integer',
                Rule::exists('blok', 'id')->where(function ($query) use ($request) {
                    if ($request->filled('id_wilayah')) {
                        $query->where('id_wilayah', $request->id_wilayah);
                    }
                }),
            ],
            'id_kamar' => [
                'nullable',
                'integer',
                Rule::exists('kamar', 'id')->where(function ($query) use ($request) {
                    if ($request->filled('id_blok')) {
                        $query->where('id_blok', $request->id_blok);
                    }
                }),
            ],
            'id_domisili' => [
                'nullable',
                'integer',
                Rule::exists('domisili', 'id')->where(function ($query) use ($request) {
                    if ($request->filled('id_kamar')) {
                        $query->where('id_kamar', $request->id_kamar);
                    }
                }),
            ],
            'tanggal_keluar' => 'nullable|date',
            'updated_by' => 'required|integer',
            'status' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $santri->update($validator->validated());

        return new PdResource(true, 'Data berhasil diubah', $santri);
    }

    public function destroy($id)
    {
        $santri = Santri::findOrFail($id);

        $santri->delete();
        return new PdResource(true, 'Data berhasil dihapus', null);
    }

    public function pesertaDidikSantri(Request $request)
    {
        $query = Santri::Active()
            ->leftjoin('peserta_didik', 'santri.id_peserta_didik', '=', 'peserta_didik.id')
            ->join('biodata', 'peserta_didik.id_biodata', '=', 'biodata.id')
            ->leftjoin('kabupaten', 'biodata.id_kabupaten', '=', 'kabupaten.id')
            ->leftJoin('berkas', 'berkas.id_biodata', '=', 'biodata.id')
            ->leftJoin('jenis_berkas', 'berkas.id_jenis_berkas', '=', 'jenis_berkas.id')
            ->leftjoin('wilayah', 'santri.id_wilayah', '=', 'wilayah.id')
            ->leftjoin('blok', 'santri.id_blok', '=', 'blok.id')
            ->leftjoin('kamar', 'santri.id_kamar', '=', 'kamar.id')
            ->leftjoin('pelajar', 'peserta_didik.id', '=', 'pelajar.id_peserta_didik')
            ->leftjoin('lembaga', 'pelajar.id_lembaga', '=', 'lembaga.id')
            ->select(
                'santri.id',
                'santri.nis',
                'biodata.nama',
                'biodata.niup',
                'kamar.nama_kamar',
                'blok.nama_blok',
                'lembaga.nama_lembaga',
                'wilayah.nama_wilayah',
                DB::raw("CONCAT('Kab. ', kabupaten.nama_kabupaten) as kota_asal"),
                'santri.created_at',
                'santri.updated_at',
                DB::raw("COALESCE(MAX(berkas.file_path), 'default.jpg') as foto_profil")
            )
            ->groupBy(
                'santri.id',
                'biodata.nama',
                'santri.nis',
                'wilayah.nama_wilayah',
                'biodata.niup',
                'kamar.nama_kamar',
                'blok.nama_blok',
                'lembaga.nama_lembaga',
                'kabupaten.nama_kabupaten',
                'santri.created_at',
                'santri.updated_at',
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
                    "nis" => $item->nis,
                    "nama" => $item->nama,
                    "niup" => $item->niup,
                    "kamar" => $item->nama_kamar,
                    "blok" => $item->nama_blok,
                    "lembaga" => $item->nama_lembaga,
                    "wilayah" => $item->nama_wilayah,
                    "tgl_update" => Carbon::parse($item->updated_at)->translatedFormat('d F Y H:i:s'),
                    "tgl_input" =>  Carbon::parse($item->created_at)->translatedFormat('d F Y H:i:s'),
                    "foto_profil" => url($item->foto_profil)
                ];
            })
        ]);
    }
}
