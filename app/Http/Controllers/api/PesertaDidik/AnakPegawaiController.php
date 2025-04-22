<?php

namespace App\Http\Controllers\api\PesertaDidik;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Services\FilterPesertaDidikService;

class AnakPegawaiController extends Controller
{
    private FilterPesertaDidikService $filterController;

    public function __construct(FilterPesertaDidikService $filterController)
    {
        $this->filterController = $filterController;
    }

    public function getAllAnakPegawai(Request $request): JsonResponse
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

            // 6) Derived table: hitung berapa pegawai dalam setiap no_kk (hanya status=true)
            $familyPegawai = DB::table('keluarga AS k2')
                ->join('pegawai AS p', function ($j) {
                    $j->on('p.id_biodata', '=', 'k2.id_biodata')
                        ->where('p.status', true);
                })
                ->select('k2.no_kk', DB::raw('COUNT(*) AS peg_count'))
                ->groupBy('k2.no_kk');

            // Query utama: data peserta_didik all
            $query = DB::table('santri AS s')
                ->join('biodata AS b', 's.biodata_id', '=', 'b.id')
                // Ambil no_kk santri
                ->join('keluarga AS k', 'k.id_biodata', '=', 'b.id')

                // ───── JOIN untuk cari parent ─────
                // 1) Keluarga orang tua dengan no_kk yang sama
                ->join('keluarga AS parent_k', function ($join) {
                    $join->on('parent_k.no_kk', '=', 'k.no_kk');
                })
                // 2) Hubungkan ke tabel orang_tua_wali
                ->join('orang_tua_wali AS otw', 'otw.id_biodata', '=', 'parent_k.id_biodata')
                // 3) Hanya yang ayah/ibu
                ->join('hubungan_keluarga AS hk', function ($join) {
                    $join->on('hk.id', '=', 'otw.id_hubungan_keluarga')
                        ->whereIn('hk.nama_status', ['ayah', 'ibu']);
                })
                // 4) Pastikan parent itu pegawai aktif
                ->join('pegawai AS p', function ($join) {
                    $join->on('p.id_biodata', '=', 'otw.id_biodata')
                        ->where('p.status', true);
                })
                // join riwayat pendidikan aktif
                ->leftjoin('riwayat_pendidikan AS rp', fn($j) => $j->on('s.id', '=', 'rp.santri_id')->where('rp.status', 'aktif'))
                ->leftJoin('lembaga AS l', 'rp.lembaga_id', '=', 'l.id')
                ->leftJoin('jurusan AS j', 'rp.jurusan_id', '=', 'j.id')
                ->leftJoin('kelas AS kls', 'rp.kelas_id', '=', 'kls.id')
                ->leftJoin('rombel AS r', 'rp.rombel_id', '=', 'r.id')
                // join riwayat domisili aktif
                ->leftjoin('riwayat_domisili AS rd', fn($join) => $join->on('s.id', '=', 'rd.santri_id')->where('rd.status', 'aktif'))
                ->leftJoin('wilayah AS w', 'rd.wilayah_id', '=', 'w.id')
                ->leftJoin('blok AS bl', 'rd.blok_id', '=', 'bl.id')
                ->leftJoin('kamar AS km', 'rd.kamar_id', '=', 'km.id')
                // join berkas pas foto terakhir
                ->leftJoinSub($fotoLast, 'fl', fn($j) => $j->on('b.id', '=', 'fl.biodata_id'))
                ->leftJoin('berkas AS br', 'br.id', '=', 'fl.last_id')
                // join warga pesantren terakhir true (NIUP)
                ->leftJoinSub($wpLast, 'wl', fn($j) => $j->on('b.id', '=', 'wl.biodata_id'))
                ->leftJoin('warga_pesantren AS wp', 'wp.id', '=', 'wl.last_id')
                ->leftJoin('kabupaten AS kb', 'kb.id', '=', 'b.kabupaten_id')
                // hanya yang berstatus aktif
                ->where(fn($q) => $q->where('s.status', 'aktif')
                    ->orWhere('rp.status', '=', 'aktif'))
                // Hindari duplikasi baris jika ayah+ibu keduanya pegawai:
                ->distinct('s.id')
                ->select([
                    's.id',
                    DB::raw("COALESCE(b.nik, b.no_passport) AS identitas"),
                    'b.nama',
                    'wp.niup',
                    'l.nama_lembaga',
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
                // Order By Yang Status lengkap
                // ->orderByRaw('(CASE WHEN s.status = "aktif" AND rp.status = "aktif" THEN 1 ELSE 0 END) DESC')
                ->orderBy('s.id');

            // Terapkan filter dan pagination
            $query = $this->filterController->applyAllFilters($query, $request);

            $perPage     = (int) $request->input('limit', 25);
            $currentPage = (int) $request->input('page', 1);
            $results     = $query->paginate($perPage, ['*'], 'page', $currentPage);
        } catch (\Throwable $e) {
            Log::error("[AnakPegawaiController] Error: {$e->getMessage()}");
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
            'id'               => $item->id,
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
}
