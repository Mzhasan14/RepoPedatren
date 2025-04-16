<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PdResource;
use App\Models\Catatan_kognitif;
use Database\Seeders\CatatanKognitifSeeder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CatatanKognitifController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $CatatanKognitif = Catatan_kognitif::all();
        return new PdResource(true,'Data Berhasil Ditampilkan',$CatatanKognitif);
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'id_peserta_didik' => 'required|exists:peserta_didik,id',
            'id_wali_asuh' => 'required|exists:wali_asuh,id',
            'kebahasaan_nilai' => 'required|in:A,B,C,D,E',
            'kebahasaan_tindak_lanjut' => 'nullable|string',
            'baca_kitab_kuning_nilai' => 'required|in:A,B,C,D,E',
            'baca_kitab_kuning_tindak_lanjut' => 'nullable|string',
            'hafalan_tahfidz_nilai' => 'required|in:A,B,C,D,E',
            'hafalan_tahfidz_tindak_lanjut' => 'nullable|string',
            'furudul_ainiyah_nilai' => 'required|in:A,B,C,D,E',
            'furudul_ainiyah_tindak_lanjut' => 'nullable|string',
            'tulis_alquran_nilai' => 'required|in:A,B,C,D,E',
            'tulis_alquran_tindak_lanjut' => 'nullable|string',
            'baca_alquran_nilai' => 'required|in:A,B,C,D,E',
            'baca_alquran_tindak_lanjut' => 'nullable|string',
        ]);
        if ($validator->fails()){
            return response()->json([
                'status' => false,
                'message' => 'Data Gagal Ditambahkan',
                'data' => $validator->errors()
            ]);
        }
        $CatatanKognitif = Catatan_kognitif::create($validator->validated());
        return new PdResource(true,'Data berhasil ditambahkan',$CatatanKognitif);
    }

    public function show(string $id)
    {
        $CatatanKognitif = Catatan_kognitif::findOrFail($id);
        return new PdResource(true, 'Detail data', $CatatanKognitif);
    }

    public function update(Request $request, string $id)
    {
        $CatatanKognitif = Catatan_kognitif::findOrFail($id);

        
        $validator = Validator::make($request->all(),[
            'id_peserta_didik' => 'required|exists:peserta_didik,id',
            'id_wali_asuh' => 'required|exists:wali_asuh,id',
            'kebahasaan_nilai' => 'required|in:A,B,C,D,E',
            'kebahasaan_tindak_lanjut' => 'nullable|string',
            'baca_kitab_kuning_nilai' => 'required|in:A,B,C,D,E',
            'baca_kitab_kuning_tindak_lanjut' => 'nullable|string',
            'hafalan_tahfidz_nilai' => 'required|in:A,B,C,D,E',
            'hafalan_tahfidz_tindak_lanjut' => 'nullable|string',
            'furudul_ainiyah_nilai' => 'required|in:A,B,C,D,E',
            'furudul_ainiyah_tindak_lanjut' => 'nullable|string',
            'tulis_alquran_nilai' => 'required|in:A,B,C,D,E',
            'tulis_alquran_tindak_lanjut' => 'nullable|string',
            'baca_alquran_nilai' => 'required|in:A,B,C,D,E',
            'baca_alquran_tindak_lanjut' => 'nullable|string',
        ]);
        if ($validator->fails()){
            return response()->json([
                'status' => false,
                'message' => 'Data Gagal Ditambahkan',
                'data' => $validator->errors()
            ]);
        }
        $CatatanKognitif->update($validator->validated());
        return new PdResource(true, 'Data berhasil diupdate', $CatatanKognitif);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $CatatanKognitif = Catatan_kognitif::findOrFail($id);
        $CatatanKognitif->delete();
        return new PdResource(true, 'Data berhasil di hapus', $CatatanKognitif);

    }
    public function dataCatatanKognitif(Request $request)
    {
        $query = Catatan_kognitif::Active()
                            ->join('santri as CatatanSantri','CatatanSantri.id','=','catatan_kognitif.id_santri')
                            ->leftJoin('peserta_didik as PesertaSantri','PesertaSantri.id','=','CatatanSantri.id_peserta_didik')
                            ->leftJoin('domisili_santri','domisili_santri.id_santri','=','CatatanSantri.id')
                            ->leftJoin('wilayah','wilayah.id','=','domisili_santri.id_wilayah')
                            ->leftJoin('blok','blok.id','=','domisili_santri.id_blok')
                            ->leftJoin('kamar','kamar.id','=','domisili_santri.id_kamar')
                            ->leftJoin('wali_asuh','wali_asuh.id','=','catatan_kognitif.id_wali_asuh')
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
                                'catatan_kognitif.id',
                                'CatatanBiodata.nama',
                                DB::raw("GROUP_CONCAT(DISTINCT blok.nama_blok SEPARATOR ', ') as blok"),
                                DB::raw("GROUP_CONCAT(DISTINCT wilayah.nama_wilayah SEPARATOR ', ') as wilayah"),
                                DB::raw("GROUP_CONCAT(DISTINCT jurusan.nama_jurusan SEPARATOR ', ') as jurusan"),
                                DB::raw("GROUP_CONCAT(DISTINCT lembaga.nama_lembaga SEPARATOR ', ') as lembaga"),
                                'catatan_kognitif.kebahasaan_nilai',
                                'catatan_kognitif.kebahasaan_tindak_lanjut',
                                'catatan_kognitif.baca_kitab_kuning_nilai',
                                'catatan_kognitif.baca_kitab_kuning_tindak_lanjut',
                                'catatan_kognitif.hafalan_tahfidz_nilai',
                                'catatan_kognitif.hafalan_tahfidz_tindak_lanjut',
                                'catatan_kognitif.furudul_ainiyah_nilai',
                                'catatan_kognitif.furudul_ainiyah_tindak_lanjut',
                                'catatan_kognitif.tulis_alquran_nilai',
                                'catatan_kognitif.tulis_alquran_tindak_lanjut',
                                'catatan_kognitif.baca_alquran_nilai',
                                'catatan_kognitif.baca_alquran_tindak_lanjut',
                                'PencatatBiodata.nama as pencatat',
                                DB::raw("COALESCE((SELECT 'wali asuh' WHERE wali_asuh.id IS NOT NULL), NULL) as wali_asuh"),
                                'catatan_kognitif.created_at'
                            )
                            ->groupBy(
                                'catatan_kognitif.id',
                                'CatatanBiodata.nama',
                                'catatan_kognitif.kebahasaan_nilai',
                                'catatan_kognitif.kebahasaan_tindak_lanjut',
                                'catatan_kognitif.baca_kitab_kuning_nilai',
                                'catatan_kognitif.baca_kitab_kuning_tindak_lanjut',
                                'catatan_kognitif.hafalan_tahfidz_nilai',
                                'catatan_kognitif.hafalan_tahfidz_tindak_lanjut',
                                'catatan_kognitif.furudul_ainiyah_nilai',
                                'catatan_kognitif.furudul_ainiyah_tindak_lanjut',
                                'catatan_kognitif.tulis_alquran_nilai',
                                'catatan_kognitif.tulis_alquran_tindak_lanjut',
                                'catatan_kognitif.baca_alquran_nilai',
                                'catatan_kognitif.baca_alquran_tindak_lanjut',
                                'PencatatBiodata.nama',
                                'wali_asuh.id',
                                'catatan_kognitif.created_at'
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
            $query->whereYear('catatan_kognitif.created_at', $year)
                  ->whereMonth('catatan_kognitif.created_at', $month);
      
                }

