<?php

namespace App\Http\Controllers\api\formulir;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class PesertaDidikFormulir extends Controller
{
    // Tampilan Formulir Biodata Peserta Didik By Id
    public function getBiodata($id)
    {
        // Validasi UUID
        if (!Str::isUuid($id)) {
            return response()->json(['error' => 'Invalid UUID'], 400);
        }

        try {
            // Query untuk mengambil biodata peserta didik beserta relasi terkait
            $biodata = DB::table('peserta_didik')
                ->join('biodata as b_anak', 'peserta_didik.id_biodata', '=', 'b_anak.id')
                ->join('keluarga as k_anak', 'b_anak.id', '=', 'k_anak.id_biodata')
                ->leftJoin('keluarga as k_ortu', 'k_anak.no_kk', '=', 'k_ortu.no_kk')
                ->join('orang_tua_wali as otw', function ($join) {
                    $join->on('k_ortu.id_biodata', '=', 'otw.id_biodata')
                        ->where('otw.wali', true);
                })
                ->join('biodata as b_ortu', 'otw.id_biodata', '=', 'b_ortu.id')
                ->join('hubungan_keluarga as hk', 'otw.id_hubungan_keluarga', '=', 'hk.id')
                ->leftJoin('negara as negara_anak', 'b_anak.id_negara', '=', 'negara_anak.id')
                ->leftJoin('negara as negara_ortu', 'b_ortu.id_negara', '=', 'negara_ortu.id')
                ->leftJoin('provinsi', 'b_ortu.id_provinsi', '=', 'provinsi.id')
                ->leftJoin('kabupaten', 'b_ortu.id_kabupaten', '=', 'kabupaten.id')
                ->leftJoin('kecamatan', 'b_ortu.id_kecamatan', '=', 'kecamatan.id')
                ->where('peserta_didik.id', $id)
                ->select(
                    // Data Anak
                    DB::raw("CASE 
                        WHEN LOWER(negara_anak.nama_negara) = 'indonesia' THEN 'WNI' 
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
                ->first();
        } catch (\Exception $e) {
            Log::error("Error in getBiodata: " . $e->getMessage());
            return response()->json([
                "status"  => "error",
                "message" => "Terjadi kesalahan pada server"
            ], 500);
        }

        // Jika data tidak ditemukan, kembalikan respons error 404
        if (!$biodata) {
            return response()->json([
                "status"  => "succes",
                "message" => "Data Kosong",
                "data"    => []
            ], 200);
        }

        // Format dan kembalikan data dalam format JSON
        return response()->json([
            "status"  => "success",
            "message" => "Data ditemukan",
            "data"    => [
                'data_peserta_didik' => [
                    "kewarganegaraan"             => $biodata->kewarganegaraan,
                    "no_kk"                       => $biodata->no_kk,
                    "no_passport"                 => $biodata->no_passport,
                    "nik"                         => $biodata->nik,
                    "nama_peserta_didik"          => $biodata->nama_anak,
                    "jenis_kelamin"               => $biodata->jenis_kelamin,
                    "tempat_lahir"                => $biodata->tempat_lahir,
                    "tanggal_lahir"               => $biodata->tanggal_lahir,
                    "umur"                        => $biodata->umur,
                    "anak_keberapa"               => $biodata->anak_keberapa,
                    "dari_saudara"                => $biodata->dari_saudara,
                    "tinggal_bersama"             => $biodata->tinggal_bersama,
                    "jenjang_pendidikan_terakhir" => $biodata->jenjang_pendidikan_terakhir,
                    "nama_pendidikan_terakhir"    => $biodata->nama_pendidikan_terakhir,
                ],
                'data_ortu' => [
                    "no_telp_ortu"   => $biodata->no_telepon,
                    "no_telp_ortu_2" => $biodata->no_telepon_2,
                    "email_ortu"     => $biodata->email,
                    "pekerjaan"      => $biodata->pekerjaan,
                    "penghasilan"    => $biodata->penghasilan,
                    "negara"         => $biodata->negara_ortu,
                    "provinsi"       => $biodata->provinsi_ortu,
                    "kabupaten"      => $biodata->kabupaten_ortu,
                    "kecamatan"      => $biodata->kecamatan_ortu,
                    "jalan"          => $biodata->jalan,
                    "kode_pos"       => $biodata->kode_pos,
                    "wafat"          => $biodata->wafat,
                ]
            ]
        ]);
    }

    // Tampilan Formulir Keluarga Peserta Didik By Id
    public function getKeluarga($id)
    {
        // Validasi UUID
        if (!Str::isUuid($id)) {
            return response()->json(['error' => 'Invalid UUID'], 400);
        }

        try {
            // Ambil data peserta didik untuk mendapatkan no_kk dan id_biodata
            $peserta = DB::table('peserta_didik')
                ->join('biodata as b_anak', 'peserta_didik.id_biodata', '=', 'b_anak.id')
                ->join('keluarga as k_anak', 'b_anak.id', '=', 'k_anak.id_biodata')
                ->select(
                    'peserta_didik.id as peserta_id',
                    'b_anak.id as biodata_id',
                    'k_anak.no_kk'
                )
                ->where('peserta_didik.id', $id)
                ->first();

            // Jika data tidak ditemukan, kembalikan respons error 404
            if (!$peserta) {
                return response()->json([
                    "status"  => "succes",
                    "message" => "Data Kosong",
                    "data"    => []
                ], 200);
            }

            // Ambil data orang tua/wali
            $ortu = DB::table('keluarga')
                ->where('no_kk', $peserta->no_kk)
                ->join('orang_tua_wali', 'keluarga.id_biodata', '=', 'orang_tua_wali.id_biodata')
                ->join('biodata as b_ortu', 'orang_tua_wali.id_biodata', '=', 'b_ortu.id')
                ->join('hubungan_keluarga as hk', 'orang_tua_wali.id_hubungan_keluarga', '=', 'hk.id')
                ->select(
                    'b_ortu.nama',
                    'b_ortu.nik',
                    DB::raw("CONCAT('Orang Tua (', hk.nama_status, ')') as keterangan"),
                    'orang_tua_wali.wali'
                )
                ->get();

            // Ambil data saudara kandung (peserta didik lain dengan no_kk yang sama, 
            // namun tidak terdaftar sebagai orang tua/wali)
            $siblings = DB::table('keluarga')
                ->where('no_kk', $peserta->no_kk)
                ->whereNotIn('id_biodata', function ($query) {
                    $query->select('id_biodata')->from('orang_tua_wali');
                })
                ->join('biodata', 'keluarga.id_biodata', '=', 'biodata.id')
                ->select('biodata.id', 'biodata.nama', 'biodata.nik')
                ->get();

            // Gabungkan data orang tua dan saudara kandung
            if ($siblings->isEmpty()) {
                $data = $ortu->map(function ($item) {
                    return [
                        'nama'       => $item->nama,
                        'nik'        => $item->nik,
                        'keterangan' => $item->keterangan,
                    ];
                });
            } else {
                $siblingsData = $siblings->map(function ($item) {
                    return [
                        'nama'       => $item->nama,
                        'nik'        => $item->nik,
                        'keterangan' => 'Saudara Kandung',
                    ];
                });
                $ortuData = $ortu->map(function ($item) {
                    return [
                        'nama'       => $item->nama,
                        'nik'        => $item->nik,
                        'keterangan' => $item->keterangan,
                    ];
                });
                $data = $ortuData->merge($siblingsData);
            }
        } catch (\Exception $e) {
            Log::error('Error getKeluarga: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan pada server'
            ], 500);
        }

        // Jika data kosong
        if ($data->isEmpty()) {
            return response()->json([
                'status'  => 'succes',
                'message' => 'Data Kosong',
                'data'    => []
            ], 200);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Data ditemukan',
            'data'    => $data,
        ]);
    }

