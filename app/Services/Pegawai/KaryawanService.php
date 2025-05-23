<?php
namespace App\Services\Pegawai;

use App\Models\Berkas;
use App\Models\Biodata;
use App\Models\JenisBerkas;
use Illuminate\Http\UploadedFile;
use App\Models\Keluarga;
use App\Models\Pegawai\Karyawan;
use App\Models\Pegawai\Pegawai;
use App\Models\WargaPesantren;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

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
        return DB::table('karyawan')
                        // join pegawai yang hanya berstatus true atau akif
                        ->join('pegawai',function ($join){
                            $join->on('pegawai.id','=','karyawan.pegawai_id')
                                ->where('pegawai.status_aktif','aktif')
                                ->whereNull('pegawai.deleted_at');
                        })
                        ->join('biodata as b','b.id','=','pegawai.biodata_id')
                        // relasi ke golongan jabatan yang hanya berstatus true
                        ->leftJoin('golongan_jabatan as g',function ($join) {
                            $join->on('karyawan.golongan_jabatan_id', '=', 'g.id')
                                ->where('g.status', true);
                        })
                        // join ke warga pesantren terakhir true (NIUP)
                        ->leftJoinSub($wpLast, 'wl', fn($j) => $j->on('b.id', '=', 'wl.biodata_id')) 
                        ->leftJoin('warga_pesantren AS wp', 'wp.id', '=', 'wl.last_id') 
                        // join berkas pas foto terakhir
                        ->leftJoinSub($fotoLast, 'fl', fn($j) => $j->on('b.id', '=', 'fl.biodata_id'))                            
                        ->leftJoin('berkas AS br', 'br.id', '=', 'fl.last_id')
                        ->leftJoin('lembaga as l','l.id','=','karyawan.lembaga_id')
                        ->whereNull('karyawan.deleted_at')
                        ->where('karyawan.status_aktif','aktif')
                        ->select(
                            'pegawai.biodata_id as biodata_uuid',
                            'b.nama',
                            'wp.niup',
                            'b.nik',
                            DB::raw("TIMESTAMPDIFF(YEAR, b.tanggal_lahir, CURDATE()) AS umur"),
                            'karyawan.keterangan_jabatan as KeteranganJabatan',
                            'l.nama_lembaga',
                            'karyawan.jabatan',
                            'g.nama_golongan_jabatan as nama_golongan',
                            'b.nama_pendidikan_terakhir as pendidikanTerakhir',
                            DB::raw("DATE_FORMAT(karyawan.updated_at, '%Y-%m-%d %H:%i:%s') AS tgl_update"),
                            DB::raw("DATE_FORMAT(karyawan.created_at, '%Y-%m-%d %H:%i:%s') AS tgl_input"),
                            DB::raw("COALESCE(MAX(br.file_path), 'default.jpg') as foto_profil")
                            )->groupBy(
                                'pegawai.biodata_id', 
                                'b.nama',
                                'b.nik',
                                'wp.niup',
                                'b.tanggal_lahir',
                                'karyawan.keterangan_jabatan',
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
            "biodata_id" => $item->biodata_uuid,
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
        ]);
    }

}