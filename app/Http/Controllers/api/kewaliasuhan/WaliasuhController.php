<?php

namespace App\Http\Controllers\api\kewaliasuhan;

use App\Models\Biodata;
use App\Exports\BaseExport;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Kewaliasuhan\Wali_asuh;
use App\Models\Kewaliasuhan\Kewaliasuhan;
use App\Services\Kewaliasuhan\WaliasuhService;
use App\Http\Requests\Kewaliasuhan\waliAsuhRequest;
use App\Services\Kewaliasuhan\DetailWaliasuhService;
use App\Http\Requests\Kewaliasuhan\KeluarWaliasuhRequest;
use App\Services\Kewaliasuhan\Filters\FilterWaliasuhService;

class WaliasuhController extends Controller
{
    private WaliasuhService $waliasuhService;

    private FilterWaliasuhService $filterWaliasuhService;

    private DetailWaliasuhService $detailWaliasuhService;

    public function __construct(WaliasuhService $waliasuhService, FilterWaliasuhService $filterWaliasuhService, DetailWaliasuhService $detailWaliasuhService)
    {
        $this->waliasuhService = $waliasuhService;
        $this->filterWaliasuhService = $filterWaliasuhService;
        $this->detailWaliasuhService = $detailWaliasuhService;
    }

    public function getAllWaliasuh(Request $request)
    {
        $query = $this->waliasuhService->getAllWaliasuh($request);
        $query = $this->filterWaliasuhService->WaliasuhFilters($query, $request);

        $perPage = (int) $request->input('limit', 25);
        $currentPage = (int) $request->input('page', 1);
        $results = $query->paginate($perPage, ['*'], 'page', $currentPage);

        if ($results->isEmpty()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Data kosong',
                'data' => [],
            ], 200);
        }

        $formatted = $this->waliasuhService->formatData($results);

        return response()->json([
            'total_data' => $results->total(),
            'current_page' => $results->currentPage(),
            'per_page' => $results->perPage(),
            'total_pages' => $results->lastPage(),
            'data' => $formatted,
        ]);
    }

    public function getDetailWaliasuh(string $bioId)
    {
        $waliasuh = Biodata::find($bioId);
        if (! $waliasuh) {
            return response()->json([
                'status' => 'error',
                'message' => 'ID wali asuh tidak ditemukan',
                'data' => [],
            ], 404);
        }

        $data = $this->detailWaliasuhService->getDetailWaliasuh($bioId);

        return response()->json([
            'status' => true,
            'data' => $data,
        ], 200);
    }

    public function index($id): JsonResponse
    {
        try {
            $result = $this->waliasuhService->index($id);

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
            Log::error('Gagal ambil data waliasuh: '.$e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat menampilkan data.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(waliAsuhRequest $request, $bioId): JsonResponse
    {
        try {
            $validated = $request->validated();
            $result = $this->waliasuhService->store($validated, $bioId);

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
            Log::error('Gagal tambah waliasuh: '.$e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $result = $this->waliasuhService->show($id);

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
            Log::error('Gagal ambil detail Waliasuh: '.$e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat menampilkan data.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(waliAsuhRequest $request, $id)
    {
        $result = $this->waliasuhService->update($request->validated(), $id);
        if (! $result['status']) {
            return response()->json([
                'message' => $result['message'] ??
                    'Data tidak ditemukan.',
            ], 200);
        }

        return response()->json([
            'message' => 'waliasuh berhasil diperbarui',
            'data' => $result['data'],
        ]);
    }

    public function keluarWaliasuh(KeluarWaliasuhRequest $request, $id): JsonResponse
    {
        try {
            $validated = $request->validated();
            $result = $this->waliasuhService->keluarWaliasuh($validated, $id);

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
            Log::error('Gagal keluar khadam: '.$e->getMessage());

            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        DB::transaction(function () use ($id) {
            $waliasuh = Wali_Asuh::findOrFail($id);
            if (! $waliasuh) {
                return response()->json([
                    'success' => false,
                    'message' => 'wali asuh tidak ditemukan',
                ], 404);
            }
            $originalAttributes = $waliasuh->getAttributes();
            $relations = Kewaliasuhan::where('id_wali_asuh', $id)->get();
            // 1. Nonaktifkan semua relasi
            Kewaliasuhan::where('id_wali_asuh', $id)
                ->update([
                    'tanggal_berakhir' => now(),
                    'status' => false,
                    'deleted_at' => now(),
                    'deleted_by' => Auth::id(),
                ]);

            // 2. Nonaktifkan wali asuh
            Wali_Asuh::where('id', $id)
                ->update([
                    'id_grup_wali_asuh' => null,
                    'status' => false,
                    'deleted_at' => now(),
                    'deleted_by' => Auth::id(),
                ]);

            activity('anak_asuh_delete')
                ->performedOn($waliasuh)
                ->withProperties([
                    'old_attributes' => $originalAttributes,
                    'deleted_by' => Auth::id(),
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'affected_relations' => $relations->pluck('id'), // ID relasi yang dinonaktifkan
                ])
                ->event('nonaktif_wali_asuh')
                ->log('Wali asuh dinonaktifkan beserta semua relasinya');

            // Log untuk setiap relasi yang dinonaktifkan
            foreach ($relations as $relation) {
                activity('kewaliasuhan_delete')
                    ->performedOn($relation)
                    ->withProperties([
                        'id_wali_asuh' => $relation->id_wali_asuh,
                        'id_anak_asuh' => $relation->id_anak_asuh,
                        'deleted_by' => Auth::id(),
                    ])
                    ->event('nonaktif_relasi_wali_asuh')
                    ->log('Relasi kewaliasuhan dinonaktifkan karena wali asuh dinonaktifkan');
            }
        });

        return response()->json(['message' => 'Wali asuh dihapus']);

    }

    public function getWaliasuh()
    {
        $data = Wali_Asuh::with([
            'santri.biodata:id,nama',
            'grupWaliAsuh:id,nama_grup',
        ])
            ->select('id', 'id_santri', 'id_grup_wali_asuh')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'nama' => $item->santri->biodata->nama ?? null,
                    'nama_grup' => $item->grupWaliAsuh->nama_grup ?? null,
                ];
            });

        return response()->json($data);
    }

    public function exportExcel(Request $request)
    {
        $defaultExportFields = [
            'nis',
            'nama',
            'nama_kamar',
            'nama_blok',
            'nama_wilayah',
            'grup_wali_asuh',
            'angkatan',
            'kota_asal',
            'created_at',
            'updated_at',
        ];

        $columnOrder = [
            'nis',
            'nama',
            'nama_kamar',
            'nama_blok',
            'nama_wilayah',
            'grup_wali_asuh',
            'angkatan',
            'kota_asal',
            'created_at',
            'updated_at',
        ];

        // Ambil kolom dari checkbox user (opsional)
        $optionalFields = $request->input('fields', []);

        // Gabungkan default dan optional (hindari duplikat & atur urutan)
        $fields = array_unique(array_merge($defaultExportFields, $optionalFields));
        $fields = array_values(array_intersect($columnOrder, $fields));

        // Ambil query dari service
        $query = $this->waliasuhService->getExportWaliasuhQuery($fields, $request);

        // Ambil semua atau limit berdasarkan request
        $results = $request->input('all') === 'true'
            ? $query->get()
            : $query->limit((int) $request->input('limit', 100))->get();

        // Format data & heading
        $addNumber = true;
        $formatted = $this->waliasuhService->formatDataExportWaliasuh($results, $fields, $addNumber);
        $headings = $this->waliasuhService->getFieldExportWaliasuhHeadings($fields, $addNumber);

        $filename = 'wali_asuh_' . now()->format('Y-m-d_H-i-s') . '.xlsx';

        return Excel::download(new \App\Exports\BaseExport($formatted, $headings), $filename);
    }
}
