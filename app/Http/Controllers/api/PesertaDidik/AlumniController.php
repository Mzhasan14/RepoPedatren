<?php

namespace App\Http\Controllers\api\PesertaDidik;

use Illuminate\Http\Request;
use App\Exports\PesertaDidik\AlumniExport;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Services\PesertaDidik\AlumniService;
use App\Http\Requests\PesertaDidik\AlumniRequest;
use App\Services\PesertaDidik\Filters\FilterAlumniService;

class AlumniController extends Controller
{
    private AlumniService $alumniService;
    private FilterAlumniService $filterController;

    public function __construct(AlumniService $alumniService, FilterAlumniService $filterController)
    {
        $this->alumniService = $alumniService;
        $this->filterController = $filterController;
    }

    public function alumni(Request $request)
    {
        try {
            $query = $this->alumniService->getAllAlumni($request);
            $query = $this->filterController->alumniFilters($query, $request);

            $perPage     = (int) $request->input('limit', 25);
            $currentPage = (int) $request->input('page', 1);
            $results     = $query->paginate($perPage, ['*'], 'page', $currentPage);
        } catch (\Throwable $e) {
            Log::error("[AlumniController] Error: {$e->getMessage()}");
            return response()->json([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan pada server',
            ], 500);
        }

        if ($results->isEmpty()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Data kosong',
                'data'    => [],
            ], 200);
        }

        $formatted = $this->alumniService->formatData($results);

        return response()->json([
            "total_data"   => $results->total(),
            "current_page" => $results->currentPage(),
            "per_page"     => $results->perPage(),
            "total_pages"  => $results->lastPage(),
            "data"         => $formatted
        ]);
    }

    public function setAlumniSantri(AlumniRequest $request)
    {
        try {
            $ids = $request->validated()['santri_ids'];

            $alumni = $this->alumniService->setAlumniSantri($ids);

            return response()->json([
                'success' => true,
                'message' => "$alumni santri berhasil diubah menjadi alumni."
            ], 200);
        } catch (\Exception $e) {
            
            Log::error('Gagal update santri ke alumni: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server.',
            ], 500);
        }
    }

    public function setAlumniPelajar(AlumniRequest $request)
    {
        try {
            $ids = $request->validated()['santri_ids'];

            $alumni = $this->alumniService->setAlumniPelajar($ids);

            return response()->json([
                'success' => true,
                'message' => "$alumni pelajar berhasil diubah menjadi alumni.",
                'updated_count' => $alumni
            ], 200);
        } catch (\Exception $e) {
            
            Log::error('Gagal update pelajar ke alumni: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server.',
            ], 500);
        }
    }

     public function alumniExport()
    {
        return Excel::download(new AlumniExport, 'alumni.xlsx');
    }
}
