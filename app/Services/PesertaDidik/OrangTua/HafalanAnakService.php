<?php

namespace App\Services\PesertaDidik\OrangTua;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class HafalanAnakService
{
    public function getTahfidzAnak($request)
    {
        $user = Auth::user();
        $noKk = $user->no_kk;

        // 🔹 Ambil semua anak dari KK yang sama, exclude ortu
        $anak = DB::table('keluarga as k')
            ->join('biodata as b', 'k.id_biodata', '=', 'b.id')
            ->join('santri as s', 'b.id', '=', 's.biodata_id')
            ->select('s.id as santri_id')
            ->where('k.no_kk', $noKk)
            ->get();

        if ($anak->isEmpty()) {
            return [
                'success' => false,
                'message' => 'Tidak ada data anak yang ditemukan.',
                'data' => null,
                'status' => 404,
            ];
        }

        // 🔹 Cek apakah santri_id request valid
        $dataAnak = $anak->firstWhere('santri_id', $request['santri_id'] ?? null);

        if (!$dataAnak) {
            return [
                'success' => false,
                'message' => 'Santri tidak valid untuk user ini.',
                'data'    => null,
                'status'  => 403,
            ];
        }

        $tahfidz = DB::table('tahfidz as t')
            ->leftJoin('santri', 't.santri_id', '=', 'santri.id')
            ->leftJoin('biodata', 'santri.biodata_id', '=', 'biodata.id')
            ->leftJoin('tahun_ajaran', 't.tahun_ajaran_id', '=', 'tahun_ajaran.id')
            ->leftjoin('users as u', 't.created_by', '=', 'u.id')
            ->select(
                'biodata.nama as santri_nama',
                't.tanggal',
                't.jenis_setoran',
                DB::raw("
                CONCAT(
                    t.surat,
                    ' ',
                    t.ayat_mulai,
                    CASE
                        WHEN t.ayat_selesai IS NOT NULL AND t.ayat_selesai != t.ayat_mulai THEN CONCAT('-', t.ayat_selesai)
                        ELSE ''
                    END
                ) AS surat
            "),
                't.nilai',
                't.catatan',
                't.status',
                'u.name as pencatat'
            )
            ->where('t.santri_id', $request['santri_id'])
            ->whereNull('biodata.deleted_at')
            ->whereNull('santri.deleted_at')
            ->orderBy('t.id', 'desc')
            ->get();

        // Query rekap tahfidz
        $rekap = DB::table('rekap_tahfidz as rt')
            ->join('santri', 'rt.santri_id', '=', 'santri.id')
            ->leftJoin('domisili_santri as ds', 'santri.id', '=', 'ds.santri_id')
            ->join('biodata', 'santri.biodata_id', '=', 'biodata.id')
            ->leftJoin('pendidikan as pd', 'santri.id', '=', 'pd.biodata_id')
            ->join('tahun_ajaran', 'rt.tahun_ajaran_id', '=', 'tahun_ajaran.id')
            ->select(
                'santri.nis',
                'biodata.nama as santri_nama',
                'tahun_ajaran.tahun_ajaran as tahun_ajaran',
                'rt.total_surat',
                'rt.persentase_khatam',
                'rt.surat_tersisa',
                'rt.sisa_persentase',
                'rt.jumlah_setoran',
                'rt.rata_rata_nilai',
                'rt.tanggal_mulai',
                'rt.tanggal_selesai',
            )
            ->where('rt.santri_id', $request['santri_id'])
            ->orderBy('rt.id', 'desc')
            ->first();

        return [
            'tahfidz' => $tahfidz,
            'rekap_tahfidz' => $rekap
        ];
    }

    public function getNadhomanAnak($request)
    {
        $user = Auth::user();
        $noKk = $user->no_kk;

        // 🔹 Ambil semua anak dari KK yang sama, exclude ortu
        $anak = DB::table('keluarga as k')
            ->join('biodata as b', 'k.id_biodata', '=', 'b.id')
            ->join('santri as s', 'b.id', '=', 's.biodata_id')
            ->select('s.id as santri_id')
            ->where('k.no_kk', $noKk)
            ->get();

        if ($anak->isEmpty()) {
            return [
                'success' => false,
                'message' => 'Tidak ada data anak yang ditemukan.',
                'data' => null,
                'status' => 404,
            ];
        }

        // 🔹 Cek apakah santri_id request valid
        $dataAnak = $anak->firstWhere('santri_id', $request['santri_id'] ?? null);

        if (!$dataAnak) {
            return [
                'success' => false,
                'message' => 'Santri tidak valid untuk user ini.',
                'data'    => null,
                'status'  => 403,
            ];
        }

        $nadhoman = DB::table('nadhoman as n')
            ->leftJoin('santri', 'n.santri_id', '=', 'santri.id')
            ->leftJoin('biodata', 'santri.biodata_id', '=', 'biodata.id')
            ->leftJoin('kitab', 'n.kitab_id', '=', 'kitab.id')
            ->leftJoin('tahun_ajaran', 'n.tahun_ajaran_id', '=', 'tahun_ajaran.id')
            ->leftJoin('users as u', 'n.created_by', '=', 'u.id')
            ->select(
                'biodata.nama as santri_nama',
                'kitab.nama_kitab',
                'n.tanggal',
                'n.jenis_setoran',
                DB::raw("
                    CONCAT(
                        n.bait_mulai,
                        CASE
                            WHEN n.bait_selesai IS NOT NULL AND n.bait_selesai != n.bait_mulai 
                            THEN CONCAT('-', n.bait_selesai)
                            ELSE ''
                        END
                    ) AS bait
                "),
                'n.nilai',
                'n.catatan',
                'n.status',
                'u.name as pencatat'
            )
            ->where('n.santri_id', $request['santri_id'])
            ->whereNull('biodata.deleted_at')
            ->whereNull('santri.deleted_at')
            ->orderBy('n.id', 'desc')
            ->get();

        // Query rekap nadhoman
        $rekap = DB::table('rekap_nadhoman as rn')
            ->join('santri', 'rn.santri_id', '=', 'santri.id')
            ->leftJoin('domisili_santri as ds', 'santri.id', '=', 'ds.santri_id')
            ->join('biodata', 'santri.biodata_id', '=', 'biodata.id')
            ->leftJoin('pendidikan as pd', 'santri.id', '=', 'pd.biodata_id')
            ->join('kitab', 'rn.kitab_id', '=', 'kitab.id')
            ->join('tahun_ajaran', 'rn.tahun_ajaran_id', '=', 'tahun_ajaran.id')
            ->select(
                'santri.nis',
                'biodata.nama as santri_nama',
                'kitab.nama_kitab',
                'rn.total_bait',
                'rn.persentase_selesai'
            )
            ->where('rn.santri_id', $request['santri_id'])
            ->orderBy('rn.id', 'desc')
            ->get();

        return [
            'nadhoman'        => $nadhoman,
            'rekap_nadhoman'  => $rekap
        ];
    }

    // public function get($request)
    // {
    //     try {
    //         $user = Auth::user();
    //         $bioId = $user->biodata_id;

    //         // 🔹 Ambil nomor KK orang tua
    //         $noKk = DB::table('keluarga as k')
    //             ->where('k.id_biodata', $bioId)
    //             ->value('no_kk');

    //         if (!$noKk) {
    //             return [
    //                 'success' => false,
    //                 'message' => 'Data keluarga tidak ditemukan.',
    //                 'data' => null,
    //                 'status' => 404,
    //             ];
    //         }

    //         // 🔹 Ambil semua anak dari KK yang sama, exclude ortu
    //         $anak = DB::table('keluarga as k')
    //             ->join('biodata as b', 'k.id_biodata', '=', 'b.id')
    //             ->join('santri as s', 'b.id', '=', 's.biodata_id')
    //             ->leftJoin('orang_tua_wali as otw', 'b.id', '=', 'otw.id_biodata')
    //             ->select('s.id as santri_id')
    //             ->whereNull('otw.id_biodata')
    //             ->where('k.no_kk', $noKk)
    //             ->where('k.id_biodata', '!=', $bioId)
    //             ->get();

    //         if ($anak->isEmpty()) {
    //             return [
    //                 'success' => false,
    //                 'message' => 'Tidak ada data anak yang ditemukan.',
    //                 'data' => null,
    //                 'status' => 404,
    //             ];
    //         }

    //         // 🔹 Cek apakah santri_id request valid
    //         $dataAnak = $anak->firstWhere('santri_id', $request->santri_id ?? null);

    //         if (!$dataAnak) {
    //             return [
    //                 'success' => false,
    //                 'message' => 'Santri tidak valid untuk user ini.',
    //                 'data'    => null,
    //                 'status'  => 403,
    //             ];
    //         }

    //         $query = DB::table('rekap_tahfidz as rt')
    //             ->join('santri as s', 'rt.santri_id', '=', 's.id')
    //             ->leftJoin('domisili_santri as ds', 's.id', '=', 'ds.santri_id')
    //             ->join('biodata as b', 's.biodata_id', '=', 'b.id')
    //             ->leftJoin('pendidikan as pd', 's.id', '=', 'pd.biodata_id')
    //             ->join('tahun_ajaran as ta', 'rt.tahun_ajaran_id', '=', 'ta.id')
    //             ->select(
    //                 's.id',
    //                 's.nis',
    //                 'b.nama as s_nama',
    //                 'rt.total_surat',
    //                 'rt.persentase_khatam',
    //                 'rt.surat_tersisa',
    //                 'rt.sisa_persentase',
    //                 'rt.jumlah_setoran',
    //                 'rt.rata_rata_nilai',
    //                 'rt.tanggal_mulai',
    //                 'rt.tanggal_selesai',
    //                 'ta.tahun_ajaran as tahun_ajaran'
    //             )
    //             ->groupBy(
    //                 's.id',
    //                 's.nis',
    //                 'b.nama',
    //                 'rt.total_surat',
    //                 'rt.persentase_khatam',
    //                 'rt.surat_tersisa',
    //                 'rt.sisa_persentase',
    //                 'rt.jumlah_setoran',
    //                 'rt.rata_rata_nilai',
    //                 'rt.tanggal_mulai',
    //                 'rt.tanggal_selesai',
    //                 'ta.tahun_ajaran',
    //                 'rt.id'
    //             )
    //             ->where('s.id', $dataAnak->santri_id)
    //             ->orderBy('rt.id', 'desc');

    //         if (! $request->filled('tahun_ajaran')) {
    //             return $query;
    //         }

    //         if ($request->filled('tahun_ajaran')) {
    //             $query->where('rt.tahun_ajaran', $request->tahun_ajaran);
    //         }

    //         return $query;
    //     } catch (Exception $e) {
    //         Log::error('ViewOrangTuaService@getAllRekap error: ' . $e->getMessage(), [
    //             'exception' => $e,
    //             'user_id'   => Auth::id(),
    //             'santri_id' => $request->santri_id ?? null,
    //         ]);

    //         return [
    //             'success' => false,
    //             'message' => 'Terjadi kesalahan saat mengambil rekap.',
    //             'status'  => 500,
    //             'data'    => []
    //         ];
    //     }
    // }
}
