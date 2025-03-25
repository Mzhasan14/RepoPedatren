<?php

namespace App\Http\Controllers\api\formulir;

use App\Models\Santri;
use App\Models\Biodata;
use App\Models\Pelajar;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Peserta_didik;
use App\Models\RiwayatSantri;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class PesertaDidikFormulir extends Controller
{
    public function getBiodata($id)
    {
        // Validasi UUID
        if (!Str::isUuid($id)) {
            return response()->json(['error' => 'Invalid UUID'], 400);
        }

        $query = Peserta_didik::join('biodata as b_anak', 'peserta_didik.id_biodata', '=', 'b_anak.id')
            ->join('keluarga as k_anak', 'b_anak.id', '=', 'k_anak.id_biodata') // Cari No KK anak
            ->leftjoin('keluarga as k_ortu', 'k_anak.no_kk', '=', 'k_ortu.no_kk') // Cari anggota keluarga lain dengan No KK yang sama
            ->join('orang_tua_wali as otw', function ($join) {
                $join->on('k_ortu.id_biodata', '=', 'otw.id_biodata')
                    ->where('otw.wali', true); // Hanya ambil yang berstatus wali
            })
            ->join('biodata as b_ortu', 'otw.id_biodata', '=', 'b_ortu.id') // Hubungkan orang tua ke biodata mereka
            ->join('hubungan_keluarga as hk', 'otw.id_hubungan_keluarga', '=', 'hk.id') // Status hubungan keluarga
            ->leftJoin('negara as negara_anak', 'b_anak.id_negara', '=', 'negara_anak.id')
            ->leftJoin('negara as negara_ortu', 'b_ortu.id_negara', '=', 'negara_ortu.id')
            ->leftJoin('provinsi', 'b_ortu.id_provinsi', '=', 'provinsi.id')
            ->leftJoin('kabupaten', 'b_ortu.id_kabupaten', '=', 'kabupaten.id')
            ->leftJoin('kecamatan', 'b_ortu.id_kecamatan', '=', 'kecamatan.id')
            ->select(
                // Data Anak
                DB::raw("CASE 
                WHEN negara_anak.nama_negara = 'indonesia' THEN 'WNI' 
                ELSE 'WNA' 
            END as kewarganegaraan"),
                'k_anak.no_kk',
                'b_anak.no_passport',
                'b_anak.nik',
                'b_anak.nama as nama_anak',
                'b_anak.jenis_kelamin',
                'b_anak.tempat_lahir',
                'b_anak.tanggal_lahir',
                DB::raw("CONCAT('umur ', TIMESTAMPDIFF(YEAR, b_anak.tanggal_lahir, CURDATE()), ' tahun') AS umur"),
                'b_anak.anak_keberapa',
                'b_anak.dari_saudara',
                'b_anak.tinggal_bersama',
                'b_anak.jenjang_pendidikan_terakhir',
                'b_anak.nama_pendidikan_terakhir',

                // Data Orang Tua
                'b_ortu.no_telepon',
                'b_ortu.no_telepon_2',
                'b_ortu.email',
                'otw.pekerjaan',
                'otw.penghasilan',
                'negara_ortu.nama_negara as negara_ortu',
                'provinsi.nama_provinsi as provinsi_ortu',
                'kabupaten.nama_kabupaten as kabupaten_ortu',
                'kecamatan.nama_kecamatan as kecamatan_ortu',
                'b_ortu.jalan',
                'b_ortu.kode_pos',
                'otw.wafat'
            )
            ->where('peserta_didik.id', $id)
            ->first(); // Hanya ambil satu data

        // **Perbaikan Pengecekan Jika Data Tidak Ditemukan**
        if (!$query) {
            return response()->json([
                "status" => "error",
                "message" => "Data tidak ditemukan",
                "data" => []
            ], 200);
        }

        // **Kembalikan Data dalam Format JSON**
        return response()->json([
            "status" => "success",
            "message" => "Data ditemukan",
            "data" => [
                "kewarganegaraan" => $query->kewarganegaraan,
                "no_kk" => $query->no_kk,
                "no_passport" => $query->no_passport,
                "nik" => $query->nik,
                "nama_peserta_didik" => $query->nama_anak,
                "jenis_kelamin" => $query->jenis_kelamin,
                "tempat_lahir" => $query->tempat_lahir,
                "tanggal_lahir" => $query->tanggal_lahir,
                "umur" => $query->umur,
                "anak_keberapa" => $query->anak_keberapa,
                "dari_saudara" => $query->dari_saudara,
                "tinggal_bersama" => $query->tinggal_bersama,
                "jenjang_pendidikan_terakhir" => $query->jenjang_pendidikan_terakhir,
                "nama_pendidikan_terakhir" => $query->nama_pendidikan_terakhir,
                "no_telp_ortu" => $query->no_telepon,
                "no_telp_ortu_2" => $query->no_telepon_2,
                "email_ortu" => $query->email,
                "pekerjaan" => $query->pekerjaan,
                "penghasilan" => $query->penghasilan,
                "negara" => $query->negara_ortu,
                "provinsi" => $query->provinsi_ortu,
                "kabupaten" => $query->kabupaten_ortu,
                "kecamatan" => $query->kecamatan_ortu,
                "jalan" => $query->jalan,
                "kode_pos" => $query->kode_pos,
                "wafat" => $query->wafat,
            ]
        ]);
    }

    public function getKeluarga($id)
    {
        // Validasi UUID
        if (!Str::isUuid($id)) {
            return response()->json(['error' => 'Invalid UUID'], 400);
        }

        $query = Peserta_didik::join('biodata as b_anak', 'peserta_didik.id_biodata', '=', 'b_anak.id')
            ->join('keluarga as k_anak', 'b_anak.id', '=', 'k_anak.id_biodata') // Cari No KK anak
            ->leftjoin('keluarga as k_ortu', 'k_anak.no_kk', '=', 'k_ortu.no_kk') // Cari anggota keluarga lain dengan No KK yang sama
            ->join('orang_tua_wali as otw', 'k_ortu.id_biodata', '=', 'otw.id_biodata')
            ->join('biodata as b_ortu', 'otw.id_biodata', '=', 'b_ortu.id') // Hubungkan orang tua ke biodata mereka
            ->join('hubungan_keluarga as hk', 'otw.id_hubungan_keluarga', '=', 'hk.id') // Status hubungan keluarga
            ->select(
                'b_ortu.nama',
                'b_ortu.nik',
                'hk.nama_status',
                'otw.wali'
            )
            ->where('peserta_didik.id', $id)
            ->get();

        if ($query->isEmpty()) {
            return response()->json([
                "status" => "error",
                "message" => "Data tidak ditemukan",
                "data" => []
            ], 200);
        }

        return response()->json([
            "status" => "success",
            "message" => "Data ditemukan",
            "data" => $query->map(function ($item) {
                return [
                    "nama" => $item->nama,
                    "nik" => $item->nik,
                    "status" => $item->nama_status,
                    "wali" => $item->wali,

                ];
            })
        ]);
    }

    public function getSantri($id)
    {
        // Validasi UUID
        if (!Str::isUuid($id)) {
            return response()->json(['error' => 'Invalid UUID'], 400);
        }

        $query = Santri::Active()
            ->where('santri.id_peserta_didik', $id)
            ->select(
                'santri.nis',
                DB::raw("CONCAT(
                    'Sejak ', DATE_FORMAT(santri.tanggal_masuk_santri, '%d %b %Y'), 
                    ' Sampai ', IFNULL(DATE_FORMAT(santri.tanggal_keluar_santri, '%d %b %Y'), 'Sekarang')
                ) as periode"),
                'santri.tanggal_masuk_santri'
            )->get();

        if ($query->isEmpty()) {
            return response()->json([
                "status" => "error",
                "message" => "Data tidak ditemukan",
                "data" => []
            ], 200);
        }

        return response()->json([
            "status" => "success",
            "message" => "Data ditemukan",
            "data" => $query->map(function ($item) {
                return [
                    "nis" => $item->nis,
                    "periode" => $item->periode,
                ];
            })
        ]);
    }

    public function getDomisiliSantri($id)
    {
        // Validasi UUID
        if (!Str::isUuid($id)) {
            return response()->json(['error' => 'Invalid UUID'], 400);
        }

        $query = Santri::join('peserta_didik', 'santri.id_peserta_didik', '=', 'peserta_didik.id')
            ->join('domisili_santri', 'domisili_santri.id_peserta_didik', '=', 'peserta_didik.id')
            ->join('wilayah', 'domisili_santri.id_wilayah', '=', 'wilayah.id')
            ->join('blok', 'domisili_santri.id_blok', '=', 'blok.id')
            ->join('kamar', 'domisili_santri.id_kamar', '=', 'kamar.id')
            ->select(
                'wilayah.nama_wilayah',
                'kamar.nama_kamar',
                DB::raw("CONCAT(
                    'Sejak ', DATE_FORMAT(domisili_santri.tanggal_masuk, '%d %b %Y %H:%i:%s'), 
                    ' Sampai ', IFNULL(DATE_FORMAT(domisili_santri.tanggal_keluar, '%d %b %Y %H:%i:%s'), 'Sekarang')
                ) as periode"),
            )
            ->where('peserta_didik.id', $id)
            ->get();


        if ($query->isEmpty()) {
            return response()->json([
                "status" => "error",
                "message" => "Data tidak ditemukan",
                "data" => []
            ], 200);
        }

        return response()->json([
            "status" => "success",
            "message" => "Data ditemukan",
            "data" => $query->map(function ($item) {
                return [
                    "wilayah" => $item->nama_wilayah,
                    "kamar" => $item->nama_kamar,
                    "periode" => $item->periode,
                ];
            })
        ]);
    }

    public function getPendidikan($id)
    {
        // Validasi UUID
        if (!Str::isUuid($id)) {
            return response()->json(['error' => 'Invalid UUID'], 400);
        }

        $query = Pelajar::join('peserta_didik', 'pelajar.id_peserta_didik', '=', 'peserta_didik.id')
            ->join('pendidikan_pelajar', 'pendidikan_pelajar.id_peserta_didik', '=', 'peserta_didik.id')
            ->join('lembaga', 'pendidikan_pelajar.id_lembaga', '=', 'lembaga.id')
            ->leftjoin('jurusan', 'pendidikan_pelajar.id_jurusan', '=', 'jurusan.id')
            ->leftjoin('kelas', 'pendidikan_pelajar.id_kelas', '=', 'kelas.id')
            ->leftjoin('rombel', 'pendidikan_pelajar.id_rombel', '=', 'rombel.id')
            ->select(
                'lembaga.nama_lembaga',
                'jurusan.nama_jurusan',
                DB::raw("CONCAT(
                    'Sejak ', DATE_FORMAT(pendidikan_pelajar.tanggal_masuk, '%d %b %Y %H:%i:%s'), 
                    ' Sampai ', IFNULL(DATE_FORMAT(pendidikan_pelajar.tanggal_keluar, '%d %b %Y %H:%i:%s'), 'Sekarang')
                ) as periode"),
            )
            ->where('peserta_didik.id', $id)
            ->get();


        if ($query->isEmpty()) {
            return response()->json([
                "status" => "error",
                "message" => "Data tidak ditemukan",
                "data" => []
            ], 200);
        }

        return response()->json([
            "status" => "success",
            "message" => "Data ditemukan",
            "data" => $query->map(function ($item) {
                return [
                    "lembaga" => $item->nama_lembaga,
                    "jurusan" => $item->nama_jurusan,
                    "periode" => $item->periode,
                ];
            })
        ]);
    }

    public function getBerkas($id)
    {
        // Validasi UUID
        if (!Str::isUuid($id)) {
            return response()->json(['error' => 'Invalid UUID'], 400);
        }

        $query = Peserta_didik::join('biodata', 'peserta_didik.id_biodata', '=', 'biodata.id')
            ->join('berkas', 'berkas.id_biodata', '=', 'biodata.id')
            ->join('jenis_berkas', 'berkas.id_jenis_berkas', '=', 'jenis_berkas.id')
            ->select(
                'jenis_berkas.nama_jenis_berkas',
                'berkas.file_path'
            )
            ->where('peserta_didik.id', $id)
            ->get();


        if ($query->isEmpty()) {
            return response()->json([
                "status" => "error",
                "message" => "Data tidak ditemukan",
                "data" => []
            ], 200);
        }

        return response()->json([
            "status" => "success",
            "message" => "Data ditemukan",
            "data" => $query->map(function ($item) {
                return [
                    "nama_berkas" => $item->nama_jenis_berkas,
                    "file_path" => $item->file_path,
                ];
            })
        ]);
    }

    public function getWargaPesantren($id)
    {
        // Validasi UUID
        if (!Str::isUuid($id)) {
            return response()->json(['error' => 'Invalid UUID'], 400);
        }

        $query = Peserta_didik::Active()
            ->join('biodata', 'peserta_didik.id_biodata', '=', 'biodata.id')
            ->select(
                'biodata.niup',
                'biodata.status'
            )
            ->where('peserta_didik.id', $id)
            ->get();

        if ($query->isEmpty()) {
            return response()->json([
                "status" => "error",
                "message" => "Data tidak ditemukan",
                "data" => []
            ], 200);
        }

        return response()->json([
            "status" => "success",
            "message" => "Data ditemukan",
            "data" => $query->map(function ($item) {
                return [
                    "niup" => $item->niup,
                    "status" => $item->status,
                ];
            })
        ]);
    }
}
