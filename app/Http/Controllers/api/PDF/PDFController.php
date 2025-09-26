<?php

namespace App\Http\Controllers\api\PDF;

use App\Http\Controllers\Controller;
use App\Services\Pegawai\Filters\Formulir\JadwalService;
use App\Services\PesertaDidik\SantriService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PDFController extends Controller
{
    private JadwalService $JadwalService;
    private SantriService $SantriService;

    public function __construct(
        JadwalService $JadwalService,
        SantriService $SantriService
    ) {
        $this->JadwalService = $JadwalService;
        $this->SantriService = $SantriService;
    }
    public function downloadPdf(Request $request)
    {
        try {
            // Validasi parameter
            if (
                blank($request->input('lembaga_id')) ||
                blank($request->input('jurusan_id')) ||
                blank($request->input('kelas_id')) ||
                blank($request->input('rombel_id')) ||
                blank($request->input('semester_id'))
            ) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Semua parameter wajib diisi.'
                ], 422);
            }

            // Ambil data jadwal
            $query = $this->JadwalService->getAllJadwalQuery($request);
            $results = $query->get();
            $formatted = $this->JadwalService->groupJadwalByHari($results);

            $semesterData = DB::table('semester')
                ->where('id', $request->semester_id)
                ->first();

            $tahunAjaran = '-';

            if ($semesterData) {
                $tahun = DB::table('tahun_ajaran')
                    ->where('id', $semesterData->tahun_ajaran_id)
                    ->first();

                $tahunAjaran = $tahun?->tahun_ajaran ?? '-';
            }

            // Meta info
            $meta = $results->first();
            $metaInfo = [
                'lembaga' => $meta->nama_lembaga ?? '',
                'jurusan' => $meta->nama_jurusan ?? '',
                'kelas' => $meta->nama_kelas ?? '',
                'rombel' => $meta->nama_rombel ?? '',
                'semester' => $request->semester_id == 1 ? 'Ganjil' : 'Genap',
                'tahun_ajaran' => $tahunAjaran
            ];

            // Buat PDF dari view
            $pdf = Pdf::loadView('pdf.jadwal_pelajaran', [
                'meta' => $metaInfo,
                'data' => $formatted
            ])->setPaper('A4', 'portrait');

            $filename = 'jadwal_' . $metaInfo['semester'] . '_' . $metaInfo['kelas'] . '_' . $metaInfo['rombel'] . '.pdf';

            return Response::make($pdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        } catch (\Throwable $e) {
            Log::error("Download Jadwal PDF Error: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat membuat PDF.'
            ], 500);
        }
    }

    public function downloadIdCard(Request $request)
    {
        try {
            // Ambil data santri dari service
            $santri = $this->SantriService->getAllSantri($request)->get();

            if ($santri->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data santri tidak ditemukan.'
                ], 404);
            }

            // Generate PDF
            $pdf = Pdf::loadView('pdf.id_card', [
                'santri' => $santri
            ])
                ->setPaper([0, 0, 324, 203], 'portrait')
                ->setOption('dpi', 300) // biar HD
                ->setOption('isRemoteEnabled', true);

            return $pdf->download('id_card_santri.pdf');
        } catch (\Throwable $e) {
            Log::error("Download ID Card Error: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat membuat ID Card.'
            ], 500);
        }
    }
}
