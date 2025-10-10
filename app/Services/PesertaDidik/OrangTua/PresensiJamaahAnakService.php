<?php

namespace App\Services\PesertaDidik\OrangTua;

use Carbon\Carbon;
use App\Models\JadwalSholat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PresensiJamaahAnakService
{
    public function getPresensiJamaahAnak($request)
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

        $now       = Carbon::now('Asia/Jakarta');
        $santriId  = $request['santri_id'] ?? null;
        $tanggal   = $request['tanggal'] ?? null;
        $sholatId  = $request['sholat_id'] ?? null;
        $jadwalId  = $request['jadwal_id'] ?? null;
        $metode    = $request['metode'] ?? null;
        $status    = strtolower($request['status'] ?? 'all');
        $showAll   = filter_var($request['all'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $gender    = $request['jenis_kelamin'] ?? null;

        if (!$santriId) {
            return [
                'success' => false,
                'message' => 'Parameter santri_id diperlukan.'
            ];
        }

        $jadwal       = $jadwalId ? JadwalSholat::with('sholat')->find($jadwalId) : null;

        if ($tanggal) {
            $jadwalNext = JadwalSholat::with('sholat')
                ->where('berlaku_mulai', '<=', $tanggal)
                ->where(function ($q) use ($tanggal) {
                    $q->whereNull('berlaku_sampai')->orWhere('berlaku_sampai', '>=', $tanggal);
                })
                ->whereTime('jam_mulai', '>', $now->format('H:i:s'))
                ->orderBy('jam_mulai')
                ->first();
        }

        if ($jadwal) {
            $statusPresensi = $now->between(
                Carbon::parse($jadwal->jam_mulai),
                Carbon::parse($jadwal->jam_selesai)
            ) ? 'waktunya_presensi' : 'belum_waktunya';
        }

        $baseQuery = DB::table('presensi_sholat')
            ->join('santri', 'presensi_sholat.santri_id', '=', 'santri.id')
            ->join('biodata as b', 'santri.biodata_id', '=', 'b.id')
            ->join('sholat', 'sholat.id', '=', 'presensi_sholat.sholat_id')
            ->where('santri.id', $santriId)
            ->where('santri.status', 'aktif');

        if ($tanggal && !$showAll) $baseQuery->whereDate('presensi_sholat.tanggal', $tanggal);
        if ($sholatId)             $baseQuery->where('presensi_sholat.sholat_id', $sholatId);
        if ($metode)               $baseQuery->where('presensi_sholat.metode', $metode);
        if ($gender)               $baseQuery->where('b.jenis_kelamin', $gender);
        if ($status !== 'all' && !in_array($status, ['tidak_hadir', 'tidak-hadir'])) {
            $baseQuery->where('presensi_sholat.status', ucfirst($status));
        }

        $total_hadir       = (clone $baseQuery)->where('presensi_sholat.status', 'Hadir')->count();
        $total_presensi    = (clone $baseQuery)->count();
        $total_santri      = DB::table('santri')
            ->join('biodata as b', 'santri.biodata_id', '=', 'b.id')
            ->where('santri.id', $santriId)
            ->where('santri.status', 'aktif')
            ->when($gender, fn($q) => $q->where('b.jenis_kelamin', $gender))
            ->count();
        $total_tidak_hadir = max($total_santri - $total_hadir, 0);


        if (in_array($status, ['tidak_hadir', 'tidak-hadir'])) {
            if (!$tanggal) {
                return [
                    'success' => false,
                    'message' => "Filter 'tidak_hadir' membutuhkan parameter 'tanggal'."
                ];
            }

            $list = DB::table('santri')
                ->join('biodata as b', 'santri.biodata_id', '=', 'b.id')
                ->leftJoin('presensi_sholat', function ($join) use ($tanggal, $sholatId, $metode, $santriId) {
                    $join->on('santri.id', '=', 'presensi_sholat.santri_id')
                        ->whereDate('presensi_sholat.tanggal', $tanggal);
                    if ($sholatId) $join->where('presensi_sholat.sholat_id', $sholatId);
                    if ($metode)   $join->where('presensi_sholat.metode', $metode);
                })
                ->leftJoin('sholat', 'sholat.id', '=', 'presensi_sholat.sholat_id')
                ->where('santri.id', $santriId)
                ->where('santri.status', 'aktif')
                ->when($gender, fn($q) => $q->where('b.jenis_kelamin', $gender))
                ->where(function ($q) {
                    $q->whereNull('presensi_sholat.id')->orWhere('presensi_sholat.status', '!=', 'Hadir');
                })
                ->select(
                    'santri.id as santri_id',
                    'b.nama as nama_santri',
                    'b.jenis_kelamin',
                    'santri.nis',
                    'sholat.id as sholat_id',
                    'sholat.nama_sholat',
                    'presensi_sholat.tanggal',
                    'presensi_sholat.status',
                    'presensi_sholat.metode',
                    'presensi_sholat.waktu_presensi',
                )
                ->orderByDesc('presensi_sholat.created_at')
                ->get();
        } else {
            $list = $baseQuery
                ->select(
                    'presensi_sholat.id as presensi_id',
                    'santri.id as santri_id',
                    'b.nama as nama_santri',
                    'b.jenis_kelamin',
                    'santri.nis',
                    'presensi_sholat.sholat_id',
                    'sholat.nama_sholat',
                    'presensi_sholat.tanggal',
                    'presensi_sholat.waktu_presensi',
                    'presensi_sholat.status',
                    'presensi_sholat.metode'
                )
                ->orderByDesc('presensi_sholat.created_at')
                ->get();
        }

        return [
            'success' => true,
            'filter' => [
                'santri_id'     => $santriId,
                'tanggal'       => $tanggal,
                'sholat_id'     => $sholatId,
                'jadwal_id'     => $jadwalId,
                'metode'        => $metode,
                'status'        => $status,
                'jenis_kelamin' => $gender,
                'all'           => $showAll,
            ],
            'totals' => [
                'total_hadir'             => $total_hadir,
                'total_tidak_hadir'       => $total_tidak_hadir,
                'total_presensi_tercatat' => $total_presensi,
                // 'total_santri'            => $total_santri,
            ],
            'data' => $list,
        ];
    }

    public function getPresensiToday($request)
    {
        $user = Auth::user();
        $noKk = $user->no_kk;

        // 🔹 Ambil semua anak dari KK yang sama, exclude ortu
        $anak = DB::table('keluarga as k')
            ->join('biodata as b', 'k.id_biodata', '=', 'b.id')
            ->join('santri as s', 'b.id', '=', 's.biodata_id')
            ->select('s.id as santri_id', 's.nis', 'b.nama as nama_santri')
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

        $dataAnak = $anak->firstWhere('santri_id', $request['santri_id'] ?? null);

        if (!$dataAnak) {
            return [
                'success' => false,
                'message' => 'Santri tidak valid untuk user ini.',
                'data'    => null,
                'status'  => 403,
            ];
        }

        $now      = Carbon::now('Asia/Jakarta');
        $tanggal  = $request['tanggal']   ?? $now->toDateString();
        $santriId = $request['santri_id'] ?? $dataAnak->santri_id;
        $sholatId = $request['sholat_id'] ?? null;

        $jadwalSholat = DB::table('sholat as s')
            ->join('jadwal_sholat as js', 'js.sholat_id', '=', 's.id')
            ->where('s.aktif', true)
            ->whereDate('js.berlaku_mulai', '<=', $tanggal)
            ->where(function ($q) use ($tanggal) {
                $q->whereNull('js.berlaku_sampai')
                    ->orWhereDate('js.berlaku_sampai', '>=', $tanggal);
            })
            ->select(
                's.id as sholat_id',
                's.nama_sholat',
                'js.jam_mulai',
                'js.jam_selesai'
            )
            ->when($sholatId, fn($q) => $q->where('s.id', $sholatId))
            ->orderBy('s.urutan')
            ->get();

        $presensi = DB::table('presensi_sholat')
            ->where('santri_id', $santriId)
            ->whereDate('tanggal', $tanggal)
            ->get()
            ->keyBy('sholat_id');

        $result = $jadwalSholat->map(function ($row) use ($presensi, $santriId, $dataAnak, $tanggal, $now) {
            $dataPresensi = $presensi->get($row->sholat_id);

            if ($dataPresensi) {
                $status = $dataPresensi->status;
                $waktu  = $dataPresensi->waktu_presensi;
                $metode = $dataPresensi->metode;
            } else {
                $mulai   = Carbon::createFromFormat('H:i:s', $row->jam_mulai, 'Asia/Jakarta')->setDateFrom($now);
                $selesai = Carbon::createFromFormat('H:i:s', $row->jam_selesai, 'Asia/Jakarta')->setDateFrom($now);

                if ($now->lt($mulai)) {
                    $status = 'belum waktunya presensi';
                } elseif ($now->between($mulai, $selesai)) {
                    $status = 'sedang waktu presensi';
                } else {
                    $status = 'tidak hadir';
                }

                $waktu  = null;
                $metode = null;
            }

            return [
                'santri_id'   => $santriId,
                'nis'         => $dataAnak->nis,
                'nama_santri' => $dataAnak->nama_santri,
                'sholat_id'   => $row->sholat_id,
                'nama_sholat' => $row->nama_sholat,
                'tanggal'     => $tanggal,
                'status'      => $status,
                'waktu_presensi' => $waktu,
                'metode'         => $metode,
            ];
        });

        return [
            'success' => true,
            'filter'  => [
                'santri_id' => $santriId,
                'tanggal'   => $tanggal,
                'sholat_id' => $sholatId,
            ],
            'data' => $result,
        ];
    }
}
