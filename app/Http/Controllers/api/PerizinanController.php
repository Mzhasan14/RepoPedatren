<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Services\FilterPerizinanService;

class PerizinanController extends Controller
{
    private FilterPerizinanService $filterController;

    public function __construct(FilterPerizinanService $filterController)
    {
        $this->filterController = $filterController;
    }

    // public function index()
    // {
    //     $perizinan = Perizinan::all();
    //     return new PdResource(true, 'Data berhasil ditampilkan', $perizinan);
    // }

    // public function store(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'id_peserta_didik' => ['required', 'integer', 'exists:peserta_didik,id'],
    //         'id_wali_asuh' => ['required', 'integer'],
    //         'pembuat' => ['required', 'string', 'max:255'],
    //         'biktren' => ['required', 'string', 'max:255'],
    //         'kamtib' => ['required', 'integer',],
    //         'alasan_izin' => ['required', 'string', 'max:1000'],
    //         'alamat_tujuan' => ['required', 'string', 'max:1000'],
    //         'tanggal_mulai' => ['required', 'date', 'after_or_equal:today'],
    //         'tanggal_akhir' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
    //         'jenis_izin' => ['required', Rule::in(['Personal', 'Rombongan'])],
    //         'status_izin' => ['required', Rule::in(['sedang proses izin', 'perizinan diterima', 'sudah berada diluar pondok', 'perizinan ditolak', 'dibatalkan'])],
    //         'status_kembali' => ['nullable', Rule::in(['telat', 'telat(sudah kembali)', 'telat(belum kembali)', 'kembali tepat waktu'])],
    //         'keterangan' => ['required', 'string', 'max:1000'],
    //         'created_by' => ['required', 'integer'],
    //         'status' => ['required', 'boolean'],
    //     ]);
    //     if ($validator->fails()) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Data gagal ditambahkan',
    //             'data' => $validator->errors()
    //         ]);
    //     }
    //     $perizinan = Perizinan::create($validator->validated());
    //     return new PdResource(true, 'Data berhasil ditambahkan', $perizinan);
    // }

    // public function show(string $id)
    // {
    //     $perizinan = Perizinan::findOrFail($id);
    //     return new PdResource(true, ' Data berhasil ditambahkan', $perizinan);
    // }

    // public function update(Request $request, string $id)
    // {
    //     $perizinan = Perizinan::findOrFail($id);
    //     $validator = Validator::make($request->all(), [
    //         'id_peserta_didik' => [
    //             'required',
    //             'integer',
    //             Rule::exists('peserta_didik', 'id'),
    //         ],
    //         'id_wali_asuh' => [
    //             'required',
    //             'integer'
    //         ],
    //         'pembuat' => ['required', 'string', 'max:255'],
    //         'biktren' => ['required', 'string', 'max:255'],
    //         'kamtib' => ['required', 'integer',],
    //         'alasan_izin' => ['required', 'string', 'max:1000'],
    //         'alamat_tujuan' => ['required', 'string', 'max:1000'],
    //         'tanggal_mulai' => ['required', 'date', 'after_or_equal:today'],
    //         'tanggal_akhir' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
    //         'jenis_izin' => ['required', Rule::in(['Personal', 'Rombongan'])],
    //         'status_izin' => ['required', Rule::in(['sedang proses izin', 'perizinan diterima', 'sudah berada diluar pondok', 'perizinan ditolak', 'dibatalkan'])],
    //         'status_kembali' => ['nullable', Rule::in(['telat', 'telat(sudah kembali)', 'telat(belum kembali)', 'kembali tepat waktu'])],
    //         'keterangan' => ['required', 'string', 'max:1000'],
    //         'updated_by' => ['nullable', 'integer', 'exists:users,id'],
    //         'status' => ['required', 'boolean'],
    //     ]);
    //     if ($validator->fails()) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Data gagal ditambahkan',
    //             'data' => $validator->errors()
    //         ]);
    //     }
    //     $perizinan->update($validator->validated());
    //     return new PdResource(true, 'Data berhasil di update', $perizinan);
    // }
    // public function destroy(string $id)
    // {
    //     $perizinan = Perizinan::findOrFail($id);
    //     $perizinan->delete();
    //     return new PdResource(true, 'Data berhasil dihapus', $perizinan);
    // }

    public function getAllPerizinan(Request $request)
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

            $query = DB::table('perizinan as pr')
                // Join ke tabel santri dan wali asuh
                ->join('santri as s', 'pr.santri_id', '=', 's.id')
                ->leftjoin('riwayat_domisili as rd', fn($j) => $j->on('s.id', '=', 'rd.santri_id')->where('rd.status', 'aktif'))
                ->leftJoin('wilayah AS w', 'rd.wilayah_id', '=', 'w.id')
                ->leftJoin('blok AS bl', 'rd.blok_id', '=', 'bl.id')
                ->leftJoin('kamar AS km', 'rd.kamar_id', '=', 'km.id')
                ->leftjoin('riwayat_pendidikan AS rp', fn($j) => $j->on('s.id', '=', 'rp.santri_id')->where('rp.status', 'aktif'))
                ->leftJoin('lembaga AS l', 'rp.lembaga_id', '=', 'l.id')
                ->leftjoin('biodata as b', 's.biodata_id', '=', 'b.id')
                ->leftjoin('provinsi as pv', 'b.provinsi_id', '=', 'pv.id')
                ->leftjoin('kabupaten as kb', 'b.kabupaten_id', '=', 'kb.id')
                ->leftjoin('kecamatan as kc', 'b.kecamatan_id', '=', 'kc.id')

