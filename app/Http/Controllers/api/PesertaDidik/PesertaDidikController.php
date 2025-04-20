<?php

namespace App\Http\Controllers\api\PesertaDidik;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\Controller;
use App\Services\FilterPesertaDidikService;

class PesertaDidikController extends Controller
{
    private FilterPesertaDidikService $filterController;

    public function __construct(FilterPesertaDidikService $filterController)
    {
        $this->filterController = $filterController;
    }

    /**
     * Get all Peserta Didik with filters and pagination
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getAllPesertaDidik(Request $request): JsonResponse
    {
        try {
            // 1) Ambil ID jenis berkas 'Pas foto'
            $pasFotoId = DB::table('jenis_berkas')
                ->where('nama_jenis_berkas', 'Pas foto')
                ->value('id');

            // Subqueries: ID terakhir berkas pas foto
            $fotoLast = DB::table('berkas')
                ->select('id_biodata', DB::raw('MAX(id) AS last_id'))
                ->where('id_jenis_berkas', $pasFotoId)
                ->groupBy('id_biodata');

            // Subqueries: ID terakhir warga pesantren yang aktif
            $wpLast = DB::table('warga_pesantren')
                ->select('id_biodata', DB::raw('MAX(id) AS last_id'))
                ->where('status', true)
                ->groupBy('id_biodata');

            // 2) Bangun query utama
            $query = DB::table('peserta_didik AS pd')
                ->join('biodata AS b', 'pd.id_biodata', '=', 'b.id')
                // join pelajar atau santri aktif
                ->leftJoin('pelajar AS p', fn($j) => $j->on('p.id_peserta_didik', '=', 'pd.id')->where('p.status', 'aktif'))
                ->leftJoin('santri AS s', fn($j) => $j->on('s.id_peserta_didik', '=', 'pd.id')->where('s.status', 'aktif'))
                // join riwayat pendidikan aktif
                ->leftJoin('riwayat_pendidikan AS rp', fn($j) => $j->on('pd.id', '=', 'rp.id_peserta_didik')->where('rp.status', 'aktif'))
                ->leftJoin('lembaga AS l', 'rp.id_lembaga', '=', 'l.id')
                ->leftJoin('jurusan AS j', 'rp.id_jurusan', '=', 'j.id')
                ->leftJoin('kelas AS kls', 'rp.id_kelas', '=', 'kls.id')
                ->leftJoin('rombel AS r', 'rp.id_rombel', '=', 'r.id')
                // join riwayat santri aktif
                ->leftJoin('riwayat_domisili AS rd', fn($join) => $join->on('pd.id', '=', 'rd.id_peserta_didik')->where('rd.status', 'aktif'))
                ->leftJoin('wilayah AS w', 'rd.id_wilayah', '=', 'w.id')
                ->leftJoin('blok AS bl', 'rd.id_blok', '=', 'bl.id')
                ->leftJoin('kamar AS km', 'rd.id_kamar', '=', 'km.id')
                ->leftJoin('kabupaten AS kb', 'kb.id', '=', 'b.id_kabupaten')
                // join berkas pas foto terakhir
                ->leftJoinSub($fotoLast, 'fl', fn($j) => $j->on('b.id', '=', 'fl.id_biodata'))
                ->leftJoin('berkas AS br', 'br.id', '=', 'fl.last_id')
                // join warga pesantren terakhir (NIUP)
                ->leftJoinSub($wpLast, 'wl', fn($j) => $j->on('b.id', '=', 'wl.id_biodata'))
                ->leftJoin('warga_pesantren AS wp', 'wp.id', '=', 'wl.last_id')
                ->where(fn($q) => $q->whereNotNull('p.id')->orWhereNotNull('s.id'))
                ->select([
                    'pd.id',
                    DB::raw("COALESCE(b.nik, b.no_passport) AS identitas"),
                    'b.nama',
                    'wp.niup',
                    'l.nama_lembaga',
                    'w.nama_wilayah',
                    DB::raw("CONCAT('Kab. ', kb.nama_kabupaten) AS kota_asal"),
                    'pd.created_at',
                    'b.updated_at',
                    DB::raw("COALESCE(br.file_path, 'default.jpg') AS foto_profil"),
                ])
                ->orderBy('pd.id');

            // Terapkan filter dan pagination
            $query = $this->filterController->applyAllFilters($query, $request);

            $perPage     = (int) $request->input('limit', 25);
            $currentPage = (int) $request->input('page', 1);
            $results     = $query->paginate($perPage, ['*'], 'page', $currentPage);
        } catch (\Throwable $e) {
            Log::error("[PelajarController] Error: {$e->getMessage()}");
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

        $formatted = collect($results->items())->map(fn($item) => [
            'id_peserta_didik' => $item->id,
            'nik_or_passport'  => $item->identitas,
            'nama'             => $item->nama,
            'niup'             => $item->niup ?? '-',
            'lembaga'          => $item->nama_lembaga ?? '-',
            'wilayah'          => $item->nama_wilayah ?? '-',
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
    
    // public function getAllPesertaDidik(Request $request)
    // {
    //     try {
    //         // Eloquent query with relationships and scopes
    //         $query = PesertaDidik::with([
    //             'biodata.kabupaten',
    //             'activePelajar',
    //             'activeRiwayatPendidikan.lembaga',
    //             'activeRiwayatPendidikan.jurusan',
    //             'activeRiwayatPendidikan.kelas',
    //             'activeRiwayatPendidikan.rombel',
    //             'activeSantri',
    //             'activeRiwayatDomisili.wilayah',
    //             'activeRiwayatDomisili.blok',
    //             'activeRiwayatDomisili.kamar',
    //             'latestWargaPesantren',
    //             'latestPasFoto'
    //         ])
    //             ->where('status', true);

    //         $query = $this->filterUmum->applyCommonFilters($query, $request);
    //         $query = $this->filterController->applyWilayahFilter($query, $request);
    //         $query = $this->filterController->applyLembagaPendidikanFilter($query, $request);
    //         $query = $this->filterController->applyStatusPesertaFilter($query, $request);
    //         $query = $this->filterController->applyStatusWargaPesantrenFilter($query, $request);
    //         $query = $this->filterController->applySorting($query, $request);
    //         $query = $this->filterController->applyAngkatanPelajar($query, $request);
    //         $query = $this->filterController->applyPhoneNumber($query, $request);
    //         $query = $this->filterController->applyPemberkasan($query, $request);


    //         // Apply custom filter scopes (defined in model or a trait)
    //         // ->filterUmum($request)
    //         // ->filterWilayah($request)
    //         // ->filterLembagaPendidikan($request)
    //         // ->filterStatusPeserta($request)
    //         // ->filterStatusWargaPesantren($request)
    //         // ->filterSorting($request)
    //         // ->filterAngkatanPelajar($request)
    //         // ->filterPhoneNumber($request)
    //         // ->filterPemberkasan($request);

    //         // Pagination defaults
    //         $perPage     = $request->input('limit', 25);
    //         $currentPage = $request->input('page', 1);

    //         // Paginate results
    //         $results = $query->paginate($perPage, ['*'], 'page', $currentPage);
    //     } catch (\Exception $e) {
    //         Log::error("Error in getAllPesertaDidik: " . $e->getMessage());
    //         return response()->json([
    //             'status'  => 'error',
    //             'message' => 'Terjadi kesalahan pada server'
    //         ], 500);
    //     }

    //     // If no data found
    //     if ($results->isEmpty()) {
    //         return response()->json([
    //             'status'  => 'success',
    //             'message' => 'Data Kosong',
    //             'data'    => []
    //         ], 200);
    //     }

    //     // Format data output
    //     $formattedData = $results->getCollection()->map(function ($item) {
    //         $biodata     = $item->biodata;
    //         $riwayat     = $item->activeRiwayatPendidikan;
    //         $domisili    = $item->activeRiwayatDomisili;
    //         $berkas      = $item->latestPasFoto;
    //         $warga       = $item->latestWargaPesantren;

    //         return [
    //             'id_peserta_didik'  => $item->id,
    //             'nik_or_passport'   => $biodata->nik ?? $biodata->no_passport,
    //             'nama'              => $biodata->nama,
    //             'niup'              => $warga->niup ?? '-',
    //             'lembaga'           => $riwayat->lembaga->nama_lembaga ?? '-',
    //             'wilayah'           => $domisili->wilayah->nama_wilayah ?? '-',
    //             'kota_asal'         => 'Kab. ' . ($biodata?->kabupaten?->nama_kabupaten ?? '-'),
    //             'tgl_update'        => $item->updated_at
    //                 ? Carbon::parse($item->updated_at)->translatedFormat('d F Y H:i:s')
    //                 : '-',
    //             'tgl_input'         => $item->created_at
    //                 ? Carbon::parse($item->created_at)->translatedFormat('d F Y H:i:s')
    //                 : '-',
    //             'foto_profil'       => url($berkas->file_path ?? 'default.jpg'),
    //         ];
    //     });

    //     // Return JSON response
    //     return response()->json([
    //         'total_data'   => $results->total(),
    //         'current_page' => $results->currentPage(),
    //         'per_page'     => $results->perPage(),
    //         'total_pages'  => $results->lastPage(),
    //         'data'         => $formattedData
    //     ]);
    // }




}
