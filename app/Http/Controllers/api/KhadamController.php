<?php

namespace App\Http\Controllers\Api;

use App\Models\Khadam;
use App\Models\JenisBerkas;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
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
        $query = DB::table('khadam as k')
            ->join('biodata as b', 'k.id_biodata', '=', 'b.id')
            ->leftJoin('berkas as br', function ($join) {
                $join->on('b.id', '=', 'br.id_biodata')
                    ->where('br.id_jenis_berkas', '=', function ($query) {
                        $query->select('id')
                            ->from('jenis_berkas')
                            ->where('nama_jenis_berkas', 'Pas foto')
                            ->limit(1);
                    })
                    ->whereRaw('br.id = (select max(b2.id) from berkas as b2 where b2.id_biodata = b.id and b2.id_jenis_berkas = br.id_jenis_berkas)');
            })
            ->leftJoin('warga_pesantren as wp', function ($join) {
                $join->on('b.id', '=', 'wp.id_biodata')
                    ->where('wp.status', true);
            })
            ->where('k.status', true)
            ->select(
                'k.id',
                'wp.niup',
                DB::raw("COALESCE(b.nik, b.no_passport) as identitas"),
                'b.nama',
                'k.keterangan',
                'b.created_at',
                'b.updated_at',
                DB::raw("COALESCE(MAX(br.file_path), 'default.jpg') as foto_profil")
            )
            ->groupBy(
                'k.id',
                'wp.niup',
                'b.nama',
                'b.created_at',
                'b.updated_at',
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
                    "niup" => $item->niup,
                    "nama" => $item->nama,
                    "keterangan" => $item->keterangan,
                    "tgl_update" => Carbon::parse($item->updated_at)->translatedFormat('d F Y H:i:s'),
                    "tgl_input" =>  Carbon::parse($item->created_at)->translatedFormat('d F Y H:i:s'),
                    "foto_profil" => url($item->foto_profil)
                ];
            })
        ]);
    }
}
