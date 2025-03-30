<?php

namespace App\Http\Controllers\Api\Pegawai;

use App\Http\Controllers\api\FilterController;
use App\Http\Controllers\Controller;
use App\Http\Resources\PdResource;
use App\Models\JenisBerkas;
use App\Models\Kewilayahan\Kamar;
use App\Models\Pegawai\AnakPegawai;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
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
        ->leftJoin('pendidikan_pelajar','pendidikan_pelajar.id_peserta_didik','=','peserta_didik.id')
        ->leftJoin('lembaga','lembaga.id','=','pendidikan_pelajar.id_lembaga')
        ->leftJoin('jurusan','jurusan.id','=','pendidikan_pelajar.id_jurusan')
        ->leftJoin('kelas','kelas.id','=','pendidikan_pelajar.id_kelas')
        ->leftJoin('rombel','rombel.id','=','pendidikan_pelajar.id_rombel')
        ->leftJoin('pegawai','pegawai.id','=','anak_pegawai.id_pegawai')
        ->leftJoin('santri', 'peserta_didik.id', '=', 'santri.id_peserta_didik')
        ->leftJoin('berkas', 'berkas.id_biodata', '=', 'biodata.id')
        ->leftJoin('jenis_berkas', 'berkas.id_jenis_berkas', '=', 'jenis_berkas.id')
        ->leftJoin('domisili_santri','domisili_santri.id_peserta_didik','peserta_didik.id')
        ->leftjoin('wilayah', 'domisili_santri.id_wilayah', '=', 'wilayah.id')
        ->leftjoin('blok', 'domisili_santri.id_blok', '=', 'blok.id')
        ->leftjoin('kamar', 'domisili_santri.id_kamar', '=', 'kamar.id')
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






    // Ternyata ini tidak usah dikarenakan tampilannya tidak ada
    // formTampilanAwal Anak Pegawai
    // private function formTampilanAwal($perPage, $currentPage)
    // {
    //     return AnakPegawai::Active()
    //     ->leftJoin('peserta_didik','peserta_didik.id','=','anak_pegawai.id_peserta_didik')
    //     ->join('biodata','biodata.id','peserta_didik.id_biodata')
    //     ->leftJoin('kabupaten','kabupaten.id','biodata.id_kabupaten')
    //     ->leftJoin('pendidikan_pelajar','pendidikan_pelajar.id_peserta_didik','=','peserta_didik.id')
    //     ->leftJoin('lembaga','lembaga.id','=','pendidikan_pelajar.id_lembaga')
    //     ->leftJoin('jurusan','jurusan.id','=','pendidikan_pelajar.id_jurusan')
    //     ->leftJoin('kelas','kelas.id','=','pendidikan_pelajar.id_kelas')
    //     ->leftJoin('rombel','rombel.id','=','pendidikan_pelajar.id_rombel')
    //     ->leftJoin('pegawai','pegawai.id','=','anak_pegawai.id_pegawai')
    //     ->leftJoin('santri', 'peserta_didik.id', '=', 'santri.id_peserta_didik')
    //     ->leftJoin('berkas', 'berkas.id_biodata', '=', 'biodata.id')
    //     ->leftJoin('jenis_berkas', 'berkas.id_jenis_berkas', '=', 'jenis_berkas.id')
    //     ->leftJoin('domisili_santri','domisili_santri.id_peserta_didik','peserta_didik.id')
    //     ->leftjoin('wilayah', 'domisili_santri.id_wilayah', '=', 'wilayah.id')
    //     ->leftjoin('blok', 'domisili_santri.id_blok', '=', 'blok.id')
    //     ->leftjoin('kamar', 'domisili_santri.id_kamar', '=', 'kamar.id')
    //     ->select(
    //         'anak_pegawai.id',
    //         'biodata.nama',
    //         'biodata.niup',
    //         'santri.nis',
    //         DB::raw("COALESCE(biodata.nik, biodata.no_passport) as identitas"),
    //         'kabupaten.nama_kabupaten',
    //         DB::raw("GROUP_CONCAT(DISTINCT lembaga.nama_lembaga SEPARATOR ', ') as lembaga"),
    //         DB::raw("GROUP_CONCAT(DISTINCT jurusan.nama_jurusan SEPARATOR ', ') as jurusan"),
    //         DB::raw("GROUP_CONCAT(DISTINCT kelas.nama_kelas SEPARATOR ', ') as kelas"),
    //         'wilayah.nama_wilayah',
    //         'blok.nama_blok',
    //         'kamar.nama_kamar',
    //         DB::raw("DATE_FORMAT(anak_pegawai.updated_at, '%Y-%m-%d %H:%i:%s') AS tgl_update"),
    //         DB::raw("DATE_FORMAT(anak_pegawai.created_at, '%Y-%m-%d %H:%i:%s') AS tgl_input"),
    //         DB::raw("COALESCE(MAX(berkas.file_path), 'default.jpg') as foto_profil")
    //     )
    //     ->groupBy(
    //         'anak_pegawai.id', 
    //         'biodata.nama', 
    //         'biodata.niup', 
    //         'santri.nis',
    //         'biodata.nik', 
    //         'biodata.no_passport',
    //         'wilayah.nama_wilayah',
    //         'blok.nama_blok',
    //         'kamar.nama_kamar',
    //         'kabupaten.nama_kabupaten',
    //         'anak_pegawai.updated_at',
    //         'anak_pegawai.created_at'
    //     )            
    //     ->distinct() // Menghindari duplikasi data
    //     ->paginate($perPage, ['*'], 'page', $currentPage);
    // }

    // formDetail Anak Pegawai PerID
    private function formDetail($idAnakPegawai)
    {
        $biodata = AnakPegawai::where('anak_pegawai.id',$idAnakPegawai)
                        ->leftJoin('peserta_didik','peserta_didik.id','anak_pegawai.id_peserta_didik')
                        ->join('biodata', 'peserta_didik.id_biodata', '=', 'biodata.id')
                        ->leftJoin('berkas', 'biodata.id', '=', 'berkas.id_biodata')
                        ->leftJoin('jenis_berkas', 'berkas.id_jenis_berkas', '=', 'jenis_berkas.id')
                        ->leftJoin('keluarga', 'biodata.id', '=', 'keluarga.id_biodata')
                        ->leftJoin('kecamatan', 'biodata.id_kecamatan', '=', 'kecamatan.id')
                        ->leftJoin('kabupaten', 'biodata.id_kabupaten', '=', 'kabupaten.id')
                        ->leftJoin('provinsi', 'biodata.id_provinsi', '=', 'provinsi.id')
                        ->leftJoin('negara', 'biodata.id_negara', '=', 'negara.id')
                        ->select(
                            'keluarga.no_kk',
                            DB::raw("COALESCE(biodata.nik, biodata.no_passport) as identitas"),
                            'biodata.niup',
                            'biodata.nama',
                            'biodata.jenis_kelamin',
                            DB::raw("CONCAT(biodata.tempat_lahir, ', ', DATE_FORMAT(biodata.tanggal_lahir, '%e %M %Y')) as tempat_tanggal_lahir"),
                            DB::raw("CONCAT(biodata.anak_keberapa, ' dari ', biodata.dari_saudara, ' Bersaudara') as anak_dari"),
                            DB::raw("CONCAT(TIMESTAMPDIFF(YEAR, biodata.tanggal_lahir, CURDATE()), ' tahun') as umur"),
                            'kecamatan.nama_kecamatan',
                            'kabupaten.nama_kabupaten',
                            'provinsi.nama_provinsi',
                            'negara.nama_negara',
                            DB::raw("COALESCE(MAX(berkas.file_path), 'default.jpg') as foto_profil")
                        )
                        ->groupBy(
                            'keluarga.no_kk',
                            'biodata.nik',
                            'biodata.no_passport',
                            'biodata.niup',
                            'biodata.nama',
                            'biodata.jenis_kelamin',
                            'biodata.tempat_lahir',
                            'biodata.tanggal_lahir',
                            'biodata.anak_keberapa',
                            'biodata.dari_saudara',
                            'kecamatan.nama_kecamatan',
                            'kabupaten.nama_kabupaten',
                            'provinsi.nama_provinsi',
                            'negara.nama_negara'
                        )
                        ->first();
        if ($biodata) {
            $data['biodata'] = [
                "nokk" => $biodata->no_kk,
                "nik/nopassport" => $biodata->identitas,
                "niup" => $biodata->niup,
                "nama" => $biodata->nama,
                "jenis_kelamin" => $biodata->jenis_kelamin,
                "Tempat, Tanggal Lahir" => $biodata->tempat_tanggal_lahir,
                "Anak Ke" => $biodata->anak_dari,
                "umur" => $biodata->umur,
                "Kecamatan" => $biodata->nama_kecamatan,
                "Kabupaten" => $biodata->nama_kabupaten,
                "Provinsi" => $biodata->nama_provinsi,
                "Warganegara" => $biodata->nama_negara,
                "foto_profil" => url($biodata->foto_profil)
            ];
        }
        // **2. DATA KELUARGA (Jika Ada)**

        $keluarga = AnakPegawai::where('anak_pegawai.id', $idAnakPegawai)
            ->join('peserta_didik','peserta_didik.id','anak_pegawai.id_peserta_didik')
            ->join('biodata as b_anak', 'peserta_didik.id_biodata', '=', 'b_anak.id')
            ->join('keluarga as k_anak', 'b_anak.id', '=', 'k_anak.id_biodata') // Cari No KK anak
            ->leftjoin('keluarga as k_ortu', 'k_anak.no_kk', '=', 'k_ortu.no_kk') // Cari anggota keluarga lain dengan No KK yang sama
            ->join('orang_tua_wali as otw', 'k_ortu.id_biodata', '=', 'otw.id_biodata')
            ->join('biodata as b_ortu', 'otw.id_biodata', '=', 'b_ortu.id') // Hubungkan orang tua ke biodata mereka
            ->join('hubungan_keluarga as hk', 'otw.id_hubungan_keluarga', '=', 'hk.id') // Status hubungan keluarga
            ->select(
                'b_ortu.nama',
                'b_ortu.nik',
                'hk.nama_status',
                'otw.wali'
            )
            ->get();

        if ($keluarga->isNotEmpty()) {
            $data['keluarga'] = $keluarga->map(function ($item) {
                return [
                    "nama" => $item->nama,
                    "nik" => $item->nik,
                    "status" => $item->nama_status,
                    "wali" => $item->wali,
                ];
            })->toArray();
        }
        
        $statusSantri = AnakPegawai::where('anak_pegawai.id', $idAnakPegawai)
                                ->leftJoin('peserta_didik', 'anak_pegawai.id_peserta_didik', '=', 'peserta_didik.id') 
                                ->join('biodata', 'peserta_didik.id_biodata', '=', 'biodata.id') 
                                ->leftJoin('santri', 'santri.id_peserta_didik', '=', 'peserta_didik.id')
                                ->select(
                                    'santri.nis',
                                    DB::raw("
                                        CONCAT(
                                            'Sejak ', DATE_FORMAT(santri.tanggal_masuk_santri, '%e %M %Y'),
                                            ' sampai ',
                                            IFNULL(DATE_FORMAT(santri.tanggal_keluar_santri, '%e %M %Y'), 'saat ini')
                                        ) AS keterangan
                                    "),
                                    'santri.tanggal_masuk_santri',
                                    'santri.tanggal_keluar_santri'
                                )
                                ->distinct()
                                ->first();

        if ($statusSantri) { 
            $data['santri'] = [
                "NIS" => $statusSantri->nis,
                "keterangan" => $statusSantri->keterangan,
                "tanggal_mulai" => $statusSantri->tanggal_masuk_santri,
                "tanggal_akhir" => $statusSantri->tanggal_keluar_santri,
            ];
        }

        // // **4. DOMISILI (Jika Ada)**

        $domisili = AnakPegawai::where('anak_pegawai.id', $idAnakPegawai)
                            ->leftJoin('peserta_didik', 'anak_pegawai.id_peserta_didik', '=', 'peserta_didik.id') 
                            ->join('biodata', 'peserta_didik.id_biodata', '=', 'biodata.id') 
                            ->leftJoin('domisili_santri','domisili_santri.id_peserta_didik','=','peserta_didik.id')
                            ->leftJoin('wilayah','wilayah.id','=','domisili_santri.id_wilayah')
                            ->select(
                                'wilayah.nama_wilayah',
                                DB::raw("
                                CONCAT(
                                    'Sejak ', DATE_FORMAT(domisili_santri.tanggal_masuk, '%e %M %Y %H:%i:%s'),
                                    ' sampai ',
                                    IFNULL(DATE_FORMAT(domisili_santri.tanggal_keluar, '%e %M %Y %H:%i:%s'), 'saat ini')
                                ) AS keterangan
                            ")                            
                            )
                            ->distinct()
                            ->first();
        if ($domisili){
            $data['domisili'] = [
                "wilayah" => $domisili->nama_wilayah,
                "keterangan" => $domisili->keterangan
            ];
        }

        // // **5. WALI ASUH (Jika Ada)**

        $waliAsuh = AnakPegawai::where('anak_pegawai.id', $idAnakPegawai)
                            ->leftJoin('peserta_didik', 'anak_pegawai.id_peserta_didik', '=', 'peserta_didik.id') 
                            ->join('biodata', 'peserta_didik.id_biodata', '=', 'biodata.id') 
                            ->leftJoin('santri','santri.id_peserta_didik','=','peserta_didik.id')
                            ->leftJoin('wali_asuh','wali_asuh.nis','=','santri.nis')
                            ->select(
                                'wali_asuh.nis',
                                DB::raw("
                                    CONCAT(
                                        'Sejak ', DATE_FORMAT(santri.tanggal_masuk_santri, '%e %M %Y'),
                                        ' sampai ',
                                        IFNULL(DATE_FORMAT(santri.tanggal_keluar_santri, '%e %M %Y'), 'saat ini')
                                    ) AS keterangan
                                "),
                                'santri.tanggal_masuk_santri',
                                'santri.tanggal_keluar_santri'
                            )
                            ->distinct()
                            ->first();

    if ($waliAsuh) { 
        $data['WaliAsuh'] = [
            "NIS" => $waliAsuh->nis,
            "keterangan" => $waliAsuh->keterangan,
            "tanggal_mulai" => $waliAsuh->tanggal_masuk_santri,
            "tanggal_akhir" => $waliAsuh->tanggal_keluar_santri,
        ];
    }
        
        // // **6. PENDIDIKAN (Jika Ada)**

        $pendidikan = AnakPegawai::where('anak_pegawai.id', $idAnakPegawai)
                        ->leftJoin('peserta_didik', 'anak_pegawai.id_peserta_didik', '=', 'peserta_didik.id') 
                        ->join('biodata', 'peserta_didik.id_biodata', '=', 'biodata.id') 
                        ->leftJoin('pelajar','pelajar.id_peserta_didik','=','peserta_didik.id')
                        ->leftJoin('pendidikan_pelajar','pendidikan_pelajar.id_peserta_didik','=','peserta_didik.id')
                        ->leftJoin('lembaga','lembaga.id','=','pendidikan_pelajar.id_lembaga')
                        ->leftJoin('jurusan','jurusan.id','=','pendidikan_pelajar.id_jurusan')
                        ->leftJoin('rombel','rombel.id','=','pendidikan_pelajar.id_rombel')
                        ->leftJoin('kelas','kelas.id','=','pendidikan_pelajar.id_kelas')
                        ->select(
                                DB::raw("CONCAT(lembaga.nama_lembaga, ' - ', jurusan.nama_jurusan) AS lembaga_jurusan"),
                                DB::raw("
                                    CONCAT(
                                        'Sejak ', DATE_FORMAT(pendidikan_pelajar.tanggal_masuk, '%e %M %Y %H:%i:%s'),
                                        ' sampai ',
                                        IFNULL(DATE_FORMAT(pendidikan_pelajar.tanggal_keluar, '%e %M %Y %H:%i:%s'), 'saat ini')
                                    ) AS keterangan
                                "),
                                'lembaga.nama_lembaga',
                                'jurusan.nama_jurusan',
                                'kelas.nama_kelas',
                                'rombel.nama_rombel',
                                'pelajar.no_induk',
                                'pendidikan_pelajar.tanggal_masuk',
                                'pendidikan_pelajar.tanggal_keluar'
                        )
                        ->distinct()
                        ->first();

        if ($pendidikan) { 
            $data['Pendidikan'] = [
                "lembaga_jurusan" => $pendidikan->lembaga_jurusan,
                "keterangan" => $pendidikan->keterangan,
                "lembaga" => $pendidikan->nama_lembaga,
                "jurusan" => $pendidikan->nama_jurusan,
                "kelas" => $pendidikan->nama_kelas,
                "rombel" => $pendidikan->nama_rombel,
                "no_induk" => $pendidikan->no_induk,
                "tanggal_mulai" => $pendidikan->tanggal_masuk,
                "tanggal_akhir" => $pendidikan->tanggal_keluar,
            ];
        }

        // // **6. Warga Pesantren (Jika Ada)**
        $Wargapesantren = AnakPegawai::where('anak_pegawai.id', $idAnakPegawai)
                        ->leftJoin('peserta_didik', 'anak_pegawai.id_peserta_didik', '=', 'peserta_didik.id') 
                        ->join('biodata', 'peserta_didik.id_biodata', '=', 'biodata.id') 
                        ->select(
                            'biodata.niup',
                            DB::raw("
                            CASE 
                                WHEN biodata.status = 1 THEN 'Iya'
                                ELSE 'Tidak'
                            END AS aktif
                        ")
                        )->distinct()
                         ->first();
        if ($Wargapesantren) { 
            $data['WargaPesantren'] = [
                "niup" => $Wargapesantren->niup,
                "aktif" => $Wargapesantren->aktif
            ];
        }

        return $data;
    }
     // **Mengambil Data Anak Pegawai (Tampilan Awal + Detail)**
     public function getAnakPegawai($id)
     {
             $data = $this->formDetail($id); 
         
             return response()->json([
                 "data" => [$data],
             ]);
    }
}
