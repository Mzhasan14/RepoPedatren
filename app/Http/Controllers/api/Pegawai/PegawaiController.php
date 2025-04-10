<?php

namespace App\Http\Controllers\Api\Pegawai;

use App\Http\Controllers\api\FilterController;
use App\Http\Controllers\Controller;
use App\Http\Resources\PdResource;
use App\Models\JenisBerkas;
use App\Models\Pegawai\Pegawai;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class PegawaiController extends Controller
{
    protected $filterController;
    protected $filter;

    public function __construct()
    {
        // Inisialisasi controller filter
        $this->filterController = new FilterController();
        $this->filter = new FilterKepegawaianController();
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
                        ->join('biodata as b','b.id','pegawai.id_biodata')
                        ->leftJoin('peserta_didik as pd','b.id','pd.id_biodata')
                        ->leftJoin('santri as s','pd.id','s.id_peserta_didik')
                        ->leftJoin('domisili_santri as ds','s.id','ds.id_santri')
                        ->leftJoin('wilayah as w','ds.id_wilayah','w.id')
                        ->leftJoin('kabupaten as kb','kb.id','b.id_kabupaten')
                        ->leftJoin('warga_pesantren as wp','b.id','=','wp.id_biodata')
                        ->leftJoin('pengajar','pengajar.id_pegawai','=','pegawai.id')
                        ->leftJoin('pengurus','pengurus.id_pegawai','=','pegawai.id')
                        ->leftJoin('karyawan','karyawan.id_pegawai','=','pegawai.id')
                        ->leftJoin('rombel as r','pegawai.id_rombel','=','r.id')
                        ->leftJoin('kelas as k','pegawai.id_kelas','=','k.id')
                        ->leftJoin('jurusan as j','pegawai.id_jurusan','=','j.id')
                        ->leftJoin('lembaga as l','pegawai.id_lembaga','=','l.id')
                        ->leftJoin('entitas_pegawai','entitas_pegawai.id_pegawai','=','pegawai.id')
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
                        ->select(
                            'pegawai.id as id',
                            'b.nama as nama',
                            'wp.niup',
                            'pengurus.id as pengurus',
                            'karyawan.id as karyawan',
                            'pengajar.id as pengajar',
                            DB::raw("TIMESTAMPDIFF(YEAR, b.tanggal_lahir, CURDATE()) AS umur"),
                            DB::raw("TRIM(BOTH ', ' FROM CONCAT_WS(', ', 
                            GROUP_CONCAT(DISTINCT CASE WHEN pengajar.id IS NOT NULL THEN 'Pengajar' END SEPARATOR ', '),
                            GROUP_CONCAT(DISTINCT CASE WHEN karyawan.id IS NOT NULL THEN 'Karyawan' END SEPARATOR ', '),
                            GROUP_CONCAT(DISTINCT CASE WHEN pengurus.id IS NOT NULL THEN 'Pengurus' END SEPARATOR ', ')
                        )) as status"),
                            'b.nama_pendidikan_terakhir as pendidikanTerkahir',
                            DB::raw("COALESCE(MAX(br.file_path), 'default.jpg') as foto_profil")
                            )->groupBy(
                                'pegawai.id', 
                                'b.nama',
                                'wp.niup',
                                'pengurus.id',
                                'karyawan.id',
                                'pengajar.id',
                                'b.tanggal_lahir',
                                'b.nama_pendidikan_terakhir'
                            );


        $query = $this->filterController->applyCommonFilters($query, $request);
        $query = $this->filter->applySearchFilter($query, $request);
        $query = $this->filter->applyLembagaFilter($query, $request);
        $query = $this->filter->applyEntitasPegawaiFilter($query, $request);
        $query = $this->filter->applyWargaPesantrenFilter($query, $request);
        $query = $this->filter->applyPemberkasanFilter($query, $request);
        $query = $this->filter->applyUmurFilter($query, $request);
        $query = $this->filter->applyPhoneFilter($query, $request);


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

    private function getFormDetail($idPegawai)
    {
        $biodata = Pegawai::where('pegawai.id',$idPegawai)
                        ->join('biodata as b', 'pegawai.id_biodata', '=', 'b.id')
                        ->leftJoin('warga_pesantren as wp', 'b.id', '=', 'wp.id_biodata')
                        ->leftJoin('berkas as br', 'b.id', '=', 'br.id_biodata')
                        ->leftJoin('keluarga as k', 'b.id', '=', 'k.id_biodata')
                        ->leftJoin('kecamatan as kc', 'b.id_kecamatan', '=', 'kc.id')
                        ->leftJoin('kabupaten as kb', 'b.id_kabupaten', '=', 'kb.id')
                        ->leftJoin('provinsi as pv', 'b.id_provinsi', '=', 'pv.id')
                        ->leftJoin('negara as ng', 'b.id_negara', '=', 'ng.id')
                        ->where('pegawai.status',1)
                        ->select(
                            'k.no_kk',
                            DB::raw("COALESCE(b.nik, b.no_passport) as identitas"),
                            'wp.niup',
                            'b.nama',
                            'b.jenis_kelamin',
                            DB::raw("CONCAT(b.tempat_lahir, ', ', DATE_FORMAT(b.tanggal_lahir, '%e %M %Y')) as tempat_tanggal_lahir"),
                            DB::raw("CONCAT(b.anak_keberapa, ' dari ', b.dari_saudara, ' Bersaudara') as anak_dari"),
                            DB::raw("CONCAT(TIMESTAMPDIFF(YEAR, b.tanggal_lahir, CURDATE()), ' tahun') as umur"),
                            'kc.nama_kecamatan',
                            'kb.nama_kabupaten',
                            'pv.nama_provinsi',
                            'ng.nama_negara',
                            DB::raw("COALESCE(MAX(br.file_path), 'default.jpg') as foto_profil")
                        )
                        ->groupBy(
                            'k.no_kk',
                            'b.nik',
                            'b.no_passport',
                            'wp.niup',
                            'b.nama',
                            'b.jenis_kelamin',
                            'b.tempat_lahir',
                            'b.tanggal_lahir',
                            'b.anak_keberapa',
                            'b.dari_saudara',
                            'kc.nama_kecamatan',
                            'kb.nama_kabupaten',
                            'pv.nama_provinsi',
                            'ng.nama_negara'
                        )
                        ->first();
            if (!$biodata) {
                return ['error' => 'Data tidak ditemukan'];
            }

            // Format data Biodata
            $data = [];
            $data['Biodata'] = [
                "nokk"                 => $biodata->no_kk ?? '-',
                "nik_nopassport"       => $biodata->identitas,
                "niup"                 => $biodata->niup ?? '-',
                "nama"                 => $biodata->nama,
                "jenis_kelamin"        => $biodata->jenis_kelamin,
                "tempat_tanggal_lahir" => $biodata->tempat_tanggal_lahir,
                "anak_ke"              => $biodata->anak_dari,
                "umur"                 => $biodata->umur,
                "kecamatan"            => $biodata->nama_kecamatan ?? '-',
                "kabupaten"            => $biodata->nama_kabupaten ?? '-',
                "provinsi"             => $biodata->nama_provinsi ?? '-',
                "warganegara"          => $biodata->nama_negara ?? '-',
                "foto_profil"          => URL::to($biodata->foto_profil)
            ];
        //DATA KELUARGA (Jika Ada)

        $keluarga = Pegawai::where('pegawai.id', $idPegawai)
            ->join('biodata as b_anak', 'pegawai.id_biodata', '=', 'b_anak.id')
            ->join('peserta_didik as pd','b_anak.id','pd.id_biodata')
            ->join('pelajar as p', 'pd.id', '=', 'p.id_peserta_didik')
            ->join('keluarga as k_anak', 'b_anak.id', '=', 'k_anak.id_biodata')
            ->leftJoin('keluarga as k_ortu', 'k_anak.no_kk', '=', 'k_ortu.no_kk')
            ->join('orang_tua_wali', 'k_ortu.id_biodata', '=', 'orang_tua_wali.id_biodata')
            ->join('biodata as b_ortu', 'orang_tua_wali.id_biodata', '=', 'b_ortu.id')
            ->join('hubungan_keluarga', 'orang_tua_wali.id_hubungan_keluarga', '=', 'hubungan_keluarga.id')
            ->where('pegawai.status', 1)
            ->select(
                'b_ortu.nama',
                'b_ortu.nik',
                DB::raw("'Orang Tua' as hubungan"),
                'hubungan_keluarga.nama_status',
                'orang_tua_wali.wali'
            )
            ->get();

        // Ambil data saudara kandung (peserta didik lain dalam KK yang sama, tetapi bukan orang tua/wali)
        $saudara = DB::table('keluarga as k_saudara')
            ->join('biodata as b_saudara', 'k_saudara.id_biodata', '=', 'b_saudara.id')
            ->where('k_saudara.no_kk', function ($query) use ($idPegawai) {
                $query->select('k_anak.no_kk')
                    ->from('peserta_didik as pd')
                    ->join('biodata as b_anak', 'pd.id_biodata', '=', 'b_anak.id')
                    ->join('keluarga as k_anak', 'b_anak.id', '=', 'k_anak.id_biodata')
                    ->where('pd.id', $idPegawai)
                    ->limit(1);
            })
            ->whereNotIn('k_saudara.id_biodata', function ($query) {
                $query->select('id_biodata')->from('orang_tua_wali');
            })
            ->whereNotIn('k_saudara.id_biodata', function ($query) use ($idPegawai) {
                $query->select('id_biodata')
                    ->from('peserta_didik')
                    ->where('id', $idPegawai);
            })
            ->select(
                'b_saudara.nama',
                'b_saudara.nik',
                DB::raw("'Saudara Kandung' as hubungan"),
                DB::raw("NULL as nama_status"),
                DB::raw("NULL as wali")
            )
            ->get();

        if ($saudara->isNotEmpty()) {
            $keluarga = $keluarga->merge($saudara);
        }

        if ($keluarga->isNotEmpty()) {
            $data['Keluarga'] = $keluarga->map(function ($item) {
                return [
                    "nama"   => $item->nama,
                    "nik"    => $item->nik,
                    "status" => $item->nama_status ?? $item->hubungan,
                    "wali"   => $item->wali  ?? '-',
                ];
            });
        }
        //  STATUS SANTRI (Jika Ada)

        $santri = Pegawai::where('pegawai.id', $idPegawai)
                               ->join('biodata as b', 'pegawai.id_biodata', '=', 'b.id')
                                ->leftJoin('peserta_didik as pd', 'pd.id_biodata', '=', 'b.id') 
                                ->join('santri as s', 's.id_peserta_didik', '=', 'pd.id')
                                ->where('pegawai.status', 1)
                                ->select(
                                    's.nis',
                                    's.tanggal_masuk_santri',
                                    's.tanggal_keluar_santri'
                                )
                                ->get();
                
                            if ($santri->isNotEmpty()) {
                                $data['Santri'] = $santri->map(function ($item) {
                                    return [
                                        'Nis'           => $item->nis,
                                        'Tanggal_Mulai' => $item->tanggal_masuk_santri,
                                        'Tanggal_Akhir' => $item->tanggal_keluar_santri ?? "-",
                                    ];
                                });
                            }
        //  DOMISILI (Jika Ada)

        $domisili = Pegawai::where('pegawai.id', $idPegawai)
                            ->join('biodata as b', 'pegawai.id_biodata', '=', 'b.id')
                            ->leftJoin('peserta_didik as pd', 'pd.id_biodata', '=', 'b.id') 
                            ->join('santri as s', 's.id_peserta_didik', '=', 'pd.id')
                            ->join('domisili_santri as ds', 'ds.id_santri', '=', 's.id')
                            ->join('wilayah as w', 'ds.id_wilayah', '=', 'w.id')
                            ->join('blok as bl', 'ds.id_blok', '=', 'bl.id')
                            ->join('kamar as km', 'ds.id_kamar', '=', 'km.id')
                            ->select(
                                'km.nama_kamar',
                                'bl.nama_blok',
                                'w.nama_wilayah',
                                'ds.tanggal_masuk',
                                'ds.tanggal_keluar'
                            )->distinct()
                            ->get();
            
                        if ($domisili->isNotEmpty()) {
                            $data['Domisili'] = $domisili->map(function ($item) {
                                return [
                                    'Kamar'             => $item->nama_kamar,
                                    'Blok'              => $item->nama_blok,
                                    'Wilayah'           => $item->nama_wilayah,
                                    'tanggal_ditempati' => $item->tanggal_masuk,
                                    'tanggal_pindah'    => $item->tanggal_keluar ?? "-",
                                ];
                            });
                        }

        //  KEWALIASUHAN (Jika Ada)

        $kewaliasuhan = Pegawai::where('pegawai.id', $idPegawai)
                            ->join('biodata as b', 'pegawai.id_biodata', '=', 'b.id')
                            ->leftJoin('peserta_didik as pd', 'pd.id_biodata', '=', 'b.id') 
                            ->join('santri as s', 's.id_peserta_didik', '=', 'pd.id')
                            ->join('pelajar as p', 'p.id_peserta_didik', '=', 'pd.id')
                            ->leftJoin('wali_asuh', 's.id', '=', 'wali_asuh.id_santri')
                            ->leftJoin('anak_asuh', 's.id', '=', 'anak_asuh.id_santri')
                            ->leftJoin('grup_wali_asuh', 'grup_wali_asuh.id', '=', 'wali_asuh.id_grup_wali_asuh')
                            ->leftJoin('kewaliasuhan', function ($join) {
                                $join->on('kewaliasuhan.id_wali_asuh', '=', 'wali_asuh.id')
                                    ->orOn('kewaliasuhan.id_anak_asuh', '=', 'anak_asuh.id');
                            })
                            ->leftJoin('anak_asuh as anak_asuh_data', 'kewaliasuhan.id_anak_asuh', '=', 'anak_asuh_data.id')
                            ->leftJoin('santri as santri_anak', 'anak_asuh_data.id_santri', '=', 'santri_anak.id')
                            ->leftJoin('peserta_didik as pd_anak', 'santri_anak.id_peserta_didik', '=', 'pd_anak.id')
                            ->leftJoin('biodata as bio_anak', 'pd_anak.id_biodata', '=', 'bio_anak.id')
                            ->leftJoin('wali_asuh as wali_asuh_data', 'kewaliasuhan.id_wali_asuh', '=', 'wali_asuh_data.id')
                            ->leftJoin('santri as santri_wali', 'wali_asuh_data.id_santri', '=', 'santri_wali.id')
                            ->leftJoin('peserta_didik as pd_wali', 'santri_wali.id_peserta_didik', '=', 'pd_wali.id')
                            ->leftJoin('biodata as bio_wali', 'pd_wali.id_biodata', '=', 'bio_wali.id')
                            ->havingRaw('relasi_santri IS NOT NULL') // Filter untuk menghindari hasil NULL
                            ->select(
                                'grup_wali_asuh.nama_grup',
                                DB::raw("CASE 
                                 WHEN wali_asuh.id IS NOT NULL THEN 'Wali Asuh'
                                 WHEN anak_asuh.id IS NOT NULL THEN 'Anak Asuh'
                                 ELSE 'Bukan Wali Asuh atau Anak Asuh'
                             END as status_santri"),
                                DB::raw("CASE 
                                 WHEN wali_asuh.id IS NOT NULL THEN GROUP_CONCAT(DISTINCT bio_anak.nama SEPARATOR ', ')
                                 WHEN anak_asuh.id IS NOT NULL THEN GROUP_CONCAT(DISTINCT bio_wali.nama SEPARATOR ', ')
                                 ELSE NULL
                             END as relasi_santri")
                            )
                            ->groupBy(
                                'grup_wali_asuh.nama_grup',
                                'wali_asuh.id',
                                'anak_asuh.id'
                            )
                            ->get();
            
            
                        if ($kewaliasuhan->isNotEmpty()) {
                            $data['Status_Santri']['Kewaliasuhan'] = $kewaliasuhan->map(function ($item) {
                                return [
                                    'group'   => $item->nama_grup ?? '-',
                                    'Sebagai' => $item->status_santri,
                                    $item->status_santri === 'Anak Asuh' ? 'Nama Wali Asuh' : 'Nama Anak Asuh'
                                    => $item->relasi_santri ?? "-",
                                ];
                            });
                        }
        
        // PENDIDIKAN (Jika Ada)

        $pelajar = Pegawai::where('pegawai.id', $idPegawai)
                            ->join('biodata as b','b.id','=','pegawai.id_biodata')
                            ->join('peserta_didik as pd','b.id','pd.id_biodata')
                            ->join('pelajar as p', 'p.id_peserta_didik', '=', 'pd.id')
                            ->join('pendidikan_pelajar as pp', 'pp.id_pelajar', '=', 'p.id')
                            ->join('lembaga as l', 'pp.id_lembaga', '=', 'l.id')
                            ->leftJoin('jurusan as j', 'pp.id_jurusan', '=', 'j.id')
                            ->leftJoin('kelas as k', 'pp.id_kelas', '=', 'k.id')
                            ->leftJoin('rombel as r', 'pp.id_rombel', '=', 'r.id')
                            ->where('pegawai.status', 1)
                            ->select(
                                'p.no_induk',
                                'l.nama_lembaga',
                                'j.nama_jurusan',
                                'k.nama_kelas',
                                'r.nama_rombel',
                                'p.tanggal_masuk_pelajar',
                                'p.tanggal_keluar_pelajar'
                            )
                            ->get();
            
                        if ($pelajar->isNotEmpty()) {
                            $data['Pendidikan'] = $pelajar->map(function ($item) {
                                return [
                                    'no_induk'     => $item->no_induk,
                                    'nama_lembaga' => $item->nama_lembaga,
                                    'nama_jurusan' => $item->nama_jurusan,
                                    'nama_kelas'   => $item->nama_kelas ?? "-",
                                    'nama_rombel'  => $item->nama_rombel ?? "-",
                                    'tahun_masuk'  => $item->tanggal_masuk_pelajar,
                                    'tahun_lulus'  => $item->tanggal_keluar_pelajar ?? "Masih Aktif",
                                ];
                            });
                        }

        // KARYAWAN (Jika Ada)

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
            });
        }

        //  Pengajar**
        
        $pengajar = Pegawai::where('pegawai.id', $idPegawai)
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
                        if ($pengajar) {
                            $data['pengajar'] = [
                                "nama_lembaga" => $pengajar->nama_lembaga, // BENAR
                                "PekerjaanKontrak" => $pengajar->PekerjaanKontrak,
                                "kategori_golongan" => $pengajar->nama_kategori_golongan,
                                "golongan" => $pengajar->nama_golongan,
                                "keterangan" => $pengajar->keterangan,
                                "total_waktu_materi" => $pengajar->total_waktu_materi, // Harus sesuai dengan nama di SELECT
                                "total_materi" => $pengajar->total_materi, // Harus sesuai dengan nama di SELECT
                            ];
                        }
               //  Pengurus (jika ada )

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
            };


        //  Warga Pesantren (Jika Ada)
        $Wargapesantren = Pegawai::where('pegawai.id', $idPegawai)
                        ->join('biodata', 'pegawai.id_biodata', '=', 'biodata.id') 
                        ->leftJoin('peserta_didik', 'peserta_didik.id_biodata', '=', 'biodata.id') 
                        ->leftjoin('warga_pesantren as wp','wp.id_biodata','biodata.id')
                        ->select(
                            'wp.niup',
                            DB::raw("
                            CASE 
                                WHEN wp.status = 1 THEN 'Iya'
                                ELSE 'Tidak'
                            END AS aktif
                        ")
                        )->first();
        if ($Wargapesantren) { 
            $data['WargaPesantren'] = [
                "niup" => $Wargapesantren->niup,
                "aktif" => $Wargapesantren->aktif
            ];
        }
               // catatan afektif (Jika ada)
        $catatanAfektif = Pegawai::where('pegawai.id',$idPegawai)
                        ->join('biodata','biodata.id','pegawai.id_biodata')
                        ->join('peserta_didik as pd','biodata.id','pd.id_biodata')
                        ->join('santri as s', 's.id_peserta_didik', '=', 'pd.id')
                        ->join('pelajar as p', 'p.id_peserta_didik', '=', 'pd.id')
                        ->join('catatan_afektif as ca', 's.id', '=', 'ca.id_santri')
                        ->where('pd.status', true)
                        ->select(
                            'ca.kebersihan_nilai',
                            'ca.kebersihan_tindak_lanjut',
                            'ca.kepedulian_nilai',
                            'ca.kepedulian_tindak_lanjut',
                            'ca.akhlak_nilai',
                            'ca.akhlak_tindak_lanjut'
                        )
                        ->latest('ca.created_at')
                        ->first();
        
                    if ($catatanAfektif) {
                        $data['Catatan_Progress']['afektif'] = [
                            'Keterangan' => [
                                'kebersihan'               => $catatanAfektif->kebersihan_nilai ?? "-",
                                'tindak_lanjut_kebersihan' => $catatanAfektif->kebersihan_tindak_lanjut ?? "-",
                                'kepedulian'               => $catatanAfektif->kepedulian_nilai ?? "-",
                                'tindak_lanjut_kepedulian' => $catatanAfektif->kepedulian_tindak_lanjut ?? "-",
                                'akhlak'                   => $catatanAfektif->akhlak_nilai ?? "-",
                                'tindak_lanjut_akhlak'     => $catatanAfektif->akhlak_tindak_lanjut ?? "-",
                            ]
                        ];
                    }
                // catatan kognitif (jika ada)
        $catatanKognitif = Pegawai::where('pegawai.id',$idPegawai)
                        ->join('biodata','biodata.id','pegawai.id_biodata')
                        ->join('peserta_didik as pd','biodata.id','pd.id_biodata')
                        ->join('santri as s', 's.id_peserta_didik', '=', 'pd.id')
                        ->join('pelajar as p', 'p.id_peserta_didik', '=', 'pd.id')
                        ->join('catatan_kognitif as ck', 's.id', '=', 'ck.id_santri')
                        ->where('pd.status', true)
                        ->select(
                            'ck.kebahasaan_nilai',
                            'ck.kebahasaan_tindak_lanjut',
                            'ck.baca_kitab_kuning_nilai',
                            'ck.baca_kitab_kuning_tindak_lanjut',
                            'ck.hafalan_tahfidz_nilai',
                            'ck.hafalan_tahfidz_tindak_lanjut',
                            'ck.furudul_ainiyah_nilai',
                            'ck.furudul_ainiyah_tindak_lanjut',
                            'ck.tulis_alquran_nilai',
                            'ck.tulis_alquran_tindak_lanjut',
                            'ck.baca_alquran_nilai',
                            'ck.baca_alquran_tindak_lanjut'
                        )
                        ->latest('ck.created_at')
                        ->first();
        
                    if ($catatanKognitif) {
                        $data['Catatan_Progress']['Kognitif'] = [
                            'Keterangan' => [
                                'kebahasaan'                      => $catatanKognitif->kebahasaan_nilai ?? "-",
                                'tindak_lanjut_kebahasaan'        => $catatanKognitif->kebahasaan_tindak_lanjut ?? "-",
                                'baca_kitab_kuning'               => $catatanKognitif->baca_kitab_kuning_nilai ?? "-",
                                'tindak_lanjut_baca_kitab_kuning' => $catatanKognitif->baca_kitab_kuning_tindak_lanjut ?? "-",
                                'hafalan_tahfidz'                 => $catatanKognitif->hafalan_tahfidz_nilai ?? "-",
                                'tindak_lanjut_hafalan_tahfidz'   => $catatanKognitif->hafalan_tahfidz_tindak_lanjut ?? "-",
                                'furudul_ainiyah'                 => $catatanKognitif->furudul_ainiyah_nilai ?? "-",
                                'tindak_lanjut_furudul_ainiyah'   => $catatanKognitif->furudul_ainiyah_tindak_lanjut ?? "-",
                                'tulis_alquran'                   => $catatanKognitif->tulis_alquran_nilai ?? "-",
                                'tindak_lanjut_tulis_alquran'     => $catatanKognitif->tulis_alquran_tindak_lanjut ?? "-",
                                'baca_alquran'                    => $catatanKognitif->baca_alquran_nilai ?? "-",
                                'tindak_lanjut_baca_alquran'      => $catatanKognitif->baca_alquran_tindak_lanjut ?? "-",
                            ]
                        ];
                    }
            // Data Kunjungan Mahrom (jika aada)
            $pengunjung = DB::table('pengunjung_mahrom')
                ->join('santri as s', 'pengunjung_mahrom.id_santri', '=', 's.id')
                ->join('peserta_didik as pd', 's.id_peserta_didik', '=', 'pd.id')
                ->join('biodata','biodata.id','pd.id_biodata')
                ->join('pegawai','biodata.id','pegawai.id_biodata')
                ->where('pegawai.id', $idPegawai)
                ->where('pd.status', true)
                ->select(
                    'pengunjung_mahrom.nama_pengunjung',
                    'pengunjung_mahrom.tanggal'
                )
                ->get();

            if ($pengunjung->isNotEmpty()) {
                $data['Kunjungan_Mahrom']['Di_kunjungi_oleh'] = $pengunjung->map(function ($item) {
                    return [
                        'Nama'    => $item->nama_pengunjung,
                        'Tanggal' => Carbon::parse($item->tanggal)->format('d-m-Y'),
                    ];
                });
            }
            return $data;
        }
     // **Mengambil Data pegawai ( Detail)**
     public function getPegawai($idPegawai)
     {
         // Validasi bahwa ID adalah UUID
         if (!Str::isUuid($idPegawai)) {
            return response()->json(['error' => 'ID tidak valid'], 400);
        }

        try {
            // Cari data peserta didik berdasarkan UUID
            $pegawai = Pegawai::find($idPegawai);
            if (!$pegawai) {
                return response()->json(['error' => 'Data tidak ditemukan'], 404);
            }

            // Ambil detail peserta didik dari fungsi helper
            $data = $this->getFormDetail($pegawai->id);
            if (empty($data)) {
                return response()->json(['error' => 'Data Kosong'], 200);
            }

            return response()->json($data, 200);
        } catch (\Exception $e) {
            Log::error("Error in getDetailPelajar: " . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan pada server'], 500);
        }
     }
}
