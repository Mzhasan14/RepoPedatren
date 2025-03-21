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
            ->leftJoin('materi_ajar','materi_ajar.id_pengajar','=','pengajar.id')
            ->select(
                'pengajar.id',
                'biodata.nama',
                'biodata.niup',
                DB::raw("TIMESTAMPDIFF(YEAR, biodata.tanggal_lahir, CURDATE()) AS umur"),
                DB::raw("
                GROUP_CONCAT(DISTINCT materi_ajar.nama_materi SEPARATOR ', ') AS daftar_materi"),
                DB::raw("
                CONCAT(
                    FLOOR(SUM(DISTINCT materi_ajar.jumlah_menit) / 60), ' jam ',
                    MOD(SUM(DISTINCT materi_ajar.jumlah_menit), 60), ' menit'
                ) AS total_waktu_materi
            "),     
                DB::raw("COUNT(DISTINCT materi_ajar.nama_materi) AS total_materi"),
                DB::raw("
                CASE 
                    WHEN TIMESTAMPDIFF(YEAR, entitas_pegawai.tanggal_masuk, COALESCE(entitas_pegawai.tanggal_keluar, CURDATE())) = 0 
                    THEN CONCAT('Belum setahun sejak ', DATE_FORMAT(entitas_pegawai.tanggal_masuk, '%Y-%m-%d'))
                    ELSE CONCAT(TIMESTAMPDIFF(YEAR, entitas_pegawai.tanggal_masuk, COALESCE(entitas_pegawai.tanggal_keluar, CURDATE())), ' Tahun sejak ', DATE_FORMAT(entitas_pegawai.tanggal_masuk, '%Y-%m-%d'))
                END AS masa_kerja"),
                'golongan.nama_golongan',
                'biodata.nama_pendidikan_terakhir',
                DB::raw("DATE_FORMAT(pengajar.updated_at, '%Y-%m-%d %H:%i:%s') AS tgl_update"),
                DB::raw("DATE_FORMAT(pengajar.created_at, '%Y-%m-%d %H:%i:%s') AS tgl_input"),
                'lembaga.nama_lembaga',    
                DB::raw("COALESCE(MAX(berkas.file_path), 'default.jpg') as foto_profil")
                )   
                 ->groupBy(
                    'pengajar.id',
                    'biodata.nama',
                    'biodata.niup',
                    'biodata.tanggal_lahir',
                    'golongan.nama_golongan',
                    'biodata.nama_pendidikan_terakhir',
                    'pengajar.updated_at',
                    'pengajar.created_at',
                    'lembaga.nama_lembaga',
                    'entitas_pegawai.tanggal_masuk',
                    'entitas_pegawai.tanggal_keluar'
                );   
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

        // 🔹Materi Ajar
        if ($request->has('materi_ajar')) {
            if (strtolower($request->materi_ajar) === 'materi ajar 1') {
                // Hanya pengajar yang memiliki 1 materi ajar
                $query->havingRaw('COUNT(DISTINCT materi_ajar.id) = 1');
            } elseif (strtolower($request->materi_ajar) === 'materi ajar lebih dari 1') {
                // Hanya pengajar yang memiliki lebih dari 1 materi ajar
                $query->havingRaw('COUNT(DISTINCT materi_ajar.id) > 1');
            }
        }
        
            // 🔹 Filter Jabatan
        if ($request->has('jabatan')) {
            $query->where('pengajar.jabatan', strtolower($request->jabatan));
        }     
        // Filter Masa Kerja
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

        // Filter Warga Pesantren
        if ($request->filled('warga_pesantren')) {
            if (strtolower($request->warga_pesantren) === 'memiliki niup') {
                // Hanya tampilkan data yang memiliki NIUP
                $query->whereNotNull('biodata.niup')->where('biodata.niup', '!=', '');
            } elseif (strtolower($request->warga_pesantren) === 'tidak memiliki niup') {
                // Hanya tampilkan data yang tidak memiliki NIUP
                $query->whereNull('biodata.niup')->orWhereRaw("TRIM(biodata.niup) = ''");
            }
        }

                // Filter Umur
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

                // Filter No Telepon
        if ($request->filled('phone_number')) {
            if (strtolower($request->phone_number) === 'mempunyai') {
                // Hanya tampilkan data yang memiliki nomor telepon
                $query->whereNotNull('biodata.no_telepon')->where('biodata.no_telepon', '!=', '');
            } elseif (strtolower($request->phone_number) === 'tidak mempunyai') {
                // Hanya tampilkan data yang tidak memiliki nomor telepon
                $query->whereNull('biodata.no_telepon')->orWhere('biodata.no_telepon', '');
            }
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
                    "umur" => $item->umur,
                    "daftar_materi" => $item->daftar_materi,
                    "total_waktu_materi" => $item->total_waktu_materi,
                    "total_materi" => $item->total_materi,
                    "masa_kerja" => $item->masa_kerja,
                    "golongan" => $item->nama_golongan,
                    "pendidikan_terakhir" => $item->nama_pendidikan_terakhir,
                    "tgl_update" => $item->tgl_update,
                    "tgl_input" => $item->tgl_input,
                    "lembaga" => $item->nama_lembaga,
                    "foto_profil" => url($item->foto_profil)
                ];
            })
        ]);
    }
}