// Filter berdasarkan kategori catatan kognitif
if ($request->filled('materi')) {
    $materiMap = [
        'Kebahasaan' => 'kebahasaan_nilai',
        'Baca Kitab Kuning' => 'baca_kitab_kuning_nilai',
        'Pengetahuan Keislaman' => 'pengetahuan_keislaman_nilai',
        'Keterampilan Hidup' => 'keterampilan_hidup_nilai',
        'Pengetahuan Umum' => 'pengetahuan_umum_nilai',
        'Sains' => 'sains_nilai',
    ];

    $kategori = $request->materi;

    if (array_key_exists($kategori, $materiMap)) {
        $query->whereNotNull($materiMap[$kategori]);
    }
}
// Filter berdasarkan skor nilai dari semua kategori penilaian kognitif
if ($request->filled('score') && in_array($request->score, ['A', 'B', 'C', 'D', 'E'])) {
    $materiFields = [
        'catatan_kognitif.kebahasaan_nilai',
        'catatan_kognitif.baca_kitab_kuning_nilai',
        'catatan_kognitif.hafalan_tahfidz_nilai',
        'catatan_kognitif.furudul_ainiyah_nilai',
        'catatan_kognitif.tulis_alquran_nilai',
        'catatan_kognitif.baca_alquran_nilai',
    ];

    $query->where(function ($q) use ($materiFields, $request) {
        foreach ($materiFields as $field) {
            $q->orWhere($field, $request->score);
        }
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
                        'kategori' => 'Kebahasaan',
                        'nilai' => $item->kebahasaan_nilai,
                        'tindak_lanjut' => $item->kebahasaan_tindak_lanjut,
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
                        'kategori' => 'Baca Kitab Kuning',
                        'nilai' => $item->baca_kitab_kuning_nilai,
                        'tindak_lanjut' => $item->baca_kitab_kuning_tindak_lanjut,
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
                        'kategori' => 'Hafalan Tahfidz',
                        'nilai' => $item->hafalan_tahfidz_nilai,
                        'tindak_lanjut' => $item->hafalan_tahfidz_tindak_lanjut,
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
                        'kategori' => 'Furudul Ainiyah',
                        'nilai' => $item->furudul_ainiyah_nilai,
                        'tindak_lanjut' => $item->furudul_ainiyah_tindak_lanjut,
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
                        'kategori' => 'Tulis Al-Quran',
                        'nilai' => $item->tulis_alquran_nilai,
                        'tindak_lanjut' => $item->tulis_alquran_tindak_lanjut,
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
                        'kategori' => 'Baca Al-Quran',
                        'nilai' => $item->baca_alquran_nilai,
                        'tindak_lanjut' => $item->baca_alquran_tindak_lanjut,
                        'pencatat' => $item->pencatat,
                        'jabatanPencatat' => $item->wali_asuh,
                        'waktu_pencatatan' => $item->created_at->format('d M Y H:i:s'),
                    ]
                ];
            })
        ]);
        
    }
}
