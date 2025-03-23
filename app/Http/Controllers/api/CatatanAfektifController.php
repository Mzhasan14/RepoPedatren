<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PdResource;
use App\Models\Catatan_afektif;
use Illuminate\Http\Request;
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

    public function dataCatatanAfektif()
    {
        $query = Catatan_afektif::join('santri as CatatanSantri','CatatanSantri.id','=','catatan_afektif.id_santri')
                                ->leftJoin('domisili','domisili.id','=','CatatanSantri.id_domisili')
                                ->leftJoin('wilayah','wilayah.id','=','CatatanSantri.id_wilayah')
                                ->leftJoin('blok','blok.id','=','CatatanSantri.id_blok')
                                ->leftJoin('wali_asuh','wali_asuh.id','=','catatan_afektif.id_wali_asuh')
                                ->leftJoin('peserta_didik as CatatanPeserta','CatatanPeserta.id','=','CatatanSantri.id_peserta_didik')
                                ->leftJoin('pelajar','pelajar.id_peserta_didik','=','CatatanPeserta.id')
                                ->leftJoin('lembaga','lembaga.id','pelajar.id_lembaga')
                                ->leftJoin('jurusan','jurusan.id','pelajar.id_jurusan')
                                ->join('biodata as CatatanBiodata','CatatanBiodata.id','=','CatatanPeserta.id_biodata')
                                ->leftJoin('santri as PencatatSantri','PencatatSantri.nis','=','wali_asuh.nis')
                                ->leftJoin('peserta_didik as PencatatPeserta','PencatatPeserta.id','=','PencatatSantri.id_peserta_didik')
                                ->join('biodata as PencatatBiodata','PencatatBiodata.id','PencatatPeserta.id_biodata')
                                ->select(
                                    'catatan_afektif.id',
                                    'CatatanBiodata.nama',
                                    'domisili.nama_domisili',
                                    'blok.nama_blok',
                                    'wilayah.nama_wilayah',
                                    'jurusan.nama_jurusan',
                                    'lembaga.nama_lembaga',
                                    'catatan_afektif.kepedulian_nilai',
                                    'catatan_afektif.kepedulian_tindak_lanjut',
                                    'catatan_afektif.kebersihan_nilai',
                                    'catatan_afektif.kebersihan_tindak_lanjut',
                                    'catatan_afektif.akhlak_nilai',
                                    'catatan_afektif.akhlak_tindak_lanjut',
                                    'PencatatBiodata.nama as pencatat'
                                )->get();
        return response()->json([
            // "total_data" => $hasil->total(),
            // "current_page" => $hasil->currentPage(),
            // "per_page" => $hasil->perPage(),
            // "total_pages" => $hasil->lastPage(),
            "data" => $query->map(function ($item) {
                return [
                    [
                        'id_santri' => $item->id,
                        'nama_santri' => $item->nama_santri,
                        'domisili' => $item->nama_domisili,
                        'pendidikan' => $item->nama_jurusan . ' - ' . $item->nama_lembaga,
                        'kategori' => 'Kepedulian',
                        'nilai' => $item->kepedulian_nilai,
                        'tindak_lanjut' => $item->kepedulian_tindak_lanjut,
                        'pencatat' => $item->pencatat,
                        // 'waktu_pencatatan' => $item->created_at->format('d M Y H:i:s'),
                    ],
                    [
                        'id_santri' => $item->id,
                        'nama_santri' => $item->nama_santri,
                        'domisili' => $item->nama_domisili,
                        'pendidikan' => $item->nama_jurusan . ' - ' . $item->nama_lembaga,
                        'kategori' => 'Kebersihan',
                        'nilai' => $item->kebersihan_nilai,
                        'tindak_lanjut' => $item->kebersihan_tindak_lanjut,
                        'pencatat' => $item->pencatat,
                        // 'waktu_pencatatan' => $item->created_at->format('d M Y H:i:s'),
                    ],
                    [
                        'id_santri' => $item->id,
                        'nama_santri' => $item->nama_santri,
                        'domisili' => $item->nama_domisili,
                        'pendidikan' => $item->nama_jurusan . ' - ' . $item->nama_lembaga,
                        'kategori' => 'Akhlak',
                        'nilai' => $item->akhlak_nilai,
                        'tindak_lanjut' => $item->akhlak_tindak_lanjut,
                        'pencatat' => $item->pencatat,
                        // 'waktu_pencatatan' => $item->created_at->format('d M Y H:i:s'),
                    ],
                ];
            })
        ]);
    }
}
