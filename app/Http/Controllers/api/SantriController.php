<?php

namespace App\Http\Controllers\api;

use App\Models\Santri;
use App\Models\JenisBerkas;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Resources\PdResource;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

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
            'status' => 'required|boolean'
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
            ->leftJoin('berkas', 'berkas.id_biodata', '=', 'biodata.id')
            ->leftJoin('jenis_berkas', 'berkas.id_jenis_berkas', '=', 'jenis_berkas.id')
            ->leftjoin('wilayah', 'santri.id_wilayah', '=', 'wilayah.id')
            ->leftjoin('blok', 'santri.id_blok', '=', 'blok.id')
            ->leftjoin('kamar', 'santri.id_kamar', '=', 'kamar.id')
            ->leftjoin('domisili', 'santri.id_domisili', '=', 'domisili.id')
            ->leftjoin('pelajar', 'peserta_didik.id', '=', 'pelajar.id_peserta_didik')
            ->select(
                'santri.id',
                'biodata.nama',
                'santri.nis',
                'wilayah.nama_wilayah',
                DB::raw("COALESCE(MAX(berkas.file_path), 'default.jpg') as foto_profil")
            )
            ->groupBy('santri.id','biodata.nama', 'santri.nis', 'wilayah.nama_wilayah');

        // Filter Umum (Alamat dan Jenis Kelamin)
        $query = $this->filterController->applyCommonFilters($query, $request);

        // Filter Wilayah
        if ($request->filled('wilayah')) {
            $wilayah = strtolower($request->wilayah);
            $query->where('wilayah.nama_wilayah', $wilayah);
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
            $query->leftJoin('rombel', 'pelajar.id_rombel', '=', 'rombel.id')
                ->leftJoin('kelas', 'pelajar.id_kelas', '=', 'kelas.id')
                ->leftJoin('jurusan', 'pelajar.id_jurusan', '=', 'jurusan.id')
                ->leftJoin('lembaga', 'pelajar.id_lembaga', '=', 'lembaga.id');
            $query->where('lembaga.nama_lembaga', $request->lembaga);
            if ($request->filled('jurusan')) {
                $query->where('jurusan.nama_jurusan', $request->jurusan);
                if ($request->filled('kelas')) {
                    $query->where('kelas.nama_kelas', $request->kelas);
                    if ($request->filled('rombel')) {
                        $query->where('rombel.nama_rombel', $request->rombel);
                    }
                }
            }
        }

        // Filter Status Warga Pesantren
        if ($request->filled('warga_pesantren')) {
            $warga_pesantren = strtolower($request->warga_pesantren);
            if ($warga_pesantren == 'iya') {
                $query->whereNotNull('santri.id');
            } else if ($warga_pesantren == 'tidak') {
                $query->whereNull('santri.id');
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

        // Filter No Telepon
        if ($request->filled('phone_number')) {
            if ($request->phone_number == true) {
                $query->whereNotNull('biodata.no_telepon')
                    ->where('biodata.no_telepon', '!=', '');
            } else if ($request->phone_number == false) {
                $query->whereNull('biodata.no_telepon')
                    ->where('biodata.no_telepon', '=', '');
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

        // Filter Status
        if ($request->filled('status')) {
            $status = strtolower($request->status);
            if ($status == 'aktif') {
                $query->Active();
            } else if ($status == 'tidak aktif') {
                $query->NonActive();
            }
        }

        // Filter Pemberkasan (Lengkap / Tidak Lengkap)
        if ($request->filled('pemberkasan')) {
            $jumlahBerkasWajib = JenisBerkas::where('wajib', 1)->count();
            $pemberkasan = strtolower($request->pemberkasan);
            if ($pemberkasan == 'lengkap') {
                $query->havingRaw('COUNT(DISTINCT berkas.id) >= ?', [$jumlahBerkasWajib]);
            } elseif ($pemberkasan == 'tidak lengkap') {
                $query->havingRaw('COUNT(DISTINCT berkas.id) < ?', [$jumlahBerkasWajib]);
            }
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
                    "nama" => $item->nama,
                    "niup" => $item->niup,
                    "lembaga" => $item->nama_wilayah,
                    "foto_profil" => url($item->foto_profil)
                ];
            })
        ]);
    }
}
