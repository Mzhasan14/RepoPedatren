<?php

namespace App\Http\Controllers\Api\Pegawai;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Biodata;
use Illuminate\Http\Request;
use App\Models\Pegawai\Pengajar;
use App\Http\Resources\PdResource;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\FilterController;
use App\Models\JenisBerkas;

class PengajarController extends Controller
{

    protected $filterController;

    public function __construct(FilterController $filterController)
    {
        $this->filterController = $filterController;
    }

    public function index()
    {
        $pengajar = Pengajar::all();
        return new PdResource(true, 'Data berhasil ditampilkan', $pengajar);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_pegawai'   => 'required|integer',
            'id_golongan'  => 'required|integer',
            'id_lembaga'   => 'required|integer',
            'mapel'        => 'required|string|max:255',
            'created_by'   => 'required|integer',
            'status'       => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Data gagal ditambahkan',
                'data' => $validator->errors()
            ]);
        }

        $pengajar = Pengajar::create($validator->validated());
        return new PdResource(true, 'Data berhasil ditambahkan', $pengajar);
    }

    public function show(string $id)
    {
        $pengajar = Pengajar::findOrFail($id);
        return new PdResource(true, 'Data berhasil ditampilkan', $pengajar);
    }
    public function update(Request $request, string $id)
    {
        $pengajar = Pengajar::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'id_pegawai'   => 'required|integer',
            'id_golongan'  => 'required|integer',
            'id_lembaga'   => 'required|integer',
            'mapel'        => 'required|string|max:255',
            'updated_by'   => 'nullable|integer',
            'status'       => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Data gagal ditambahkan',
                'data' => $validator->errors()
            ]);
        }
        $pengajar->update($validator->validated());
        return new PdResource(true, 'Data berhasil diupdate', $pengajar);
    }
    public function destroy(string $id)
    {
        $pengajar = Pengajar::findOrFail($id);
        $pengajar->delete();
        return new PdResource(true, 'Data berhasil dihapus', $pengajar);
    }

    public function Pengajar()
    {
        $pengajar = Biodata::join('pegawai', 'biodata.id', '=', 'pegawai.id_biodata')
            ->join('pengajar', 'pegawai.id', '=', 'pengajar.id_pegawai')
            ->select(
                'pengajar.id as id_pengajar',
                'biodata.nama',
                'biodata.niup',
                'biodata.nama_pendidikan_terakhir',
                'biodata.image_url'
            )
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Data berhasil ditampilkan',
            'data' => $pengajar
        ]);
    }

    public function filterPengajar(Request $request)
    {
        $query = Pengajar::Active()
            ->join('pegawai', 'pengajar.id_pegawai', '=', 'pegawai.id')
            ->join('biodata', 'pegawai.id_biodata', '=', 'biodata.id')
            ->leftJoin('lembaga', 'pegawai.id_lembaga', '=', 'lembaga.id')
            ->leftJoin('golongan', 'pengajar.id_golongan', '=', 'golongan.id')
            ->leftJoin('kategori_golongan', 'golongan.id_kategori_golongan', '=', 'kategori_golongan.id')
            ->leftJoin('entitas_pegawai','entitas_pegawai.id_pegawai','=','pegawai.id')
            ->leftJoin('berkas', 'berkas.id_biodata', '=', 'biodata.id')
            ->leftJoin('jenis_berkas', 'berkas.id_jenis_berkas', '=', 'jenis_berkas.id')
            ->select(
                'pengajar.id',
                'biodata.nama',
                'biodata.niup',
                'lembaga.nama_lembaga',    
                DB::raw("COALESCE(MAX(berkas.file_path), 'default.jpg') as foto_profil")
                )->groupBy('pengajar.id', 'biodata.nama', 'biodata.niup','lembaga.nama_lembaga');
                

        // 🔹 Terapkan filter umum (lokasi & jenis kelamin)
        $query = $this->filterController->applyCommonFilters($query, $request);

        if ($request->has('lembaga')) {
            $query->where('lembaga.nama_lembaga', strtolower($request->lembaga));
        }

        // 🔹 Filter Kategori Golongan
        if ($request->has('kategori_golongan')) {
            $query->where('kategori_golongan.nama_kategori_golongan', strtolower($request->kategori_golongan));
        }
        // 🔹 Filter Golongan
        if ($request->has('golongan')) {
            $query->where('golongan.nama_golongan', strtolower($request->golongan));
        }


        if ($request->has('materi_ajar')) {
            $query->where('pengajar.mapel', strtolower($request->materi_ajar));
        }

        $masaKerja = $request->input('masa_kerja'); // Mengambil input dari request
        $today = now(); // Menggunakan tanggal saat ini
        
        if ($masaKerja == 1) {
            $query->whereRaw("
                TIMESTAMPDIFF(YEAR, entitas_pegawai.tanggal_masuk, COALESCE(entitas_pegawai.tanggal_keluar, ?)) < ?
            ", [$today, 1]);
        } elseif ($masaKerja == 5) {
            $query->whereRaw("
                TIMESTAMPDIFF(YEAR, entitas_pegawai.tanggal_masuk, COALESCE(entitas_pegawai.tanggal_keluar, ?)) BETWEEN ? AND ?
            ", [$today, 1, 5]);
        } elseif ($masaKerja == 10) {
            $query->whereRaw("
                TIMESTAMPDIFF(YEAR, entitas_pegawai.tanggal_masuk, COALESCE(entitas_pegawai.tanggal_keluar, ?)) BETWEEN ? AND ?
            ", [$today, 6, 10]);
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
        if ($request->filled('warga_pesantren')) {
            $query->where('pegawai.warga_pesantren', strtolower($request->warga_pesantren == 'iya' ? 1 : 0));
        }
        if ($request->filled('umur')) {
            $umurInput = $request->umur;
        
            // Cek apakah input umur dalam format rentang (misalnya "20-25")
            if (strpos($umurInput, '-') !== false) {
                [$umurMin, $umurMax] = explode('-', $umurInput);
            } else {
                // Jika input angka tunggal, jadikan batas atas dan bawah sama
                $umurMin = $umurInput;
                $umurMax = $umurInput;
            }
        
            // Filter berdasarkan umur yang dihitung dari tanggal_lahir
            $query->whereBetween(
                DB::raw('TIMESTAMPDIFF(YEAR, biodata.tanggal_lahir, CURDATE())'),
                [(int)$umurMin, (int)$umurMax]
            );
        }
        $onePage = $request->input('limit', 25);

        $currentPage =  $request->input('page', 1);

        $hasil = $query->paginate($onePage, ['*'], 'page', $currentPage);


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
                    "lembaga" => $item->nama_lembaga,
                    "foto_profil" => url($item->foto_profil)
                ];
            })
        ]);
    }
}
