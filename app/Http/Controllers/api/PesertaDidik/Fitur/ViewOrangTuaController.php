<?php

namespace App\Http\Controllers\api\PesertaDidik\Fitur;

use Exception;
use App\Models\Santri;
use Illuminate\Http\Request;
use App\Models\TagihanSantri;
use App\Models\TransaksiSaldo;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Services\PesertaDidik\OrangTua\SaldoService;
use App\Services\PesertaDidik\OrangTua\PerizinanService;
use App\Http\Requests\PesertaDidik\OrangTua\SaldoRequest;
use App\Services\PesertaDidik\OrangTua\HafalanAnakService;
use App\Services\PesertaDidik\OrangTua\PelanggaranService;
use App\Services\PesertaDidik\OrangTua\BayarTagihanService;
use App\Services\PesertaDidik\OrangTua\ProfileSantriService;
use App\Services\PesertaDidik\OrangTua\TransaksiAnakService;
use App\Http\Requests\PesertaDidik\OrangTua\PerizinanRequest;
use App\Services\PesertaDidik\OrangTua\CatatanAfektifService;
use App\Services\PesertaDidik\OrangTua\CatatanKognitifService;
use App\Http\Requests\PesertaDidik\OrangTua\PelanggaranRequest;
use App\Http\Requests\PesertaDidik\OrangTua\ViewHafalanRequest;
use App\Http\Requests\PesertaDidik\OrangTua\BayarTagihanRequest;
use App\Http\Requests\PesertaDidik\OrangTua\ViewTransaksiRequest;
use App\Services\PesertaDidik\OrangTua\PresensiJamaahAnakService;
use App\Http\Requests\PesertaDidik\OrangTua\CatatanAfektifRequest;
use App\Http\Requests\PesertaDidik\OrangTua\CatatanKognitifRequest;
use App\Http\Requests\PesertaDidik\OrangTua\KirimPesanRequest;
use App\Http\Requests\PesertaDidik\OrangTua\LimitSaldoRequest;
use App\Http\Requests\PesertaDidik\OrangTua\PresensiJamaahAnakRequest;
use App\Http\Requests\PesertaDidik\OrangTua\PresensiJamaahTodayRequest;
use App\Services\PesertaDidik\OrangTua\KirimPesanService;
use App\Services\PesertaDidik\OrangTua\LimitSaldoService;

class ViewOrangTuaController extends Controller
{
    protected $viewOrangTuaService;
    protected $viewHafalanService;
    protected $viewPresensiJamaahAnakService;
    protected $viewPerizinanService;
    protected $viewPelanggaranService;
    protected $CatatanAfektifService;
    protected $CatatanKognitifService;
    protected $ProfileSantriService;
    protected $saldoService;
    protected $bayarService;
    protected  $limitSaldo;
    protected $KirimPesanService;

    public function __construct(
        TransaksiAnakService $viewOrangTuaService,
        HafalanAnakService $viewHafalanService,
        PresensiJamaahAnakService $viewPresensiJamaahAnakService,
        PerizinanService $viewPerizinanService,
        PelanggaranService $viewPelanggaranService,
        CatatanAfektifService $CatatanAfektifService,
        CatatanKognitifService $CatatanKognitifService,
        ProfileSantriService $ProfileSantriService,
        SaldoService $saldoService,
        BayarTagihanService $bayarService,
        LimitSaldoService $limitSaldo,
        KirimPesanService $KirimPesanService
    ) {
        $this->viewOrangTuaService = $viewOrangTuaService;
        $this->viewHafalanService = $viewHafalanService;
        $this->viewPresensiJamaahAnakService = $viewPresensiJamaahAnakService;
        $this->viewPerizinanService = $viewPerizinanService;
        $this->viewPelanggaranService = $viewPelanggaranService;
        $this->CatatanAfektifService = $CatatanAfektifService;
        $this->CatatanKognitifService = $CatatanKognitifService;
        $this->ProfileSantriService = $ProfileSantriService;
        $this->saldoService = $saldoService;
        $this->bayarService = $bayarService;
        $this->limitSaldo = $limitSaldo;
        $this->KirimPesanService = $KirimPesanService;
    }

