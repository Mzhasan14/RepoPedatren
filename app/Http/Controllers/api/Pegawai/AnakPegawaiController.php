<?php

namespace App\Http\Controllers\Api\Pegawai;

use App\Http\Controllers\api\FilterController;
use App\Http\Controllers\Controller;
use App\Http\Resources\PdResource;
use App\Models\JenisBerkas;
use App\Models\Pegawai\AnakPegawai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AnakPegawaiController extends Controller
{
    protected $filterController;

    public function __construct(FilterController $filterController)
    {
        $this->filterController = $filterController;
    }

    public function index()
    {
        $anakPegawai = AnakPegawai::all();
        return new PdResource(true,'List data Anak pegawai',$anakPegawai);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),
        [
            'id_peserta_didik' => 'required|exists:peserta_didik,id',
            'id_pegawai' => 'required|exists:pegawai,id',
            'status' => 'nullable|boolean',
            'created_by' => 'required|exists:users,id',
        ]);

        if ($validator->fails()){
            return response()->json([
                'status' => false,
                'message' =>'Data gagal ditambahkan',
                'data' => $validator->errors()
            ]);
        }

        $anakPegawai = AnakPegawai::create($validator->validated());
        return new PdResource(true,'data berhasil ditambahkan',$anakPegawai);
    }

    public function show(string $id)
    {
        $anakPegawai = AnakPegawai::findOrFail($id);
        return new PdResource(true,'data berhasil ditampilkan',$anakPegawai);

    }

    public function update(Request $request, string $id)
    {
        $anakPegawai = AnakPegawai::findOrFail($id);
        $validator = Validator::make($request->all(),
        [
            'id_peserta_didik' => 'required|exists:peserta_didik,id',
            'id_pegawai' => 'required|exists:pegawai,id',
            'status' => 'required|boolean',
            'updated_by' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()){
            return response()->json([
                'status' => false,
                'message' =>'Data gagal ditambahkan',
                'data' => $validator->errors()
            ]);
        }
        $anakPegawai->update($validator->validated());
        return new PdResource(true,'data berhasil diupdate',$anakPegawai);

    }
    public function destroy(string $id)
    {
        $anakPegawai = AnakPegawai::findOrFail($id);
        $anakPegawai->delete();
        return new PdResource(true,'data berhasil dihapus',$anakPegawai);

    }

    public function dataAnakpegawai(Request $request)
    {
        $query = AnakPegawai::Active()
                            ->leftJoin('peserta_didik','peserta_didik.id','=','anak_pegawai.id_peserta_didik')
                            ->join('biodata','biodata.id','peserta_didik.id_biodata')
                            ->leftJoin('kabupaten','kabupaten.id','biodata.id_kabupaten')
                            ->leftJoin('pelajar','pelajar.id_peserta_didik','=','peserta_didik.id')
                            ->leftJoin('lembaga','lembaga.id','=','pelajar.id_lembaga')
                            ->leftJoin('jurusan','jurusan.id','=','pelajar.id_jurusan')
                            ->leftJoin('kelas','kelas.id','=','pelajar.id_kelas')
                            ->leftJoin('rombel','rombel.id','=','pelajar.id_rombel')
                            ->leftJoin('pegawai','pegawai.id','=','anak_pegawai.id_pegawai')
                            ->leftJoin('santri', 'peserta_didik.id', '=', 'santri.id_peserta_didik')
                            ->leftJoin('berkas', 'berkas.id_biodata', '=', 'biodata.id')
                            ->leftJoin('jenis_berkas', 'berkas.id_jenis_berkas', '=', 'jenis_berkas.id')
                            ->leftjoin('wilayah', 'santri.id_wilayah', '=', 'wilayah.id')
                            ->leftjoin('blok', 'santri.id_blok', '=', 'blok.id')
                            ->leftjoin('kamar', 'santri.id_kamar', '=', 'kamar.id')
                            ->leftjoin('domisili', 'santri.id_domisili', '=', 'domisili.id')
                            ->select(
                                'anak_pegawai.id',
                                'biodata.nama',
                                'biodata.niup',
                                'santri.nis',
                                DB::raw("COALESCE(biodata.nik, biodata.no_passport) as identitas"),
                                'kabupaten.nama_kabupaten',
                                DB::raw("GROUP_CONCAT(DISTINCT lembaga.nama_lembaga SEPARATOR ', ') as lembaga"),
                                DB::raw("GROUP_CONCAT(DISTINCT jurusan.nama_jurusan SEPARATOR ', ') as jurusan"),
                                DB::raw("GROUP_CONCAT(DISTINCT kelas.nama_kelas SEPARATOR ', ') as kelas"),
                                'wilayah.nama_wilayah',
                                'blok.nama_blok',
                                'kamar.nama_kamar',
                                DB::raw("DATE_FORMAT(anak_pegawai.updated_at, '%Y-%m-%d %H:%i:%s') AS tgl_update"),
                                DB::raw("DATE_FORMAT(anak_pegawai.created_at, '%Y-%m-%d %H:%i:%s') AS tgl_input"),
                                DB::raw("COALESCE(MAX(berkas.file_path), 'default.jpg') as foto_profil")
                            )
                            ->groupBy(
                                'anak_pegawai.id', 
                                'biodata.nama', 
                                'biodata.niup', 
                                'santri.nis',
                                'biodata.nik', 
                                'biodata.no_passport',
                                'wilayah.nama_wilayah',
                                'blok.nama_blok',
                                'kamar.nama_kamar',
                                'kabupaten.nama_kabupaten',
                                'anak_pegawai.updated_at',
                                'anak_pegawai.created_at'
                            ); 
        
        // Filter Umum (Alamat dan Jenis Kelamin)
        $query = $this->filterController->applyCommonFilters($query, $request);

        // Filter Search
        if ($request->filled('search')) {
            $search = strtolower($request->search);
    
            $query->where(function ($q) use ($search) {
                $q->where('biodata.nik', 'LIKE', "%$search%")
                    ->orWhere('biodata.no_passport', 'LIKE', "%$search%")
                    ->orWhere('biodata.nama', 'LIKE', "%$search%")
                    ->orWhere('biodata.niup', 'LIKE', "%$search%")
                    ->orWhere('lembaga.nama_lembaga', 'LIKE', "%$search%")
                    ->orWhere('wilayah.nama_wilayah', 'LIKE', "%$search%")
                    ->orWhere('kabupaten.nama_kabupaten', 'LIKE', "%$search%")
                    ->orWhereDate('anak_pegawai.created_at', '=', $search) // Tgl Input
                    ->orWhereDate('anak_pegawai.updated_at', '=', $search); // Tgl Update
                    });
        }
        // Filter Wilayah
        if ($request->filled('wilayah')) {
            $wilayah = strtolower($request->wilayah);
            $query->where('wilayah.nama_wilayah', $wilayah);
            if ($request->filled('blok')) {
                $blok = strtolower($request->blok);
                $query->where('blok.nama_blok', $blok);
                if ($request->filled('kamar')) {
                    $kamar = strtolower($request->kamar);
                    $query->where('kamar.nama_kamar', $kamar);
                }
            }
        }

        // Filter Lembaga
        if ($request->filled('lembaga')) {
            $query->where('lembaga.nama_lembaga', strtolower($request->lembaga));
            if ($request->filled('jurusan')) {
                $query->where('jurusan.nama_jurusan', strtolower($request->jurusan));
                if ($request->filled('kelas')) {
                    $query->where('kelas.nama_kelas', strtolower($request->kelas));
                    if ($request->filled('rombel')) {
                        $query->where('rombel.nama_rombel', strtolower($request->rombel));
                    }
                }
            }
        }

        // Filter Status Warga Pesantren
        if ($request->filled('warga_pesantren')) {
            if (strtolower($request->warga_pesantren) === 'memiliki niup') {
                // Hanya tampilkan data yang memiliki NIUP
                $query->whereNotNull('biodata.niup')->where('biodata.niup', '!=', '');
            } elseif (strtolower($request->warga_pesantren) === 'tidak memiliki niup') {
                // Hanya tampilkan data yang tidak memiliki NIUP
                $query->whereNull('biodata.niup')->orWhereRaw("TRIM(biodata.niup) = ''");
            }
        }

        // Filter semua status
        if ($request->filled('semua_status')) {
            $entitas = strtolower($request->semua_status); 
            
            if ($entitas == 'pelajar') {
                $query->whereNotNull('pelajar.id'); 
            } elseif ($entitas == 'santri') {
                $query->whereNotNull('santri.id');
            } elseif ($entitas == 'pelajar dan santri') {
                $query->whereNotNull('pelajar.id')->whereNotNull('santri.id');
            }
        }


        // Filter Angkatan Pelajar
        if ($request->filled('angkatan_pelajar')) {
            $query->where('pelajar.angkatan', strtolower($request->angkatan_pelajar));
        }

        // Filter Angkatan Santri
        if ($request->filled('angkatan_santri')) {
            $query->where('santri.angkatan', strtolower($request->angkatan_santri));
        }

        // Filter No Telepon
        if ($request->filled('phone_number')) {
            if (strtolower($request->phone_number) === 'mempunyai') {
                // Hanya tampilkan data yang memiliki nomor telepon
                $query->whereNotNull('biodata.no_telepon')->where('biodata.no_telepon', '!=', '');
            } elseif (strtolower($request->phone_number) === 'tidak mempunyai') {
                // Hanya tampilkan data yang tidak memiliki nomor telepon
                $query->whereNull('biodata.no_telepon')->orWhere('biodata.no_telepon', '');
            }
        }

        // Filter Sort By
        if ($request->filled('sort_by')) {
            $sort_by = strtolower($request->sort_by);
            $allowedSorts = ['nama', 'niup', 'angkatan', 'jenis kelamin', 'tempat lahir'];
        
            if (in_array($sort_by, $allowedSorts)) { // Validasi hanya jika sort_by ada di daftar
                if ($sort_by === 'angkatan') {
                    $query->orderBy('pelajar.angkatan', 'asc'); // Pastikan tabelnya benar
                } else {
                    $query->orderBy($sort_by, 'asc');
                }
            }
        }

        // Filter Sort Order
        if ($request->filled('sort_order')) {
            $sortOrder = strtolower($request->sort_order) == 'desc' ? 'desc' : 'asc';
            $query->orderBy('anak_pegawai.id', $sortOrder);
        }

        // Filter Pemberkasan (Lengkap / Tidak Lengkap)
        if ($request->filled('pemberkasan')) {
            $jumlahBerkasWajib = JenisBerkas::where('wajib', 1)->count();
            $pemberkasan = strtolower($request->pemberkasan);
            if ($pemberkasan == 'lengkap') {
                $query->havingRaw('COUNT(DISTINCT berkas.id) >= ?', [$jumlahBerkasWajib]);
            } elseif ($pemberkasan == 'tidak lengkap') {
                $query->havingRaw('COUNT(DISTINCT berkas.id) < ?', [$jumlahBerkasWajib]);
            }
        }

        // Ambil jumlah data per halaman (default 10 jika tidak diisi)
        $perPage = $request->input('limit', 25);

        // Ambil halaman saat ini (jika ada)
        $currentPage = $request->input('page', 1);

        // Menerapkan pagination ke hasil
        $hasil = $query->paginate($perPage, ['*'], 'page', $currentPage);


        // Jika Data Kosong
        if ($hasil->isEmpty()) {
            return response()->json([
                "status" => "error",
                "message" => "Data tidak ditemukan",
                "code" => 404
            ], 404);
        }

        return response()->json([
            "total_data" => $hasil->total(),
            "current_page" => $hasil->currentPage(),
            "per_page" => $hasil->perPage(),
            "total_pages" => $hasil->lastPage(),
            "data" => $hasil->map(function ($item) {
                return [
                    "id" => $item->id,
                    "nama" => $item->nama,
                    "niup" => $item->niup,
                    "nis" => $item->nis,
                    "NIK/no.Passport" => $item->identitas,
                    "jurusan" => $item->jurusan,
                    "kelas" => $item->kelas,
                    "wilayah" => $item->nama_wilayah,
                    "blok" => $item->nama_blok,
                    "kamar" => $item->nama_kamar,
                    "asal_kota" => $item->nama_kabupaten,
                    "lembaga" => $item->lembaga,
                    "tgl_update" => $item->tgl_update,
                    "tgl_input" => $item->tgl_input,
                    "foto_profil" => url($item->foto_profil)
                ];
            })
        ]);
    }
    public function menuWilayahBlokKamar()
    {
        $data = DB::table('kamar as k')
            ->select(
                'w.id as wilayah_id',
                'w.nama_wilayah',
                'b.id as blok_id',
                'b.id_wilayah',
                'b.nama_blok',
                'k.id as kamar_id',
                'k.id_blok',
                'k.nama_kamar'
            )
            ->rightJoin('blok as b', 'k.id_blok', '=', 'b.id')
            ->rightJoin('wilayah as w', 'b.id_wilayah', '=', 'w.id')
            ->orderBy('w.id')
            ->get();

        $wilayahs = [];

        foreach ($data as $row) {
            if (!isset($wilayahs[$row->wilayah_id])) {
                $wilayahs[$row->wilayah_id] = [
                    'id' => $row->wilayah_id,
                    'nama_wilayah' => $row->nama_wilayah,
                    'blok' => [],
                ];
            }

            if (!is_null($row->blok_id) && !isset($wilayahs[$row->wilayah_id]['blok'][$row->blok_id])) {
                $wilayahs[$row->wilayah_id]['blok'][$row->blok_id] = [
                    'id' => $row->blok_id,
                    'id_wilayah' => $row->id_wilayah,
                    'nama_blok' => $row->nama_blok,
                    'kamar' => [],
                ];
            }

            if (!is_null($row->kamar_id)) {
                $wilayahs[$row->wilayah_id]['blok'][$row->blok_id]['kamar'][] = [
                    'id' => $row->kamar_id,
                    'id_blok' => $row->id_blok,
                    'nama_kamar' => $row->nama_kamar,
                ];
            }
        }

        $result = [
            'wilayah' => array_values(array_map(function ($wilayah) {
                $wilayah['blok'] = array_values($wilayah['blok']);
                return $wilayah;
            }, $wilayahs)),
        ];

        return response()->json($result);
    }
    public function menuNegaraProvinsiKabupatenKecamatan()
{
    $data = DB::table('kecamatan as kc') // Mulai dari tabel kecamatan
        ->select(
            'n.id as negara_id', 'n.nama_negara',
            'p.id as provinsi_id', 'p.id_negara', 'p.nama_provinsi',
            'kb.id as kabupaten_id', 'kb.id_provinsi', 'kb.nama_kabupaten',
            'kc.id as kecamatan_id', 'kc.id_kabupaten', 'kc.nama_kecamatan'
        )
        ->rightJoin('kabupaten as kb', 'kc.id_kabupaten', '=', 'kb.id')
        ->rightJoin('provinsi as p', 'kb.id_provinsi', '=', 'p.id')
        ->rightJoin('negara as n', 'p.id_negara', '=', 'n.id')
        ->orderBy('n.id') // Urutkan berdasarkan kecamatan.id
        ->get();

    $negara = [];

    foreach ($data as $row) {
        if (!isset($negara[$row->negara_id])) {
            $negara[$row->negara_id] = [
                'id' => $row->negara_id,
                'nama_negara' => $row->nama_negara,
                'provinsi' => [],
            ];
        }

        if (!is_null($row->provinsi_id) && !isset($negara[$row->negara_id]['provinsi'][$row->provinsi_id])) {
            $negara[$row->negara_id]['provinsi'][$row->provinsi_id] = [
                'id' => $row->provinsi_id,
                'id_negara' => $row->id_negara,
                'nama_provinsi' => $row->nama_provinsi,
                'kabupaten' => [],
            ];
        }

        if (!is_null($row->kabupaten_id) && !isset($negara[$row->negara_id]['provinsi'][$row->provinsi_id]['kabupaten'][$row->kabupaten_id])) {
            $negara[$row->negara_id]['provinsi'][$row->provinsi_id]['kabupaten'][$row->kabupaten_id] = [
                'id' => $row->kabupaten_id,
                'id_provinsi' => $row->id_provinsi,
                'nama_kabupaten' => $row->nama_kabupaten,
                'kecamatan' => [],
            ];
        }

        if (!is_null($row->kecamatan_id)) {
            $negara[$row->negara_id]['provinsi'][$row->provinsi_id]['kabupaten'][$row->kabupaten_id]['kecamatan'][] = [
                'id' => $row->kecamatan_id,
                'id_kabupaten' => $row->id_kabupaten,
                'nama_kecamatan' => $row->nama_kecamatan,
            ];
        }
    }

    $result = [
        'negara' => array_values(array_map(function ($negaraItem) {
            $negaraItem['provinsi'] = array_values(array_map(function ($provinsi) {
                $provinsi['kabupaten'] = array_values(array_map(function ($kabupaten) {
                    $kabupaten['kecamatan'] = array_values($kabupaten['kecamatan']);
                    return $kabupaten;
                }, $provinsi['kabupaten']));
                return $provinsi;
            }, $negaraItem['provinsi']));
            return $negaraItem;
        }, $negara)),
    ];

    return response()->json($result);
    }

    public function menuLembagaJurusanKelasRombel()
    {
        $data = DB::table('rombel as r') // Mulai dari tabel rombel
            ->select(
                'l.id as lembaga_id',
                'l.nama_lembaga',
                'j.id as jurusan_id',
                'j.id_lembaga',
                'j.nama_jurusan',
                'k.id as kelas_id',
                'k.id_jurusan',
                'k.nama_kelas',
                'r.id as rombel_id',
                'r.id_kelas',
                'r.nama_rombel'
            )
            ->rightJoin('kelas as k', 'r.id_kelas', '=', 'k.id')
            ->rightJoin('jurusan as j', 'k.id_jurusan', '=', 'j.id')
            ->rightJoin('lembaga as l', 'j.id_lembaga', '=', 'l.id')
            ->orderBy('l.id') // Urutkan berdasarkan lembaga.id
            ->get();

        $lembaga = [];

        foreach ($data as $row) {
            if (!isset($lembaga[$row->lembaga_id])) {
                $lembaga[$row->lembaga_id] = [
                    'id' => $row->lembaga_id,
                    'nama_lembaga' => $row->nama_lembaga,
                    'jurusan' => [],
                ];
            }

            if (!is_null($row->jurusan_id) && !isset($lembaga[$row->lembaga_id]['jurusan'][$row->jurusan_id])) {
                $lembaga[$row->lembaga_id]['jurusan'][$row->jurusan_id] = [
                    'id' => $row->jurusan_id,
                    'id_lembaga' => $row->id_lembaga,
                    'nama_jurusan' => $row->nama_jurusan,
                    'kelas' => [],
                ];
            }

            if (!is_null($row->kelas_id) && !isset($lembaga[$row->lembaga_id]['jurusan'][$row->jurusan_id]['kelas'][$row->kelas_id])) {
                $lembaga[$row->lembaga_id]['jurusan'][$row->jurusan_id]['kelas'][$row->kelas_id] = [
                    'id' => $row->kelas_id,
                    'id_jurusan' => $row->id_jurusan,
                    'nama_kelas' => $row->nama_kelas,
                    'rombel' => [],
                ];
            }

            if (!is_null($row->rombel_id)) {
                $lembaga[$row->lembaga_id]['jurusan'][$row->jurusan_id]['kelas'][$row->kelas_id]['rombel'][] = [
                    'id' => $row->rombel_id,
                    'id_kelas' => $row->id_kelas,
                    'nama_rombel' => $row->nama_rombel,
                ];
            }
        }

        $result = [
            'lembaga' => array_values(array_map(function ($lembagaItem) {
                $lembagaItem['jurusan'] = array_values(array_map(function ($jurusan) {
                    $jurusan['kelas'] = array_values(array_map(function ($kelas) {
                        $kelas['rombel'] = array_values($kelas['rombel']);
                        return $kelas;
                    }, $jurusan['kelas']));
                    return $jurusan;
                }, $lembagaItem['jurusan']));
                return $lembagaItem;
            }, $lembaga)),
        ];

        return response()->json($result);
    }

    public function getAngkatan()
    {
        // Ambil angkatan masuk pelajar
        $angkatanMasukPelajar = DB::table('pelajar')
            ->selectRaw('YEAR(tanggal_masuk_pelajar) as tahun')
            ->groupBy('tahun')
            ->orderBy('tahun')
            ->get()
            ->map(function ($item) {
                return [
                    'tahun' => $item->tahun,
                    'label' => 'Masuk Tahun ' . $item->tahun
                ];
            });

        // Ambil angkatan masuk santri
        $angkatanMasukSantri = DB::table('santri')
            ->selectRaw('YEAR(tanggal_masuk_santri) as tahun')
            ->groupBy('tahun')
            ->orderBy('tahun')
            ->get()
            ->map(function ($item) {
                return [
                    'tahun' => $item->tahun,
                    'label' => 'Masuk Tahun ' . $item->tahun
                ];
            });

        // Ambil angkatan keluar pelajar
        $angkatanKeluarPelajar = DB::table('pelajar')
            ->selectRaw('YEAR(tanggal_keluar_pelajar) as tahun')
            ->whereNotNull('tanggal_keluar_pelajar')
            ->groupBy('tahun')
            ->orderBy('tahun')
            ->get()
            ->map(function ($item) {
                return [
                    'tahun' => $item->tahun,
                    'label' => 'Keluar Tahun ' . $item->tahun
                ];
            });

        // Ambil angkatan keluar santri
        $angkatanKeluarSantri = DB::table('santri')
            ->selectRaw('YEAR(tanggal_keluar_santri) as tahun')
            ->whereNotNull('tanggal_keluar_santri')
            ->groupBy('tahun')
            ->orderBy('tahun')
            ->get()
            ->map(function ($item) {
                return [
                    'tahun' => $item->tahun,
                    'label' => 'Keluar Tahun ' . $item->tahun
                ];
            });

        // Format response JSON
        return response()->json([
            'status' => 'success',
            'data' => [
                'angkatan_masuk' => [
                    'pelajar' => $angkatanMasukPelajar,
                    'santri' => $angkatanMasukSantri
                ],
                'angkatan_keluar' => [
                    'pelajar' => $angkatanKeluarPelajar,
                    'santri' => $angkatanKeluarSantri
                ]
            ]
        ]);
    }
}
