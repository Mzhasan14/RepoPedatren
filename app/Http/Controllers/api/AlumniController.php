<?php

namespace App\Http\Controllers\api;

use App\Models\Alumni;
use App\Models\Pelajar;
use App\Models\JenisBerkas;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class AlumniController extends Controller
{
    protected $filterController;

    public function __construct(FilterController $filterController)
    {
        $this->filterController = $filterController;
    }
    
    public function pindahAlumni(Request $request)
    {
        // Validasi input
        $request->validate([
            'angkatan' => 'required|integer',
        ]);

        $tahunSekarang = Carbon::now()->year;

        // Ambil semua ID siswa berdasarkan angkatan (bukan berdasarkan tanggal_masuk lagi!)
        $pelajarlulus = Pelajar::where('status', 'aktif')
            ->where('angkatan', $request->angkatan)
            ->pluck('id');

        if ($pelajarlulus->isEmpty()) {
            return response()->json([
                'message' => 'Tidak ada pelajar yang dipindahkan.',
                'angkatan' => $request->angkatan
            ], 200);
        }

        // Siapkan data untuk insert ke alumni
        $dataAlumni = $pelajarlulus->map(fn($id) => [
            'id_pelajar' => $id,
            'tahun_keluar' => $tahunSekarang,
            'status_alumni' => 'lulus',
            'created_by' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ])->toArray();

        // Eksekusi dalam satu transaksi
        DB::transaction(function () use ($dataAlumni, $pelajarlulus) {
            DB::table('alumni')->insert($dataAlumni);
            Pelajar::whereIn('id', $pelajarlulus)->update([
                'status' => 'alumni',
                'tanggal_keluar' => now(),
            ]);
        });

        return response()->json([
            'message' => count($dataAlumni) . " siswa berhasil dipindahkan menjadi alumni.",
            'angkatan' => $request->angkatan,
            'tahun_keluar' => $tahunSekarang
        ], 200);
    }

    public function alumni(Request $request)
    {
        $query = Alumni::Active()
        ->join('pelajar', 'alumni.id_pelajar', '=', 'pelajar.id')
        ->leftJoin('peserta_didik', 'pelajar.id_peserta_didik', '=', 'peserta_didik.id')
        ->join('biodata', 'peserta_didik.id_biodata', '=', 'biodata.id')
        ->leftJoin('berkas', 'berkas.id_biodata', '=', 'biodata.id')
        ->leftJoin('jenis_berkas', 'berkas.id_jenis_berkas', '=', 'jenis_berkas.id')
        ->leftJoin('negara', 'biodata.id_negara', '=', 'negara.id')
        ->leftJoin('provinsi', 'biodata.id_provinsi', '=', 'provinsi.id')
        ->leftJoin('kabupaten', 'biodata.id_kabupaten', '=', 'kabupaten.id')
        ->leftJoin('kecamatan', 'biodata.id_kecamatan', '=', 'kecamatan.id')
        ->leftJoin('santri', 'peserta_didik.id', '=', 'santri.id_peserta_didik')
        ->leftJoin('lembaga', 'pelajar.id_lembaga', '=', 'lembaga.id')
        ->select(
            'alumni.id',
            'biodata.nama',
            DB::raw("CONCAT('Kab. ', kabupaten.nama_kabupaten) as alamat"),
            DB::raw("CONCAT('pendidikan terakhir: ', lembaga.nama_lembaga, ' (', IFNULL(alumni.tahun_keluar, 'Belum Lulus'), ')') as nama_lembaga"),
            DB::raw("COALESCE(MAX(berkas.file_path), 'default.jpg') as foto_profil")
        )
        ->groupBy(
            'alumni.id',
            'biodata.nama',
            'kabupaten.nama_kabupaten',
            'lembaga.nama_lembaga',
            'alumni.tahun_keluar'
        );
    

        // Filter Umum (Alamat dan Jenis Kelamin)
        $query = $this->filterController->applyCommonFilters($query, $request);

        // Filter Wilayah
        if ($request->filled('wilayah')) {
            $wilayah = strtolower($request->wilayah);
            $query->leftjoin('wilayah', 'santri.id_wilayah', '=', 'wilayah.id')
                ->leftjoin('blok', 'santri.id_blok', '=', 'blok.id')
                ->leftjoin('kamar', 'santri.id_kamar', '=', 'kamar.id')
                ->leftjoin('domisili', 'santri.id_domisili', '=', 'domisili.id')
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
                    "alamat" => $item->alamat,
                    "lembaga" => $item->nama_lembaga,
                    "foto_profil" => url($item->foto_profil)
                ];
            })
        ]);
    }
}
