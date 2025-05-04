<?php
namespace App\Services\Pegawai;

use App\Models\Pegawai\Karyawan;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class KaryawanService
{
    public function getAllKaryawan(Request $request)
    {
        try
        {
       // 1) Ambil ID untuk jenis berkas "Pas foto"
        $pasFotoId = DB::table('jenis_berkas')
                ->where('nama_jenis_berkas', 'Pas foto')
                ->value('id');

        // 2) Subquery: foto terakhir per biodata
        $fotoLast = DB::table('berkas')
                ->select('biodata_id', DB::raw('MAX(id) AS last_id'))
                ->where('jenis_berkas_id', $pasFotoId)
                ->groupBy('biodata_id');
        // 3) Subquery: warga pesantren terakhir per biodata
        $wpLast = DB::table('warga_pesantren')
                ->select('biodata_id', DB::raw('MAX(id) AS last_id'))
                ->where('status', true)
                ->groupBy('biodata_id');
        // 4) Query utama
        return Karyawan::Active()
                        // join pegawai yang hanya berstatus true atau akif
                        ->join('pegawai',function ($join){
                            $join->on('pegawai.id','=','karyawan.pegawai_id')
                                ->where('pegawai.status_aktif','aktif');
                        })
                        ->join('biodata as b','b.id','=','pegawai.biodata_id')
                        ->leftJoin('golongan_jabatan as g','g.id','=','karyawan.golongan_jabatan_id')
                        // join ke warga pesantren terakhir true (NIUP)
                        ->leftJoinSub($wpLast, 'wl', fn($j) => $j->on('b.id', '=', 'wl.biodata_id')) 
                        ->leftJoin('warga_pesantren AS wp', 'wp.id', '=', 'wl.last_id') 
                        // join berkas pas foto terakhir
                        ->leftJoinSub($fotoLast, 'fl', fn($j) => $j->on('b.id', '=', 'fl.biodata_id'))                            
                        ->leftJoin('berkas AS br', 'br.id', '=', 'fl.last_id')
                        ->leftJoin('lembaga as l','l.id','=','karyawan.lembaga_id')
                        // Join riwayat Jabatan karyawan mengambil data yang terbaru
                        ->leftJoin('riwayat_jabatan_karyawan', function ($join) {
                            $join->on('riwayat_jabatan_karyawan.karyawan_id', '=', 'karyawan.id')
                                ->whereRaw('riwayat_jabatan_karyawan.tanggal_mulai = (
                                    SELECT MAX(tanggal_mulai) 
                                    FROM riwayat_jabatan_karyawan 
                                    WHERE riwayat_jabatan_karyawan.karyawan_id = karyawan.id
                                )');
                        })
                        ->select(
                            'karyawan.id',
                            'b.nama',
                            'wp.niup',
                            'b.nik',
                            DB::raw("TIMESTAMPDIFF(YEAR, b.tanggal_lahir, CURDATE()) AS umur"),
                            'riwayat_jabatan_karyawan.keterangan_jabatan as KeteranganJabatan',
                            'l.nama_lembaga',
                            'karyawan.jabatan',
                            'g.nama_golongan_jabatan as nama_golongan',
                            'b.nama_pendidikan_terakhir as pendidikanTerakhir',
                            DB::raw("DATE_FORMAT(karyawan.updated_at, '%Y-%m-%d %H:%i:%s') AS tgl_update"),
                            DB::raw("DATE_FORMAT(karyawan.created_at, '%Y-%m-%d %H:%i:%s') AS tgl_input"),
                            DB::raw("COALESCE(MAX(br.file_path), 'default.jpg') as foto_profil")
                            )->groupBy(
                                'karyawan.id', 
                                'b.nama',
                                'b.nik',
                                'wp.niup',
                                'b.tanggal_lahir',
                                'riwayat_jabatan_karyawan.keterangan_jabatan',
                                'l.nama_lembaga',
                                'karyawan.jabatan',
                                'g.nama_golongan_jabatan',
                                'b.nama_pendidikan_terakhir',
                                'karyawan.updated_at',
                                'karyawan.created_at',
                            );
                        }
                            catch (\Exception $e) {
                                Log::error('Error fetching data karyawan: ' . $e->getMessage());
                                return response()->json([
                                    "status" => "error",
                                    "message" => "Terjadi kesalahan saat mengambil data karyawan",
                                    "code" => 500
                                ], 500);
                            }
    }
    public function formatData($results)
    {
        return collect($results->items())->map(fn($item) => [
            "id" => $item->id,
            "nama" => $item->nama,
            "niup" => $item->niup ?? "-",
            "nik" => $item->nik,
            "umur" => $item->umur,
            "Keterangan_jabatan" => $item->KeteranganJabatan,
            "lembaga" => $item->nama_lembaga,
            "jenis_jabatan" => $item->jabatan,
            "golongan" => $item->nama_golongan,
            "pendidikanTerakhir" => $item->pendidikanTerakhir,
            "tgl_update" => $item->tgl_update,
            "tgl_input" => $item->tgl_input,
            "foto_profil" => url($item->foto_profil)
        ]) ;
    }
}