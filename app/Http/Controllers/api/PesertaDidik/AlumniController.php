<?php

namespace App\Http\Controllers\api\PesertaDidik;

use Illuminate\Http\Request;
use App\Models\Peserta_didik;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Controllers\api\FilterController;

class AlumniController extends Controller
{
    protected $filterController;

    public function __construct(FilterController $filterController)
    {
        $this->filterController = $filterController;
    }
    public function alumni(Request $request)
    {
        $query = Peserta_didik::leftJoin('riwayat_pelajar', 'riwayat_pelajar.id_peserta_didik', '=', 'peserta_didik.id')
            ->leftJoin('riwayat_santri', 'riwayat_santri.id_peserta_didik', '=', 'peserta_didik.id')
            ->leftjoin('pelajar', 'pelajar.id_peserta_didik', '=', 'peserta_didik.id')
            ->leftjoin('santri', 'santri.id_peserta_didik', '=', 'peserta_didik.id')
            ->leftJoin('lembaga', 'riwayat_pelajar.id_lembaga', '=', 'lembaga.id')
            ->leftJoin('jurusan', 'riwayat_pelajar.id_jurusan', '=', 'jurusan.id')
            ->leftJoin('kelas', 'riwayat_pelajar.id_kelas', '=', 'kelas.id')
            ->leftJoin('rombel', 'riwayat_pelajar.id_rombel', '=', 'rombel.id')
            ->join('biodata', 'peserta_didik.id_biodata', '=', 'biodata.id')
            ->leftJoin('berkas', 'berkas.id_biodata', '=', 'biodata.id')
            ->leftJoin('jenis_berkas', 'berkas.id_jenis_berkas', '=', 'jenis_berkas.id')
            ->leftJoin('negara', 'biodata.id_negara', '=', 'negara.id')
            ->leftJoin('provinsi', 'biodata.id_provinsi', '=', 'provinsi.id')
            ->leftJoin('kabupaten', 'biodata.id_kabupaten', '=', 'kabupaten.id')
            ->leftJoin('kecamatan', 'biodata.id_kecamatan', '=', 'kecamatan.id')
            ->select(
                'peserta_didik.id',
                'biodata.nama',
                DB::raw("CONCAT('Kab. ', kabupaten.nama_kabupaten) as alamat"),
                DB::raw("CONCAT('pendidikan terakhir: ', COALESCE(lembaga.nama_lembaga, 'Tidak Diketahui'), ' (', 
            IFNULL(YEAR(riwayat_pelajar.tanggal_keluar_pelajar), 'Belum Lulus'), ')') as nama_lembaga"),
                DB::raw("COALESCE(MAX(berkas.file_path), 'default.jpg') as foto_profil")
            )
            ->groupBy(
                'peserta_didik.id',
                'biodata.nama',
                'kabupaten.nama_kabupaten',
                'lembaga.nama_lembaga',
                'riwayat_pelajar.tanggal_keluar_pelajar'
            )
            ->where(function ($query) {
                $query->where('riwayat_pelajar.status_pelajar', 'alumni')
                    ->orWhere('riwayat_santri.status_santri', 'alumni');
            });


        // Filter Umum (Alamat dan Jenis Kelamin)
        $query = $this->filterController->applyCommonFilters($query, $request);

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

        // Filter Status 
        if ($request->filled('status')) {
            $status = strtolower($request->status);
            if ($status == 'alumni santri') {
                $query->whereNotNull('riwayat_santri.id');
            } else if ($status == 'alumni santri non-pelajar') {
                $query->whereNotNull('riwayat_santri.id')->whereNull('riwayat_pelajar.id');
            } else if ($status == 'alumni santri, tetapi masih pelajar aktif') {
                $query->whereNotNull('riwayat_santri.id')->whereNotNull('pelajar.id');
            } else if ($status == 'alumni pelajar') {
                $query->whereNotNull('riwayat_pelajar.id');
            } else if ($status == 'alumni pelajar non-santri') {
                $query->whereNotNull('riwayat_pelajar.id')->whereNull('riwayat_santri.id');
            } else if ($status == 'alumni pelajar, tetapi masih santri aktif') {
                $query->whereNotNull('riwayat_pelajar.id')->whereNotNull('santri.id');
            } else if ($status == 'alumni santri sekaligus pelajar') {
                $query->whereNotNull('riwayat_santri.id')->whereNotNull('riwayat_pelajar.id');
            }
        }


        // Filter Angkatan Pelajar
        if ($request->filled('angkatan_pelajar')) {
            $query->where('riwayat_pelajar.angkatan', $request->angkatan_pelajar);
        }

        // Filter Angkatan Santri
        if ($request->filled('angkatan_santri')) {
            $query->where('riwayat_santri.angkatan', $request->angkatan_santri);
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
                    "kabupaten" => $item->alamat,
                    "lembaga" => $item->nama_lembaga,
                    "foto_profil" => url($item->foto_profil)
                ];
            })
        ]);
    }
}