                // Join ke tabel users untuk biktren, pengasuh, kamtib
                ->leftjoin('users as biktren', 'pr.biktren', '=', 'biktren.id')
                ->leftjoin('users as pengasuh',  'pr.pengasuh',  '=', 'pengasuh.id')
                ->leftjoin('users as kamtib',  'pr.kamtib',  '=', 'kamtib.id')

                // Join ke tabel users untuk created_by 
                ->join('users as creator', 'pr.created_by', '=', 'creator.id')

                // join berkas pas foto terakhir
                ->leftJoinSub($fotoLast, 'fl', fn($j) => $j->on('b.id', '=', 'fl.biodata_id'))
                ->leftJoin('berkas AS br', 'br.id', '=', 'fl.last_id')
                // Pilih kolom yang diinginkan
                ->select([
                    'pr.id',
                    'b.nama as nama_santri',
                    'b.jenis_kelamin',
                    'w.nama_wilayah',
                    'bl.nama_blok',
                    'km.nama_kamar',
                    'l.nama_lembaga',
                    'pv.nama_provinsi',
                    'kb.nama_kabupaten',
                    'kc.nama_kecamatan',
                    'pr.alasan_izin',
                    'pr.alamat_tujuan',
                    'pr.tanggal_mulai',
                    'pr.tanggal_akhir',

                    // kolom bermalam: kalau tanggal mulai dan tanggal akhir berbeda → bermalam,
                    // kalau sama tanggalnya → sehari
                    DB::raw("
                        CASE
                        WHEN DATE(pr.tanggal_mulai) = DATE(pr.tanggal_akhir) THEN 'sehari'
                        ELSE 'bermalam'
                        END AS bermalam
                    "),

                    // tambahan: kolom lama_izin
                    DB::raw("
                        CASE
                            WHEN TIMESTAMPDIFF(HOUR, pr.tanggal_mulai, pr.tanggal_akhir) < 24 THEN
                            CONCAT(TIMESTAMPDIFF(HOUR, pr.tanggal_mulai, pr.tanggal_akhir), ' jam')
                            WHEN TIMESTAMPDIFF(DAY, pr.tanggal_mulai, pr.tanggal_akhir) < 7 THEN
                            CONCAT(TIMESTAMPDIFF(DAY, pr.tanggal_mulai, pr.tanggal_akhir), ' hari')
                            WHEN TIMESTAMPDIFF(DAY, pr.tanggal_mulai, pr.tanggal_akhir) < 30 THEN
                            CONCAT(CEIL(TIMESTAMPDIFF(DAY, pr.tanggal_mulai, pr.tanggal_akhir) / 7), ' minggu')
                            ELSE
                            CONCAT(CEIL(TIMESTAMPDIFF(DAY, pr.tanggal_mulai, pr.tanggal_akhir) / 30), ' bulan')
                        END
                        AS lama_izin
                        "),
                    'pr.tanggal_kembali',
                    'pr.jenis_izin',
                    'pr.status_izin',
                    'creator.name as pembuat',
                    'pengasuh.name as nama_pengasuh',
                    'biktren.name as nama_biktren',
                    'kamtib.name as nama_kamtib',
                    'pr.keterangan',
                    'pr.status_kembali',
                    'pr.created_at',
                    'pr.updated_at',
                    DB::raw("COALESCE(br.file_path, 'default.jpg') AS foto_profil"),
                ])
                // (Opsional) urutkan berdasarkan tanggal mulai terbaru
                ->orderBy('pr.id', 'desc');

            // Terapkan filter dan pagination
            $query = $this->filterController->perizinanFilters($query, $request);


            $perPage     = (int) $request->input('limit', 25);
            $currentPage = (int) $request->input('page', 1);
            $results     = $query->paginate($perPage, ['*'], 'page', $currentPage);
        } catch (\Throwable $e) {
            Log::error("[PerizinanController] Error: {$e->getMessage()}");
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
            'id'                => $item->id,
            'nama_santri'       => $item->nama_santri,
            'jenis_kelamin'     => $item->jenis_kelamin,
            'wilayah'      => $item->nama_wilayah,
            'blok'         => $item->nama_blok ?? '-',
            'kamar'        => $item->nama_kamar ?? '-',
            'lembaga'      => $item->nama_lembaga ?? '-',
            'provinsi'     => $item->nama_provinsi ?? '-',
            'kabupaten'    => $item->nama_kabupaten ?? '-',
            'kecamatan'    => $item->nama_kecamatan ?? '-',
            'alasan_izin'       => $item->alasan_izin,
            'alamat_tujuan'     => $item->alamat_tujuan,
            'tanggal_mulai'     => Carbon::parse($item->tanggal_mulai)
                ->translatedFormat('d F Y H:i:s'),
            'tanggal_akhir'     => Carbon::parse($item->tanggal_akhir)
                ->translatedFormat('d F Y H:i:s'),
            'bermalam'          => $item->bermalam,
            'lama_izin'         => $item->lama_izin,
            'tanggal_kembali'   => Carbon::parse($item->tanggal_kembali)
                ->translatedFormat('d F Y H:i:s') ?? '-',
            'jenis_izin'        => $item->jenis_izin,
            'status_izin'       => $item->status_izin,
            'pembuat'           => $item->pembuat,
            'nama_pengasuh'    => $item->nama_pengasuh ?? '-',
            'nama_biktren'      => $item->nama_biktren ?? '-',
            'nama_kamtib'       => $item->nama_kamtib ?? '-',
            'keterangan'        => $item->keterangan ?? '-',
            'status_kembali'    => $item->status_kembali,
            'tgl_input'         => Carbon::parse($item->created_at)
                ->translatedFormat('d F Y H:i:s'),
            'tgl_update'        => Carbon::parse($item->updated_at)
                ->translatedFormat('d F Y H:i:s') ?? '-',
            'foto_profil'       => url($item->foto_profil),
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
