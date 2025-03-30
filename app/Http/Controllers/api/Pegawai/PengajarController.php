<?php

namespace App\Http\Controllers\Api\Pegawai;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Biodata;
use Illuminate\Http\Request;
use App\Models\Pegawai\Pengajar;
use App\Http\Resources\PdResource;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\FilterController;
use App\Models\JenisBerkas;

class PengajarController extends Controller
{

    protected $filterController;

    public function __construct(FilterController $filterController)
    {
        $this->filterController = $filterController;
    }

    public function index()
    {
        $pengajar = Pengajar::all();
        return new PdResource(true, 'Data berhasil ditampilkan', $pengajar);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_pegawai'   => 'required|integer',
            'id_golongan'  => 'required|integer',
            'id_lembaga'   => 'required|integer',
            'created_by'   => 'required|integer',
            'status'       => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Data gagal ditambahkan',
                'data' => $validator->errors()
            ]);
        }

        $pengajar = Pengajar::create($validator->validated());
        return new PdResource(true, 'Data berhasil ditambahkan', $pengajar);
    }

    public function show(string $id)
    {
        $pengajar = Pengajar::findOrFail($id);
        return new PdResource(true, 'Data berhasil ditampilkan', $pengajar);
    }
    public function update(Request $request, string $id)
    {
        $pengajar = Pengajar::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'id_pegawai'   => 'required|integer',
            'id_golongan'  => 'required|integer',
            'id_lembaga'   => 'required|integer',
            'updated_by'   => 'nullable|integer',
            'status'       => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Data gagal ditambahkan',
                'data' => $validator->errors()
            ]);
        }
        $pengajar->update($validator->validated());
        return new PdResource(true, 'Data berhasil diupdate', $pengajar);
    }
    public function destroy(string $id)
    {
        $pengajar = Pengajar::findOrFail($id);
        $pengajar->delete();
        return new PdResource(true, 'Data berhasil dihapus', $pengajar);
    }

    public function filterPengajar(Request $request)
    {
        $query = Pengajar::Active()
            ->join('pegawai', 'pengajar.id_pegawai', '=', 'pegawai.id')
            ->join('biodata', 'pegawai.id_biodata', '=', 'biodata.id')
            ->leftJoin('kabupaten','kabupaten.id','biodata.id_kabupaten')
            ->leftJoin('lembaga', 'pegawai.id_lembaga', '=', 'lembaga.id')
            ->leftJoin('golongan', 'pengajar.id_golongan', '=', 'golongan.id')
            ->leftJoin('kategori_golongan', 'golongan.id_kategori_golongan', '=', 'kategori_golongan.id')
            ->leftJoin('entitas_pegawai','entitas_pegawai.id_pegawai','=','pegawai.id')
            ->leftJoin('berkas', 'berkas.id_biodata', '=', 'biodata.id')
            ->leftJoin('jenis_berkas', 'berkas.id_jenis_berkas', '=', 'jenis_berkas.id')
            ->leftJoin('materi_ajar','materi_ajar.id_pengajar','=','pengajar.id')
            ->select(
                'pengajar.id',
                'biodata.nama',
                'biodata.niup',
                DB::raw("TIMESTAMPDIFF(YEAR, biodata.tanggal_lahir, CURDATE()) AS umur"),
                DB::raw("
                GROUP_CONCAT(DISTINCT materi_ajar.nama_materi SEPARATOR ', ') AS daftar_materi"),
                DB::raw("
                CONCAT(
                    FLOOR(SUM(DISTINCT materi_ajar.jumlah_menit) / 60), ' jam ',
                    MOD(SUM(DISTINCT materi_ajar.jumlah_menit), 60), ' menit'
                ) AS total_waktu_materi
            "),     
                DB::raw("COUNT(DISTINCT materi_ajar.nama_materi) AS total_materi"),
                DB::raw("
                CASE 
                    WHEN TIMESTAMPDIFF(YEAR, entitas_pegawai.tanggal_masuk, COALESCE(entitas_pegawai.tanggal_keluar, CURDATE())) = 0 
                    THEN CONCAT('Belum setahun sejak ', DATE_FORMAT(entitas_pegawai.tanggal_masuk, '%Y-%m-%d'))
                    ELSE CONCAT(TIMESTAMPDIFF(YEAR, entitas_pegawai.tanggal_masuk, COALESCE(entitas_pegawai.tanggal_keluar, CURDATE())), ' Tahun sejak ', DATE_FORMAT(entitas_pegawai.tanggal_masuk, '%Y-%m-%d'))
                END AS masa_kerja"),
                'golongan.nama_golongan',
                'biodata.nama_pendidikan_terakhir',
                DB::raw("DATE_FORMAT(pengajar.updated_at, '%Y-%m-%d %H:%i:%s') AS tgl_update"),
                DB::raw("DATE_FORMAT(pengajar.created_at, '%Y-%m-%d %H:%i:%s') AS tgl_input"),
                'lembaga.nama_lembaga',    
                DB::raw("COALESCE(MAX(berkas.file_path), 'default.jpg') as foto_profil")
                )   
                 ->groupBy(
                    'pengajar.id',
                    'biodata.nama',
                    'biodata.niup',
                    'biodata.tanggal_lahir',
                    'golongan.nama_golongan',
                    'biodata.nama_pendidikan_terakhir',
                    'pengajar.updated_at',
                    'pengajar.created_at',
                    'lembaga.nama_lembaga',
                    'entitas_pegawai.tanggal_masuk',
                    'entitas_pegawai.tanggal_keluar'
                );   
        // 🔹 Terapkan filter umum (lokasi & jenis kelamin)
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
                    ->orWhere('kabupaten.nama_kabupaten', 'LIKE', "%$search%")
                    ->orWhereDate('pengajar.created_at', '=', $search) // Tgl Input
                    ->orWhereDate('pengajar.updated_at', '=', $search); // Tgl Update
                    });
        }

        if ($request->has('lembaga')) {
            $query->where('lembaga.nama_lembaga', strtolower($request->lembaga));
        }

        // 🔹 Filter Kategori Golongan
        if ($request->has('kategori_golongan')) {
            $query->where('kategori_golongan.nama_kategori_golongan', strtolower($request->kategori_golongan));
        }
        // 🔹 Filter Golongan
        if ($request->has('golongan')) {
            $query->where('golongan.nama_golongan', strtolower($request->golongan));
        }

        // 🔹Materi Ajar
        if ($request->has('materi_ajar')) {
            if (strtolower($request->materi_ajar) === 'materi ajar 1') {
                // Hanya pengajar yang memiliki 1 materi ajar
                $query->havingRaw('COUNT(DISTINCT materi_ajar.id) = 1');
            } elseif (strtolower($request->materi_ajar) === 'materi ajar lebih dari 1') {
                // Hanya pengajar yang memiliki lebih dari 1 materi ajar
                $query->havingRaw('COUNT(DISTINCT materi_ajar.id) > 1');
            }
        }
        
            // 🔹 Filter Jabatan
        if ($request->has('jabatan')) {
            $query->where('pengajar.jabatan', strtolower($request->jabatan));
        }     
        // Filter Masa Kerja
        $masaKerja = $request->input('masa_kerja'); // Mengambil input dari request
        $today = now(); // Menggunakan tanggal saat ini
        
        if (preg_match('/^(\d+)-(\d+)$/', $masaKerja, $matches)) {
            // Jika input dalam format "min-max" (contoh: "1-5")
            $min = (int) $matches[1];
            $max = (int) $matches[2];
        
            $query->whereRaw("
                TIMESTAMPDIFF(YEAR, entitas_pegawai.tanggal_masuk, COALESCE(entitas_pegawai.tanggal_keluar, ?)) BETWEEN ? AND ?
            ", [$today, $min, $max]);
        } elseif (is_numeric($masaKerja)) {
            // Jika input hanya angka (contoh: "1" untuk kurang dari 1 tahun)
            $query->whereRaw("
                TIMESTAMPDIFF(YEAR, entitas_pegawai.tanggal_masuk, COALESCE(entitas_pegawai.tanggal_keluar, ?)) < ?
            ", [$today, (int) $masaKerja]);
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
                    "daftar_materi" => $item->daftar_materi,
                    "total_waktu_materi" => $item->total_waktu_materi,
                    "total_materi" => $item->total_materi,
                    "masa_kerja" => $item->masa_kerja,
                    "golongan" => $item->nama_golongan,
                    "pendidikan_terakhir" => $item->nama_pendidikan_terakhir,
                    "tgl_update" => $item->tgl_update,
                    "tgl_input" => $item->tgl_input,
                    "lembaga" => $item->nama_lembaga,
                    "foto_profil" => url($item->foto_profil)
                ];
            })
        ]);
    }
    // private function getFormTampilanList($perPage, $currentPage)
    // {
    //    return Pengajar::Active()
    //     ->join('pegawai', 'pengajar.id_pegawai', '=', 'pegawai.id')
    //     ->join('biodata', 'pegawai.id_biodata', '=', 'biodata.id')
    //     ->leftJoin('kabupaten','kabupaten.id','biodata.id_kabupaten')
    //     ->leftJoin('lembaga', 'pegawai.id_lembaga', '=', 'lembaga.id')
    //     ->leftJoin('golongan', 'pengajar.id_golongan', '=', 'golongan.id')
    //     ->leftJoin('kategori_golongan', 'golongan.id_kategori_golongan', '=', 'kategori_golongan.id')
    //     ->leftJoin('entitas_pegawai','entitas_pegawai.id_pegawai','=','pegawai.id')
    //     ->leftJoin('berkas', 'berkas.id_biodata', '=', 'biodata.id')
    //     ->leftJoin('jenis_berkas', 'berkas.id_jenis_berkas', '=', 'jenis_berkas.id')
    //     ->leftJoin('materi_ajar','materi_ajar.id_pengajar','=','pengajar.id')
    //     ->select(
    //         'pengajar.id',
    //         'biodata.nama',
    //         'biodata.niup',
    //         DB::raw("TIMESTAMPDIFF(YEAR, biodata.tanggal_lahir, CURDATE()) AS umur"),
    //         DB::raw("
    //         GROUP_CONCAT(DISTINCT materi_ajar.nama_materi SEPARATOR ', ') AS daftar_materi"),
    //         DB::raw("
    //         CONCAT(
    //             FLOOR(SUM(DISTINCT materi_ajar.jumlah_menit) / 60), ' jam ',
    //             MOD(SUM(DISTINCT materi_ajar.jumlah_menit), 60), ' menit'
    //         ) AS total_waktu_materi
    //     "),     
    //         DB::raw("COUNT(DISTINCT materi_ajar.nama_materi) AS total_materi"),
    //         DB::raw("
    //         CASE 
    //             WHEN TIMESTAMPDIFF(YEAR, entitas_pegawai.tanggal_masuk, COALESCE(entitas_pegawai.tanggal_keluar, CURDATE())) = 0 
    //             THEN CONCAT('Belum setahun sejak ', DATE_FORMAT(entitas_pegawai.tanggal_masuk, '%Y-%m-%d'))
    //             ELSE CONCAT(TIMESTAMPDIFF(YEAR, entitas_pegawai.tanggal_masuk, COALESCE(entitas_pegawai.tanggal_keluar, CURDATE())), ' Tahun sejak ', DATE_FORMAT(entitas_pegawai.tanggal_masuk, '%Y-%m-%d'))
    //         END AS masa_kerja"),
    //         'golongan.nama_golongan',
    //         'biodata.nama_pendidikan_terakhir',
    //         DB::raw("DATE_FORMAT(pengajar.updated_at, '%Y-%m-%d %H:%i:%s') AS tgl_update"),
    //         DB::raw("DATE_FORMAT(pengajar.created_at, '%Y-%m-%d %H:%i:%s') AS tgl_input"),
    //         'lembaga.nama_lembaga',    
    //         DB::raw("COALESCE(MAX(berkas.file_path), 'default.jpg') as foto_profil")
    //         )   
    //          ->groupBy(
    //             'pengajar.id',
    //             'biodata.nama',
    //             'biodata.niup',
    //             'biodata.tanggal_lahir',
    //             'golongan.nama_golongan',
    //             'biodata.nama_pendidikan_terakhir',
    //             'pengajar.updated_at',
    //             'pengajar.created_at',
    //             'lembaga.nama_lembaga',
    //             'entitas_pegawai.tanggal_masuk',
    //             'entitas_pegawai.tanggal_keluar'
    //         )
    //         ->distinct()
    //         ->paginate($perPage, ['*'], 'page',$currentPage);   
    // }
    private function formDetail($idPengajar)
    {
        $biodata = Pengajar::where('pengajar.id',$idPengajar)
                        ->join('pegawai','pegawai.id','=','pengajar.id_pegawai')
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

        $keluarga = Pengajar::where('pengajar.id', $idPengajar)
            ->join('pegawai','pegawai.id','=','pengajar.id_pegawai')
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

        $statusSantri = Pengajar::where('pengajar.id', $idPengajar)
                                ->join('pegawai','pegawai.id','=','pengajar.id_pegawai')
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

        $domisili = Pengajar::where('pengajar.id', $idPengajar)
                            ->join('pegawai','pegawai.id','=','pengajar.id_pegawai')
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

        $waliAsuh = Pengajar::where('pengajar.id', $idPengajar)
                            ->join('pegawai','pegawai.id','=','pengajar.id_pegawai')
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

        $pendidikan = Pengajar::where('pengajar.id', $idPengajar)
                        ->join('pegawai','pegawai.id','=','pengajar.id_pegawai')
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

        // // **6. Pengajar**

        $pengurus = Pengajar::where('pengajar.id', $idPengajar)
                        ->join('pegawai','pegawai.id','=','pengajar.id_pegawai')
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

        // // **6. Warga Pesantren (Jika Ada)**
        $Wargapesantren = Pengajar::where('pengajar.id', $idPengajar)
                        ->join('pegawai','pegawai.id','=','pengajar.id_pegawai')
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
         // **Mengambil Data Pengajar ( Detail)**
         public function getPengajar($idPengajar)
         {
             $data = $this->formDetail($idPengajar); 
         
             return response()->json([
                 "data" => [$data],
             ]);
         }
}
