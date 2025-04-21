<?php

namespace App\Http\Controllers\api\formulir;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class PesertaDidikFormulir extends Controller
{
    // Tampilan Formulir Biodata Santri By Id
    public function getBiodata($id)
    {
        if (!Str::isUuid($id)) {
            return response()->json(['error' => 'Invalid UUID'], 400);
        }

        try {
            $biodata = DB::table('santri as s')
                ->join('biodata as b_anak', 's.biodata_id', '=', 'b_anak.id')
                ->join('keluarga as k_anak', 'b_anak.id', '=', 'k_anak.id_biodata')
                ->leftJoin('keluarga as k_ortu', 'k_anak.no_kk', '=', 'k_ortu.no_kk')
                ->join('orang_tua_wali as otw', function ($join) {
                    $join->on('k_ortu.id_biodata', '=', 'otw.id_biodata')
                        ->where('otw.wali', true);
                })
                ->join('biodata as b_ortu', 'otw.id_biodata', '=', 'b_ortu.id')
                ->join('hubungan_keluarga as hk', 'otw.id_hubungan_keluarga', '=', 'hk.id')
                ->leftJoin('negara as negara_anak', 'b_anak.negara_id', '=', 'negara_anak.id')
                ->leftJoin('negara as negara_ortu', 'b_ortu.negara_id', '=', 'negara_ortu.id')
                ->leftJoin('provinsi', 'b_ortu.provinsi_id', '=', 'provinsi.id')
                ->leftJoin('kabupaten', 'b_ortu.kabupaten_id', '=', 'kabupaten.id')
                ->leftJoin('kecamatan', 'b_ortu.kecamatan_id', '=', 'kecamatan.id')
                ->where('s.id', $id)
                ->select(
                    DB::raw("CASE WHEN LOWER(negara_anak.nama_negara) = 'indonesia' THEN 'WNI' ELSE 'WNA' END as kewarganegaraan"),
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

        if (!$biodata) {
            return response()->json([
                "status"  => "success",
                "message" => "Data Kosong",
                "data"    => []
            ], 200);
        }

        return response()->json([
            "status"  => "success",
            "message" => "Data ditemukan",
            "data"    => [
                'data_santri' => [
                    "kewarganegaraan"             => $biodata->kewarganegaraan,
                    "no_kk"                       => $biodata->no_kk,
                    "no_passport"                 => $biodata->no_passport,
                    "nik"                         => $biodata->nik,
                    "nama_santri"                 => $biodata->nama_anak,
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

    // Tampilan Formulir Keluarga Santri By Id
    public function getKeluarga($id)
    {
        if (!Str::isUuid($id)) {
            return response()->json(['error' => 'Invalid UUID'], 400);
        }

        try {
            $santri = DB::table('santri as s')
                ->join('biodata as b_anak', 's.biodata_id', '=', 'b_anak.id')
                ->join('keluarga as k_anak', 'b_anak.id', '=', 'k_anak.id_biodata')
                ->select('s.id as santri_id', 'b_anak.id as biodata_id', 'k_anak.no_kk')
                ->where('s.id', $id)
                ->first();

            if (!$santri) {
                return response()->json([
                    "status"  => "success",
                    "message" => "Data Kosong",
                    "data"    => []
                ], 200);
            }

            $ortu = DB::table('keluarga')
                ->where('no_kk', $santri->no_kk)
                ->join('orang_tua_wali', 'keluarga.id_biodata', '=', 'orang_tua_wali.id_biodata')
                ->join('biodata as b_ortu', 'orang_tua_wali.id_biodata', '=', 'b_ortu.id')
                ->join('hubungan_keluarga as hk', 'orang_tua_wali.id_hubungan_keluarga', '=', 'hk.id')
                ->select('b_ortu.nama', 'b_ortu.nik', DB::raw("CONCAT('Orang Tua (', hk.nama_status, ')') as keterangan"), 'orang_tua_wali.wali')
                ->get();

            $siblings = DB::table('keluarga')
                ->where('no_kk', $santri->no_kk)
                ->whereNotIn('id_biodata', function ($query) {
                    $query->select('id_biodata')->from('orang_tua_wali');
                })
                ->where('id_biodata', '!=', $santri->biodata_id)
                ->join('biodata as b', 'keluarga.id_biodata', '=', 'b.id')
                ->select('b.id', 'b.nama', 'b.nik')
                ->get();

            if ($siblings->isEmpty()) {
                $data = $ortu->map(fn($item) => [
                    'nama'       => $item->nama,
                    'nik'        => $item->nik,
                    'keterangan' => $item->keterangan,
                ]);
            } else {
                $siblingsData = $siblings->map(fn($item) => [
                    'nama'       => $item->nama,
                    'nik'        => $item->nik,
                    'keterangan' => 'Saudara Kandung',
                ]);
                $ortuData = $ortu->map(fn($item) => [
                    'nama'       => $item->nama,
                    'nik'        => $item->nik,
                    'keterangan' => $item->keterangan,
                ]);
                $data = $ortuData->merge($siblingsData);
            }
        } catch (\Exception $e) {
            Log::error('Error getKeluarga: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan pada server'
            ], 500);
        }

        if ($data->isEmpty()) {
            return response()->json([
                'status'  => 'success',
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

    // Tampilan Formulir Status Santri By Id
    public function getSantri($id)
    {
        if (!Str::isUuid($id)) {
            return response()->json(['error' => 'Invalid UUID'], 400);
        }

        try {
            $santriData = DB::table('santri')
                ->where('id', $id)
                ->where('status', 'aktif')
                ->select(
                    'nis',
                    DB::raw("CONCAT('Sejak ', DATE_FORMAT(tanggal_masuk, '%d %b %Y'), ' Sampai ', IFNULL(DATE_FORMAT(tanggal_keluar, '%d %b %Y'), 'Sekarang')) as periode"),
                    'tanggal_masuk'
                )
                ->get();
        } catch (\Exception $e) {
            Log::error("Error in getSantri: " . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan pada server'
            ], 500);
        }

        if ($santriData->isEmpty()) {
            return response()->json([
                "status"  => "success",
                "message" => "Data Kosong",
                "data"    => []
            ], 200);
        }

        return response()->json([
            "status"  => "success",
            "message" => "Data ditemukan",
            "data"    => $santriData->map(fn($item) => [
                'nis'     => $item->nis,
                'periode' => $item->periode,
            ])
        ]);
    }

    // Tampilan Formulir Domisili Santri By Id
    public function getDomisiliSantri($id)
    {
        if (!Str::isUuid($id)) {
            return response()->json(['error' => 'Invalid UUID'], 400);
        }

        try {
            $domisiliData = DB::table('santri as s')
                ->join('riwayat_domisili as rd', 'rd.santri_id', '=', 's.id')
                ->join('wilayah as w', 'rd.wilayah_id', '=', 'w.id')
                ->join('blok as bl', 'rd.blok_id', '=', 'bl.id')
                ->join('kamar as km', 'rd.kamar_id', '=', 'km.id')
                ->where('s.id', $id)
                ->select(
                    'w.nama_wilayah',
                    'bl.nama_blok',
                    'km.nama_kamar',
                    DB::raw("CONCAT('Sejak ', DATE_FORMAT(rd.tanggal_masuk, '%d %b %Y %H:%i:%s'), ' Sampai ', COALESCE(DATE_FORMAT(rd.tanggal_keluar, '%d %b %Y %H:%i:%s'), 'Sekarang')) as periode")
                )
                ->get();
        } catch (\Exception $e) {
            Log::error("Error in getDomisiliSantri: " . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan pada server'
            ], 500);
        }

        if ($domisiliData->isEmpty()) {
            return response()->json([
                "status"  => "success",
                "message" => "Data Kosong",
                "data"    => []
            ], 200);
        }

        $formatted = $domisiliData->map(fn($item) => [
            'wilayah' => $item->nama_wilayah,
            'blok'    => $item->nama_blok,
            'kamar'   => $item->nama_kamar,
            'periode' => $item->periode,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Data ditemukan',
            'data'    => $formatted
        ]);
    }

    // Tampilan Formulir Pendidikan Santri By Id
    public function getPendidikan($id)
    {
        if (!Str::isUuid($id)) {
            return response()->json(['error' => 'Invalid UUID'], 400);
        }

        try {
            $pendidikanData = DB::table('santri as s')
                ->join('riwayat_pendidikan as rp', 'rp.santri_id', '=', 's.id')
                ->join('lembaga as l', 'rp.lembaga_id', '=', 'l.id')
                ->leftJoin('jurusan as j', 'rp.jurusan_id', '=', 'j.id')
                ->leftJoin('kelas as k', 'rp.kelas_id', '=', 'k.id')
                ->leftJoin('rombel as r', 'rp.rombel_id', '=', 'r.id')
                ->where('s.id', $id)
                ->select(
                    'l.nama_lembaga',
                    'j.nama_jurusan',
                    DB::raw("CONCAT('Sejak ', DATE_FORMAT(rp.tanggal_masuk, '%d %b %Y %H:%i:%s'), ' Sampai ', IFNULL(DATE_FORMAT(rp.tanggal_keluar, '%d %b %Y %H:%i:%s'), 'Sekarang')) as periode")
                )
                ->get();
        } catch (\Exception $e) {
            Log::error("Error in getPendidikan: " . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan pada server'
            ], 500);
        }

        if ($pendidikanData->isEmpty()) {
            return response()->json([
                "status"  => "success",
                "message" => "Data Kosong",
                "data"    => []
            ], 200);
        }

        $data = $pendidikanData->map(fn($item) => [
            'lembaga' => $item->nama_lembaga,
            'jurusan' => $item->nama_jurusan,
            'periode' => $item->periode,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Data ditemukan',
            'data'    => $data,
        ]);
    }

    // Tampilan Formulir Berkas Santri By Id
    public function getBerkas($id)
    {
        if (!Str::isUuid($id)) {
            return response()->json(['error' => 'Invalid UUID'], 400);
        }

        try {
            $berkasData = DB::table('santri as s')
                ->join('biodata as b', 's.biodata_id', '=', 'b.id')
                ->join('berkas as br', 'br.biodata_id', '=', 'b.id')
                ->join('jenis_berkas as jb', 'br.jenis_berkas_id', '=', 'jb.id')
                ->where('s.id', $id)
                ->select('jb.nama_jenis_berkas', 'br.file_path')
                ->get();
        } catch (\Exception $e) {
            Log::error("Error in getBerkas: " . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan pada server'
            ], 500);
        }

        if ($berkasData->isEmpty()) {
            return response()->json([
                "status"  => "success",
                "message" => "Data Kosong",
                "data"    => []
            ], 200);
        }

        $data = $berkasData->map(fn($item) => [
            'nama_berkas' => $item->nama_jenis_berkas,
            'file_path'   => $item->file_path,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Data ditemukan',
            'data'    => $data,
        ]);
    }

    // Tampilan Formulir Status Warga Pesantren Santri By Id
    public function getWargaPesantren($id)
    {
        if (!Str::isUuid($id)) {
            return response()->json(['error' => 'Invalid UUID'], 400);
        }

        try {
            $wargaPesantrenData = DB::table('santri as s')
                ->join('biodata as b', 's.biodata_id', '=', 'b.id')
                ->leftJoin('warga_pesantren as wp', function ($join) {
                    $join->on('b.id', '=', 'wp.biodata_id')
                        ->whereRaw('(SELECT MAX(wp2.id) FROM warga_pesantren wp2 WHERE wp2.biodata_id = b.id) = wp.id');
                })
                ->where('s.id', $id)
                ->select('wp.niup', 'wp.status')
                ->get();
        } catch (\Exception $e) {
            Log::error("Error in getWargaPesantren: " . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan pada server'
            ], 500);
        }

        if ($wargaPesantrenData->isEmpty()) {
            return response()->json([
                "status"  => "success",
                "message" => "Data Kosong",
                "data"    => []
            ], 200);
        }

        $data = $wargaPesantrenData->map(fn($item) => [
            'niup'   => $item->niup,
            'status' => $item->status,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Data ditemukan',
            'data'    => $data,
        ]);
    }
}
