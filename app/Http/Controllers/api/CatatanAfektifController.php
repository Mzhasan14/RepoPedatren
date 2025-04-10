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
        $query = Catatan_afektif::Active()
                                ->join('santri as CatatanSantri','CatatanSantri.id','=','catatan_afektif.id_santri')
                                ->leftJoin('peserta_didik as PesertaSantri','PesertaSantri.id','=','CatatanSantri.id_peserta_didik')
                                ->leftJoin('domisili_santri','domisili_santri.id_santri','=','CatatanSantri.id')
                                ->leftJoin('wilayah','wilayah.id','=','domisili_santri.id_wilayah')
                                ->leftJoin('blok','blok.id','=','domisili_santri.id_blok')
                                ->leftJoin('kamar','kamar.id','=','domisili_santri.id_kamar')
                                ->leftJoin('wali_asuh','wali_asuh.id','=','catatan_afektif.id_wali_asuh')
                                ->leftJoin('peserta_didik as CatatanPeserta','CatatanPeserta.id','=','CatatanSantri.id_peserta_didik')
                                ->leftJoin('pelajar','CatatanPeserta.id','=','pelajar.id_peserta_didik')
                                ->leftJoin('pendidikan_pelajar','pendidikan_pelajar.id_pelajar','=','pelajar.id')
                                ->leftJoin('lembaga','lembaga.id','pendidikan_pelajar.id_lembaga')
                                ->leftJoin('jurusan','jurusan.id','pendidikan_pelajar.id_jurusan')
                                ->leftJoin('kelas','kelas.id','pendidikan_pelajar.id_kelas')
                                ->leftJoin('rombel','rombel.id','pendidikan_pelajar.id_rombel')
                                ->join('biodata as CatatanBiodata','CatatanBiodata.id','=','CatatanPeserta.id_biodata')
                                ->leftJoin('santri as PencatatSantri','PencatatSantri.id','=','wali_asuh.id_santri')
                                ->leftJoin('peserta_didik as PencatatPeserta','PencatatPeserta.id','=','PencatatSantri.id_peserta_didik')
                                ->join('biodata as PencatatBiodata','PencatatBiodata.id','PencatatPeserta.id_biodata')
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
                                    'catatan_afektif.created_at'
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
                                    'catatan_afektif.created_at'
                                )->distinct();
        // Filter berdasarkan lokasi (negara, provinsi, kabupaten, kecamatan, desa)
        if ($request->filled('negara')) {
            $query->join('negara', 'CatatanBiodata.id_negara', '=', 'negara.id')
                ->where('negara.nama_negara', $request->negara);
            if ($request->filled('provinsi')) {
                $query->leftjoin('provinsi', 'CatatanBiodata.id_provinsi', '=', 'provinsi.id');
                $query->where('provinsi.nama_provinsi', $request->provinsi);
                if ($request->filled('kabupaten')) {
                    $query->leftjoin('kabupaten', 'CatatanBiodata.id_kabupaten', '=', 'kabupaten.id');
                    $query->where('kabupaten.nama_kabupaten', $request->kabupaten);
                    if ($request->filled('kecamatan')) {
                        $query->leftjoin('kecamatan', 'CatatanBiodata.id_kecamatan', '=', 'kecamatan.id');
                        $query->where('kecamatan.nama_kecamatan', $request->kecamatan);
                    }
                }
            }
        }
        // Filter Search
        if ($request->filled('search')) {
            $search = strtolower($request->search);
    
            $query->where(function ($q) use ($search) {
                $q->where('CatatanBiodata.nik', 'LIKE', "%$search%")
                    ->orWhere('CatatanBiodata.no_passport', 'LIKE', "%$search%")
                    ->orWhere('CatatanBiodata.nama', 'LIKE', "%$search%")
                    ->orWhere('CatatanBiodata.niup', 'LIKE', "%$search%")
                    ->orwhere('PencatatBiodata.nik', 'LIKE', "%$search%")
                    ->orWhere('PencatatBiodata.no_passport', 'LIKE', "%$search%")
                    ->orWhere('PencatatBiodata.nama', 'LIKE', "%$search%")
                    ->orWhere('PencatatBiodata.niup', 'LIKE', "%$search%");
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
            if (strtolower($request->phone_number) === 'mempunyai') {
                // Hanya tampilkan data yang memiliki nomor telepon
                $query->whereNotNull('CatatanBiodata.no_telepon')->where('CatatanBiodata.no_telepon', '!=', '');
            } elseif (strtolower($request->phone_number) === 'tidak mempunyai') {
                // Hanya tampilkan data yang tidak memiliki nomor telepon
                $query->whereNull('CatatanBiodata.no_telepon')->orWhere('CatatanBiodata.no_telepon', '');
            }
        }
        if ($request->filled('periode')) {
            [$year, $month] = explode('-', $request->periode);
            $query->whereYear('catatan_afektif.created_at', $year)
                  ->whereMonth('catatan_afektif.created_at',$month);
                }
                if ($request->filled('materi')) {
                    if ($request->materi === 'Akhlak') {
                        $query->where('catatan_afektif.akhlaq_nilai', 'Akhlak');
                    } elseif ($request->materi === 'Kebersihan') {
                        $query->where('catatan_afektif.kebersihan_nilai', 'Kebersihan');
                    } elseif ($request->materi === 'Kepedulian') {
                        $query->where('catatan_afektif.kepedulian_nilai', 'Kepedulian');
                    } else {
                    }
                }
               // Filter berdasarkan score/nilai
    if ($request->filled('score') && in_array($request->score, ['A', 'B', 'C', 'D', 'E'])) {
        if ($request->materi === 'Akhlak') {
            $query->where('catatan_afektif.akhlaq_nilai', $request->score);
        } elseif ($request->materi === 'Kebersihan') {
            $query->where('catatan_afektif.kebersihan_nilai', $request->score);
        } elseif ($request->materi === 'Kepedulian') {
            $query->where('catatan_afektif.kepedulian_nilai', $request->score);
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
                        'nilai' => $item->kepedulian_nilai,
                        'tindak_lanjut' => $item->kepedulian_tindak_lanjut,
                        'pencatat' => $item->pencatat,
                        'jabatanPencatat' => $item->wali_asuh,
                        'waktu_pencatatan' => $item->created_at->format('d M Y H:i:s'),
                    ],
                    [
                        'id_santri' => $item->id,
                        'nama_santri' => $item->nama,
                        'blok' => $item->blok,
                        'wilayah' => $item->wilayah,
                        'pendidikan' => $item->jurusan,
                        'lembaga' => $item->lembaga,
                        'kategori' => 'Kebersihan',
                        'nilai' => $item->kebersihan_nilai,
                        'tindak_lanjut' => $item->kebersihan_tindak_lanjut,
                        'pencatat' => $item->pencatat,
                        'jabatanPencatat' => $item->wali_asuh,
                        'waktu_pencatatan' => $item->created_at->format('d M Y H:i:s'),
                    ],
                    [
                        'id_santri' => $item->id,
                        'nama_santri' => $item->nama,
                        'blok' => $item->blok,
                        'wilayah' => $item->wilayah,
                        'pendidikan' => $item->jurusan,
                        'lembaga' => $item->lembaga,
                        'kategori' => 'Akhlak',
                        'nilai' => $item->akhlak_nilai,
                        'tindak_lanjut' => $item->akhlak_tindak_lanjut,
                        'pencatat' => $item->pencatat,
                        'jabatanPencatat' => $item->wali_asuh,
                        'waktu_pencatatan' => $item->created_at->format('d M Y H:i:s'),
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
