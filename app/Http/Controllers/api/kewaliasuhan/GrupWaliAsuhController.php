<?php

namespace App\Http\Controllers\api\kewaliasuhan;

use id;
use App\Models\Biodata;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Resources\PdResource;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\Kewaliasuhan\grupWaliasuhRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Kewaliasuhan\Wali_asuh;
use App\Models\Kewaliasuhan\Grup_WaliAsuh;
use App\Services\Kewaliasuhan\GrupWaliasuhService;
use App\Services\Kewaliasuhan\Filters\FilterGrupWaliasuhService;
use Illuminate\Support\Arr;

class GrupWaliAsuhController extends Controller
{

    private GrupWaliasuhService $grupWaliasuhService;
    private FilterGrupWaliasuhService $filterGrupWaliasuhService;

    public function __construct(GrupWaliasuhService $grupWaliasuhService, FilterGrupWaliasuhService $filterGrupWaliasuhService){
        $this->grupWaliasuhService = $grupWaliasuhService;
        $this->filterGrupWaliasuhService = $filterGrupWaliasuhService;
    }

    public function getAllGrupWaliasuh(Request $request)
    {
        $query = $this->grupWaliasuhService->getAllGrupWaliasuh($request);
        $query = $this->filterGrupWaliasuhService->GrupWaliasuhFIlters($query, $request);

        $perPage     = (int) $request->input('limit', 25);
        $currentPage = (int) $request->input('page', 1);
        $results     = $query->paginate($perPage, ['*'], 'page', $currentPage);

        if ($results->isEmpty()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Data kosong',
                'data'    => [],
            ], 200);
        }

        $formatted = $this->grupWaliasuhService->formatData($results);

        return response()->json([
            "total_data"   => $results->total(),
            "current_page" => $results->currentPage(),
            "per_page"     => $results->perPage(),
            "total_pages"  => $results->lastPage(),
            "data"         => $formatted
        ]);
    }

    public function store(grupWaliasuhRequest $request)
    {
        try {
            $validated = $request->validated();
            $result = $this->grupWaliasuhService->store($validated);
            if (!$result['status']) {
                return response()->json([
                    'message' => $result['message']
                ], 200);
            }
            return response()->json([
                'message' => 'Data berhasil ditambah',
                'data' => $result['data']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Update grup
    public function update(grupWaliasuhRequest $request, $id)
    {
        $result = $this->grupWaliasuhService->update($request->validated(), $id);
        if (!$result['status']) {
            return response()->json([
                'message' => $result['message'] ??
                    'Data tidak ditemukan.'
            ], 200);
        }
        return response()->json([
            'message' => 'Grup waliasuh berhasil diperbarui',
            'data' => $result['data']
        ]);
    }

    public function getGrup()
    {
        return response()->json(
            Grup_WaliAsuh::select('id', 'nama_grup')->get()
        );
    }

    public function destroy($id)
    {
        return DB::transaction(function () use ($id) {
            if (!Auth::id()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Pengguna tidak terautentikasi'
                ], 401);
            }

            $grup = Grup_WaliAsuh::withTrashed()->find($id);

            if (!$grup) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data grup wali asuh tidak ditemukan'
                ], 404);
            }

            if ($grup->trashed()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data grup sudah dihapus sebelumnya'
                ], 410);
            }

            // Cek apakah grup masih memiliki anggota aktif
            $hasActiveMembers = Wali_asuh::where('id_grup_wali_asuh', $id)
                ->where('status', true)
                ->exists();

            if ($hasActiveMembers) {
                return response()->json([
                    'status' => false,
                    'message' => 'Tidak dapat menghapus grup yang masih memiliki anggota aktif'
                ], 400);
            }

            // Soft delete
            $grup->delete();

            // Log activity
            activity('grup_wali_asuh_delete')
                ->performedOn($grup)
                ->withProperties([
                    'deleted_at' => now(),
                    'deleted_by' => Auth::id()
                ])
                ->event('delete_grup_wali_asuh')
                ->log('Grup wali asuh berhasil dihapus (soft delete)');

            return response()->json([
                'status' => true,
                'message' => 'Grup wali asuh berhasil dihapus',
                'data' => [
                    'deleted_at' => $grup->deleted_at
                ]
            ]);
        });
    }

    /**
     * Display a listing of the resource.
     */
    // public function index()
    // {
    //      $grupWaliAsuh = Grup_WaliAsuh::Active()->latest()->paginate(5);
    //     return new PdResource(true, 'list grup wali asuh', $grupWaliAsuh);
    // }

    // /**
    //  * Store a newly created resource in storage.
    //  */
    // public function store(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'nama_grup' => 'required',
    //         'created_by' => 'required',
    //         'status' => 'nullable',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json($validator->errors(), 422);
    //     }

    //     $grupWaliAsuh = Grup_WaliAsuh::create($validator->validated());

    //     return new PdResource(true, 'Data berhasil ditambah', $grupWaliAsuh);
    // }

    // /**
    //  * Display the specified resource.
    //  */
    // public function show(string $id)
    // {
    //     $grupWaliAsuh = Grup_WaliAsuh::findOrFail($id);
    //     return new PdResource(true, 'Detail data', $grupWaliAsuh);
    // }

    // /**
    //  * Update the specified resource in storage.
    //  */
    // public function update(Request $request, string $id)
    // {
    //     $grupWaliAsuh = Grup_WaliAsuh::findOrFail($id);

    //     $validator = Validator::make($request->all(), [
    //         'nama_grup' => 'required',
    //         'updated_by' => 'nullable',
    //         'status' => 'nullable'
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json($validator->errors(), 422);
    //     }

    //     $grupWaliAsuh->update($request->validated());
    //     return new PdResource(true, 'Data berhasil diubah', $grupWaliAsuh);
    // }

    // /**
    //  * Remove the specified resource from storage.
    //  */
    // public function destroy(string $id)
    // {
    //     $grupWaliAsuh = Grup_WaliAsuh::findOrFail($id);
    //     $grupWaliAsuh->delete();
    //     return new PdResource(true,'Data berhasil dihapus',null);
    // }


    // public function kewaliasuhan() {

    //      $grupKewaliasuhan = Grup_WaliAsuh::join('wali_asuh as wa1','grup_wali_asuh.id','=','wa1.id_grup_wali_asuh')
    //      ->join('wilayah','grup_wali_asuh.id_wilayah','=','wilayah.id')
    //      ->join('santri','santri.nis','=','wa1.nis')
    //      ->join('peserta_didik','santri.id_peserta_didik','=','peserta_didik.id')
    //      ->join('biodata','peserta_didik.id_biodata','=','biodata.id')
         
    //      ->select(
    //         'grup_wali_asuh.id',
    //         'grup_wali_asuh.nama_grup',
    //         'santri.nis as Nis_WaliAsuh',
    //         'biodata.nama as Nama_WaliAsuh',
    //         'wilayah.nama_wilayah',
    //         'grup_wali_asuh.updated_by as Tanggal Update Group'

    //      )->get();

    //     return new PdResource(true, 'list grup kewaliasuhan', $grupKewaliasuhan);
    // }
}
