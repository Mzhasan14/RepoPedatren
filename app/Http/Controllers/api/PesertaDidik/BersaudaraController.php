<?php

namespace App\Http\Controllers\api\PesertaDidik;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Controllers\api\FilterController;
use App\Http\Controllers\api\PesertaDidik\FilterPesertaDidikController;

class BersaudaraController extends Controller
{
    protected $filterController;
    protected $filterUmum;

    public function __construct()
    {
        // Inisialisasi controller filter
        $this->filterController = new FilterPesertaDidikController();
        $this->filterUmum = new FilterController();
    }

    public function getAllBersaudara(Request $request)
    {
        try {
            $query = DB::table('peserta_didik as pd')
            ->join('biodata as b', 'pd.id_biodata', '=', 'b.id')
            ->join('keluarga', 'keluarga.id_biodata', '=', 'b.id')
            ->leftJoin('kabupaten as kb', 'kb.id', '=', 'b.id_kabupaten')
            ->leftJoin('berkas as br', function ($join) {
                $join->on('b.id', '=', 'br.id_biodata')
                     ->where('br.id_jenis_berkas', '=', function ($query) {
                         $query->select('id')
                               ->from('jenis_berkas')
                               ->where('nama_jenis_berkas', 'Pas foto')
                               ->limit(1);
                     })
                     ->whereRaw('br.id = (
                        select max(b2.id) 
                        from berkas as b2 
                        where b2.id_biodata = b.id 
                          and b2.id_jenis_berkas = br.id_jenis_berkas
                     )');
            })
            ->leftjoin('pelajar as p', function ($join) {
                $join->on('p.id_peserta_didik', '=', 'pd.id')
                    ->where('p.status', 'aktif');
            })
            ->leftJoin('riwayat_pendidikan as rp', function ($join) {
                $join->on('rp.id_peserta_didik', '=', 'pd.id')
                    ->where('rp.status', 'aktif');
            })
            ->leftJoin('santri as s', function ($join) {
                $join->on('s.id_peserta_didik', '=', 'pd.id')
                    ->where('s.status', 'aktif');
            })
            ->leftJoin('riwayat_domisili as rd', function ($join) {
                $join->on('rd.id_peserta_didik', '=', 'pd.id')
                    ->where('rd.status', 'aktif');
            })
            ->leftJoin('lembaga', 'rp.id_lembaga', '=', 'lembaga.id')
            ->leftJoin('wilayah', 'rd.id_wilayah', '=', 'wilayah.id')
            ->leftJoin('warga_pesantren as wp', function ($join) {
                $join->on('b.id', '=', 'wp.id_biodata')
                     ->where('wp.status', true)
                     ->whereRaw('wp.id = (
                        select max(wp2.id) 
                        from warga_pesantren as wp2 
                        where wp2.id_biodata = b.id 
                          and wp2.status = true
                     )');
            })
            // Join derived table untuk mengambil nama ibu dan ayah dari keluarga berdasarkan no_kk
            ->leftJoin(DB::raw('(
                SELECT 
                    k.no_kk,
                    MAX(CASE WHEN hk.nama_status = "ibu" THEN b.nama END) as nama_ibu,
                    MAX(CASE WHEN hk.nama_status = "ayah" THEN b.nama END) as nama_ayah
                FROM orang_tua_wali otw
                JOIN keluarga k ON k.id_biodata = otw.id_biodata
                JOIN biodata b ON b.id = otw.id_biodata
                JOIN hubungan_keluarga hk ON hk.id = otw.id_hubungan_keluarga
                GROUP BY k.no_kk
            ) as parents'), 'keluarga.no_kk', '=', 'parents.no_kk')

            // Hanya tampilkan peserta didik yang memiliki saudara kandung (lebih dari 1 peserta didik per no_kk)
            ->whereIn('keluarga.no_kk', function ($sub) {
                $sub->select('k2.no_kk')
                    ->from('keluarga as k2')
                    // pastikan semua anak punya peserta_didik aktif
                    ->join('peserta_didik as pd2', 'k2.id_biodata', '=', 'pd2.id_biodata')
                    ->leftjoin('pelajar as p2', function ($join) {
                        $join->on('p2.id_peserta_didik', '=', 'pd2.id')
                            ->where('p2.status', 'aktif');
                    })
                    ->leftJoin('santri as s2', function ($join) {
                        $join->on('s2.id_peserta_didik', '=', 'pd2.id')
                            ->where('s2.status', 'aktif');
                    })
                    // buang orang tua
                    ->whereNotIn('k2.id_biodata', function($q){
                        $q->select('id_biodata')->from('orang_tua_wali');
                    })
                    ->where('pd2.status', true)
                    ->where(function ($q) {
                        $q->where(function ($sub) {
                            // Kondisi untuk data santri lengkap dan aktif
                            $sub->where('s2.status', 'aktif');
                        })
                            ->orWhere(function ($sub) {
                                // Kondisi untuk data pelajar lengkap dan aktif
                                $sub->where('p2.status', 'aktif');
                            });
                    })
                    ->groupBy('k2.no_kk')
                    // minimal 2 anggota yang lolos semua kriteria
                    ->havingRaw('COUNT(*) > 1');
            })
            ->select(
                'pd.id',
                DB::raw("COALESCE(b.nik, b.no_passport) as identitas"),
                'keluarga.no_kk',
                'b.nama',
                'wp.niup',
                'lembaga.nama_lembaga',
                'wilayah.nama_wilayah',
                DB::raw("CONCAT('Kab. ', kb.nama_kabupaten) as kota_asal"),
                DB::raw("COALESCE(MAX(br.file_path), 'default.jpg') as foto_profil"),
                'b.created_at',
                'b.updated_at',
                DB::raw("COALESCE(parents.nama_ibu, 'Tidak Diketahui') as nama_ibu"),
                DB::raw("COALESCE(parents.nama_ayah, 'Tidak Diketahui') as nama_ayah")
            )
            ->groupBy(
                'pd.id',
                'b.nik',
                'b.no_passport',
                'keluarga.no_kk',
                'b.nama',
                'wp.niup',
                'lembaga.nama_lembaga',
                'wilayah.nama_wilayah',
                'kb.nama_kabupaten',
                'b.created_at',
                'b.updated_at',
                'parents.nama_ibu',
                'parents.nama_ayah'
            )
            ->orderBy('keluarga.no_kk');
        

            // Terapkan filter umum
            $query = $this->filterUmum->applyCommonFilters($query, $request);
            // Terapkan filter-filter spesifik
            // $query = $this->filterController->applyWilayahFilter($query, $request);
            // $query = $this->filterController->applyLembagaPendidikanFilter($query, $request);
            // $query = $this->filterController->applyStatusPesertaFilter($query, $request);
            // $query = $this->filterController->applyStatusWargaPesantrenFilter($query, $request);
            // $query = $this->filterController->applySorting($query, $request);
            // $query = $this->filterController->applyStatusSaudara($query, $request);

            // Pagination (default 25 per halaman)
            $perPage     = $request->input('limit', 25);
            $currentPage = $request->input('page', 1);
            $results     = $query->paginate($perPage, ['*'], 'page', $currentPage);
        } catch (\Exception $e) {
            Log::error("Error in getAllBersaudara: " . $e->getMessage());
            return response()->json([
                "status"  => "error",
                "message" => "Terjadi kesalahan pada server"
            ], 500);
        }

        // Jika data kosong
        if ($results->isEmpty()) {
            return response()->json([
                'status'  => 'succes',
                'message' => 'Data Kosong',
                'data'    => []
            ], 200);
        }

        // Format output data agar mudah dipahami
        $formattedData = $results->map(function ($item) {
            return [
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
            ];
        });

        return response()->json([
            "total_data"   => $results->total(),
            "current_page" => $results->currentPage(),
            "per_page"     => $results->perPage(),
            "total_pages"  => $results->lastPage(),
            "data"         => $formattedData
        ]);
    }
}
