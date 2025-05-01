<?php

namespace App\Http\Controllers\api\PesertaDidik;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\Controller;
use App\Services\PesertaDidik\FilterAlumniService;

class AlumniController extends Controller
{
    private FilterAlumniService $filterController;

    public function __construct(FilterAlumniService $filterController)
    {
        $this->filterController = $filterController;
    }

    public function alumni(Request $request)
    {
        try {
            // 1) Sub‐query: tanggal_keluar riwayat_pendidikan alumni terakhir per santri
            $riwayatLast = DB::table('riwayat_pendidikan')
                ->select('santri_id', DB::raw('MAX(tanggal_keluar) AS max_tanggal_keluar'))
                ->where('status', 'alumni')
                ->groupBy('santri_id');

            // 2) Sub‐query: santri alumni terakhir
            $santriLast = DB::table('santri')
                ->select('id', DB::raw('MAX(id) AS last_id'))
                ->where('status', 'alumni')
                ->groupBy('id');

            // 3) Ambil ID untuk jenis berkas "Pas foto"
            $pasFotoId = DB::table('jenis_berkas')
                ->where('nama_jenis_berkas', 'Pas foto')
                ->value('id');

            // 4) Subquery: foto terakhir per biodata
            $fotoLast = DB::table('berkas')
                ->select('biodata_id', DB::raw('MAX(id) AS last_id'))
                ->where('jenis_berkas_id', $pasFotoId)
                ->groupBy('biodata_id');

            // 5) Subquery: warga_pesantren terakhir per biodata (status = true)
            $wpLast = DB::table('warga_pesantren')
                ->select('biodata_id', DB::raw('MAX(id) AS last_id'))
                ->where('status', true)
                ->groupBy('biodata_id');

            // 5) Query utama
            $query = DB::table('santri as s')
                // Biodata dasar
                ->join('biodata as b', 's.biodata_id', '=', 'b.id')

                // Riwayat_pendidikan alumni terakhir → lembaga
                ->leftJoinSub($riwayatLast, 'lr', fn($j) => $j->on('lr.santri_id', '=', 's.id'))
                ->leftJoin('riwayat_pendidikan as rp', fn($j) => $j->on('rp.santri_id', '=', 'lr.santri_id')->on('rp.tanggal_keluar', '=', 'lr.max_tanggal_keluar'))
                ->leftJoin('lembaga as l', 'rp.lembaga_id', '=', 'l.id')

                // Domisili alumni terakhir → wilayah/blok/kamar
                ->leftJoinSub($santriLast, 'ld', fn($j) => $j->on('ld.id', '=', 's.id'))
                // join berkas pas foto terakhir
                ->leftJoinSub($fotoLast, 'fl', fn($j) => $j->on('b.id', '=', 'fl.biodata_id'))
                ->leftJoin('berkas AS br', 'br.id', '=', 'fl.last_id')
                // join warga pesantren terakhir true (NIUP)
                ->leftJoinSub($wpLast, 'wl', fn($j) => $j->on('b.id', '=', 'wl.biodata_id'))
                ->leftJoin('warga_pesantren AS wp', 'wp.id', '=', 'wl.last_id')
                ->leftJoin('kabupaten AS kb', 'kb.id', '=', 'b.kabupaten_id')
                // Filter: hanya santri alumni
                ->where(fn($q) => $q->where('s.status', 'alumni')->orWhere('rp.status', 'alumni'))

                ->select([
                    's.id',
                    'wp.niup',
                    'b.nama',
                    DB::raw('YEAR(rp.tanggal_keluar)  AS tahun_keluar_pelajar'),
                    DB::raw('YEAR(s.tanggal_masuk)  AS tahun_masuk_santri'),
                    DB::raw('YEAR(s.tanggal_keluar) AS tahun_keluar_santri'),
                    'l.nama_lembaga',
                    'kb.nama_kabupaten AS kota_asal',
                    's.created_at',
                    // ambil updated_at terbaru antar s, rp, rd
                    DB::raw("
                        GREATEST(
                            s.updated_at,
                            COALESCE(rp.updated_at, s.updated_at)
                        ) AS updated_at
                    "),
                    DB::raw("COALESCE(br.file_path, 'default.jpg') AS foto_profil"),
                ])
                ->orderBy('s.id');



            // Terapkan filter dan pagination
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

        // Format data output agar mudah dipahami
        $formatted = collect($results->items())->map(fn($item) => [
            "id" => $item->id,
            "nama" => $item->nama,
            "niup" => $item->niup ?? '-',
            "lembaga" => $item->nama_lembaga ?? '-',
            "tahun_keluar_pendidikan" => $item->tahun_keluar_pelajar ?? '-',
            "tahun_masuk_santri" => $item->tahun_masuk_santri ?? '-',
            "tahun_keluar_santri" => $item->tahun_keluar_santri ?? '-',
            "kota_asal" => $item->kota_asal,
            "tgl_update"       => Carbon::parse($item->updated_at)->translatedFormat('d F Y H:i:s') ?? '-',
            "tgl_input"        => Carbon::parse($item->created_at)->translatedFormat('d F Y H:i:s'),
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
}