    // public function getTransaksiAnak(ViewTransaksiRequest $request): JsonResponse
    // {
    //     try {
    //         $filters = array_filter($request->only([
    //             'santri_id',
    //             'outlet_id',
    //             'kategori_id',
    //             'date_from',
    //             'date_to',
    //             'q'
    //         ]));

    //         $perPage = $request->get('per_page', 25);

    //         $result = $this->viewOrangTuaService->getTransaksiAnak($filters, $perPage);

    //         $status = $result['status'] ?? 200;

    //         return response()->json($result, $status);
    //     } catch (\Throwable $e) {
    //         Log::error('ViewOrangTuaController@getTransaksiAnak error: ' . $e->getMessage(), [
    //             'exception' => $e,
    //             'user_id'   => Auth::id(),
    //             'filters'   => $request->all()
    //         ]);

    //         return response()->json([
    //             'success' => false,
    //             'status'  => 500,
    //             'message' => 'Terjadi kesalahan saat mengambil daftar transaksi.',
    //             'data'    => []
    //         ], 500);
    //     }
    // }

    public function getTahfidzAnak(ViewHafalanRequest $request)
    {
        try {
            $dataAnak = $request->validated();

            $result = $this->viewHafalanService->getTahfidzAnak($dataAnak);

            return response()->json($result, 200);
        } catch (Exception $e) {
            Log::error('ViewOrangTuaController@getTahfidzAnak error: ' . $e->getMessage(), [
                'exception' => $e,
                'user_id'   => Auth::id(),
                'santri_id' => $request->santri_id ?? null,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data tahfidz anak.',
                'status'  => 500,
                'data'    => []
            ], 500);
        }
    }

    public function getNadhomanAnak(ViewHafalanRequest $request)
    {
        try {
            $dataAnak = $request->validated();

            $result = $this->viewHafalanService->getNadhomanAnak($dataAnak);

            return response()->json($result, 200);
        } catch (Exception $e) {
            Log::error('ViewOrangTuaController@getNadhomanAnak error: ' . $e->getMessage(), [
                'exception' => $e,
                'user_id'   => Auth::id(),
                'santri_id' => $request->santri_id ?? null,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data nadhoman anak.',
                'status'  => 500,
                'data'    => []
            ], 500);
        }
    }

    public function getPresensiJamaahAnak(PresensiJamaahAnakRequest $request): JsonResponse
    {
        try {

            $result = $this->viewPresensiJamaahAnakService->getPresensiJamaahAnak($request->validated());

            return response()->json($result, 200);
        } catch (Exception $e) {
            Log::error('ViewOrangTuaController@getPresensiJamaahAnak error: ' . $e->getMessage(), [
                'exception' => $e,
                'user_id'   => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data presensi jamaah anak.',
                'status'  => 500,
                'data'    => []
            ], 500);
        }
    }

    public function getPresensiToday(PresensiJamaahTodayRequest $request): JsonResponse
    {
        try {
            $result = $this->viewPresensiJamaahAnakService->getPresensiToday($request->validated());

            return response()->json($result, 200);
        } catch (Exception $e) {
            Log::error('ViewOrangTuaController@getPresensiToday error: ' . $e->getMessage(), [
                'exception' => $e,
                'user_id'   => Auth::id(),
                'santri_id' => request()->get('santri_id'),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data presensi hari ini.',
                'status'  => 500,
                'data'    => []
            ], 500);
        }
    }

