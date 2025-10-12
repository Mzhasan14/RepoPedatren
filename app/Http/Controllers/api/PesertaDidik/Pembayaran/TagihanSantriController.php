<?php

namespace App\Http\Controllers\api\PesertaDidik\Pembayaran;

use Exception;
use App\Models\Saldo;
use App\Models\Santri;
use App\Models\Tagihan;
use Illuminate\Http\Request;
use App\Models\TagihanSantri;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Services\PesertaDidik\Pembayaran\TagihanSantriService;
use App\Http\Requests\PesertaDidik\Pembayaran\TagihanSantriRequest;
use App\Http\Requests\PesertaDidik\Pembayaran\TagihanSantriManualRequest;

class TagihanSantriController extends Controller
{
    protected TagihanSantriService $service;

    public function __construct(TagihanSantriService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $query = TagihanSantri::select([
            'id',
            'tagihan_id',
            'santri_id',
            'periode',
            'total_potongan',
            'total_tagihan',
            'status',
            'tanggal_jatuh_tempo',
            'tanggal_bayar',
            'keterangan',
            'created_by',
            'created_at',
            'updated_at',
        ])
            ->with([
                'tagihan:id,nama_tagihan,nominal',
                'santri:id,biodata_id,nis',
                'santri.biodata:id,nama',
            ]);

        $query->when($request->periode, function ($q, $periode) {
            $q->where('periode', $periode);
        });

        $query->when($request->status, function ($q, $status) {
            $q->where('status', $status);
        });

        $query->when($request->tagihan_id, function ($q, $tagihanId) {
            $q->where('tagihan_id', $tagihanId);
        });

        $query->when($request->search, function ($q, $search) {
            $q->whereHas('santri.biodata', function ($sub) use ($search) {
                $sub->where('nama', 'like', "%{$search}%");
            })->orWhereHas('santri', function ($sub) use ($search) {
                $sub->where('nis', 'like', "%{$search}%");
            });
        });

        $data = $query->paginate(25)->through(function ($item) {
            return [
                'id' => $item->id,
                'tagihan_id' => $item->tagihan_id,
                'santri_id' => $item->santri_id,
                'nama_tagihan' => $item->tagihan->nama_tagihan ?? null,
                'nama_santri' => $item->santri->biodata->nama ?? null,
                'nis' => $item->santri->nis ?? null,
                'periode' => $item->periode,
                'nominal' => $item->tagihan->nominal,
                'total_potongan' => $item->total_potongan,
                'total_tagihan' => $item->total_tagihan,
                'status' => $item->status,
                'tanggal_jatuh_tempo' => $item->tanggal_jatuh_tempo,
                'tanggal_bayar' => $item->tanggal_bayar,
                'keterangan' => $item->keterangan,
                'created_by' => $item->created_by,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
            ];
        });

        return response()->json($data);
    }

    public function filters()
    {
        $periodes = TagihanSantri::select('periode')
            ->distinct()
            ->orderBy('periode', 'desc')
            ->pluck('periode');

        $tagihans = DB::table('tagihan')
            ->select('id', 'nama_tagihan')
            ->orderBy('nama_tagihan')
            ->get();

        return response()->json([
            'periodes' => $periodes,
            'tagihans' => $tagihans,
        ]);
    }

    public function tagihanSantriByTagihanId(Request $request, $id)
    {
        $perPage = $request->input('per_page', 25);
        $page    = $request->input('page', 1);
        $search  = $request->input('search');
        $status  = $request->input('status');

        $query = TagihanSantri::select([
            'id',
            'tagihan_id',
            'santri_id',
            'periode',
            'total_potongan',
            'total_tagihan',
            'status',
            'tanggal_jatuh_tempo',
            'tanggal_bayar',
            'keterangan',
            'created_by',
            'created_at',
            'updated_at',
        ])
            ->with([
                'santri:id,biodata_id,nis',
                'santri.biodata:id,nama',
            ])
            ->where('tagihan_id', $id);

        if (!empty($search)) {
            $query->whereHas('santri', function ($q) use ($search) {
                $q->where('nis', 'like', "%{$search}%")
                    ->orWhereHas('biodata', function ($qb) use ($search) {
                        $qb->where('nama', 'like', "%{$search}%");
                    });
            });
        }

        if (!empty($status)) {
            $allowedStatuses = ['lunas', 'pending', 'batal'];
            if (in_array(strtolower($status), $allowedStatuses)) {
                $query->where('status', strtolower($status));
            }
        }

        $data = $query->paginate(
            $perPage,
            ['*'],
            'page',
            $page
        )->through(function ($item) {
            return [
                'id'             => $item->id,
                'nama_santri'    => $item->santri->biodata->nama ?? null,
                'nis'            => $item->santri->nis ?? null,
                'periode'        => $item->periode ?? null,
                'total_potongan' => $item->total_potongan,
                'total_tagihan'  => $item->total_tagihan,
                'status'         => $item->status,
                'tanggal_bayar'  => $item->tanggal_bayar,
                'keterangan'     => $item->keterangan,
                'created_by'     => $item->created_by,
                'created_at'     => $item->created_at,
                'updated_at'     => $item->updated_at,
            ];
        });

        return response()->json($data);
    }

