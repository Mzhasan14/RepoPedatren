<?php

namespace App\Http\Controllers\api\PesertaDidik;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PesertaDidik\PelajarExport;
use App\Services\PesertaDidik\FilterPelajarService;

class PelajarController extends Controller
{

    private FilterPelajarService $filterController;

    public function __construct(FilterPelajarService $filterController)
    {
        $this->filterController = $filterController;
    }

    public function getAllPelajar(Request $request)
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
                // wajib punya relasi riwayat pendidikan aktif
                ->join('riwayat_pendidikan AS rp', fn($j) => $j->on('s.id', '=', 'rp.santri_id')->where('rp.status', 'aktif'))
                ->leftJoin('lembaga AS l', 'rp.lembaga_id', '=', 'l.id')
                ->leftJoin('jurusan AS j', 'rp.jurusan_id', '=', 'j.id')
                ->leftJoin('kelas AS kls', 'rp.kelas_id', '=', 'kls.id')
                ->leftJoin('rombel AS r', 'rp.rombel_id', '=', 'r.id')
                // join riwayat domisili aktif
                ->leftjoin('riwayat_domisili AS rd', fn($join) => $join->on('s.id', '=', 'rd.santri_id')->where('rd.status', 'aktif'))
                ->leftJoin('wilayah AS w', 'rd.wilayah_id', '=', 'w.id')
                // join berkas pas foto terakhir
                ->leftJoinSub($fotoLast, 'fl', fn($j) => $j->on('b.id', '=', 'fl.biodata_id'))
                ->leftJoin('berkas AS br', 'br.id', '=', 'fl.last_id')
                // join warga pesantren terakhir true (NIUP)
                ->leftJoinSub($wpLast, 'wl', fn($j) => $j->on('b.id', '=', 'wl.biodata_id'))
                ->leftJoin('warga_pesantren AS wp', 'wp.id', '=', 'wl.last_id')
                ->leftJoin('kabupaten AS kb', 'kb.id', '=', 'b.kabupaten_id')
                ->select([
                    's.id',
                    'rp.no_induk',
                    'b.nama',
                    'l.nama_lembaga',
                    'j.nama_jurusan',
                    'kls.nama_kelas',
                    'r.nama_rombel',
                    'w.nama_wilayah',
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
            $query = $this->filterController->pelajarFilters($query, $request);

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

        // Format data untuk respon JSON
        $formatted = collect($results->items())->map(fn($item) => [
            "id" => $item->id,
            "no_induk" => $item->no_induk,
            "nama" => $item->nama,
            "lembaga" => $item->nama_lembaga,
            "jurusan" => $item->nama_jurusan,
            "kelas" => $item->nama_kelas ?? '-',
            "rombel" => $item->nama_rombel ?? '-',
            "wilayah" => $item->nama_wilayah ?? '-',
            "kota_asal" => $item->kota_asal,
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

    public function pelajarExport(Request $request, FilterPelajarService $filterService)
    {
        return Excel::download(new PelajarExport($request, $filterService), 'pelajar.xlsx');
    }
    
}
