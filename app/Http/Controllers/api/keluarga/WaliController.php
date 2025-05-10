<?php

namespace App\Http\Controllers\api\keluarga;

use App\Models\OrangTuaWali;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Services\Keluarga\DetailWaliService;
use App\Services\Keluarga\Filters\FilterWaliService;

class WaliController extends Controller
{

    private DetailWaliService $detailWaliService;
    private FilterWaliService $filterController;

    public function __construct(FilterWaliService $filterController, DetailWaliService $detailWaliService) {
        $this->filterController = $filterController;
        $this->detailWaliService = $detailWaliService;
    }

    /**
     * Get all Wali with filters and pagination
     *
     * @param Request $request
     * @return JsonResponse
     */

     public function getAllWali(Request $request):JsonResponse {
        // 1) Ambil ID untuk jenis berkas "Pas foto"
        $pasFotoId = DB::table('jenis_berkas')
            ->where('nama_jenis_berkas', 'Pas foto')
            ->value('id');

        // 2) Subquery: foto terakhir per biodata
        $fotoLast = DB::table('berkas')
            ->select('biodata_id', DB::raw('MAX(id) AS last_id'))
            ->where('jenis_berkas_id', $pasFotoId)
            ->groupBy('biodata_id');

        // 3) Query utama: data orang_tua all
        $query = DB::table('orang_tua_wali AS o')
            ->join('biodata AS b', 'o.id_biodata', '=', 'b.id')
            // join berkas pas foto terakhir
            ->leftJoinSub($fotoLast, 'fl', fn($j) => $j->on('b.id', '=', 'fl.biodata_id'))
            ->leftJoin('berkas AS br', 'br.id', '=', 'fl.last_id')
            ->join('hubungan_keluarga AS hk', 'hk.id', '=', 'o.id_hubungan_keluarga')
            ->join('keluarga AS kel', 'b.id', '=', 'kel.id_biodata') //dari orangtua ke tabel keluarga
            ->join('keluarga as ka', 'kel.no_kk','=', 'ka.no_kk') //dari keluarga ke keluarga lainnya
            ->join('biodata as ba', 'ka.id_biodata','=','ba.id') //dari keluarga ke anak
            ->leftJoin('kabupaten AS  kb', 'kb.id', '=', 'b.kabupaten_id')
            // hanya yang berstatus aktif
            ->where(fn($q) => $q->where([
                ['o.status', true],
                ['o.wali', true]
            ]))
            ->select([
                'o.id',
                DB::raw("COALESCE(b.nik, b.no_passport) AS identitas"),
                'b.nama',
                'b.no_telepon AS telepon_1',
                'b.no_telepon_2 AS telepon_2',
                'kb.nama_kabupaten AS kota_asal',
                'o.created_at',
                // ambil updated_at terbaru antar s, rp, rd
                DB::raw("
                        GREATEST(
                            o.updated_at,
                            hk.updated_at,
                            kel.updated_at
                        ) AS updated_at
                    "),
                DB::raw("COALESCE(br.file_path, 'default.jpg') AS foto_profil"),
            ])
            ->groupBy([
                'o.id',
                'b.nik',
                'b.no_passport',
                'b.nama',
                'b.no_telepon',
                'b.no_telepon_2',
                'kb.nama_kabupaten',
                'o.created_at',
                'o.updated_at',
                'hk.updated_at',
                'kel.updated_at',
                'br.file_path'
            ])
            ->orderBy('o.id');

        // Terapkan filter dan pagination
        $query = $this->filterController->applyAllFilters($query, $request);


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

        $formatted = collect($results->items())->map(fn($item) => [
            'id'               => $item->id,
            'nik_or_passport'  => $item->identitas,
            'nama'             => $item->nama,
            'telepon_1'             => $item->telepon_1,
            'telepon_2'          => $item->telepon_2,
            'kota_asal'        => $item->kota_asal,
            'tgl_update'       => Carbon::parse($item->updated_at)->translatedFormat('d F Y H:i:s') ?? '-',
            'tgl_input'        => Carbon::parse($item->created_at)->translatedFormat('d F Y H:i:s'),
            'foto_profil'      => url($item->foto_profil),
        ]);

        return response()->json([
            'total_data'   => $results->total(),
            'current_page' => $results->currentPage(),
            'per_page'     => $results->perPage(),
            'total_pages'  => $results->lastPage(),
            'data'         => $formatted,
        ]);
     }

    public function getDetailWali(string $WaliId)
    {
        $wali = OrangTuaWali::find($WaliId);
        if (!$wali) {
            return response()->json([
                'status' => 'error',
                'message' => 'ID Wali tidak ditemukan',
                'data' => []
            ], 404);
        }

        $data = $this->detailWaliService->getDetailWali($WaliId);

        return response()->json([
            'status' => true,
            'data'    => $data,
        ], 200);
    }
    /**
     * Display a listing of the resource.
     */
    // public function index()
    // {
    //     //
    // }

    // /**
    //  * Store a newly created resource in storage.
    //  */
    // public function store(Request $request)
    // {
    //     //
    // }

    // /**
    //  * Display the specified resource.
    //  */
    // public function show(string $id)
    // {
    //     //
    // }

    // /**
    //  * Update the specified resource in storage.
    //  */
    // public function update(Request $request, string $id)
    // {
    //     //
    // }

    // /**
    //  * Remove the specified resource from storage.
    //  */
    // public function destroy(string $id)
    // {
    //     //
    // }
}
