<?php

namespace App\Http\Controllers\api\PesertaDidik;

use Illuminate\Http\Request;
use App\Exports\SantriExport;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Services\FilterSantriService;

class SantriController extends Controller
{
    private FilterSantriService $filterController;

    public function __construct(FilterSantriService $filterController)
    {
        $this->filterController = $filterController;
    }

    /**
     * Get all Santri with filters and pagination
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getAllSantri(Request $request)
    {
        try {
            // 1) Ambil ID untuk jenis berkas "Pas foto"
            $pasFotoId = DB::table('jenis_berkas')
                ->where('nama_jenis_berkas', 'Pas foto')
                ->value('id');

            // 2) Subquery: foto terakhir per biodata
            $fotoLast = DB::table('berkas')
                ->select('biodata_id', DB::raw('MAX(id) AS last_id'))
                ->where('jenis_berkas_id', $pasFotoId)
                ->groupBy('biodata_id');

            // 3) Subquery: warga_pesantren terakhir per biodata (status = true)
            $wpLast = DB::table('warga_pesantren')
                ->select('biodata_id', DB::raw('MAX(id) AS last_id'))
                ->where('status', true)
                ->groupBy('biodata_id');

            // Query utama: data peserta_didik all
            $query = DB::table('santri AS s')
                ->join('biodata AS b', 's.biodata_id', '=', 'b.id')
                // wajib punya relasi riwayat domisili aktif
                ->leftjoin('riwayat_domisili AS rd', fn($join) => $join->on('s.id', '=', 'rd.santri_id')->where('rd.status', 'aktif'))
                ->leftjoin('wilayah AS w', 'rd.wilayah_id', '=', 'w.id')
                ->leftjoin('blok AS bl', 'rd.blok_id', '=', 'bl.id')
                ->leftjoin('kamar AS km', 'rd.kamar_id', '=', 'km.id')
                // join riwayat pendidikan aktif
                ->leftjoin('riwayat_pendidikan AS rp', fn($j) => $j->on('s.id', '=', 'rp.santri_id')->where('rp.status', 'aktif'))
                ->leftJoin('lembaga AS l', 'rp.lembaga_id', '=', 'l.id')
                // join berkas pas foto terakhir
                ->leftJoinSub($fotoLast, 'fl', fn($j) => $j->on('b.id', '=', 'fl.biodata_id'))
                ->leftJoin('berkas AS br', 'br.id', '=', 'fl.last_id')
                // join warga pesantren terakhir true (NIUP)
                ->leftJoinSub($wpLast, 'wl', fn($j) => $j->on('b.id', '=', 'wl.biodata_id'))
                ->leftJoin('warga_pesantren AS wp', 'wp.id', '=', 'wl.last_id')
                ->leftJoin('kabupaten AS kb', 'kb.id', '=', 'b.kabupaten_id')
                // hanya yang berstatus aktif
                ->where('s.status', 'aktif')
                ->select([
                    's.id',
                    's.nis',
                    'b.nama',
                    'wp.niup',
                    'km.nama_kamar',
                    'bl.nama_blok',
                    'l.nama_lembaga',
                    'w.nama_wilayah',
                    DB::raw('YEAR(s.tanggal_masuk) as angkatan'),
                    'kb.nama_kabupaten AS kota_asal',
                    's.created_at',
                    // ambil updated_at terbaru antar s, rp, rd
                    DB::raw("
                        GREATEST(
                            s.updated_at,
                            COALESCE(rp.updated_at, s.updated_at),
                            COALESCE(rd.updated_at, s.updated_at)
                        ) AS updated_at
                    "),
                    DB::raw("COALESCE(br.file_path, 'default.jpg') AS foto_profil"),
                ])
                ->orderBy('s.id');

            // Terapkan filter dan pagination
            $query = $this->filterController->santriFilters($query, $request);

            $perPage     = (int) $request->input('limit', 25);
            $currentPage = (int) $request->input('page', 1);
            $results     = $query->paginate($perPage, ['*'], 'page', $currentPage);
        } catch (\Throwable $e) {
            Log::error("[SantriController] Error: {$e->getMessage()}");
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

        // Format data untuk respon JSON
        $formatted = collect($results->items())->map(fn($item) => [
            "id" => $item->id,
            "nis" => $item->nis,
            "nama" => $item->nama,
            "niup" => $item->niup ?? '-',
            "kamar" => $item->nama_kamar ?? '-',
            "blok" => $item->nama_blok ?? '-',
            "lembaga" => $item->nama_lembaga ?? '-',
            "wilayah" => $item->nama_wilayah ?? '-',
            "angkatan" =>$item->angkatan,
            "kota_asal" =>$item->kota_asal,
            "tgl_update" => Carbon::parse($item->updated_at)->translatedFormat('d F Y H:i:s') ?? '-',
            "tgl_input" =>  Carbon::parse($item->created_at)->translatedFormat('d F Y H:i:s'),
            "foto_profil" => url($item->foto_profil)
        ]);

        // Kembalikan respon JSON dengan data yang sudah diformat
        return response()->json([
            "total_data"   => $results->total(),
            "current_page" => $results->currentPage(),
            "per_page"     => $results->perPage(),
            "total_pages"  => $results->lastPage(),
            "data"         => $formatted
        ]);
    }

    /**
     * Get Santri Non Domisili with filters and pagination
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getNonDomisili(Request $request)
    {
        try {
            // 1) Ambil ID untuk jenis berkas "Pas foto"
            $pasFotoId = DB::table('jenis_berkas')
                ->where('nama_jenis_berkas', 'Pas foto')
                ->value('id');

            // 2) Subquery: foto terakhir per biodata
            $fotoLast = DB::table('berkas')
                ->select('biodata_id', DB::raw('MAX(id) AS last_id'))
                ->where('jenis_berkas_id', $pasFotoId)
                ->groupBy('biodata_id');

            // 3) Subquery: warga_pesantren terakhir per biodata (status = true)
            $wpLast = DB::table('warga_pesantren')
                ->select('biodata_id', DB::raw('MAX(id) AS last_id'))
                ->where('status', true)
                ->groupBy('biodata_id');

            // Query utama: data peserta_didik all
            $query = DB::table('santri AS s')
                ->join('biodata AS b', 's.biodata_id', '=', 'b.id')
                // wajib punya relasi riwayat domisili aktif
                ->leftjoin('riwayat_domisili AS rd', fn($join) => $join->on('s.id', '=', 'rd.santri_id')->where('rd.status', 'aktif'))
                // join riwayat pendidikan aktif
                ->leftjoin('riwayat_pendidikan AS rp', fn($j) => $j->on('s.id', '=', 'rp.santri_id')->where('rp.status', 'aktif'))
                ->leftJoin('lembaga AS l', 'rp.lembaga_id', '=', 'l.id')
                // join berkas pas foto terakhir
                ->leftJoinSub($fotoLast, 'fl', fn($j) => $j->on('b.id', '=', 'fl.biodata_id'))
                ->leftJoin('berkas AS br', 'br.id', '=', 'fl.last_id')
                // join warga pesantren terakhir true (NIUP)
                ->leftJoinSub($wpLast, 'wl', fn($j) => $j->on('b.id', '=', 'wl.biodata_id'))
                ->leftJoin('warga_pesantren AS wp', 'wp.id', '=', 'wl.last_id')
                ->leftJoin('kabupaten AS kb', 'kb.id', '=', 'b.kabupaten_id')
                // hanya yang berstatus aktif
                ->where('s.status', 'aktif')
                ->where(fn($q) => $q->whereNull('rd.id')->orWhere('rd.status', '!=', 'aktif'))
                ->select([
                    's.id',
                    's.nis',
                    'b.nama',
                    'wp.niup',
                    'l.nama_lembaga',
                    DB::raw('YEAR(s.tanggal_masuk) as angkatan'),
                    'kb.nama_kabupaten AS kota_asal',
                    's.created_at',
                    // ambil updated_at terbaru antar s, rp, rd
                    DB::raw("
                        GREATEST(
                            s.updated_at,
                            COALESCE(rp.updated_at, s.updated_at),
                            COALESCE(rd.updated_at, s.updated_at)
                        ) AS updated_at
                    "),
                    DB::raw("COALESCE(br.file_path, 'default.jpg') AS foto_profil"),
                ])
                ->orderBy('s.id');

            // Terapkan filter dan pagination
            $query = $this->filterController->santriFilters($query, $request);

            $perPage     = (int) $request->input('limit', 25);
            $currentPage = (int) $request->input('page', 1);
            $results     = $query->paginate($perPage, ['*'], 'page', $currentPage);
        } catch (\Throwable $e) {
            Log::error("[NonDomisiliController] Error: {$e->getMessage()}");
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

        // Format data untuk respon JSON
        $formatted = collect($results->items())->map(fn($item) => [
            "id" => $item->id,
            "nis" => $item->nis,
            "nama" => $item->nama,
            "niup" => $item->niup ?? '-',
            "lembaga" => $item->nama_lembaga ?? '-',
            "angkatan" =>$item->angkatan,
            "kota_asal" =>$item->kota_asal,
            "tgl_update" => Carbon::parse($item->updated_at)->translatedFormat('d F Y H:i:s') ?? '-',
            "tgl_input" =>  Carbon::parse($item->created_at)->translatedFormat('d F Y H:i:s'),
            "foto_profil" => url($item->foto_profil)
        ]);

        // Kembalikan respon JSON dengan data yang sudah diformat
        return response()->json([
            "total_data"   => $results->total(),
            "current_page" => $results->currentPage(),
            "per_page"     => $results->perPage(),
            "total_pages"  => $results->lastPage(),
            "data"         => $formatted
        ]);
    }

    public function santriExport(Request $request, FilterSantriService $filterService)
    {
        return Excel::download(new SantriExport($request, $filterService), 'santri.xlsx');
    }

    // public function nonDomisiliExport(Request $request, FilterSantriService $filterService)
    // {
    //     return Excel::download(new Export($request, $filterService), 'santri_non_domisili.xlsx');
    // }
}
