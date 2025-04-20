<?php

namespace App\Http\Controllers\api\PesertaDidik;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Services\FilterBersaudaraService;

class BersaudaraController extends Controller
{
    private FilterBersaudaraService $filterController;

    public function __construct(FilterBersaudaraService $filterController)
    {
        $this->filterController = $filterController;
    }

    public function getAllBersaudara(Request $request)
    {
        try {
            // 1) Lookup ID “Pas foto”
            $pasFotoId = DB::table('jenis_berkas')
                ->where('nama_jenis_berkas', 'Pas foto')
                ->value('id');

            // 2) Derived table: foto terakhir per biodata
            $fotoLast = DB::table('berkas')
                ->select('id_biodata', DB::raw('MAX(id) AS last_id'))
                ->where('id_jenis_berkas', $pasFotoId)
                ->groupBy('id_biodata');

            // 3) Derived table: warga_pesantren terakhir per biodata
            $wpLast = DB::table('warga_pesantren')
                ->select('id_biodata', DB::raw('MAX(id) AS last_id'))
                ->where('status', true)
                ->groupBy('id_biodata');

            // 4) Derived table: nama ibu & ayah per no_kk
            $parents = DB::table('orang_tua_wali AS otw')
                ->join('keluarga AS k2', 'k2.id_biodata', '=', 'otw.id_biodata')
                ->join('biodata AS b2', 'b2.id', '=', 'otw.id_biodata')
                ->join('hubungan_keluarga AS hk', 'hk.id', '=', 'otw.id_hubungan_keluarga')
                ->select([
                    'k2.no_kk',
                    DB::raw("MAX(CASE WHEN hk.nama_status='ibu' THEN b2.nama END) AS nama_ibu"),
                    DB::raw("MAX(CASE WHEN hk.nama_status='ayah' THEN b2.nama END) AS nama_ayah"),
                ])
                ->groupBy('k2.no_kk');

            // 5) Derived table: keluarga dengan >1 anak aktif
            $siblings = DB::table('keluarga AS k2')
                ->join('peserta_didik AS pd2', 'k2.id_biodata', '=', 'pd2.id_biodata')
                ->leftJoin('pelajar AS p2', function ($j) {
                    $j->on('p2.id_peserta_didik', '=', 'pd2.id')
                        ->where('p2.status', 'aktif');
                })
                ->leftJoin('santri AS s2', function ($j) {
                    $j->on('s2.id_peserta_didik', '=', 'pd2.id')
                        ->where('s2.status', 'aktif');
                })
                ->whereNotIn('k2.id_biodata', function ($q) {
                    $q->select('id_biodata')->from('orang_tua_wali');
                })
                ->where(function ($q) {
                    $q->whereNotNull('p2.id_peserta_didik')
                        ->orWhereNotNull('s2.id_peserta_didik');
                })
                ->select('k2.no_kk', DB::raw('COUNT(*) AS cnt'))
                ->groupBy('k2.no_kk')
                ->having('cnt', '>', 1);

            // 6) Query utama: peserta_didik dengan relasi lengkap
            $query = DB::table('peserta_didik AS pd')
                // biodata & keluarga
                ->join('biodata AS b', 'pd.id_biodata', '=', 'b.id')
                ->join('keluarga AS k', 'k.id_biodata', '=', 'b.id')
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
                // orang tua
                ->joinSub($parents, 'parents', fn($j) => $j->on('k.no_kk', '=', 'parents.no_kk'))
                // hanya keluarga dengan >1 anak aktif
                ->joinSub($siblings, 'sib', fn($join) => $join->on('k.no_kk', '=', 'sib.no_kk'))
                ->where(fn($q) => $q->whereNotNull('p.id')->orWhereNotNull('s.id'))
                ->select([
                    'pd.id',
                    DB::raw("COALESCE(b.nik, b.no_passport) AS identitas"),
                    'k.no_kk',
                    'b.nama',
                    'wp.niup',
                    'l.nama_lembaga',
                    'w.nama_wilayah',
                    DB::raw("CONCAT('Kab. ', kb.nama_kabupaten) AS kota_asal"),
                    'br.file_path AS foto_profil',
                    'b.created_at',
                    'b.updated_at',
                    DB::raw("COALESCE(parents.nama_ibu, 'Tidak Diketahui') AS nama_ibu"),
                    DB::raw("COALESCE(parents.nama_ayah, 'Tidak Diketahui') AS nama_ayah"),
                ])
                ->orderBy('k.no_kk');


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

        // Format data untuk response
        $formatted = collect($results->items())->map(fn($item) => [
            "id_peserta_didik" => $item->id,
            "nik_nopassport"   => $item->identitas,
            "nokk"             => $item->no_kk,
            "nama"             => $item->nama,
            "niup"             => $item->niup ?? '-',
            "lembaga"          => $item->nama_lembaga ?? '-',
            "wilayah"          => $item->nama_wilayah ?? '-',
            "kota_asal"        => $item->kota_asal,
            "ibu_kandung"      => $item->nama_ibu,
            "ayah_kandung"     => $item->nama_ayah,
            "tgl_update"       => Carbon::parse($item->updated_at)->translatedFormat('d F Y H:i:s') ?? '-',
            "tgl_input"        => Carbon::parse($item->created_at)->translatedFormat('d F Y H:i:s'),
            "foto_profil"      => url($item->foto_profil),
        ]);

        return response()->json([
            "total_data"   => $results->total(),
            "current_page" => $results->currentPage(),
            "per_page"     => $results->perPage(),
            "total_pages"  => $results->lastPage(),
            "data"         => $formatted
        ]);
    }
}
