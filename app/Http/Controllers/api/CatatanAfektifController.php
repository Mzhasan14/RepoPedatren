<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\api\FilterController;
use App\Http\Controllers\Controller;
use App\Http\Resources\PdResource;
use App\Models\Catatan_afektif;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CatatanAfektifController extends Controller
{

    public function index()
    {
        $CatatanAfektif = Catatan_afektif::all();
        return new PdResource(true,'Data Berhasil Ditampilkan',$CatatanAfektif);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'id_peserta_didik' => 'required|exists:peserta_didik,id',
            'id_wali_asuh' => 'required|exists:wali_asuh,id',
            'kepedulian_nilai' => 'required|in:A,B,C,D,E',
            'kepedulian_tindak_lanjut' => 'required|string',
            'kebersihan_nilai' => 'required|in:A,B,C,D,E',
            'kebersihan_tindak_lanjut' => 'required|string',
            'akhlak_nilai' => 'required|in:A,B,C,D,E',
            'akhlak_tindak_lanjut' => 'required|string',
            'created_by' => 'required|integer',
            'status' => 'required|boolean',
        ]);
        if ($validator->fails()){
            return response()->json([
                'status' => false,
                'message' => 'Data Gagal Ditambahkan',
                'data' => $validator->errors()
            ]);
        }
        $CatatanAfektif = Catatan_afektif::create($validator->validated());
        return new PdResource(true,'Data berhasil ditambahkan',$CatatanAfektif);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $CatatanAfektif = Catatan_afektif::findOrFail($id);
        return new PdResource(true, 'Detail data', $CatatanAfektif);
    }

    public function update(Request $request, string $id)
    {
        $CatatanAfektif = Catatan_afektif::findOrFail($id);

        $validator = Validator::make($request->all(),[
            'id_peserta_didik' => 'required|exists:peserta_didik,id',
            'id_wali_asuh' => 'required|exists:wali_asuh,id',
            'kepedulian_nilai' => 'required|in:A,B,C,D,E',
            'kepedulian_tindak_lanjut' => 'required|string',
            'kebersihan_nilai' => 'required|in:A,B,C,D,E',
            'kebersihan_tindak_lanjut' => 'required|string',
            'akhlak_nilai' => 'required|in:A,B,C,D,E',
            'akhlak_tindak_lanjut' => 'required|string',
            'updated_by' => 'nullable|integer',
            'status' => 'required|boolean',
        ]);
        if ($validator->fails()){
            return response()->json([
                'status' => false,
                'message' => 'Data Gagal Ditambahkan',
                'data' => $validator->errors()
            ]);
        }
        $CatatanAfektif->update($validator->validated());
        return new PdResource(true, 'Data berhasil di Update', $CatatanAfektif);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $CatatanAfektif = Catatan_afektif::findOrFail($id);
        $CatatanAfektif->delete();
        return new PdResource(true, 'Data berhasil di hapus', $CatatanAfektif);

    }

    public function dataCatatanAfektif(Request $request)
    {
        try{
        // 1) Ambil ID untuk jenis berkas "Pas foto"
        $pasFotoId = DB::table('jenis_berkas')
                ->where('nama_jenis_berkas', 'Pas foto')
                ->value('id');

        // 2) Subquery: foto terakhir per biodata
        $fotoLast = DB::table('berkas')
                ->select('biodata_id', DB::raw('MAX(id) AS last_id'))
                ->where('jenis_berkas_id', $pasFotoId)
                ->groupBy('biodata_id');
            $query = Catatan_afektif::Active()
                    ->join('santri as CatatanSantri', 'CatatanSantri.id', '=', 'catatan_afektif.id_santri')
                    ->join('biodata as CatatanBiodata', 'CatatanBiodata.id', '=', 'CatatanSantri.biodata_id')
                    ->leftJoin('riwayat_domisili as domisili_santri', 'domisili_santri.santri_id', '=', 'CatatanSantri.id')
                    ->leftJoin('wilayah', 'wilayah.id', '=', 'domisili_santri.wilayah_id')
                    ->leftJoin('blok', 'blok.id', '=', 'domisili_santri.blok_id')
                    ->leftJoin('kamar', 'kamar.id', '=', 'domisili_santri.kamar_id')
                    ->leftJoin('riwayat_pendidikan', 'riwayat_pendidikan.santri_id', '=', 'CatatanSantri.id')
                    ->leftJoin('lembaga', 'lembaga.id', '=', 'riwayat_pendidikan.lembaga_id')
                    ->leftJoin('jurusan', 'jurusan.id', '=', 'riwayat_pendidikan.jurusan_id')
                    ->leftJoin('kelas', 'kelas.id', '=', 'riwayat_pendidikan.kelas_id')
                    ->leftJoin('rombel', 'rombel.id', '=', 'riwayat_pendidikan.rombel_id')
                    ->leftJoin('wali_asuh', 'wali_asuh.id', '=', 'catatan_afektif.id_wali_asuh')
                    ->leftJoin('santri as PencatatSantri', 'PencatatSantri.id', '=', 'wali_asuh.id_santri')
                    ->leftJoin('biodata as PencatatBiodata', 'PencatatBiodata.id', '=', 'PencatatSantri.biodata_id')
                    // join foto CatatanSantri
                    ->leftJoinSub($fotoLast, 'fotoLastCatatan', function($join) {
                        $join->on('CatatanBiodata.id', '=', 'fotoLastCatatan.biodata_id');
                    })
                    ->leftJoin('berkas as FotoCatatan', 'FotoCatatan.id', '=', 'fotoLastCatatan.last_id')

                    // join foto PencatatSantri
                    ->leftJoinSub($fotoLast, 'fotoLastPencatat', function($join) {
                        $join->on('PencatatBiodata.id', '=', 'fotoLastPencatat.biodata_id');
                    })
                    ->leftJoin('berkas as FotoPencatat', 'FotoPencatat.id', '=', 'fotoLastPencatat.last_id')

                    ->select(
                        'catatan_afektif.id',
                        'CatatanBiodata.nama',
                        DB::raw("GROUP_CONCAT(DISTINCT blok.nama_blok SEPARATOR ', ') as blok"),
                        DB::raw("GROUP_CONCAT(DISTINCT wilayah.nama_wilayah SEPARATOR ', ') as wilayah"),
                        DB::raw("GROUP_CONCAT(DISTINCT jurusan.nama_jurusan SEPARATOR ', ') as jurusan"),
                        DB::raw("GROUP_CONCAT(DISTINCT lembaga.nama_lembaga SEPARATOR ', ') as lembaga"),
                        'catatan_afektif.kepedulian_nilai',
                        'catatan_afektif.kepedulian_tindak_lanjut',
                        'catatan_afektif.kebersihan_nilai',
                        'catatan_afektif.kebersihan_tindak_lanjut',
                        'catatan_afektif.akhlak_nilai',
                        'catatan_afektif.akhlak_tindak_lanjut',
                        'PencatatBiodata.nama as pencatat',
                        DB::raw("CASE WHEN wali_asuh.id IS NOT NULL THEN 'wali asuh' ELSE NULL END as wali_asuh"),
                        'catatan_afektif.created_at',
                        DB::raw("COALESCE(MAX(FotoCatatan.file_path), 'default.jpg') as foto_catatan"),
                        DB::raw("COALESCE(MAX(FotoPencatat.file_path), 'default.jpg') as foto_pencatat"),
                    )
                    ->groupBy(
                        'catatan_afektif.id',
                        'CatatanBiodata.nama',
                        'catatan_afektif.kepedulian_nilai',
                        'catatan_afektif.kepedulian_tindak_lanjut',
                        'catatan_afektif.kebersihan_nilai',
                        'catatan_afektif.kebersihan_tindak_lanjut',
                        'catatan_afektif.akhlak_nilai',
                        'catatan_afektif.akhlak_tindak_lanjut',
                        'PencatatBiodata.nama',
                        'wali_asuh.id',
                        'catatan_afektif.created_at',
            
                    )
                    ->distinct();
        
        // Filter berdasarkan lokasi (negara, provinsi, kabupaten, kecamatan, desa)
        if ($request->filled('negara')) {
            $query->leftJoin('negara', 'CatatanBiodata.negara_id', '=', 'negara.id')
                ->where('negara.nama_negara', $request->negara);
            if ($request->filled('provinsi')) {
                $query->leftjoin('provinsi', 'CatatanBiodata.provinsi_id', '=', 'provinsi.id');
                $query->where('provinsi.nama_provinsi', $request->provinsi);
                if ($request->filled('kabupaten')) {
                    $query->leftjoin('kabupaten', 'CatatanBiodata.kabupaten_id', '=', 'kabupaten.id');
                    $query->where('kabupaten.nama_kabupaten', $request->kabupaten);
                    if ($request->filled('kecamatan')) {
                        $query->leftjoin('kecamatan', 'CatatanBiodata.kecamatan_id', '=', 'kecamatan.id');
                        $query->where('kecamatan.nama_kecamatan', $request->kecamatan);
                    }
                }
            }
        }
        // Filter Search
        if ($request->filled('nama')) {
            $query->whereRaw("MATCH(nama) AGAINST(? IN BOOLEAN MODE)", [$request->nama]);
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
        // Filter jenis kelamin
        if ($request->filled('jenis_kelamin')) {
            $jenis_kelamin = strtolower($request->jenis_kelamin);
            if ($jenis_kelamin == 'laki-laki') {
                $query->where('CatatanBiodata.jenis_kelamin', 'l');
            } else if ($jenis_kelamin == 'perempuan') {
               $query->where('CatatanBiodata.jenis_kelamin', 'p');
            }
        } 
        // Filter No Telepon
        if ($request->filled('phone_number')) {
            $query->where(function ($q) use ($request) {
                if (strtolower($request->phone_number) === 'mempunyai') {
                    $q->whereNotNull('CatatanBiodata.no_telepon')
                      ->where('CatatanBiodata.no_telepon', '!=', '');
                } elseif (strtolower($request->phone_number) === 'tidak mempunyai') {
                    $q->where(function($q2){
                        $q2->whereNull('CatatanBiodata.no_telepon')
                           ->orWhere('CatatanBiodata.no_telepon', '');
                    });
                }
            });
        }
        
        if ($request->filled('periode')) {
            [$year, $month] = explode('-', $request->periode);
            $query->whereYear('catatan_afektif.created_at', $year)
                  ->whereMonth('catatan_afektif.created_at',$month);
            }

        if ($request->filled('materi')) {
                $materi = strtolower($request->materi);
            
                if (in_array($materi, ['akhlak', 'kebersihan', 'kepedulian'])) {
                    if ($materi === 'akhlak') {
                        $query->whereNotNull('catatan_afektif.akhlak_nilai');
                    } elseif ($materi === 'kebersihan') {
                        $query->whereNotNull('catatan_afektif.kebersihan_nilai');
                    } elseif ($materi === 'kepedulian') {
                        $query->whereNotNull('catatan_afektif.kepedulian_nilai');
                    }
                }
        }
            
        // Filter berdasarkan score (cek apakah field materi == A/B/C/D/E)
        if ($request->filled('score') && in_array($request->score, ['A', 'B', 'C', 'D', 'E'])) {
            $score = $request->score;

            $query->where(function ($q) use ($score) {
                $q->where('catatan_afektif.akhlak_nilai', $score)
                ->orWhere('catatan_afektif.kebersihan_nilai', $score)
                ->orWhere('catatan_afektif.kepedulian_nilai', $score);
            });
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
            "data" => $hasil->flatMap(function ($item) {
                return [
                    [
                        'id_santri' => $item->id,
                        'nama_santri' => $item->nama,
                        'blok' => $item->blok,
                        'wilayah' => $item->wilayah,
                        'pendidikan' => $item->jurusan,
                        'lembaga' => $item->lembaga,
                        'kategori' => 'Kepedulian',
                        'nilai_kepedulian' => $item->kepedulian_nilai,
                        'tindak_lanjut_kepedulian' => $item->kepedulian_tindak_lanjut,
                        'pencatat' => $item->pencatat,
                        'jabatanPencatat' => $item->wali_asuh,
                        'waktu_pencatatan' => $item->created_at->format('d M Y H:i:s'),
                        'foto_catatan' => url($item->foto_catatan),
                        'foto_pencatat' => url($item->foto_pencatat),
                    ],
                    [
                        'id_santri' => $item->id,
                        'nama_santri' => $item->nama,
                        'blok' => $item->blok,
                        'wilayah' => $item->wilayah,
                        'pendidikan' => $item->jurusan,
                        'lembaga' => $item->lembaga,
                        'kategori' => 'Akhlak',
                        'nilai_akhlak' => $item->akhlak_nilai,
                        'tindak_lanjut_akhlak' => $item->akhlak_tindak_lanjut,
                        'pencatat' => $item->pencatat,
                        'jabatanPencatat' => $item->wali_asuh,
                        'waktu_pencatatan' => $item->created_at->format('d M Y H:i:s'),
                        'foto_catatan' => url($item->foto_catatan),
                        'foto_pencatat' => url($item->foto_pencatat),
                    ],
                    [
                        'id_santri' => $item->id,
                        'nama_santri' => $item->nama,
                        'blok' => $item->blok,
                        'wilayah' => $item->wilayah,
                        'pendidikan' => $item->jurusan,
                        'lembaga' => $item->lembaga,
                        'kategori' => 'Kebersihan',
                        'nilai_kebersihan' => $item->kebersihan_nilai,
                        'tindak_lanjut_kebersihan' => $item->kebersihan_tindak_lanjut,
                        'pencatat' => $item->pencatat,
                        'jabatanPencatat' => $item->wali_asuh,
                        'waktu_pencatatan' => $item->created_at->format('d M Y H:i:s'),
                        'foto_catatan' => url($item->foto_catatan),
                        'foto_pencatat' => url($item->foto_pencatat),
                    ],
                ];
            })
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Terjadi kesalahan pada server',
            'error' => $e->getMessage(),
        ], 500);
    }
    }
}
