<?php

namespace App\Http\Controllers\api\PesertaDidik;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\PesertaDidik;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\Controller;
use App\Http\Controllers\api\FilterController;

class PesertaDidikController extends Controller
{
    protected $filterController;
    protected $filterUmum;

    public function __construct()
    {
        // Inisialisasi controller filter
        $this->filterController = new FilterPesertaDidikController();
        $this->filterUmum = new FilterController();
    }

    /**
     * Fungsi untuk mengambil Tampilan awal peserta didik.
     */
    public function getAllPesertaDidik(Request $request)
    {
        try {
            $query = DB::table('peserta_didik as pd')
                ->join('biodata as b', 'pd.id_biodata', '=', 'b.id')
                ->leftjoin('pelajar as p', function ($join) {
                    $join->on('p.id_peserta_didik', '=', 'pd.id')
                        ->where('p.status', 'aktif');
                })
                ->leftJoin('riwayat_pendidikan as rp', function ($join) {
                    $join->on('rp.id_peserta_didik', '=', 'pd.id')
                        ->where('rp.status', 'aktif');
                })
                ->leftJoin('lembaga as l', 'rp.id_lembaga', '=', 'l.id')
                ->leftJoin('jurusan as j', 'rp.id_jurusan', '=', 'j.id')
                ->leftJoin('kelas as k', 'rp.id_kelas', '=', 'k.id')
                ->leftJoin('rombel as r', 'rp.id_rombel', '=', 'r.id')
                ->leftJoin('santri as s', function ($join) {
                    $join->on('s.id_peserta_didik', '=', 'pd.id')
                        ->where('s.status', 'aktif');
                })
                ->leftJoin('riwayat_domisili as rd', function ($join) {
                    $join->on('rd.id_peserta_didik', '=', 'pd.id')
                        ->where('rd.status', 'aktif');
                })
                ->leftJoin('wilayah as w', 'rd.id_wilayah', '=', 'w.id')
                ->leftjoin('blok as bl', 'rd.id_blok', '=', 'bl.id')
                ->leftjoin('kamar as km', 'rd.id_kamar', '=', 'km.id')
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
                ->where('pd.status', true)
                ->where(function ($q) {
                    $q->where(function ($sub) {
                        // Kondisi untuk data santri lengkap dan aktif
                        $sub->where('s.status', 'aktif');
                    })
                        ->orWhere(function ($sub) {
                            // Kondisi untuk data pelajar lengkap dan aktif
                            $sub->where('p.status', 'aktif');
                        });
                })
                ->select([
                    'pd.id',
                    DB::raw("COALESCE(b.nik, b.no_passport) AS identitas"),
                    'b.nama',
                    'wp.niup',
                    DB::raw("COALESCE(MAX(l.nama_lembaga), '-') AS nama_lembaga"), // Ambil salah satu data lembaga
                    DB::raw("COALESCE(MAX(w.nama_wilayah), '-') AS nama_wilayah"), // Ambil salah satu data wilayah santri
                    DB::raw("CONCAT('Kab. ', kb.nama_kabupaten) AS kota_asal"),
                    'b.created_at',
                    'b.updated_at',
                    DB::raw("COALESCE(MAX(br.file_path), 'default.jpg') as foto_profil")
                ])
                ->groupBy([
                    'pd.id',
                    'b.nik',
                    'b.no_passport',
                    'b.nama',
                    'wp.niup',
                    'kb.nama_kabupaten',
                    'b.created_at',
                    'b.updated_at',
                    'br.file_path'
                ]);


            // Terapkan filter umum (contoh: filter alamat dan jenis kelamin)
            $query = $this->filterUmum->applyCommonFilters($query, $request);

            // Terapkan filter-filter terpisah
            $query = $this->filterController->applyWilayahFilter($query, $request);
            $query = $this->filterController->applyLembagaPendidikanFilter($query, $request);
            $query = $this->filterController->applyStatusPesertaFilter($query, $request);
            $query = $this->filterController->applyStatusWargaPesantrenFilter($query, $request);
            $query = $this->filterController->applySorting($query, $request);
            $query = $this->filterController->applyAngkatanPelajar($query, $request);
            $query = $this->filterController->applyPhoneNumber($query, $request);
            $query = $this->filterController->applyPemberkasan($query, $request);

            // Pagination: batasi jumlah data per halaman (default 25)
            $perPage     = $request->input('limit', 25);
            $currentPage = $request->input('page', 1);
            $results     = $query->paginate($perPage, ['*'], 'page', $currentPage);
        } catch (\Exception $e) {
            Log::error("Error in getAllPesertaDidik: " . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan pada server'
            ], 500);
        }

        // Jika data tidak ditemukan, kembalikan respons error dengan status 404
        if ($results->isEmpty()) {
            return response()->json([
                'status'  => 'succes',
                'message' => 'Data Kosong',
                'data'    => []
            ], 200);
        }

        // Format data output agar mudah dipahami
        $formattedData = $results->map(function ($item) {
            return [
                "id_peserta_didik"              => $item->id,
                "nik_or_passport" => $item->identitas,
                "nama"            => $item->nama,
                "niup"            => $item->niup ?? '-',
                "lembaga"         => $item->nama_lembaga ?? '-',
                "wilayah"         => $item->nama_wilayah ?? '-',
                "kota_asal"       => $item->kota_asal,
                "tgl_update"      => $item->updated_at ? Carbon::parse($item->updated_at)->translatedFormat('d F Y H:i:s') : '-',
                "tgl_input"       => $item->created_at ? Carbon::parse($item->created_at)->translatedFormat('d F Y H:i:s') : '-',
                "foto_profil"     => url($item->foto_profil)
            ];
        });

        // Kembalikan respon JSON dengan data yang sudah diformat
        return response()->json([
            "total_data"   => $results->total(),
            "current_page" => $results->currentPage(),
            "per_page"     => $results->perPage(),
            "total_pages"  => $results->lastPage(),
            "data"         => $formattedData
        ]);
    }

    

  
}
