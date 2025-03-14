<?php

namespace App\Http\Controllers\Api;

use App\Models\Khadam;
use App\Models\JenisBerkas;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Resources\PdResource;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class KhadamController extends Controller
{
    protected $filterController;

    public function __construct(FilterController $filterController)
    {
        $this->filterController = $filterController;
    }

    public function index()
    {
        $khadam = Khadam::Active();
        return new PdResource(true, 'Data berhasil ditampilkan', $khadam);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_peserta_didik' => ['required', 'integer', Rule::unique('khadam', 'id_peserta_didik')],
            'keterangan' => 'required|string|max:255',
            'status' => 'required|boolean',
            'created_by' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Data gagal ditambahkan',
                'data' => $validator->errors()
            ]);
        }

        $khadam = Khadam::create($validator->validated());
        return new PdResource(true, 'Data berhasil ditambahkan', $khadam);
    }
    public function show(string $id)
    {
        $khadam = Khadam::findOrFail($id);
        return new PdResource(true, 'Data berhasil di tampilkan', $khadam);
    }
    public function update(Request $request, string $id)
    {
        $khadam = Khadam::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'id_peserta_didik' => [
                'required',
                'integer',
                Rule::unique('khadam', 'id_peserta_didik')->ignore($id)
            ],
            'keterangan' => 'required|string|max:255',
            'status' => 'required|boolean',
            'updated_by' => 'nullable|integer',
        ]);


        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Data gagal di update',
                'data' => $validator->errors()
            ]);
        }

        $khadam->update($validator->validated());
        return new PdResource(true, 'Data berhasil diupdate', $khadam);
    }

    public function destroy(string $id)
    {
        $khadam = Khadam::findOrFail($id);
        $khadam->delete();
        return new PdResource(true, 'Data berhasil dihapus', $khadam);
    }

    public function khadam(Request $request)
    {
        $query = Khadam::join('biodata', 'khadam.id_biodata', '=', 'biodata.id')
            ->leftjoin('berkas', 'biodata.id', '=', 'berkas.id_biodata')
            ->leftjoin('jenis_berkas', 'berkas.id_jenis_berkas', '=', 'jenis_berkas.id')
            ->leftjoin('peserta_didik', 'peserta_didik.id_biodata', '=', 'biodata.id')
            ->leftjoin('pelajar', 'peserta_didik.id', '=', 'pelajar.id_peserta_didik')
            ->leftjoin('santri', 'peserta_didik.id', '=', 'santri.id_peserta_didik')
            ->leftjoin('lembaga as lp', 'pelajar.id_lembaga', '=', 'lp.id')
            ->leftjoin('pegawai', 'biodata.id', '=', 'pegawai.id_biodata')
            ->leftjoin('lembaga as lg', 'pegawai.id_lembaga', '=', 'lg.id')
            ->select(
                'khadam.id',
                'biodata.nama',
                'khadam.keterangan',
                DB::raw("COALESCE(MAX(berkas.file_path), 'default.jpg') as foto_profil")
            )
            ->groupBy(
                'khadam.id',
                'biodata.nama',
                'khadam.keterangan'
            );

        // Filter Umum (Alamat dan Jenis Kelamin)
        $query = $this->filterController->applyCommonFilters($query, $request);

        // Filter Wilayah
        if ($request->filled('wilayah')) {
            $wilayah = strtolower($request->wilayah);
            $query->leftjoin('wilayah', 'santri.id_wilayah', '=', 'wilayah.id')
                ->leftjoin('blok', 'santri.id_blok', '=', 'blok.id')
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
                    "keterangan" => $item->keterangan,
                    "foto_profil" => url($item->foto_profil)
                ];
            })
        ]);
    }
}
