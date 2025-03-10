<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Models\Peserta_didik;
use Illuminate\Validation\Rule;
use App\Http\Resources\PdResource;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

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
        $query = Peserta_didik::join('biodata', 'peserta_didik.id_biodata', '=', 'biodata.id')
            ->join('pelajar', 'peserta_didik.id', '=', 'pelajar.id_peserta_didik')
            ->join('rombel', 'pelajar.id_rombel', '=', 'rombel.id')
            ->join('kelas', 'pelajar.id_kelas', '=', 'kelas.id')
            ->join('jurusan', 'pelajar.id_jurusan', '=', 'jurusan.id')
            ->join('lembaga', 'pelajar.id_lembaga', '=', 'lembaga.id')
            ->leftjoin('santri', 'peserta_didik.id', '=', 'santri.id_peserta_didik');

        // Filter Umum (Alamat dan Jenis Kelamin)
        $query = $this->filterController->applyCommonFilters($query, $request);

        // Filter Wilayah
        if ($request->filled('wilayah')) {
            $wilayah = strtolower($request->wilayah);
            $query->join('wilayah', 'santri.id_wilayah', '=', 'wilayah.id')
                ->join('blok', 'santri.id_blok', '=', 'blok.id')
                ->join('kamar', 'santri.id_kamar', '=', 'kamar.id')
                ->join('domisili', 'santri.id_domisili', '=', 'domisili.id')
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
            if ($warga_pesantren == 'ya') {
                $query->whereNotNull('santri.id');
            } else if ($warga_pesantren == 'tidak') {
                $query->whereNull('santri.id');
            }
        }

        // Filter Angkatan Pelajar
        if ($request->filled('angkatan_pelajar')) {
            $query->whereRaw('YEAR(pelajar.tanggal_masuk) = ?', [$request->angkatan_pelajar]);
        }

        // Filter Angkatan Santri
        if ($request->filled('angkatan_santri')) {
            $query->whereRaw('YEAR(santri.tanggal_masuk) = ?', [$request->angkatan_santri]);
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

        // Ambil Data
        $hasil = $query->select('biodata.nama', 'biodata.niup', 'lembaga.nama_lembaga');

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
                    "nama" => $item->nama,
                    "niup" => $item->niup,
                    "lembaga" => $item->nama_lembaga,
                ];
            })
        ]);



        //             "foto" => $item->foto ? Storage::url($item->foto) : null

    }
}
