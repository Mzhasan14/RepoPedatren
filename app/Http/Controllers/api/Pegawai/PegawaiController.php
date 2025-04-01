<?php

namespace App\Http\Controllers\Api\Pegawai;

use App\Http\Controllers\api\FilterController;
use App\Http\Controllers\Controller;
use App\Http\Resources\PdResource;
use App\Models\JenisBerkas;
use App\Models\Pegawai\Pegawai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PegawaiController extends Controller
{
    protected $filterController;

    public function __construct(FilterController $filterController)
    {
        $this->filterController = $filterController;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $pegawai = Pegawai::all();
        return new PdResource(true,'Data berhasil ditampilkan', $pegawai);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'id_biodata' => 'required|integer',
            'created_by' => 'required|integer',
            'status'     => 'required|boolean',
        ]);
        if($validator->fails()){
            return response()->json([
                'status' => false,
                'message' => 'Data gagal buat',
                'data' => $validator->errors()
            ]);
        }

        $pegawai = Pegawai::create($validator->validated());
        return new PdResource(true, 'Data berhasil ditambahkan', $pegawai);
    }

    public function show(string $id)
    {
        $pegawai = Pegawai::findOrFail($id);
        return new PdResource(true,'Data berhasil ditampilkan',$pegawai);
    }


    public function update(Request $request, string $id)
    {
        $pegawai = Pegawai::findOrFail($id);
        $validator = Validator::make($request->all(),[
            'id_biodata' => 'required|integer',
            'updated_by' => 'nullable|integer',
            'status'     => 'required|boolean',
        ]);
        if($validator->fails()){
            return response()->json([
                'status' => false,
                'message' => 'Data gagal buat',
                'data' => $validator->errors()
            ]);
        }
        $pegawai->update($validator->validated());
        return new PdResource(true,'Data berhasil diupdate',$pegawai);
        
    }

    public function destroy(string $id)
    {
        $pegawai = Pegawai::findOrFail($id);
        $pegawai->delete();
        return new PdResource(true,'Data berhasil dihapus',$pegawai);
    }

    public function dataPegawai(Request $request)
    {
        $query = Pegawai::Active()
                        ->join('biodata','biodata.id','pegawai.id_biodata')
                        ->leftJoin('kabupaten','kabupaten.id','biodata.id_kabupaten')
                        ->leftJoin('pengajar','pengajar.id_pegawai','=','pegawai.id')
                        ->leftJoin('pengurus','pengurus.id_pegawai','=','pegawai.id')
                        ->leftJoin('karyawan','karyawan.id_pegawai','=','pegawai.id')
                        ->leftJoin('rombel','pegawai.id_rombel','=','rombel.id')
                        ->leftJoin('kelas','pegawai.id_kelas','=','kelas.id')
                        ->leftJoin('jurusan','pegawai.id_jurusan','=','jurusan.id')
                        ->leftJoin('lembaga','pegawai.id_lembaga','=','lembaga.id')
                        ->leftJoin('entitas_pegawai','entitas_pegawai.id_pegawai','=','pegawai.id')
                        ->leftJoin('berkas', 'berkas.id_biodata', '=', 'biodata.id')
                        ->leftJoin('jenis_berkas', 'berkas.id_jenis_berkas', '=', 'jenis_berkas.id')
                        ->select(
                            'pegawai.id as id',
                            'biodata.nama as nama',
                            'biodata.niup',
                            'pengurus.id as pengurus',
                            'karyawan.id as karyawan',
                            'pengajar.id as pengajar',
                            DB::raw("TIMESTAMPDIFF(YEAR, biodata.tanggal_lahir, CURDATE()) AS umur"),
                            DB::raw("TRIM(BOTH ', ' FROM CONCAT_WS(', ', 
                            GROUP_CONCAT(DISTINCT CASE WHEN pengajar.id IS NOT NULL THEN 'Pengajar' END SEPARATOR ', '),
                            GROUP_CONCAT(DISTINCT CASE WHEN karyawan.id IS NOT NULL THEN 'Karyawan' END SEPARATOR ', '),
                            GROUP_CONCAT(DISTINCT CASE WHEN pengurus.id IS NOT NULL THEN 'Pengurus' END SEPARATOR ', ')
                        )) as status"),
                            'biodata.nama_pendidikan_terakhir as pendidikanTerkahir',
                            DB::raw("COALESCE(MAX(berkas.file_path), 'default.jpg') as foto_profil")
                            )->groupBy(
                                'pegawai.id', 
                                'biodata.nama',
                                'biodata.niup',
                                'pengurus.id',
                                'karyawan.id',
                                'pengajar.id',
                                'biodata.tanggal_lahir',
                                'biodata.nama_pendidikan_terakhir'
                            );


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
                    ->orWhere('kabupaten.nama_kabupaten', 'LIKE', "%$search%");
                    });
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
        if ($request->filled('entitas')) {
            $entitas = strtolower($request->entitas); 
            
            if ($entitas == 'pengajar') {
                $query->whereNotNull('pengajar.id');
            } elseif ($entitas == 'pengurus') {
                $query->whereNotNull('pengurus.id');
            } elseif ($entitas == 'karyawan') {
                $query->whereNotNull('karyawan.id');
            } elseif ($entitas == 'pengajar pengurus') {
                $query->whereNotNull('pengajar.id')
                      ->orWhereNotNull('pengurus.id');
            } elseif ($entitas == 'pengajar karyawan') {
                $query->whereNotNull('pengajar.id')
                      ->orWhereNotNull('karyawan.id');
            } elseif ($entitas == 'pengurus karyawan') {
                $query->whereNotNull('pengurus.id')
                      ->orWhereNotNull('karyawan.id');
            } elseif ($entitas == 'pengajar pengurus karyawan') {
                $query->whereNotNull('pengajar.id')
                      ->orWhereNotNull('pengurus.id')
                      ->orWhereNotNull('karyawan.id');
            }
        }
        
                // Filter Warga Pesantren 
         if ($request->filled('warga_pesantren')) {
            if (strtolower($request->warga_pesantren) === 'memiliki niup') {
                // Hanya tampilkan data yang memiliki NIUP
                $query->whereNotNull('biodata.niup')->where('biodata.niup', '!=', '');
            } elseif (strtolower($request->warga_pesantren) === 'tidak memiliki niup') {
                // Hanya tampilkan data yang tidak memiliki NIUP
                $query->whereNull('biodata.niup')->orWhereRaw("TRIM(biodata.niup) = ''");
            }
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
                // Filter Umur 
            if ($request->filled('umur')) {
            $umurInput = $request->umur;
        
            // Cek apakah input umur dalam format rentang (misalnya "20-25")
            if (strpos($umurInput, '-') !== false) {
                [$umurMin, $umurMax] = explode('-', $umurInput);
            } else {
                // Jika input angka tunggal, jadikan batas atas dan bawah sama
                $umurMin = $umurInput;
                $umurMax = $umurInput;
            }
        
            // Filter berdasarkan umur yang dihitung dari tanggal_lahir
            $query->whereBetween(
                DB::raw('TIMESTAMPDIFF(YEAR, biodata.tanggal_lahir, CURDATE())'),
                [(int)$umurMin, (int)$umurMax]
            );
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

        $onePage = $request->input('limit', 25);

        $currentPage =  $request->input('page', 1);

        $hasil = $query->paginate($onePage, ['*'], 'page', $currentPage);


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
                    "umur" => $item->umur,
                    "status" => $item->status,
                    "pendidikanTerkahir" => $item->pendidikanTerkahir,
                    "pengurus" => $item->pengurus ? true : false,
                    "karyawan" => $item->karyawan ? true : false,
                    "pengajar" => $item->pengajar ? true : false,
                    "foto_profil" => url($item->foto_profil)
                ];
            })
        ]);
    }

    // Ternyata ini tidak usah dikarenakan tampilannya tidak ada
    // private function getFormTampilanList($perPage,$currentPage)
    // {
    //     return Pegawai::Active()
    //     ->join('biodata','biodata.id','pegawai.id_biodata')
    //     ->leftJoin('kabupaten','kabupaten.id','biodata.id_kabupaten')
    //     ->leftJoin('pengajar','pengajar.id_pegawai','=','pegawai.id')
    //     ->leftJoin('pengurus','pengurus.id_pegawai','=','pegawai.id')
    //     ->leftJoin('karyawan','karyawan.id_pegawai','=','pegawai.id')
    //     ->leftJoin('rombel','pegawai.id_rombel','=','rombel.id')
    //     ->leftJoin('kelas','pegawai.id_kelas','=','kelas.id')
    //     ->leftJoin('jurusan','pegawai.id_jurusan','=','jurusan.id')
    //     ->leftJoin('lembaga','pegawai.id_lembaga','=','lembaga.id')
    //     ->leftJoin('entitas_pegawai','entitas_pegawai.id_pegawai','=','pegawai.id')
    //     ->leftJoin('berkas', 'berkas.id_biodata', '=', 'biodata.id')
    //     ->leftJoin('jenis_berkas', 'berkas.id_jenis_berkas', '=', 'jenis_berkas.id')
    //     ->select(
    //         'pegawai.id as id',
    //         'biodata.nama as nama',
    //         'biodata.niup',
    //         'pengurus.id as pengurus',
    //         'karyawan.id as karyawan',
    //         'pengajar.id as pengajar',
    //         DB::raw("TIMESTAMPDIFF(YEAR, biodata.tanggal_lahir, CURDATE()) AS umur"),
    //         DB::raw("TRIM(BOTH ', ' FROM CONCAT_WS(', ', 
    //         GROUP_CONCAT(DISTINCT CASE WHEN pengajar.id IS NOT NULL THEN 'Pengajar' END SEPARATOR ', '),
    //         GROUP_CONCAT(DISTINCT CASE WHEN karyawan.id IS NOT NULL THEN 'Karyawan' END SEPARATOR ', '),
    //         GROUP_CONCAT(DISTINCT CASE WHEN pengurus.id IS NOT NULL THEN 'Pengurus' END SEPARATOR ', ')
    //     )) as status"),
    //         'biodata.nama_pendidikan_terakhir as pendidikanTerkahir',
    //         DB::raw("COALESCE(MAX(berkas.file_path), 'default.jpg') as foto_profil")
    //         )->groupBy(
    //             'pegawai.id', 
    //             'biodata.nama',
    //             'biodata.niup',
    //             'pengurus.id',
    //             'karyawan.id',
    //             'pengajar.id',
    //             'biodata.tanggal_lahir',
    //             'biodata.nama_pendidikan_terakhir'
    //         )->distinct()
    //          ->paginate($perPage, ['*'], 'page', $currentPage);

    // }
    private function getFormDetail($idPegawai)
    {
        $biodata = Pegawai::where('pegawai.id',$idPegawai)
                        ->join('biodata', 'pegawai.id_biodata', '=', 'biodata.id')
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

        $keluarga = Pegawai::where('pegawai.id', $idPegawai)
            ->join('biodata', 'pegawai.id_biodata', '=', 'biodata.id')
            ->join('peserta_didik','peserta_didik.id_biodata','biodata.id')
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
            ->distinct()
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
        // // **3. STATUS SANTRI (Jika Ada)**

        $statusSantri = Pegawai::where('pegawai.id', $idPegawai)
                                ->join('biodata', 'pegawai.id_biodata', '=', 'biodata.id')
                                ->leftJoin('peserta_didik','peserta_didik.id_biodata','biodata.id')
                                ->leftJoin('santri','santri.id_peserta_didik','=','peserta_didik.id')
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

        $domisili = Pegawai::where('pegawai.id', $idPegawai)
                            ->join('biodata', 'pegawai.id_biodata', '=', 'biodata.id')
                            ->leftJoin('peserta_didik','peserta_didik.id_biodata','=','biodata.id')
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

        $waliAsuh = Pegawai::where('pegawai.id', $idPegawai)
                            ->join('biodata', 'pegawai.id_biodata', '=', 'biodata.id')
                            ->leftJoin('peserta_didik','peserta_didik.id_biodata','=','biodata.id')
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

        $pendidikan = Pegawai::where('pegawai.id', $idPegawai)
                        ->join('biodata', 'pegawai.id_biodata', '=', 'biodata.id')
                        ->leftJoin('peserta_didik','peserta_didik.id_biodata','=','biodata.id')
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

        // // **6. KARYAWAN (Jika Ada)**

        $Karyawan = Pegawai::where('pegawai.id', $idPegawai)
                        ->join('biodata', 'pegawai.id_biodata', '=', 'biodata.id')
                        ->leftJoin('karyawan', 'karyawan.id_pegawai', '=', 'pegawai.id') 
                        ->leftJoin('riwayat_jabatan_karyawan', 'riwayat_jabatan_karyawan.id_karyawan', '=', 'karyawan.id')
                        ->select(
                            'riwayat_jabatan_karyawan.keterangan_jabatan',
                            DB::raw("
                                CONCAT(
                                    'Sejak ', DATE_FORMAT(riwayat_jabatan_karyawan.tanggal_mulai, '%e %b %Y'),
                                    ' Sampai ',
                                    IFNULL(DATE_FORMAT(riwayat_jabatan_karyawan.tanggal_selesai, '%e %b %Y'), 'Sekarang')
                                ) AS masa_jabatan
                            ")
                        )->orderBy('riwayat_jabatan_karyawan.tanggal_mulai', 'asc')
                         ->distinct()
                         ->get(); 
        if ($Karyawan->isNotEmpty()) {
            $data['karyawan'] = $Karyawan->map(function ($item) {
                return [
                    "keterangan_jabatan" => $item->keterangan_jabatan,
                    "masa_jabatan" => $item->masa_jabatan,
                ];
            })->toArray();
        }

        // // **6. Pengajar**
        
        $pengurus = Pegawai::where('pegawai.id', $idPegawai)
        ->join('pengajar','pegawai.id','=','pengajar.id_pegawai')
        ->leftJoin('lembaga','lembaga.id','=','pegawai.id_lembaga')
        ->join('biodata', 'pegawai.id_biodata', '=', 'biodata.id')
        ->leftJoin('golongan','golongan.id','=','pengajar.id_golongan')
        ->leftJoin('kategori_golongan','kategori_golongan.id','=','golongan.id_kategori_golongan')
        ->leftJoin('materi_ajar','materi_ajar.id_pengajar','=','pengajar.id')
        ->select(
            'lembaga.nama_lembaga',
            'pengajar.jabatan as PekerjaanKontrak',
            'kategori_golongan.nama_kategori_golongan',
            'golongan.nama_golongan',
            DB::raw("
            CONCAT(
                'Sejak ', DATE_FORMAT(pengajar.tahun_masuk, '%e %M %Y %H:%i:%s'),
                ' sampai ',
                IFNULL(DATE_FORMAT(pengajar.tahun_keluar, '%e %M %Y %H:%i:%s'), 'saat ini')
                ) AS keterangan
                "),
                DB::raw("
                CONCAT(
                    FLOOR(SUM(materi_ajar.jumlah_menit) / 60), ' jam ',
                    MOD(SUM(materi_ajar.jumlah_menit), 60), ' menit'
                    ) AS total_waktu_materi
                    "),
                    DB::raw('COUNT(DISTINCT materi_ajar.id) as total_materi')
                    )
                    ->groupBy(
                        'lembaga.nama_lembaga',
                        'pengajar.jabatan',
                        'kategori_golongan.nama_kategori_golongan',
                        'golongan.nama_golongan',
                        'pengajar.tahun_masuk',
                        'pengajar.tahun_keluar'
                        )
                        ->first();
                        if ($pengurus) {
                            $data['pengajar'] = [
                                "nama_lembaga" => $pengurus->nama_lembaga, // BENAR
                                "PekerjaanKontrak" => $pengurus->PekerjaanKontrak,
                                "kategori_golongan" => $pengurus->nama_kategori_golongan,
                                "golongan" => $pengurus->nama_golongan,
                                "keterangan" => $pengurus->keterangan,
                                "total_waktu_materi" => $pengurus->total_waktu_materi, // Harus sesuai dengan nama di SELECT
                                "total_materi" => $pengurus->total_materi, // Harus sesuai dengan nama di SELECT
                            ];
                        }
               // // **6. Pengurus**

            $pengurus = Pegawai::where('pegawai.id', $idPegawai)
                                ->join('pengurus','pegawai.id','=','pengurus.id_pegawai')
                                ->join('biodata', 'pegawai.id_biodata', '=', 'biodata.id')
                                ->select(
                                    'pengurus.keterangan_jabatan',
                                    DB::raw("
                                        CONCAT(
                                            'Sejak ', DATE_FORMAT(pengurus.tahun_masuk, '%e %b %Y'),
                                            ' Sampai ',
                                            IFNULL(DATE_FORMAT(pengurus.tahun_keluar, '%e %b %Y'), 'Sekarang')
                                        ) AS masa_jabatan
                                    ")
                                )->distinct()
                                ->first(); 
            if ($pengurus) {
            $data['pengurus'] = [
                    "keterangan_jabatan" => $pengurus->keterangan_jabatan,
                    "masa_jabatan" => $pengurus->masa_jabatan,
                ];
            }


        // // **8. Warga Pesantren (Jika Ada)**
        $Wargapesantren = Pegawai::where('pegawai.id', $idPegawai)
                        ->join('biodata', 'pegawai.id_biodata', '=', 'biodata.id')
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
     // **Mengambil Data Karyawan ( Detail)**
     public function getPegawai($idPegawai)
     {
         $data = $this->getFormDetail($idPegawai); 
     
         return response()->json([
             "data" => [$data],
         ]);
     }
}
