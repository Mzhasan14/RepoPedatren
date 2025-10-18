<?php

namespace App\Http\Controllers\api\PesertaDidik\Fitur;

use Exception;
use App\Models\Santri;
use Illuminate\Http\Request;
use App\Models\TagihanSantri;
use App\Models\TransaksiSaldo;
use App\Models\VirtualAccount;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Services\PesertaDidik\OrangTua\SaldoService;
use App\Services\PesertaDidik\OrangTua\PerizinanService;
use App\Http\Requests\PesertaDidik\OrangTua\SaldoRequest;
use App\Services\PesertaDidik\OrangTua\KirimPesanService;
use App\Services\PesertaDidik\OrangTua\LimitSaldoService;
use App\Services\PesertaDidik\OrangTua\HafalanAnakService;
use App\Services\PesertaDidik\OrangTua\PelanggaranService;
use App\Services\PesertaDidik\OrangTua\BayarTagihanService;
use App\Services\PesertaDidik\OrangTua\ProfileSantriService;
use App\Services\PesertaDidik\OrangTua\TransaksiAnakService;
use App\Http\Requests\PesertaDidik\OrangTua\PerizinanRequest;
use App\Services\PesertaDidik\OrangTua\CatatanAfektifService;
use App\Http\Requests\PesertaDidik\OrangTua\KirimPesanRequest;
use App\Http\Requests\PesertaDidik\OrangTua\LimitSaldoRequest;
use App\Services\PesertaDidik\OrangTua\CatatanKognitifService;
use App\Http\Requests\PesertaDidik\OrangTua\PelanggaranRequest;
use App\Http\Requests\PesertaDidik\OrangTua\ViewHafalanRequest;
use App\Http\Requests\PesertaDidik\OrangTua\BayarTagihanRequest;
use App\Http\Requests\PesertaDidik\OrangTua\ViewTransaksiRequest;
use App\Services\PesertaDidik\OrangTua\PresensiJamaahAnakService;
use App\Http\Requests\PesertaDidik\OrangTua\CatatanAfektifRequest;
use App\Http\Requests\PesertaDidik\OrangTua\CatatanKognitifRequest;
use App\Http\Requests\PesertaDidik\OrangTua\VirtualAccountAnakRequest;
use App\Http\Requests\PesertaDidik\OrangTua\PresensiJamaahAnakRequest;
use App\Http\Requests\PesertaDidik\OrangTua\PresensiJamaahTodayRequest;

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
                'santri.kartu:id,santri_id,uid_kartu,limit_saldo',
                'outlet:id,nama_outlet',
                'kategori:id,nama_kategori',
                'userOutlet:id,user_id,outlet_id',
            ])
                ->where('santri_id', $santriId)
                ->orderByDesc('id');

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
                    'limit_saldo'   => number_format($item->santri->kartu->limit_saldo, 0, ',', '.') ?? null,
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
        $dataAnak = $anak->firstWhere('santri_id', $santriId);

        if (!$dataAnak) {
            return response()->json([
                'success' => false,
                'message' => 'Santri tidak valid untuk user ini.',
                'data'    => null,
            ], 403);
        }
        $santri = Santri::with('biodata')->findOrFail($santriId);

        $tagihanSantri = TagihanSantri::with([
            'tagihan',
        ])
            ->where('santri_id', $santriId)
            ->orderBy('id', 'desc')
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

    public function VirtualAccountAnak(VirtualAccountAnakRequest $request)
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
            $dataAnak = $anak->firstWhere('santri_id', $request->santri_id);

            if (!$dataAnak) {
                return response()->json([
                    'success' => false,
                    'message' => 'Santri tidak valid untuk user ini.',
                    'data'    => null,
                ], 403);
            }

            // Ambil santri
            $santri = Santri::query()
                ->select('santri.id', 'santri.nis')
                ->where('santri.status', 'aktif')
                ->where('santri.id', $request->santri_id)
                ->first();

            if (!$santri) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data santri tidak ditemukan atau tidak aktif.',
                    'data'    => null,
                ], 404);
            }

            $nis = $santri->nis;

            // Lewati jika format NIS tidak sesuai
            if (strlen($nis) !== 10) {
                return response()->json([
                    'success' => false,
                    'message' => 'Format NIS tidak valid.',
                    'data' => null,
                ], 422);
            }

            // Buang digit ke-5 dan ke-6 dari NIS
            $vaNumber = substr($nis, 0, 4) . substr($nis, 6);

            // Cek apakah VA sudah ada
            $exists = VirtualAccount::where('santri_id', $santri->id)
                ->where('status', true)
                ->exists();

            // Jika belum ada, buat baru
            if (!$exists) {
                $va = VirtualAccount::create([
                    'santri_id' => $santri->id,
                    'va_number' => $vaNumber,
                    'status'    => true,
                ]);

                // ✅ Catat aktivitas pembuatan VA (Spatie Activity Log)
                activity('virtual_account')
                    ->causedBy($user)
                    ->performedOn($va)
                    ->withProperties([
                        'santri_id'  => $santri->id,
                        'va_number'  => $vaNumber,
                        'no_kk'      => $noKk,
                        'created_by' => $user->id,
                        'ip'         => request()->ip(),
                        'user_agent' => request()->userAgent(),
                    ])
                    ->event('created')
                    ->log("Virtual Account {$vaNumber} berhasil dibuat untuk santri ID {$santri->id}");
            }

            // Ambil VA aktif
            $va = VirtualAccount::where('santri_id', $request->santri_id)
                ->where('status', true)
                ->first();

            if ($va) {
                return response()->json([
                    'success' => true,
                    'message' => 'Virtual account ditemukan.',
                    'data' => [
                        'id' => $va->id,
                        'santri_id' => $va->santri_id,
                        'va_number' => $va->va_number,
                        'status' => $va->status,
                    ],
                ], 200);
            }

            return response()->json([
                'success' => false,
                'message' => 'Virtual account belum tersedia untuk santri ini.',
                'data' => null,
            ], 404);
        } catch (\Exception $e) {
            Log::error('Gagal membuat/mengambil Virtual Account Anak', [
                'user_id'   => Auth::id(),
                'santri_id' => $request->santri_id,
                'error'     => $e->getMessage(),
                'trace'     => $e->getTraceAsString(),
            ]);

            activity('virtual_account')
                ->causedBy(Auth::user())
                ->withProperties([
                    'santri_id' => $request->santri_id,
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'error' => $e->getMessage(),
                ])
                ->event('failed')
                ->log("Gagal memproses Virtual Account untuk santri ID {$request->santri_id}");

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses virtual account.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
