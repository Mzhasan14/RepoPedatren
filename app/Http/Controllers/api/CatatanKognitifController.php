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
                    ->join('santri as CatatanSantri', 'CatatanSantri.id', '=', 'catatan_kognitif.id_santri')
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
                    ->leftJoin('wali_asuh', 'wali_asuh.id', '=', 'catatan_kognitif.id_wali_asuh')
                    ->leftJoin('santri as PencatatSantri', 'PencatatSantri.id', '=', 'wali_asuh.id_santri')
                    ->leftJoin('biodata as PencatatBiodata', 'PencatatBiodata.id', '=', 'PencatatSantri.biodata_id')
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
                        DB::raw("CASE WHEN wali_asuh.id IS NOT NULL THEN 'wali asuh' ELSE NULL END as wali_asuh"),
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
                        )
                    ->distinct();
    
          // Filter berdasarkan lokasi (negara, provinsi, kabupaten, kecamatan, desa)
          if ($request->filled('negara')) {
            $query->leftJoin('negara', 'CatatanBiodata.negara_id', '=', 'negara.id')
                ->where('negara.nama_negara', $request->negara);
    
            if ($request->filled('provinsi')) {
                $query->leftJoin('provinsi', 'CatatanBiodata.provinsi_id', '=', 'provinsi.id')
                    ->where('provinsi.nama_provinsi', $request->provinsi);
    
                if ($request->filled('kabupaten')) {
                    $query->leftJoin('kabupaten', 'CatatanBiodata.kabupaten_id', '=', 'kabupaten.id')
                        ->where('kabupaten.nama_kabupaten', $request->kabupaten);
    
                    if ($request->filled('kecamatan')) {
                        $query->leftJoin('kecamatan', 'CatatanBiodata.kecamatan_id', '=', 'kecamatan.id')
                            ->where('kecamatan.nama_kecamatan', $request->kecamatan);
                    }
                }
            }
        }
    
        // Filter Search Nama
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
    
        // Filter Jenis Kelamin
        if ($request->filled('jenis_kelamin')) {
            $jenis_kelamin = strtolower($request->jenis_kelamin);
            if ($jenis_kelamin == 'laki-laki') {
                $query->where('CatatanBiodata.jenis_kelamin', 'l');
            } else if ($jenis_kelamin == 'perempuan') {
                $query->where('CatatanBiodata.jenis_kelamin', 'p');
            }
        }
    
        // Filter Nomor Telepon
        if ($request->filled('phone_number')) {
            $query->where(function ($q) use ($request) {
                if (strtolower($request->phone_number) === 'mempunyai') {
                    $q->whereNotNull('CatatanBiodata.no_telepon')
                      ->where('CatatanBiodata.no_telepon', '!=', '');
                } elseif (strtolower($request->phone_number) === 'tidak mempunyai') {
                    $q->where(function($q2) {
                        $q2->whereNull('CatatanBiodata.no_telepon')
                           ->orWhere('CatatanBiodata.no_telepon', '');
                    });
                }
            });
        }
    
        // Filter Periode
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
                'Hafalan Tahfidz' => 'hafalan_tahfidz_nilai',
                'Furudul Ainiyah' => 'furudul_ainiyah_nilai',
                'Tulis Al-Quran' => 'tulis_alquran_nilai',
                'Baca Al-Quran' => 'baca_alquran_nilai',
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
                        'kebahasaan_nilai' => $item->kebahasaan_nilai,
                        'kebahasaan_tindak_lanjut' => $item->kebahasaan_tindak_lanjut,
                        'baca_kitab_kuning_nilai' => $item->baca_kitab_kuning_nilai,
                        'baca_kitab_kuning_tindak_lanjut' => $item->baca_kitab_kuning_tindak_lanjut,
                        'hafalan_tahfidz_nilai' => $item->hafalan_tahfidz_nilai,
                        'hafalan_tahfidz_tindak_lanjut' => $item->hafalan_tahfidz_tindak_lanjut,
                        'furudul_ainiyah_nilai' => $item->furudul_ainiyah_nilai,
                        'furudul_ainiyah_tindak_lanjut' => $item->furudul_ainiyah_tindak_lanjut,
                        'tulis_alquran_nilai' => $item->tulis_alquran_nilai,
                        'tulis_alquran_tindak_lanjut' => $item->tulis_alquran_tindak_lanjut,
                        'baca_alquran_nilai' => $item->baca_alquran_nilai,
                        'baca_alquran_tindak_lanjut' => $item->baca_alquran_tindak_lanjut,
                        'pencatat' => $item->pencatat,
                        'jabatanPencatat' => $item->wali_asuh,
                        'waktu_pencatatan' => $item->created_at->format('d M Y H:i:s'),
                    ]
                ];
            })
        ]);
        
    }
}
