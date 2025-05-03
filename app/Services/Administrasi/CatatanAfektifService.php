<?php

namespace App\Services\Administrasi;

use App\Models\Catatan_afektif;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class CatatanAfektifService
{
    public function getAllCatatanAfektif(Request $request)
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
            
            return Catatan_afektif::Active()
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
    }          
    catch (\Exception $e) {
        Log::error('Error fetching data Catatan Afektif: ' . $e->getMessage());
        return response()->json([
            "status" => "error",
            "message" => "Terjadi kesalahan saat mengambil data Catatan Afektif",
            "code" => 500
        ], 500);
        }
    }

    public function formatData($results)
    {
        return collect($results->items())->flatMap(fn($item) => [
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
                'nilai' => $item->akhlak_nilai,
                'tindak_lanjut' => $item->akhlak_tindak_lanjut,
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
                'nilai' => $item->kebersihan_nilai,
                'tindak_lanjut' => $item->kebersihan_tindak_lanjut,
                'pencatat' => $item->pencatat,
                'jabatanPencatat' => $item->wali_asuh,
                'waktu_pencatatan' => $item->created_at->format('d M Y H:i:s'),
                'foto_catatan' => url($item->foto_catatan),
                'foto_pencatat' => url($item->foto_pencatat),
            ],
        ]);
        
    }
}