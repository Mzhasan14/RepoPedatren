<?php

namespace App\Http\Controllers\api;

use App\Models\Santri;
use App\Models\Pelajar;
use App\Models\JenisBerkas;
use Illuminate\Http\Request;
use App\Models\AlumniPelajar;
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


    // Fitur pindah alumni pelajar berdasarkan inputan angkatan
    public function AlumniPelajarByAngkatan(Request $request)
    {
        // Validasi input
        $request->validate([
            'angkatan' => 'required|integer|min:1900|max:' . Carbon::now()->year,
        ]);

        $tahunSekarang = Carbon::now()->year;

        // Ambil semua data pelajar yang aktif berdasarkan angkatan
        $pelajarlulus = Pelajar::join('peserta_didik', 'pelajar.id_peserta_didik', '=', 'peserta_didik.id')
            ->leftJoin('rombel', 'pelajar.id_rombel', '=', 'rombel.id')
            ->leftJoin('kelas', 'pelajar.id_kelas', '=', 'kelas.id')
            ->leftJoin('jurusan', 'pelajar.id_jurusan', '=', 'jurusan.id')
            ->leftJoin('lembaga', 'pelajar.id_lembaga', '=', 'lembaga.id')
            ->where('pelajar.status', 'aktif')
            ->where('pelajar.angkatan', $request->angkatan)
            ->select([
                'peserta_didik.id',
                'pelajar.id_lembaga',
                'pelajar.id_jurusan',
                'pelajar.id_kelas',
                'pelajar.id_rombel'
            ])
            ->get();

        if ($pelajarlulus->isEmpty()) {
            return response()->json([
                'message' => 'Tidak ada pelajar yang dipindahkan.',
                'angkatan' => $request->angkatan
            ], 200);
        }

        // Siapkan data untuk insert ke alumni_pelajar
        $dataAlumniPelajar = $pelajarlulus->map(fn($pelajar) => [
            'id_peserta_didik' => $pelajar->id,
            'id_lembaga' => $pelajar->id_lembaga,
            'id_jurusan' => $pelajar->id_jurusan,
            'id_kelas' => $pelajar->id_kelas,
            'id_rombel' => $pelajar->id_rombel,
            'tahun_keluar' => $tahunSekarang,
            'status_alumni' => 'lulus',
            'wafat' => false,
            // 'created_by' => auth()->id() ?? 1, // Gunakan auth jika tersedia
            'created_by' => 1, // Gunakan auth jika tersedia
            'created_at' => now(),
            'updated_at' => now(),
        ])->toArray();

        // Eksekusi dalam satu transaksi
        DB::transaction(function () use ($dataAlumniPelajar, $pelajarlulus) {
            DB::table('alumni_pelajar')->insert($dataAlumniPelajar);

            // Update status pelajar secara bulk (tanpa looping)
            Pelajar::whereIn('id', $pelajarlulus->pluck('id_pelajar'))->update([
                'status' => 'alumni',
                'tanggal_keluar' => now(),
            ]);
        });

        return response()->json([
            'message' => count($dataAlumniPelajar) . " siswa berhasil dipindahkan menjadi alumni.",
            'angkatan' => $request->angkatan,
            'tahun_keluar' => $tahunSekarang
        ], 200);
    }

    // Fitur pindah alumni santri berdasarkan inputan angkatan
    public function AlumniSantriByAngkatan(Request $request)
    {
        // Validasi input
        $request->validate([
            'angkatan' => 'required|integer|min:1900|max:' . Carbon::now()->year,
        ]);

        $tahunSekarang = Carbon::now()->year;

        // Ambil semua data santri yang aktif berdasarkan angkatan
        $santrilulus = Santri::join('peserta_didik', 'santri.id_peserta_didik', '=', 'peserta_didik.id')
            ->leftJoin('domisili', 'santri.id_domisili', '=', 'domisili.id')
            ->leftJoin('kamar', 'santri.id_kamar', '=', 'kamar.id')
            ->leftJoin('blok', 'santri.id_blok', '=', 'blok.id')
            ->leftJoin('wilayah', 'santri.id_wilayah', '=', 'wilayah.id')
            ->where('santri.status', 'aktif')
            ->where('santri.angkatan', $request->angkatan)
            ->select([
                'peserta_didik.id',
                'santri.id_wilayah',
                'santri.id_blok',
                'santri.id_kamar',
                'santri.id_domisili'
            ])
            ->get();

        if ($santrilulus->isEmpty()) {
            return response()->json([
                'message' => 'Tidak ada santri yang dipindahkan.',
                'angkatan' => $request->angkatan
            ], 200);
        }

        // Siapkan data untuk insert ke alumni_santri
        $dataAlumniSantri = $santrilulus->map(fn($santri) => [
            'id_peserta_didik' => $santri->id,
            'id_wilayah' => $santri->id_wilayah,
            'id_blok' => $santri->id_blok,
            'id_kamar' => $santri->id_kamar,
            'id_domisili' => $santri->id_domisili,
            'tahun_keluar' => $tahunSekarang,
            'status_alumni' => 'lulus',
            'wafat' => false,
            // 'created_by' => auth()->id() ?? 1, // Gunakan auth jika tersedia
            'created_by' => 1, // Gunakan auth jika tersedia
            'created_at' => now(),
            'updated_at' => now(),
        ])->toArray();

        // Eksekusi dalam satu transaksi
        DB::transaction(function () use ($dataAlumniSantri, $santrilulus) {
            DB::table('alumni_santri')->insert($dataAlumniSantri);

            // Update status Santri secara bulk (tanpa looping)
            Santri::whereIn('id', $santrilulus->pluck('id_santri'))->update([
                'status' => 'alumni',
                'tanggal_keluar' => now(),
            ]);
        });

        return response()->json([
            'message' => count($dataAlumniSantri) . " santri berhasil dipindahkan menjadi alumni.",
            'angkatan' => $request->angkatan,
            'tahun_keluar' => $tahunSekarang
        ], 200);
    }

    public function AlumniPelajarByID(Request $request)
    {
        // Validasi Input
        $request->validate([
            'angkatan' => 'nullable|integer|min:1900|max:' . Carbon::now()->year,
            'id_pelajar' => 'nullable|array',
            'id_pelajar.*' => 'integer|exists:pelajar,id',
        ]);

        $tahunSekarang = Carbon::now()->year;

        // Ambil pelajar yang akan diluluskan
        $query = Pelajar::query();

        if ($request->filled('angkatan')) {
            $query->where('angkatan', $request->angkatan);
        }

        if ($request->filled('id_pelajar')) {
            $query->whereIn('id', $request->id_pelajar);
        }

        $pelajarLulus = $query->get();

        if ($pelajarLulus->isEmpty()) {
            return response()->json([
                'message' => 'Tidak ada pelajar yang dapat diluluskan.',
                'angkatan' => $request->angkatan
            ], 200);
        }

        // Persiapkan data untuk alumni_pelajar
        $dataAlumni = $pelajarLulus->map(fn($pelajar) => [
            'id_peserta_didik' => $pelajar->id_peserta_didik,
            'id_lembaga' => $pelajar->id_lembaga,
            'id_jurusan' => $pelajar->id_jurusan,
            'id_kelas' => $pelajar->id_kelas,
            'id_rombel' => $pelajar->id_rombel,
            'tahun_keluar' => $tahunSekarang,
            'status_alumni' => 'lulus',
            'created_by' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ])->toArray();

        // Jalankan transaksi database
        DB::transaction(function () use ($dataAlumni, $pelajarLulus) {
            DB::table('alumni_pelajar')->insert($dataAlumni);
            Pelajar::whereIn('id', $pelajarLulus->pluck('id'))->update([
                'status' => 'alumni',
                'tanggal_keluar' => now(),
            ]);
        });

        return response()->json([
            'message' => count($dataAlumni) . " pelajar berhasil diluluskan.",
            'angkatan' => $request->angkatan,
            'tahun_keluar' => $tahunSekarang
        ], 200);
    }

    public function AlumniSantriByID(Request $request)
    {
        // Validasi Input
        $request->validate([
            'angkatan' => 'nullable|integer|min:1900|max:' . Carbon::now()->year,
            'id_santri' => 'nullable|array',
            'id_santri.*' => 'integer|exists:santri,id',
        ]);

        $tahunSekarang = Carbon::now()->year;

        // Ambil santri yang akan diluluskan
        $query = Santri::query();

        if ($request->filled('angkatan')) {
            $query->where('angkatan', $request->angkatan);
        }

        if ($request->filled('id_santri')) {
            $query->whereIn('id', $request->id_santri);
        }

        $santriLulus = $query->get();

        if ($santriLulus->isEmpty()) {
            return response()->json([
                'message' => 'Tidak ada santri yang dapat diluluskan.',
                'angkatan' => $request->angkatan
            ], 200);
        }

        // Persiapkan data untuk alumni_santri
        $dataAlumni = $santriLulus->map(fn($santri) => [
            'id_peserta_didik' => $santri->id_peserta_didik,
            'id_wilayah' => $santri->id_wilayah,
            'id_blok' => $santri->id_blok,
            'id_kamar' => $santri->id_kamar,
            'id_domisili' => $santri->id_domisili,
            'tahun_keluar' => $tahunSekarang,
            'status_alumni' => 'lulus',
            'created_by' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ])->toArray();

        // Jalankan transaksi database
        DB::transaction(function () use ($dataAlumni, $santriLulus) {
            DB::table('alumni_santri')->insert($dataAlumni);
            Santri::whereIn('id', $santriLulus->pluck('id'))->update([
                'status' => 'alumni',
                'tanggal_keluar' => now(),
            ]);
        });

        return response()->json([
            'message' => count($dataAlumni) . " santri berhasil diluluskan.",
            'angkatan' => $request->angkatan,
            'tahun_keluar' => $tahunSekarang
        ], 200);
    }

    public function alumni(Request $request)
    {
        $query = AlumniPelajar::leftjoin('peserta_didik', 'alumni_pelajar.id_peserta_didik', '=', 'peserta_didik.id')
            ->join('biodata', 'peserta_didik.id_biodata', '=', 'biodata.id')
            ->leftjoin('alumni_santri', 'peserta_didik.id', '=', 'alumni_santri.id_peserta_didik')
            ->leftJoin('berkas', 'berkas.id_biodata', '=', 'biodata.id')
            ->leftJoin('jenis_berkas', 'berkas.id_jenis_berkas', '=', 'jenis_berkas.id')
            ->leftJoin('negara', 'biodata.id_negara', '=', 'negara.id')
            ->leftJoin('provinsi', 'biodata.id_provinsi', '=', 'provinsi.id')
            ->leftJoin('kabupaten', 'biodata.id_kabupaten', '=', 'kabupaten.id')
            ->leftJoin('kecamatan', 'biodata.id_kecamatan', '=', 'kecamatan.id')
            ->leftJoin('santri', 'peserta_didik.id', '=', 'santri.id_peserta_didik')
            ->leftJoin('lembaga', 'alumni_pelajar.id_lembaga', '=', 'lembaga.id')
            ->select(
                'peserta_didik.id',
                'biodata.nama',
                DB::raw("CONCAT('Kab. ', kabupaten.nama_kabupaten) as alamat"),
                DB::raw("CONCAT('pendidikan terakhir: ', lembaga.nama_lembaga, ' (', IFNULL(alumni_pelajar.tahun_keluar, 'Belum Lulus'), ')') as nama_lembaga"),
                DB::raw("COALESCE(MAX(berkas.file_path), 'default.jpg') as foto_profil")
            )
            ->groupBy(
                'peserta_didik.id',
                'biodata.nama',
                'kabupaten.nama_kabupaten',
                'lembaga.nama_lembaga',
                'alumni_pelajar.tahun_keluar'
            );

        // Filter Umum (Alamat dan Jenis Kelamin)
        $query = $this->filterController->applyCommonFilters($query, $request);

        // Filter Wilayah
        // if ($request->filled('wilayah')) {
        //     $wilayah = strtolower($request->wilayah);
        //     $query->leftjoin('wilayah', 'santri.id_wilayah', '=', 'wilayah.id')
        //         ->leftjoin('blok', 'santri.id_blok', '=', 'blok.id')
        //         ->leftjoin('kamar', 'santri.id_kamar', '=', 'kamar.id')
        //         ->leftjoin('domisili', 'santri.id_domisili', '=', 'domisili.id')
        //         ->where('wilayah.nama_wilayah', $wilayah);
        //     if ($request->filled('blok')) {
        //         $blok = strtolower($request->blok);
        //         $query->where('blok.nama_blok', $blok);
        //         if ($request->filled('kamar')) {
        //             $kamar = strtolower($request->kamar);
        //             $query->where('kamar.nama_kamar', $kamar);
        //         }
        //     }
        // }

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