    public function perizinan(PerizinanRequest $request)
    {
        try {
            $result = $this->viewPerizinanService->perizinan($request->validated());

            return response()->json($result, 200);
        } catch (Exception $e) {
            Log::error('ViewOrangTuaController@ perizinan error : ', $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data presensi hari ini.',
                'status'  => 500,
                'data'    => []
            ], 500);
        }
    }

    public function pelanggaran(PelanggaranRequest $request)
    {
        try {
            $result = $this->viewPelanggaranService->pelanggaran($request->validated());

            return response()->json($result, 200);
        } catch (Exception $e) {
            Log::error('ViewOrangTuaController@ pelanggaran error : ', $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data presensi hari ini.',
                'status'  => 500,
                'data'    => []
            ], 500);
        }
    }
    public function catatanAfektif(CatatanAfektifRequest $request)
    {
        try {
            $result = $this->CatatanAfektifService->catatanAfektif($request->validated());

            return response()->json($result, 200);
        } catch (Exception $e) {
            Log::error('ViewOrangTuaController@ Catatan Afektif error : ', $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data Catatan Afektif.',
                'status'  => 500,
                'data'    => []
            ], 500);
        }
    }

    public function catatanKognitif(CatatanKognitifRequest $request)
    {
        try {
            $result = $this->CatatanKognitifService->catatanKognitif($request->validated());

            return response()->json($result, 200);
        } catch (Exception $e) {
            Log::error('ViewOrangTuaController@ Catatan Afektif error : ', $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data Catatan Afektif.',
                'status'  => 500,
                'data'    => []
            ], 500);
        }
    }
    public function ProfileSantri(CatatanKognitifRequest $request)
    {
        try {
            $result = $this->ProfileSantriService->ProfileSantri($request->validated());

            return response()->json($result, $result['status']);
        } catch (Exception $e) {
            Log::error('ViewOrangTuaController@ProfileSantri error : ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data Profile Santri.',
                'status'  => 500,
                'data'    => []
            ], 500);
        }
    }

    public function saldo(SaldoRequest $request)
    {
        try {
            $result = $this->saldoService->saldo($request->validated());
            return response()->json($result, 200);
        } catch (Exception $e) {
            Log::error('ViewOrangTuaController@saldo error : ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data Profile Santri.',
                'status'  => 500,
                'data'    => []
            ], 500);
        }
    }
    public function bayar(BayarTagihanRequest $request)
    {
        try {
            $result = $this->bayarService->bayar($request->validated());
            return response()->json($result, 200);
        } catch (Exception $e) {
            Log::error('ViewOrangTuaController@saldo error : ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data Profile Santri.',
                'status'  => 500,
                'data'    => []
            ], 500);
        }
    }

    public function transaksiSaldoAnak(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $noKk = $user->no_kk;

            // Ambil semua anak dari KK
            $anak = DB::table('keluarga as k')
                ->join('biodata as b', 'k.id_biodata', '=', 'b.id')
                ->join('santri as s', 'b.id', '=', 's.biodata_id')
                ->select('s.id as santri_id')
                ->where('k.no_kk', $noKk)
                ->get();

            if ($anak->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada data anak yang ditemukan.',
                    'data'    => null,
                ], 404);
            }

            // Validasi santri_id request
            $santriId = $request->input('santri_id');
            $dataAnak = $anak->firstWhere('santri_id', $santriId);

            if (!$dataAnak) {
                return response()->json([
                    'success' => false,
                    'message' => 'Santri tidak valid untuk user ini.',
                    'data'    => null,
                ], 403);
            }

            $query = TransaksiSaldo::with([
                'santri:id,nis,biodata_id',
                'santri.biodata:id,nama',
                'santri.kartu:id,santri_id,uid_kartu',
                'outlet:id,nama_outlet',
                'kategori:id,nama_kategori',
                'userOutlet:id,user_id,outlet_id'
            ])
                ->where('santri_id', $santriId)
                ->orderByDesc('created_at');

            // Filter tambahan
            if ($request->filled('outlet_id')) {
                $query->where('outlet_id', $request->outlet_id);
            }
            if ($request->filled('kategori_id')) {
                $query->where('kategori_id', $request->kategori_id);
            }
            if ($request->filled('tipe')) {
                $query->where('tipe', $request->tipe);
            }
            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }
            if ($request->filled('q')) {
                $q = $request->q;
                $query->where(function ($sub) use ($q) {
                    $sub->whereHas('santri.biodata', function ($qb) use ($q) {
                        $qb->whereRaw("MATCH(nama) AGAINST(? IN BOOLEAN MODE)", [$q]);
                    });
                    $sub->orWhereHas('santri', fn($qb) => $qb->where('nis', $q));
                });
            }

            // Hitung total tiap tipe
            $totalTopup  = (clone $query)->where('tipe', 'topup')->sum('jumlah');
            $totalDebit  = (clone $query)->where('tipe', 'debit')->sum('jumlah');
            $totalKredit = (clone $query)->where('tipe', 'kredit')->sum('jumlah');
            $totalRefund = (clone $query)->where('tipe', 'refund')->sum('jumlah');

            // Ambil pagination dari request
            $perPage = (int) $request->input('per_page', 25);
            $page    = (int) $request->input('page', 1);

            $results = $query->paginate($perPage, ['*'], 'page', $page);

            $data = $results->getCollection()->map(function ($item) {
                return [
                    'id'         => $item->id,
                    'tipe'       => $item->tipe,
                    'total_bayar'     => (float) $item->jumlah,
                    'keterangan' => $item->keterangan,
                    'tanggal'    => $item->created_at,
                    'outlet'     => $item->outlet,
                    'kategori'   => $item->kategori,
                    'uid_kartu'  => optional($item->santri->kartu)->uid_kartu,
                ];
            });

            $results->setCollection($data);

            return response()->json([
                'success'         => true,
                'status'          => 200,
                'total_data'      => $results->total(),
                'current_page'    => $results->currentPage(),
                'per_page'        => $results->perPage(),
                'total_pages'     => $results->lastPage(),
                'rekap' => [
                    'total_topup'  => (float) $totalTopup,
                    'total_debit'  => (float) $totalDebit,
                    'total_kredit' => (float) $totalKredit,
                    'total_refund' => (float) $totalRefund,
                ],
                'data' => $results->items(),
            ], 200);
        } catch (Exception $e) {
            Log::error('TransaksiSaldoController@transaksiSaldoAnak error: ' . $e->getMessage(), [
                'exception' => $e,
                'user_id'   => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil transaksi saldo.',
            ], 500);
        }
    }

    public function getTagihanAnak($santriId)
    {
        $santri = Santri::with('biodata')->findOrFail($santriId);

        $tagihanSantri = TagihanSantri::with([
            'tagihan',
        ])
            ->where('santri_id', $santriId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'data' => $tagihanSantri->map(function ($ts) {
                return [
                    'id' => $ts->id,
                    'tagihan' => [
                        'id'           => $ts->tagihan->id ?? null,
                        'nama_tagihan' => $ts->tagihan->nama_tagihan ?? null,
                    ],
                    'periode'            => $ts->periode ?? null,
                    'nominal'            => number_format($ts->tagihan->nominal ?? 0, 2, ',', '.'),
                    'total_potongan'     => number_format($ts->total_potongan ?? 0, 2, ',', '.'),
                    'total_tagihan'      => number_format($ts->total_tagihan ?? 0, 2, ',', '.'),
                    'status'             => $ts->status,
                    'tanggal_jatuh_tempo' => $ts->tanggal_jatuh_tempo?->format('d/m/Y'),
                    'tanggal_bayar'      => $ts->tanggal_bayar?->format('d/m/Y H:i'),
                    'keterangan'         => $ts->keterangan,
                ];
            }),
        ]);
    }

    public function setLimitSaldo(LimitSaldoRequest $request)
    {
        $result = $this->limitSaldo->setLimitSaldo(
            santriId: $request->santri_id,
            limitSaldo: $request->limit_saldo,
            takTerbatas: $request->tak_terbatas
        );

        return response()->json($result, $result['success'] ? 200 : 422);
    }
    public function SendMessage(KirimPesanRequest $request)
    {
        try {
            $result = $this->KirimPesanService->SendMessage($request->validated());
            return response()->json($result, 200);
        } catch (Exception $e) {
            Log::error('ViewOrangTuaController@saldo error : ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengirim data Pesan Ortu.',
                'status'  => 500,
                'data'    => []
            ], 500);
        }
    }
    public function ReadMessageOrtu(CatatanKognitifRequest $request)
    {
        try {
            $result = $this->KirimPesanService->ReadMessageOrtu($request->validated());
            return response()->json($result, 200);
        } catch (Exception $e) {
            Log::error('ViewOrangTuaController@saldo error : ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data Pesan Ortu.',
                'status'  => 500,
                'data'    => []
            ], 500);
        }
    }
}