    public function show($id)
    {
        $item = TagihanSantri::select([
            'id',
            'tagihan_id',
            'santri_id',
            'periode',
            'total_potongan',
            'total_tagihan',
            'status',
            'tanggal_jatuh_tempo',
            'tanggal_bayar',
            'keterangan',
            'created_by',
            'created_at',
            'updated_at',
        ])
            ->with([
                'tagihan:id,nama_tagihan,nominal',
                'santri:id,biodata_id,nis',
                'santri.biodata:id,nama',
            ])
            ->findOrFail($id);

        $data = [
            'id' => $item->id,
            'tagihan_id' => $item->tagihan_id,
            'santri_id' => $item->santri_id,
            'nama_tagihan' => $item->tagihan->nama_tagihan ?? null,
            'nama_santri' => $item->santri->biodata->nama ?? null,
            'nis' => $item->santri->nis ?? null,
            'periode' => $item->periode,
            'nominal' => $item->tagihan->nominal,
            'total_potongan' => $item->total_potongan,
            'total_tagihan' => $item->total_tagihan,
            'status' => $item->status,
            'tanggal_jatuh_tempo' => $item->tanggal_jatuh_tempo,
            'tanggal_bayar' => $item->tanggal_bayar,
            'keterangan' => $item->keterangan,
            'created_by' => $item->created_by,
            'created_at' => $item->created_at,
            'updated_at' => $item->updated_at,
        ];

        return response()->json($data);
    }

    public function generate(TagihanSantriRequest $request): JsonResponse
    {
        try {
            $result = $this->service->generate(
                $request->input('tagihan_id'),
                $request->input('periode'),
                $request->only(['all', 'santri_ids', 'jenis_kelamin'])
            );

            // Pilih status code berdasarkan hasil
            $statusCode = $result['success'] ? 200 : 400;

            // ✅ Return langsung hasil dari service (tanpa nested "data")
            return response()->json($result, $statusCode);
        } catch (\Throwable $e) {
            Log::error('Gagal generate tagihan santri', [
                'tagihan_id' => $request->input('tagihan_id'),
                'periode'    => $request->input('periode'),
                'filter'     => $request->only(['all', 'santri_ids', 'jenis_kelamin']),
                'exception'  => $e->getMessage(),
                'trace'      => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan internal. Silakan coba beberapa saat lagi.',
            ], 500);
        }
    }

