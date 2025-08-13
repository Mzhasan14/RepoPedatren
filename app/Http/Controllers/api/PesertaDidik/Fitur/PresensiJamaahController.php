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

    /**
     * List presensi dengan filter
     * GET /api/presensi
     *
     * Query params:
     *  - tanggal (YYYY-MM-DD) optional, default today
     *  - sholat_id optional
     *  - tidak_hadir=1 optional -> hanya yang status != Hadir
     *  - telat=1 optional -> hanya yang telat (requires sholat_id to determine jam_mulai)
     *  - telat_minutes optional -> default 10
     */
    public function index(Request $request)
    {
        $tanggal = $request->query('tanggal', Carbon::now('Asia/Jakarta')->toDateString());
        $sholatId = $request->query('sholat_id');
        $tidakHadir = $request->query('tidak_hadir'); // boolean
        $telat = $request->query('telat');
        $telatMinutes = (int) $request->query('telat_minutes', 10);

        if ($tidakHadir && $sholatId) {
            // Ambil semua santri + join presensi pada tanggal & sholat tertentu
            $list = DB::table('santri')
                ->join('biodata as b', 'santri.id', '=', 'b.santri_id')
                ->leftJoin('presensi_sholat', function ($join) use ($tanggal, $sholatId) {
                    $join->on('santri.id', '=', 'presensi_sholat.santri_id')
                        ->where('presensi_sholat.tanggal', '=', $tanggal)
                        ->where('presensi_sholat.sholat_id', '=', $sholatId);
                })
                ->leftJoin('sholat', 'sholat.id', '=', 'presensi_sholat.sholat_id')
                ->select(
                    'santri.id as santri_id',
                    'b.nama as nama_santri',
                    'santri.nis',
                    'sholat.id as sholat_id',
                    'sholat.nama_sholat',
                    'presensi_sholat.tanggal',
                    'presensi_sholat.waktu_presensi',
                    'presensi_sholat.status',
                    'presensi_sholat.metode'
                )
                ->where(function ($q) {
                    // Tidak ada presensi (null) atau status != Hadir
                    $q->whereNull('presensi_sholat.id')
                        ->orWhere('presensi_sholat.status', '!=', 'Hadir');
                })
                ->orderBy('santri.nama')
                ->get();

            return response()->json([
                'success' => true,
                'filter' => 'tidak_hadir',
                'data' => $list,
            ]);
        }

        // ===== Default: Ambil presensi biasa =====
        $query = PresensiSholat::with(['santri.biodata', 'sholat'])
            ->whereDate('tanggal', $tanggal);

        if ($sholatId) {
            $query->where('sholat_id', $sholatId);
        }

        $list = $query->get();

        // Filter telat
        if ($telat && $sholatId) {
            $jadwal = JadwalSholat::where('sholat_id', $sholatId)
                ->where('berlaku_mulai', '<=', $tanggal)
                ->where(function ($q) use ($tanggal) {
                    $q->whereNull('berlaku_sampai')
                        ->orWhere('berlaku_sampai', '>=', $tanggal);
                })
                ->first();

            if (! $jadwal) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ditemukan jadwal untuk sholat ini pada tanggal tersebut.'
                ], 422);
            }

            $startCarbon = Carbon::createFromFormat('H:i:s', $jadwal->jam_mulai, 'Asia/Jakarta')
                ->addMinutes($telatMinutes);

            $list = $list->filter(function ($item) use ($startCarbon) {
                if (! $item->waktu_presensi) return false;
                $presensiTime = Carbon::createFromFormat('H:i:s', $item->waktu_presensi, 'Asia/Jakarta');
                return $presensiTime->greaterThan($startCarbon);
            })->values();
        }

        $data = $list->map(function ($p) {
            return [
                'id' => $p->id ?? null,
                'santri_id' => $p->santri_id,
                'nama_santri' => $p->santri->biodata->nama ?? $p->nama_santri ?? null,
                'nis' => $p->santri->nis ?? null,
                'sholat_id' => $p->sholat_id,
                'nama_sholat' => $p->sholat->nama_sholat ?? $p->nama_sholat ?? null,
                'tanggal' => $p->tanggal,
                'waktu_presensi' => $p->waktu_presensi,
                'status' => $p->status,
                'metode' => $p->metode,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
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
}