    // Tampilan Formulir Status Santri Peserta Didik By Id
    public function getSantri($id)
    {
        // Validasi UUID
        if (!Str::isUuid($id)) {
            return response()->json(['error' => 'Invalid UUID'], 400);
        }

        try {
            // Query untuk mengambil data santri
            $santriData = DB::table('santri')
                ->where('santri.id_peserta_didik', $id)
                ->where('santri.status', 'aktif')
                ->select(
                    'santri.nis',
                    DB::raw("CONCAT(
                        'Sejak ', DATE_FORMAT(santri.tanggal_masuk, '%d %b %Y'), 
                        ' Sampai ', IFNULL(DATE_FORMAT(santri.tanggal_keluar, '%d %b %Y'), 'Sekarang')
                    ) as periode"),
                    'santri.tanggal_masuk'
                )
                ->get();
        } catch (\Exception $e) {
            Log::error("Error in getSantri: " . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan pada server'
            ], 500);
        }

        // Jika data tidak ditemukan
        if ($santriData->isEmpty()) {
            return response()->json([
                "status"  => "succes",
                "message" => "Data Kosong",
                "data"    => []
            ], 200);
        }

        // Mapping hasil query ke format respons JSON
        return response()->json([
            "status"  => "success",
            "message" => "Data ditemukan",
            "data"    => $santriData->map(function ($item) {
                return [
                    "nis"     => $item->nis,
                    "periode" => $item->periode,
                ];
            })
        ]);
    }

    // Tampilan Formulir Domisili Peserta Didik By Id
    public function getDomisiliSantri($id)
    {
        // Validasi UUID
        if (!Str::isUuid($id)) {
            return response()->json(['error' => 'Invalid UUID'], 400);
        }

        try {
            $domisiliData = DB::table('santri')
                ->join('peserta_didik', 'santri.id_peserta_didik', '=', 'peserta_didik.id')
                ->join('domisili_santri', 'domisili_santri.id_santri', '=', 'santri.id')
                ->join('wilayah', 'domisili_santri.id_wilayah', '=', 'wilayah.id')
                ->join('blok', 'domisili_santri.id_blok', '=', 'blok.id')
                ->join('kamar', 'domisili_santri.id_kamar', '=', 'kamar.id')
                ->where('peserta_didik.id', $id)
                ->select(
                    'wilayah.nama_wilayah',
                    'kamar.nama_kamar',
                    DB::raw("CONCAT('Sejak ', DATE_FORMAT(domisili_santri.tanggal_masuk, '%d %b %Y %H:%i:%s'), ' Sampai ', COALESCE(DATE_FORMAT(domisili_santri.tanggal_keluar, '%d %b %Y %H:%i:%s'), 'Sekarang')) as periode")
                )
                ->get();
        } catch (\Exception $e) {
            Log::error("Error in getDomisiliSantri: " . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan pada server'
            ], 500);
        }

        // Jika data tidak ditemukan
        if ($domisiliData->isEmpty()) {
            return response()->json([
                "status"  => "succes",
                "message" => "Data Kosong",
                "data"    => []
            ], 200);
        }

        // Format output data
        $formattedData = $domisiliData->map(function ($item) {
            return [
                'wilayah' => $item->nama_wilayah,
                'kamar'   => $item->nama_kamar,
                'periode' => $item->periode,
            ];
        });

        return response()->json([
            'status'  => 'success',
            'message' => 'Data ditemukan',
            'data'    => $formattedData
        ]);
    }

    // Tampilan Formulir Pendidikan Peserta Didik By Id
    public function getPendidikan($id)
    {
        // Validasi UUID
        if (!Str::isUuid($id)) {
            return response()->json(['error' => 'Invalid UUID'], 400);
        }

        try {
            // Query untuk mengambil data pendidikan pelajar beserta relasi terkait
            $pendidikanData = DB::table('pelajar')
                ->join('peserta_didik', 'pelajar.id_peserta_didik', '=', 'peserta_didik.id')
                ->join('pendidikan_pelajar', 'pendidikan_pelajar.id_pelajar', '=', 'pelajar.id')
                ->join('lembaga', 'pendidikan_pelajar.id_lembaga', '=', 'lembaga.id')
                ->leftJoin('jurusan', 'pendidikan_pelajar.id_jurusan', '=', 'jurusan.id')
                ->leftJoin('kelas', 'pendidikan_pelajar.id_kelas', '=', 'kelas.id')
                ->leftJoin('rombel', 'pendidikan_pelajar.id_rombel', '=', 'rombel.id')
                ->where('peserta_didik.id', $id)
                ->where('pelajar.status', 'aktif')
                ->select(
                    'lembaga.nama_lembaga',
                    'jurusan.nama_jurusan',
                    DB::raw("CONCAT(
                        'Sejak ', DATE_FORMAT(pendidikan_pelajar.tanggal_masuk, '%d %b %Y %H:%i:%s'), 
                        ' Sampai ', IFNULL(DATE_FORMAT(pendidikan_pelajar.tanggal_keluar, '%d %b %Y %H:%i:%s'), 'Sekarang')
                    ) as periode")
                )
                ->get();
        } catch (\Exception $e) {
            Log::error("Error in getPendidikan: " . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan pada server'
            ], 500);
        }

        // Jika data tidak ditemukan
        if ($pendidikanData->isEmpty()) {
            return response()->json([
                "status"  => "succes",
                "message" => "Data Kosong",
                "data"    => []
            ], 200);
        }

        // Mapping hasil query ke format respons JSON
        $data = $pendidikanData->map(function ($item) {
            return [
                'lembaga'  => $item->nama_lembaga,
                'jurusan'  => $item->nama_jurusan,
                'periode'  => $item->periode,
            ];
        });

        return response()->json([
            'status'  => 'success',
            'message' => 'Data ditemukan',
            'data'    => $data,
        ]);
    }

    // Tampilan Formulir Berkas Peserta Didik By Id
    public function getBerkas($id)
    {
        // Validasi UUID
        if (!Str::isUuid($id)) {
            return response()->json(['error' => 'Invalid UUID'], 400);
        }

        try {
            // Query untuk mengambil data berkas peserta didik
            $berkasData = DB::table('peserta_didik')
                ->join('biodata', 'peserta_didik.id_biodata', '=', 'biodata.id')
                ->join('berkas', 'berkas.id_biodata', '=', 'biodata.id')
                ->join('jenis_berkas', 'berkas.id_jenis_berkas', '=', 'jenis_berkas.id')
                ->where('peserta_didik.id', $id)
                ->select(
                    'jenis_berkas.nama_jenis_berkas',
                    'berkas.file_path'
                )
                ->get();
        } catch (\Exception $e) {
            Log::error("Error in getBerkas: " . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan pada server'
            ], 500);
        }

        // Jika data tidak ditemukan
        if ($berkasData->isEmpty()) {
            return response()->json([
                "status"  => "succes",
                "message" => "Data Kosong",
                "data"    => []
            ], 200);
        }

        // Mapping hasil query ke format respons JSON
        $data = $berkasData->map(function ($item) {
            return [
                'nama_berkas' => $item->nama_jenis_berkas,
                'file_path'   => $item->file_path,
            ];
        });

        return response()->json([
            'status'  => 'success',
            'message' => 'Data ditemukan',
            'data'    => $data,
        ]);
    }

    // Tampilan Formulir Status Warga Pesantren Peserta Didik By Id
    public function getWargaPesantren($id)
    {
        // Validasi UUID
        if (!Str::isUuid($id)) {
            return response()->json(['error' => 'Invalid UUID'], 400);
        }

        try {
            $wargaPesantrenData = DB::table('peserta_didik as pd')
                ->join('biodata as b', 'pd.id_biodata', '=', 'b.id')
                ->leftJoin('warga_pesantren as wp', function ($join) {
                    $join->on('b.id', '=', 'wp.id_biodata')
                        ->whereRaw('wp.id = (
                        select max(wp2.id)
                        from warga_pesantren as wp2
                        where wp2.id_biodata = b.id
                     )');
                })
                ->where('pd.id', $id)
                ->select(
                    'wp.niup',
                    'wp.status'
                )
                ->get();
        } catch (\Exception $e) {
            Log::error("Error in getWargaPesantren: " . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan pada server'
            ], 500);
        }

        // Jika data tidak ditemukan
        if ($wargaPesantrenData->isEmpty()) {
            return response()->json([
                "status"  => "succes",
                "message" => "Data Kosong",
                "data"    => []
            ], 200);
        }

        // Mapping hasil query ke format respons JSON
        $data = $wargaPesantrenData->map(function ($item) {
            return [
                'niup'   => $item->niup,
                'status' => $item->status,
            ];
        });

        return response()->json([
            'status'  => 'success',
            'message' => 'Data ditemukan',
            'data'    => $data,
        ]);
    }
}