    public function batalTagihan(Request $request)
    {
        $request->validate([
            'tagihan_santri_id' => 'required|integer|exists:tagihan_santri,id',
        ]);

        try {
            DB::beginTransaction();

            $user = Auth::user();
            $tagihan = TagihanSantri::lockForUpdate()->findOrFail($request->tagihan_santri_id);

            if ($tagihan->status === 'batal') {
                return response()->json([
                    'message' => 'Tagihan ini sudah dibatalkan sebelumnya.'
                ], 400);
            }

            if ($tagihan->status === 'lunas') {
                $saldo = Saldo::firstOrCreate(['santri_id' => $tagihan->santri_id]);

                $saldo->saldo += $tagihan->total_tagihan;
                $saldo->save();

                $tagihan->update([
                    'status' => 'batal',
                    'keterangan' => 'Pembatalan tagihan (refund otomatis) oleh ' . ($user->name ?? 'Sistem'),
                    'updated_by' => $user->id ?? null,
                ]);

                DB::commit();

                return response()->json([
                    'message' => 'Tagihan dibatalkan dan saldo telah dikembalikan.',
                    'data' => [
                        'tagihan_id' => $tagihan->id,
                        'saldo_akhir' => $saldo->saldo
                    ]
                ]);
            }

            if ($tagihan->status === 'pending') {
                $tagihan->update([
                    'status' => 'batal',
                    'keterangan' => 'Tagihan dibatalkan oleh ' . ($user->name ?? 'Sistem'),
                    'updated_by' => $user->id ?? null,
                ]);

                DB::commit();

                return response()->json([
                    'message' => 'Tagihan berhasil dibatalkan.',
                    'data' => $tagihan
                ]);
            }
            return response()->json([
                'message' => 'Status tagihan tidak bisa dibatalkan.',
                'status' => $tagihan->status
            ], 400);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Gagal membatalkan tagihan santri', [
                'tagihan_santri_id' => $request->tagihan_santri_id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Terjadi kesalahan saat membatalkan tagihan.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // public function generateManual(TagihanSantriManualRequest $request): JsonResponse
    // {
    //     try {
    //         $santriIds = $request->santri_ids ?? [];

    //         $result = $this->service->generateManual(
    //             $request->tagihan_id,
    //             $request->periode,
    //             $santriIds
    //         );

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Tagihan manual berhasil digenerate.',
    //             'data'    => $result,
    //         ]);
    //     } catch (Exception $e) {
    //         Log::error('Gagal generate manual tagihan', [
    //             'error' => $e->getMessage(),
    //             'request' => $request->all(),
    //         ]);

    //         return response()->json([
    //             'success' => false,
    //             'message' => $e->getMessage(),
    //         ], 422);
    //     }
    // }

    // List semua tagihan per santri (opsional tambahan)
    // public function listBySantri($santriId)
    // {
    //     $tagihanSantri = TagihanSantri::with('tagihan')
    //         ->where('santri_id', $santriId)
    //         ->get();

    //     return response()->json($tagihanSantri);
    // }
    // List semua tagihan per santri (opsional tambahan)
    // public function listBySantri($santriId)
    // {
    //     // Validasi santri exists
    //     $santri = Santri::with('biodata')->findOrFail($santriId);

    //     // Ambil data tagihan santri dengan relasi lengkap
    //     $tagihanSantri = TagihanSantri::with([
    //         'tagihan',
    //         'pembayaran' => function ($query) {
    //             $query->orderBy('tanggal_bayar', 'desc');
    //         },
    //         'creator:id,name',
    //         'updater:id,name'
    //     ])
    //         ->where('santri_id', $santriId)
    //         ->orderBy('created_at', 'desc')
    //         ->get();

    //     // Hitung statistik
    //     $totalTagihan   = $tagihanSantri->count();
    //     $totalNominal   = $tagihanSantri->sum('nominal');
    //     $totalPotongan  = $tagihanSantri->sum('total_potongan');
    //     $totalTagihanNet = $tagihanSantri->sum('total_tagihan');

    //     $totalLunas     = $tagihanSantri->where('status', 'lunas')->count();
    //     $totalPending   = $tagihanSantri->where('status', 'pending')->count();
    //     $totalTerlambat = $tagihanSantri->where('status', 'terlambat')->count();

    //     // Hitung total pembayaran
    //     $totalPembayaran = $tagihanSantri->sum(function ($tagihan) {
    //         return $tagihan->pembayaran->sum('jumlah_bayar');
    //     });

    //     // Format response dengan data tambahan
    //     return response()->json([
    //         'santri' => [
    //             'id'       => $santri->id,
    //             'nis'      => $santri->nis,
    //             'nama'     => $santri->biodata->nama_lengkap ?? 'N/A',
    //             'status'   => $santri->status,
    //             'angkatan' => $santri->angkatan_id,
    //         ],
    //         'statistik' => [
    //             'total_tagihan'     => $totalTagihan,
    //             'total_nominal'     => number_format($totalNominal, 2, ',', '.'),
    //             'total_potongan'    => number_format($totalPotongan, 2, ',', '.'),
    //             'total_tagihan_net' => number_format($totalTagihanNet, 2, ',', '.'),
    //             'total_pembayaran'  => number_format($totalPembayaran, 2, ',', '.'),
    //             'persentase_lunas'  => $totalTagihan > 0 ? round(($totalLunas / $totalTagihan) * 100, 2) : 0,
    //             'status_breakdown'  => [
    //                 'lunas'     => $totalLunas,
    //                 'pending'   => $totalPending,
    //                 'terlambat' => $totalTerlambat,
    //             ]
    //         ],
    //         'data' => $tagihanSantri->map(function ($tagihan) {
    //             return [
    //                 'id' => $tagihan->id,
    //                 'tagihan' => [
    //                     'id'           => $tagihan->tagihan->id,
    //                     'nama_tagihan' => $tagihan->tagihan->nama_tagihan,
    //                     'tipe'         => $tagihan->tagihan->tipe,
    //                 ],
    //                 'periode'         => $tagihan->periode,
    //                 'nominal'         => number_format($tagihan->nominal, 2, ',', '.'),
    //                 'total_potongan'  => number_format($tagihan->total_potongan, 2, ',', '.'),
    //                 'total_tagihan'   => number_format($tagihan->total_tagihan, 2, ',', '.'),
    //                 'status'          => $tagihan->status,
    //                 'tanggal_jatuh_tempo' => $tagihan->tanggal_jatuh_tempo?->format('d/m/Y'),
    //                 'tanggal_bayar'       => $tagihan->tanggal_bayar?->format('d/m/Y H:i'),
    //                 'keterangan'      => $tagihan->keterangan,
    //                 'pembayaran_count' => $tagihan->pembayaran->count(),
    //                 'pembayaran_total' => number_format($tagihan->pembayaran->sum('jumlah_bayar'), 2, ',', '.'),
    //                 'pembayaran_terakhir' => $tagihan->pembayaran->first()?->tanggal_bayar?->format('d/m/Y H:i'),
    //                 'created_at'      => $tagihan->created_at->format('d/m/Y H:i'),
    //                 'created_by'      => $tagihan->creator?->name,
    //             ];
    //         }),
    //         'meta' => [
    //             'generated_at'  => now()->format('d/m/Y H:i:s'),
    //             'total_records' => $totalTagihan,
    //         ]
    //     ]);
    // }

  
}
