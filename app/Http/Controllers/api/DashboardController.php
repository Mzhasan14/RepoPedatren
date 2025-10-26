<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function total()
    {
        $data = [];
        $pesertaDidik = DB::table('biodata as b')
            ->leftJoin('santri AS s', fn($j) => $j->on('b.id', '=', 's.biodata_id')->where('s.status', 'aktif'))
            ->leftJoin('pendidikan AS pd', fn($j) => $j->on('b.id', '=', 'pd.biodata_id')->where('pd.status', 'aktif'))
            ->where(fn($q) => $q->where('s.status', 'aktif')
                ->orWhere('pd.status', '=', 'aktif'))
            ->where('b.status', true)
            ->where(fn($q) => $q->whereNull('b.deleted_at')
                ->whereNull('s.deleted_at'));

        if ($pesertaDidik) {
            $data['peserta_didik'] = $pesertaDidik->count();
        }

        $santri = DB::table('santri AS s')
            ->where('s.status', 'aktif')
            ->whereNull('s.deleted_at');

        if ($santri) {
            $data['santri'] = $santri->count();
        }

        $pelajar = DB::table('biodata as b')
            ->join('pendidikan AS pd', fn($j) => $j->on('b.id', '=', 'pd.biodata_id')->where('pd.status', 'aktif'))
            ->where('b.status', true)
            ->where(fn($q) => $q->whereNull('b.deleted_at')->whereNull('pd.deleted_at'));

        if ($pelajar) {
            $data['pelajar'] = $pelajar->count();
        }

        $waliAsuh =  DB::table('wali_asuh AS ws')
            ->where('ws.status', true)
            ->whereNull('ws.deleted_at');

        if ($waliAsuh) {
            $data['wali_asuh'] = $waliAsuh->count();
        }

        $pegawai = DB::table('pegawai AS p')
            ->where('p.status_aktif', 'aktif')
            ->whereNull('p.deleted_at');

        if ($pegawai) {
            $data['pegawai'] = $pegawai->count();
        }

        $pengajar = DB::table('pengajar')
            ->where('pengajar.status_aktif', 'aktif')
            ->whereNull('pengajar.tahun_akhir')
            ->whereNull('pengajar.deleted_at');

        if ($pengajar) {
            $data['pengajar'] = $pengajar->count();
        }

        $pengurus = DB::table('pengurus')
            ->where('pengurus.status_aktif', 'aktif')
            ->whereNull('pengurus.tanggal_akhir')
            ->whereNull('pengurus.deleted_at');

        if ($pengurus) {
            $data['pengurus'] = $pengurus->count();
        }

        $waliKelas = DB::table('wali_kelas')
            ->whereNull('wali_kelas.periode_akhir')
            ->where('wali_kelas.status_aktif', 'aktif')
            ->whereNull('wali_kelas.deleted_at');

        if ($waliKelas) {
            $data['wali_kelas'] = $waliKelas->count();
        }

        $karyawan = DB::table('karyawan')
            ->whereNull('karyawan.tanggal_selesai')
            ->where('karyawan.status_aktif', 'aktif')
            ->whereNull('karyawan.deleted_at');

        if ($karyawan) {
            $data['karyawan'] = $karyawan->count();
        }

        $khadam =  DB::table('khadam as kh')
            ->where('kh.status', true)
            ->whereNull('kh.deleted_at');

        if ($khadam) {
            $data['khadam'] = $khadam->count();
        }

        $rpLast = DB::table('riwayat_pendidikan')
            ->select('biodata_id', DB::raw('MAX(tanggal_keluar) AS max_tanggal_keluar'))
            ->where('status', 'lulus')
            ->groupBy('biodata_id');

        // 2) Sub‐query: santri alumni terakhir
        $santriLast = DB::table('santri')
            ->select('id', DB::raw('MAX(id) AS last_id'))
            ->where('status', 'alumni')
            ->groupBy('id');

        $alumni =  DB::table('biodata as b')
            ->leftJoin('santri AS s', fn($j) => $j->on('b.id', '=', 's.biodata_id')->where('s.status', 'alumni'))
            ->leftJoinSub($rpLast, 'lr', fn($j) => $j->on('lr.biodata_id', '=', 'b.id'))
            ->leftjoin('riwayat_pendidikan as rp', fn($j) => $j->on('rp.biodata_id', '=', 'lr.biodata_id')->on('rp.tanggal_keluar', '=', 'lr.max_tanggal_keluar'))
            ->leftJoinSub($santriLast, 'ld', fn($j) => $j->on('ld.id', '=', 's.id'))
            ->where(fn($q) => $q->where('s.status', 'alumni')
                ->orWhere('rp.status', 'lulus'))
            ->where('b.status', true)
            ->where(fn($q) => $q->whereNull('b.deleted_at')
                ->whereNull('s.deleted_at')
                ->whereNull('rp.deleted_at'));

        if ($alumni) {
            $data['alumni'] = $alumni->count();
        }

        $orangTua = DB::table('orang_tua_wali AS o')
            ->where('o.status', true);

        if ($orangTua) {
            $data['orang_tua'] = $orangTua->count();
        }

        $wali = DB::table('orang_tua_wali AS o')
            ->where('o.status', true)
            ->where('o.wali', true);

        if ($wali) {
            $data['wali'] = $wali->count();
        }

        $izin = DB::table('perizinan as pr')
            ->where('pr.status', 'sudah berada diluar pondok')
            ->whereNull('pr.deleted_at');

        if ($izin) {
            $data['dalam_masa_izin'] = $izin->count();
        }

        $telat = DB::table('perizinan as pr')
            ->whereNull('pr.tanggal_kembali')
                ->where('pr.tanggal_akhir', '<', now())
            ->whereNull('pr.deleted_at');

        if ($telat) {
            $data['telat_belum_kembali'] = $telat->count();
        }

        return response()->json($data);
    }
}
