<?php

namespace App\Http\Controllers\api\PesertaDidik;

use App\Models\Pelajar;
use App\Models\JenisBerkas;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use App\Http\Resources\PdResource;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\api\FilterController;

class PelajarController extends Controller
{
    protected $filterController;

    public function __construct(FilterController $filterController)
    {
        $this->filterController = $filterController;
    }

    public function index()
    {
        $pelajar = Pelajar::Active()->latest()->paginate(10);
        return new PdResource(true, 'Data Pelajar', $pelajar);
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_peserta_didik' => [
                'required',
                'integer',
                Rule::unique('pelajar', 'id_peserta_didik')
            ],
            'id_lembaga' => ['required', 'integer', Rule::exists('lembaga', 'id')],
            'id_jurusan' => [
                'nullable',
                'integer',
                Rule::exists('jurusan', 'id')->where(function ($query) use ($request) {
                    if ($request->filled('id_lembaga')) {
                        $query->where('id_lembaga', $request->id_lembaga);
                    }
                }),
            ],
            'id_kelas' => [
                'nullable',
                'integer',
                Rule::exists('kelas', 'id')->where(function ($query) use ($request) {
                    if ($request->filled('id_jurusan')) {
                        $query->where('id_jurusan', $request->id_jurusan);
                    }
                }),
            ],
            'id_rombel' => [
                'nullable',
                'integer',
                Rule::exists('rombel', 'id')->where(function ($query) use ($request) {
                    if ($request->filled('id_kelas')) {
                        $query->where('id_kelas', $request->id_kelas);
                    }
                }),
            ],
            'no_induk' => 'nullable|string',
            'tanggal_masuk' => 'required|date',
            'tanggal_keluar' => 'nullable|date',
            'created_by' => 'required|integer',
            'status' => 'required|boolean'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $pelajar = Pelajar::create($validator->validated());

        return new PdResource(true, 'Data berhasil ditambah', $pelajar);
    }

    public function show($id)
    {
        $pelajar = Pelajar::findOrFail($id);
        return new PdResource(true, 'Detail Peserta Didik', $pelajar);
    }

    public function update(Request $request, $id)
    {

        $pelajar = Pelajar::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'id_lembaga' => ['required', 'integer', Rule::exists('lembaga', 'id')],
            'id_jurusan' => [
                'nullable',
                'integer',
                Rule::exists('jurusan', 'id')->where(function ($query) use ($request) {
                    if ($request->filled('id_lembaga')) {
                        $query->where('id_lembaga', $request->id_lembaga);
                    }
                }),
            ],
            'id_kelas' => [
                'nullable',
                'integer',
                Rule::exists('kelas', 'id')->where(function ($query) use ($request) {
                    if ($request->filled('id_jurusan')) {
                        $query->where('id_jurusan', $request->id_jurusan);
                    }
                }),
            ],
            'id_rombel' => [
                'nullable',
                'integer',
                Rule::exists('rombel', 'id')->where(function ($query) use ($request) {
                    if ($request->filled('id_kelas')) {
                        $query->where('id_kelas', $request->id_kelas);
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

        $pelajar->update($validator->validated());

        return new PdResource(true, 'Data berhasil diubah', $pelajar);
    }

    public function destroy($id)
    {
        $pelajar = Pelajar::findOrFail($id);

        $pelajar->delete();
        return new PdResource(true, 'Data berhasil dihapus', null);
    }

    public function pesertaDidikPelajar(Request $request)
    {
        $query = Pelajar::Active()
            ->leftjoin('peserta_didik', 'pelajar.id_peserta_didik', 'peserta_didik.id')
            ->join('biodata', 'peserta_didik.id_biodata', '=', 'biodata.id')
            ->leftJoin('kabupaten', 'kabupaten.id', '=', 'biodata.id_kabupaten')
            ->leftJoin('berkas', 'berkas.id_biodata', '=', 'biodata.id')
            ->leftJoin('jenis_berkas', 'berkas.id_jenis_berkas', '=', 'jenis_berkas.id')
            ->leftJoin('rombel', 'pelajar.id_rombel', '=', 'rombel.id')
            ->leftJoin('kelas', 'pelajar.id_kelas', '=', 'kelas.id')
            ->leftJoin('jurusan', 'pelajar.id_jurusan', '=', 'jurusan.id')
            ->leftJoin('lembaga', 'pelajar.id_lembaga', '=', 'lembaga.id')
            ->leftJoin('santri', 'peserta_didik.id', '=', 'santri.id_peserta_didik')
            ->leftjoin('wilayah', 'santri.id_wilayah', '=', 'wilayah.id')
            ->select(
                'pelajar.id',
                'pelajar.no_induk',
                'biodata.nama',
                'biodata.niup',
                'lembaga.nama_lembaga',
                'jurusan.nama_jurusan',
                'kelas.nama_kelas',
                'rombel.nama_rombel',
                'wilayah.nama_wilayah',
                DB::raw("CONCAT('Kab. ', kabupaten.nama_kabupaten) as kota_asal"),
                'biodata.created_at',
                'biodata.updated_at',
                DB::raw("COALESCE(MAX(berkas.file_path), 'default.jpg') as foto_profil")
            )
            ->groupBy(
                'pelajar.id',
                'pelajar.no_induk',
                'biodata.nama',
                'biodata.niup',
                'lembaga.nama_lembaga',
                'jurusan.nama_jurusan',
                'kelas.nama_kelas',
                'rombel.nama_rombel',
                'wilayah.nama_wilayah',
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
                    "no_induk" => $item->no_induk,
                    "nama" => $item->nama,
                    "niup" => $item->niup,
                    "lembaga" => $item->nama_lembaga,
                    "jurusan" => $item->nama_jurusan,
                    "kelas" => $item->nama_kelas,
                    "rombel" => $item->nama_rombel,
                    "wilayah" => $item->nama_wilayah,
                    "kota_asal" => $item->kota_asal,
                    "tgl_update" => Carbon::parse($item->updated_at)->translatedFormat('d F Y H:i:s'),
                    "tgl_input" =>  Carbon::parse($item->created_at)->translatedFormat('d F Y H:i:s'),
                    "foto_profil" => url($item->foto_profil)
                ];
            })
        ]);
    }
}
