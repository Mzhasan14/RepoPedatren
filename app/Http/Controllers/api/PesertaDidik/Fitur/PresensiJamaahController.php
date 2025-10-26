<?php

namespace App\Http\Controllers\api\PesertaDidik\Fitur;

use Carbon\Carbon;
use App\Models\JadwalSholat;
use Illuminate\Http\Request;
use App\Models\PresensiSholat;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\PesertaDidik\PresensiJamaahRequest;
use App\Services\PesertaDidik\Fitur\PresensiJamaahService;
use App\Http\Requests\PesertaDidik\ManualPresensiJamaahRequest;

class PresensiJamaahController extends Controller
{
    protected $service;

    public function __construct(PresensiJamaahService $service)
    {
        $this->service = $service;
    }

    /**
     * Endpoint scan kartu
     * POST /api/presensi/scan
     */
    public function scan(PresensiJamaahRequest $request)
    {
        $uid = $request->input('uid_kartu');
        $userId = $request->input('user_id'); // optional operator id

        try {
            $result = $this->service->scanByUid($uid, $userId);

            if (isset($result['status']) && $result['status'] !== 'Sukses') {
                return response()->json([
                    'success' => false,
                    'status'  => $result['status'],
                    'message' => $result['message'] ?? null,
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => 'Presensi berhasil.',
                'data'    => $result['data'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function index(Request $request)
    {
        $now          = Carbon::now('Asia/Jakarta');
        $tanggal      = $request->query('tanggal', $now->toDateString());
        $sholatId     = $request->query('sholat_id');
        $jadwalId     = $request->query('jadwal_id');
        $metode       = $request->query('metode');
        $status       = $request->query('status', 'all');
        $showAll      = filter_var($request->query('all', false), FILTER_VALIDATE_BOOLEAN);
        $jenisKelamin = $request->query('jenis_kelamin'); // L / P

        /**
         * 🔍 Auto detect jadwal sekarang / terakhir
         */
        if (!$sholatId && !$jadwalId) {
            $jadwalSekarang = JadwalSholat::with('sholat')
                ->where('berlaku_mulai', '<=', $tanggal)
                ->where(function ($q) use ($tanggal) {
                    $q->whereNull('berlaku_sampai')
                        ->orWhere('berlaku_sampai', '>=', $tanggal);
                })
                ->whereTime('jam_mulai', '<=', $now->format('H:i:s'))
                ->whereTime('jam_selesai', '>=', $now->format('H:i:s'))
                ->first();

            if ($jadwalSekarang) {
                $sholatId = $jadwalSekarang->sholat_id;
                $jadwalId = $jadwalSekarang->id;
            } else {
                $jadwalTerakhir = JadwalSholat::with('sholat')
                    ->where('berlaku_mulai', '<=', $tanggal)
                    ->where(function ($q) use ($tanggal) {
                        $q->whereNull('berlaku_sampai')
                            ->orWhere('berlaku_sampai', '>=', $tanggal);
                    })
                    ->whereTime('jam_mulai', '<=', $now->format('H:i:s'))
                    ->orderByDesc('jam_mulai')
                    ->first();

                if ($jadwalTerakhir) {
                    $sholatId = $jadwalTerakhir->sholat_id;
                    $jadwalId = $jadwalTerakhir->id;
                } else {
                    $jadwalPertama = JadwalSholat::with('sholat')
                        ->where('berlaku_mulai', '<=', $tanggal)
                        ->where(function ($q) use ($tanggal) {
                            $q->whereNull('berlaku_sampai')
                                ->orWhere('berlaku_sampai', '>=', $tanggal);
                        })
                        ->orderBy('jam_mulai')
                        ->first();

                    if ($jadwalPertama) {
                        $sholatId = $jadwalPertama->sholat_id;
                        $jadwalId = $jadwalPertama->id;
                    }
                }
            }
        }

        // 📌 Ambil jadwal sekarang/terakhir
        $jadwal = $jadwalId ? JadwalSholat::with('sholat')->find($jadwalId) : null;

        // 📌 Ambil jadwal mendatang (setelah sekarang)
        $jadwalMendatang = JadwalSholat::with('sholat')
            ->where('berlaku_mulai', '<=', $tanggal)
            ->where(function ($q) use ($tanggal) {
                $q->whereNull('berlaku_sampai')
                    ->orWhere('berlaku_sampai', '>=', $tanggal);
            })
            ->whereTime('jam_mulai', '>', $now->format('H:i:s'))
            ->orderBy('jam_mulai')
            ->first();

        // 📌 Format response jadwal sekarang/terakhir
        $jadwalResponse = $jadwal ? [
            'jadwal_id'   => $jadwal->id,
            'sholat_id'   => $jadwal->sholat_id,
            'nama_sholat' => $jadwal->sholat->nama_sholat ?? null,
            'tanggal'     => $tanggal,
            'jam_mulai'   => $jadwal->jam_mulai,
            'jam_selesai' => $jadwal->jam_selesai,
        ] : null;

        // 📌 Format response jadwal mendatang
        $jadwalMendatangResponse = $jadwalMendatang ? [
            'jadwal_id'   => $jadwalMendatang->id,
            'sholat_id'   => $jadwalMendatang->sholat_id,
            'nama_sholat' => $jadwalMendatang->sholat->nama_sholat ?? null,
            'tanggal'     => $tanggal,
            'jam_mulai'   => $jadwalMendatang->jam_mulai,
            'jam_selesai' => $jadwalMendatang->jam_selesai,
        ] : null;

        // 📌 Status presensi (hanya 2 kondisi)
        $statusPresensi = null;
        if ($jadwal) {
            if ($now->between(Carbon::parse($jadwal->jam_mulai), Carbon::parse($jadwal->jam_selesai))) {
                $statusPresensi = 'waktunya_presensi';
            } else {
                $statusPresensi = 'belum_waktunya';
            }
        }

        // 📊 Query totals
        $totalBase = DB::table('presensi_sholat')
            ->join('santri', 'presensi_sholat.santri_id', '=', 'santri.id')
            ->join('biodata as b', 'santri.biodata_id', '=', 'b.id')
            ->where('santri.status', 'aktif');

        if (!$showAll) {
            $totalBase->whereDate('presensi_sholat.tanggal', $tanggal);
        }

        if ($sholatId) $totalBase->where('presensi_sholat.sholat_id', $sholatId);
        if ($metode)   $totalBase->where('presensi_sholat.metode', $metode);
        if ($jenisKelamin) $totalBase->where('b.jenis_kelamin', $jenisKelamin);
        if ($status && strtolower($status) !== 'all' && !in_array(strtolower($status), ['tidak_hadir', 'tidak-hadir'])) {
            $totalBase->where('presensi_sholat.status', $status);
        }

        $total_hadir    = (clone $totalBase)->where('presensi_sholat.status', 'Hadir')->count();
        $total_presensi = (clone $totalBase)->count();

        $totalSantriQuery = DB::table('santri')
            ->join('biodata as b', 'santri.biodata_id', '=', 'b.id')
            ->where('santri.status', 'aktif');
        if ($jenisKelamin) $totalSantriQuery->where('b.jenis_kelamin', $jenisKelamin);
        $total_santri = $totalSantriQuery->count();

        $total_tidak_hadir = max($total_santri - $total_hadir, 0);

        // 📋 List data sesuai filter
        $isTidakHadirFilter = in_array(strtolower($status), ['tidak_hadir', 'tidak-hadir']);

        if ($isTidakHadirFilter) {
            $listQuery = DB::table('santri')
                ->join('biodata as b', 'santri.biodata_id', '=', 'b.id')
                ->leftJoin('presensi_sholat', function ($join) use ($tanggal, $sholatId, $metode) {
                    $join->on('santri.id', '=', 'presensi_sholat.santri_id')
                        ->whereDate('presensi_sholat.tanggal', $tanggal);
                    if ($sholatId) $join->where('presensi_sholat.sholat_id', $sholatId);
                    if ($metode)   $join->where('presensi_sholat.metode', $metode);
                })
                ->leftJoin('sholat', 'sholat.id', '=', 'presensi_sholat.sholat_id')
                ->where('santri.status', 'aktif')
                ->select(
                    'santri.id as santri_id',
                    'b.nama as nama_santri',
                    'b.jenis_kelamin',
                    'santri.nis',
                    'sholat.id as sholat_id',
                    'sholat.nama_sholat',
                    'presensi_sholat.id as presensi_id',
                    'presensi_sholat.tanggal',
                    'presensi_sholat.waktu_presensi',
                    'presensi_sholat.status',
                    'presensi_sholat.metode',
                    'presensi_sholat.created_at as presensi_created_at'
                );

            if ($jenisKelamin) $listQuery->where('b.jenis_kelamin', $jenisKelamin);

            $listQuery->where(function ($q) {
                $q->whereNull('presensi_sholat.id')
                    ->orWhere('presensi_sholat.status', '!=', 'Hadir');
            });

            $list = $listQuery
                ->orderByDesc('presensi_sholat.created_at')
                ->orderBy('b.nama')
                ->get();
        } else {
            $listQuery = DB::table('presensi_sholat')
                ->join('santri', 'presensi_sholat.santri_id', '=', 'santri.id')
                ->join('biodata as b', 'santri.biodata_id', '=', 'b.id')
                ->join('sholat', 'sholat.id', '=', 'presensi_sholat.sholat_id')
                ->where('santri.status', 'aktif')
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
                    'presensi_sholat.metode',
                    'presensi_sholat.created_at as presensi_created_at'
                );

            if (!$showAll) {
                $listQuery->whereDate('presensi_sholat.tanggal', $tanggal);
            }

            if ($sholatId)      $listQuery->where('presensi_sholat.sholat_id', $sholatId);
            if ($metode)        $listQuery->where('presensi_sholat.metode', $metode);
            if ($jenisKelamin)  $listQuery->where('b.jenis_kelamin', $jenisKelamin);
            if ($status && strtolower($status) !== 'all') {
                $listQuery->where('presensi_sholat.status', $status);
            }

            $list = $listQuery
                ->orderByDesc('presensi_sholat.created_at')
                ->get();
        }

        return response()->json([
            'success'          => true,
            'filter' => [
                'tanggal'       => $tanggal,
                'sholat_id'     => $sholatId,
                'jadwal_id'     => $jadwalId,
                'metode'        => $metode,
                'status'        => $status,
                'jenis_kelamin' => $jenisKelamin,
                'all'           => $showAll,
            ],
            'jadwal_sholat'    => $jadwalResponse,
            'jadwal_mendatang' => $jadwalMendatangResponse,
            'status_presensi'  => $statusPresensi,
            'totals' => [
                'total_hadir'             => $total_hadir,
                'total_tidak_hadir'       => $total_tidak_hadir,
                'total_presensi_tercatat' => $total_presensi,
                'total_santri'            => $total_santri,
            ],
            'data' => $list,
        ]);
    }

    public function manualPresensi(ManualPresensiJamaahRequest $request)
    {
        try {
            $result = $this->service->manualPresensi(
                santriId: $request->santri_id,
                operatorUserId: Auth::id() ?: null
            );

            return response()->json($result);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'Error',
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function cariSantriByUid(Request $request)
    {
        $uid = $request->input('uid_kartu');
        if (! $uid) {
            return response()->json([
                'success' => false,
                'message' => 'UID kartu tidak boleh kosong.',
            ], 422);
        }

        try {
            $santri = $this->service->cariSantriByUid($uid);
            return response()->json([
                'success' => true,
                'data' => $santri,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
