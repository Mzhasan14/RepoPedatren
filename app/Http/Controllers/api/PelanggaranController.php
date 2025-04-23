<?php

namespace App\Http\Controllers\Api;

use App\Models\Pelanggaran;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Services\FilterPelanggaranService;

class PelanggaranController extends Controller
{
    private FilterPelanggaranService $filterController;

    public function __construct(FilterPelanggaranService $filterController)
    {
        $this->filterController = $filterController;
    }
    // public function index()
    // {
    //     $pelanggaran = Pelanggaran::all();
    //     return new PdResource(true,'Data berhasil ditampilkan',$pelanggaran);
    // }

    // public function store(Request $request)
    // {
    //     $validator = Validator::make($request->all(),[
    //         'id_peserta_didik' => ['required', 'integer', 'exists:peserta_didik,id'],
    //         'status_pelanggaran' => ['required', Rule::in(['Belum diproses', 'Sedang diproses', 'Sudah diproses'])],
    //         'jenis_putusan' => ['required', Rule::in(['Belum ada putusan', 'Disanksi', 'Dibebaskan'])],
    //         'jenis_pelanggaran' => ['required', Rule::in(['Ringan', 'Sedang', 'Berat'])],
    //         'keterangan' => 'required|string|max:1000',
    //         'created_by' => 'required|integer',
    //         'status' => 'required|boolean',
    //     ]);

    //     if ($validator->fails()){
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Data Gagal ditambahkan',
    //             'data' => $validator->errors()
    //         ]);
    //     }

    //     $pelanggaran = Pelanggaran::create($validator->validated());
    //     return new PdResource(true,'Data berhasil di tampilkan',$pelanggaran);
    // }

    // public function show(string $id)
    // {
    //     $pelanggaran = Pelanggaran::findOrFail($id);
    //     return new PdResource(true,'Data berhasil ditampilkan',$pelanggaran);
    // }
    // public function update(Request $request, string $id)
    // {
    //     $pelanggaran = Pelanggaran::findOrFail($id);
    //     $validator = Validator::make($request->all(),[
    //         'id_peserta_didik' => [
    //             'required', 
    //             'integer', 
    //             Rule::exists('peserta_didik', 'id'),
    //         ],
    //     'status_pelanggaran' => ['required', Rule::in(['Belum diproses', 'Sedang diproses', 'Sudah diproses'])],
    //     'jenis_putusan' => ['required', Rule::in(['Belum ada putusan', 'Disanksi', 'Dibebaskan'])],
    //     'jenis_pelanggaran' => ['required', Rule::in(['Ringan', 'Sedang', 'Berat'])],
    //     'keterangan' => 'required|string|max:1000',
    //     'status' => 'required|boolean',
    //     'updated_by' => 'nullable|integer',
    //     ]);

    //     if ($validator->fails()){
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Data Gagal ditambahkan',
    //             'data' => $validator->errors()
    //         ]);
    //     }
    //     $pelanggaran->update($validator->validated());
    //     return new PdResource(true,'Data Berhasil Di update',$pelanggaran);

    // }

    // public function destroy(string $id)
    // {
    //     $pelanggaran = Pelanggaran::findOrFail($id);
    //     $pelanggaran->delete();
    //     return new PdResource(true,'Data berhasil dihapus',$pelanggaran);
    // }

    public function getAllPelanggaran(Request $request)
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

        $query = DB::table('pelanggaran as pl')
            ->join('santri as s', 'pl.santri_id', '=', 's.id')
            ->leftjoin('biodata as b', 's.biodata_id', '=', 'b.id')
            ->leftjoin('provinsi as pv', 'b.provinsi_id', '=', 'pv.id')
            ->leftjoin('kabupaten as kb', 'b.kabupaten_id', '=', 'kb.id')
            ->leftjoin('kecamatan as kc', 'b.kecamatan_id', '=', 'kc.id')
            ->leftjoin('riwayat_domisili as rd', fn($j) => $j->on('s.id', '=', 'rd.santri_id')->where('rd.status', 'aktif'))
            ->leftjoin('wilayah as w', 'rd.wilayah_id', '=', 'w.id')
            ->leftjoin('blok as bl', 'rd.blok_id', '=', 'bl.id')
            ->leftjoin('kamar as km', 'rd.kamar_id', '=', 'km.id')
            ->leftjoin('riwayat_pendidikan AS rp', fn($j) => $j->on('s.id', '=', 'rp.santri_id')->where('rp.status', 'aktif'))
            ->leftJoin('lembaga as l', 'rp.lembaga_id', '=', 'l.id')
            ->leftJoin('users as pencatat', 'pl.created_by', '=', 'pencatat.id')
            // join berkas pas foto terakhir
            ->leftJoinSub($fotoLast, 'fl', fn($j) => $j->on('b.id', '=', 'fl.biodata_id'))
            ->leftJoin('berkas AS br', 'br.id', '=', 'fl.last_id')
            ->select([
                // data santri
                'pl.id',
                'b.nama',
                'pv.nama_provinsi',
                'kb.nama_kabupaten',
                'kc.nama_kecamatan',
                'w.nama_wilayah',
                'bl.nama_blok',
                'km.nama_kamar',
                'l.nama_lembaga',
                // data pelanggaran
                'pl.status_pelanggaran',
                'pl.jenis_pelanggaran',
                'pl.jenis_putusan',
                'pl.diproses_mahkamah',
                'pl.keterangan',
                'pl.created_at',
                // data pencatat
                DB::raw("COALESCE(pencatat.name, '(AutoSystem)') as pencatat"),
                DB::raw("COALESCE(br.file_path, 'default.jpg') AS foto_profil"),
            ])
            ->orderBy('pl.id', 'desc');

            // Terapkan filter dan pagination
            $query = $this->filterController->applyAllFilters($query, $request);


            $perPage     = (int) $request->input('limit', 25);
            $currentPage = (int) $request->input('page', 1);
            $results     = $query->paginate($perPage, ['*'], 'page', $currentPage);
        } catch (\Throwable $e) {
            Log::error("[PelanggaranController] Error: {$e->getMessage()}");
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

        $formatted = collect($results->items())->map(function($item) {
            return [
                'id'                   => $item->id,
                'nama_santri'          => $item->nama,                      // dari b.nama
                'provinsi'             => $item->nama_provinsi ?? '-',
                'kabupaten'            => $item->nama_kabupaten ?? '-',
                'kecamatan'            => $item->nama_kecamatan ?? '-',
                'wilayah'              => $item->nama_wilayah ?? '-',
                'blok'                 => $item->nama_blok     ?? '-',
                'kamar'                => $item->nama_kamar    ?? '-',
                'lembaga'              => $item->nama_lembaga  ?? '-',
                'status_pelanggaran'   => $item->status_pelanggaran,
                'jenis_pelanggaran'    => $item->jenis_pelanggaran,
                'jenis_putusan'        => $item->jenis_putusan,
                'diproses_mahkamah'    => (bool) $item->diproses_mahkamah,
                'keterangan'           => $item->keterangan    ?? '-',
                'pencatat'             => $item->pencatat,
                'foto_profil'          => url($item->foto_profil),
                'tgl_input'            => Carbon::parse($item->created_at)
                                            ->translatedFormat('d F Y H:i:s'),
            ];
        });
        

        return response()->json([
            'total_data'   => $results->total(),
            'current_page' => $results->currentPage(),
            'per_page'     => $results->perPage(),
            'total_pages'  => $results->lastPage(),
            'data'         => $formatted,
        ]);
    }
}
