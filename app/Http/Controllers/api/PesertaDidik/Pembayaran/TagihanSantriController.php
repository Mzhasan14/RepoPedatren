<?php

namespace App\Http\Controllers\api\PesertaDidik\Pembayaran;

use App\Models\Tagihan;
use App\Models\TagihanSantri;
use App\Models\Santri;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
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

    // List semua tagihan
    public function index()
    {
        $data = Tagihan::withCount('tagihanSantri')->get();
        return response()->json($data);
    }

    // Detail satu tagihan + daftar santri yang kena tagihan
    public function show($id)
    {
        $tagihan = Tagihan::with(['tagihanSantri.santri'])->findOrFail($id);
        return response()->json($tagihan);
    }

    // Generate tagihan santri
    public function generate(TagihanSantriRequest $request)
    {
        $result = $this->service->generate(
            $request->tagihan_id,
            $request->periode,
            $request->only('jenis_kelamin')
        );

        return response()->json([
            'message'      => 'Tagihan santri berhasil digenerate.',
            'total_santri' => $result['total_santri'],
        ]);
    }

    public function generateManual(TagihanSantriManualRequest $request): JsonResponse
    {
        try {
            $result = $this->service->generateManual(
                $request->tagihan_id,
                $request->periode,
                $request->santri_ids
            );

            return response()->json([
                'message' => 'Tagihan manual berhasil dibuat.',
                'data'    => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal membuat tagihan manual.',
                'error'   => $e->getMessage()
            ], 422);
        }
    }

    // List semua tagihan per santri (opsional tambahan)
    // public function listBySantri($santriId)
    // {
    //     $tagihanSantri = TagihanSantri::with('tagihan')
    //         ->where('santri_id', $santriId)
    //         ->get();

    //     return response()->json($tagihanSantri);
    // }
    // List semua tagihan per santri (opsional tambahan)
    public function listBySantri($santriId)
    {
        // Validasi santri exists
        $santri = Santri::with('biodata')->findOrFail($santriId);

        // Ambil data tagihan santri dengan relasi lengkap
        $tagihanSantri = TagihanSantri::with([
            'tagihan',
            'pembayaran' => function ($query) {
                $query->orderBy('tanggal_bayar', 'desc');
            },
            'creator:id,name',
            'updater:id,name'
        ])
            ->where('santri_id', $santriId)
            ->orderBy('periode', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        // Hitung statistik
        $totalTagihan = $tagihanSantri->count();
        $totalNominal = $tagihanSantri->sum('nominal');
        $totalSisa = $tagihanSantri->sum('sisa');
        $totalLunas = $tagihanSantri->where('status', 'lunas')->count();
        $totalPending = $tagihanSantri->where('status', 'pending')->count();
        $totalSebagian = $tagihanSantri->where('status', 'sebagian')->count();

        // Hitung total pembayaran
        $totalPembayaran = $tagihanSantri->sum(function ($tagihan) {
            return $tagihan->pembayaran->sum('jumlah_bayar');
        });

        // Format response dengan data tambahan
        return response()->json([
            'santri' => [
                'id' => $santri->id,
                'nis' => $santri->nis,
                'nama' => $santri->biodata->nama_lengkap ?? 'N/A',
                'status' => $santri->status,
                'angkatan' => $santri->angkatan_id,
            ],
            'statistik' => [
                'total_tagihan' => $totalTagihan,
                'total_nominal' => number_format($totalNominal, 2, ',', '.'),
                'total_sisa' => number_format($totalSisa, 2, ',', '.'),
                'total_pembayaran' => number_format($totalPembayaran, 2, ',', '.'),
                'persentase_lunas' => $totalTagihan > 0 ? round(($totalLunas / $totalTagihan) * 100, 2) : 0,
                'status_breakdown' => [
                    'lunas' => $totalLunas,
                    'pending' => $totalPending,
                    'sebagian' => $totalSebagian,
                ]
            ],
            'data' => $tagihanSantri->map(function ($tagihan) {
                return [
                    'id' => $tagihan->id,
                    'tagihan' => [
                        'id' => $tagihan->tagihan->id,
                        'nama_tagihan' => $tagihan->tagihan->nama_tagihan,
                        'tipe' => $tagihan->tagihan->tipe,
                    ],
                    'periode' => $tagihan->periode,
                    'nominal' => number_format($tagihan->nominal, 2, ',', '.'),
                    'sisa' => number_format($tagihan->sisa, 2, ',', '.'),
                    'status' => $tagihan->status,
                    'tanggal_jatuh_tempo' => $tagihan->tanggal_jatuh_tempo?->format('d/m/Y'),
                    'tanggal_bayar' => $tagihan->tanggal_bayar?->format('d/m/Y H:i'),
                    'keterangan' => $tagihan->keterangan,
                    'pembayaran_count' => $tagihan->pembayaran->count(),
                    'pembayaran_total' => number_format($tagihan->pembayaran->sum('jumlah_bayar'), 2, ',', '.'),
                    'pembayaran_terakhir' => $tagihan->pembayaran->first()?->tanggal_bayar?->format('d/m/Y H:i'),
                    'created_at' => $tagihan->created_at->format('d/m/Y H:i'),
                    'created_by' => $tagihan->creator?->name,
                ];
            }),
            'meta' => [
                'generated_at' => now()->format('d/m/Y H:i:s'),
                'total_records' => $totalTagihan,
            ]
        ]);
    }
}
