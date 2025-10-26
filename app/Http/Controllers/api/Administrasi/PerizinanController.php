<?php

namespace App\Http\Controllers\api\Administrasi;

use Carbon\Carbon;
use App\Models\Perizinan;
use App\Exports\BaseExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Services\Administrasi\PerizinanService;
use App\Http\Requests\Administrasi\PerizinanRequest;
use App\Http\Requests\Administrasi\BerkasPerizinanRequest;
use App\Services\Administrasi\Filters\FilterPerizinanService;

class PerizinanController extends Controller
{
    private PerizinanService $perizinan;

    private FilterPerizinanService $filter;

    public function __construct(FilterPerizinanService $filter, PerizinanService $perizinan)
    {
        $this->filter = $filter;
        $this->perizinan = $perizinan;
    }

    public function getAllPerizinan(Request $request)
    {
        try {
            $query = $this->perizinan->getAllPerizinan($request);
            $query = $this->filter->perizinanFilters($query, $request);
            $query->latest('pr.created_at');

            $perPage = (int) $request->input('limit', 25);
            $currentPage = (int) $request->input('page', 1);
            $results = $query->paginate($perPage, ['*'], 'page', $currentPage);
        } catch (\Throwable $e) {
            Log::error("[PerizinanController] Error: {$e->getMessage()}");

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan pada server',
            ], 500);
        }

        if ($results->isEmpty()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Data kosong',
                'data' => [],
            ], 200);
        }

        $formatted = $this->perizinan->formatData($results);

        return response()->json([
            'total_data' => $results->total(),
            'current_page' => $results->currentPage(),
            'per_page' => $results->perPage(),
            'total_pages' => $results->lastPage(),
            'data' => $formatted,
        ]);
    }

    public function index($bioId)
    {
        try {
            $result = $this->perizinan->index($bioId);
            if (! $result['status']) {
                return response()->json([
                    'message' => $result['message'] ?? 'Data tidak ditemukan.',
                ], 200);
            }

            return response()->json([
                'message' => 'Data berhasil ditampilkan',
                'data' => $result['data'],
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal ambil data perizinan: ' . $e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat menampilkan data.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(PerizinanRequest $request, $bioId)
    {
        try {
            $result = $this->perizinan->store($request->validated(), $bioId);
            if (! $result['status']) {
                return response()->json([
                    'message' => $result['message'],
                ], 200);
            }

            return response()->json([
                'message' => 'Data berhasil ditambah',
                'data' => $result['data'],
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal tambah perizinan: ' . $e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $result = $this->perizinan->show($id);
            if (! $result['status']) {
                return response()->json([
                    'message' => $result['message'] ?? 'Data tidak ditemukan.',
                ], 200);
            }

            return response()->json([
                'message' => 'Detail data berhasil ditampilkan',
                'data' => $result['data'],
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal ambil detail perizinan: ' . $e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat menampilkan data.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(PerizinanRequest $request, $id)
    {
        try {
            $result = $this->perizinan->update($request->validated(), $id);
            if (! $result['status']) {
                return response()->json([
                    'message' => $result['message'],
                ], 200);
            }

            return response()->json([
                'message' => 'Data berhasil diperbarui',
                'data' => $result['data'],
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal update perizinan: ' . $e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function addBerkasPerizinan(BerkasPerizinanRequest $request, $id)
    {
        try {
            $result = $this->perizinan->addBerkasPerizinan($request->validated(), $id);
            if (! $result['status']) {
                return response()->json([
                    'message' => $result['message'],
                ], 200);
            }

            return response()->json([
                'message' => 'Data berkas berhasil ditambah',
                'data' => $result['data'],
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal tambah berkas perizinan: ' . $e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function setKeluar($id)
    {
        $perizinan = Perizinan::findOrFail($id);

        // Validasi: harus sudah disetujui kamtib dan biktren
        if (!$perizinan->approved_by_kamtib || !$perizinan->approved_by_biktren) {
            return response()->json([
                'success' => false,
                'message' => 'Perizinan belum disetujui oleh kamtib atau biktren.'
            ], 422);
        }

        // Validasi: jika tanggal_kembali sudah diisi, beri pesan error
        if (!empty($perizinan->tanggal_kembali)) {
            return response()->json([
                'success' => false,
                'message' => 'Santri sudah kembali ke pondok. Tidak dapat mengubah status keluar.'
            ], 422);
        }

        // Validasi: jika sudah keluar, beri pesan
        if ($perizinan->status === 'sudah berada diluar pondok') {
            return response()->json([
                'success' => false,
                'message' => 'Santri sudah keluar pondok.'
            ], 422);
        }

        // Update status
        $perizinan->status = 'sudah berada diluar pondok';
        $perizinan->updated_by = Auth::id();
        $perizinan->save();

        return response()->json([
            'success' => true,
            'message' => 'Status perizinan telah diupdate menjadi sudah berada diluar pondok.',
            'data' => $perizinan
        ]);
    }


    public function setKembali($id)
    {
        $perizinan = Perizinan::findOrFail($id);

        // Validasi: sudah kembali berdasarkan tanggal_kembali atau status
        if (
            !empty($perizinan->tanggal_kembali) ||
            $perizinan->status === 'kembali tepat waktu' ||
            $perizinan->status === 'telat'
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Santri sudah kembali ke pondok.'
            ], 422);
        }

        $now = Carbon::now();
        $tanggalAkhir = Carbon::parse($perizinan->tanggal_akhir);

        if ($now->greaterThan($tanggalAkhir)) {
            $perizinan->status = 'telat';
        } else {
            $perizinan->status = 'kembali tepat waktu';
        }

        $perizinan->tanggal_kembali = $now;
        $perizinan->updated_by = Auth::id();
        $perizinan->save();

        return response()->json([
            'success' => true,
            'message' => 'Status kembali berhasil diupdate.',
            'data' => $perizinan
        ]);
    }

    public function exportExcel(Request $request)
    {
        // **Default fields cerdas (paling penting + umum)**
        $defaultExportFields = [
            'nama_santri',
            'nis',
            'jenis_kelamin',
            'wilayah',
            'blok',
            'kamar',
            'lembaga',
            'kelas',
            'rombel',
            'alasan_izin',
            'alamat_tujuan',
            'tanggal_mulai',
            'tanggal_akhir',
            'bermalam',
            'lama_izin',
            'tanggal_kembali',
            'jenis_izin',
            'status',
            'pembuat',
            'nama_pengasuh',
            'nama_biktren',
            'nama_kamtib',
            'keterangan',
            'created_at',
        ];

        $columnOrder = [
            'nama_santri',
            'nis',
            'jenis_kelamin',
            'wilayah',
            'blok',
            'kamar',
            'lembaga',
            'jurusan',
            'kelas',
            'rombel',
            'provinsi',
            'kabupaten',
            'kecamatan',
            'alasan_izin',
            'alamat_tujuan',
            'tanggal_mulai',
            'tanggal_akhir',
            'bermalam',
            'lama_izin',
            'tanggal_kembali',
            'jenis_izin',
            'status',
            'pembuat',
            'nama_pengasuh',
            'nama_biktren',
            'nama_kamtib',
            'approved_by_biktren',
            'approved_by_kamtib',
            'approved_by_pengasuh',
            'keterangan',
            'created_at',
            'updated_at',
            'foto_profil',
        ];

        $optionalFields = $request->input('fields', []);

        $fields = array_unique(array_merge($defaultExportFields, $optionalFields));
        $fields = array_values(array_intersect($columnOrder, $fields));

        $query = $this->perizinan->getExportPerizinanQuery($fields, $request);
        $query = $this->filter->perizinanFilters($query, $request);
        $query = $query->latest('pr.id');

        // Jika all, ambil semua, else limit
        $results = $request->input('all') === 'true'
            ? $query->get()
            : $query->limit((int) $request->input('limit', 100))->get();

        $addNumber = true;
        $formatted = $this->perizinan->formatDataExport($results, $fields, $addNumber);
        $headings = $this->perizinan->getFieldExportHeadings($fields, $addNumber);

        $now = now()->format('Y-m-d_H-i-s');
        $filename = "perizinan_{$now}.xlsx";

        return Excel::download(new BaseExport($formatted, $headings), $filename);
    }
}
