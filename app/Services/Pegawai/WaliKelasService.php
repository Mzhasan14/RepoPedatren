<?php

namespace App\Services\Pegawai;

use App\Models\Pegawai\Pegawai;
use App\Models\Pegawai\Pengurus;
use App\Models\Pegawai\WaliKelas;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WaliKelasService
{
    public function getAllWalikelas(Request $request)
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
        return WaliKelas::Active()
                            // Join Pegawai yang Berstatus Aktif
                            ->join('pegawai', function ($join) {
                                    $join->on('wali_kelas.pegawai_id', '=', 'pegawai.id')
                                         ->where('pegawai.status_aktif', 'aktif')
                                         ->whereNull('pegawai.deleted_at');
                            })
                            ->join('biodata as b','b.id','=','pegawai.biodata_id')  
                            //  Join Warga Pesantren Terakhir Berstatus Aktif
                            ->leftJoinSub($wpLast, 'wl', fn($j) => $j->on('b.id', '=', 'wl.biodata_id'))
                            ->leftJoin('warga_pesantren AS wp', 'wp.id', '=', 'wl.last_id')   
                            // join berkas pas foto terakhir
                            ->leftJoinSub($fotoLast, 'fl', fn($j) => $j->on('b.id', '=', 'fl.biodata_id'))
                            ->leftJoin('berkas AS br', 'br.id', '=', 'fl.last_id')
                            ->leftJoin('rombel as r','r.id','=','wali_kelas.rombel_id')
                            ->leftJoin('kelas as k','k.id','=','wali_kelas.kelas_id')
                            ->leftJoin('jurusan as j','j.id','=','wali_kelas.jurusan_id')
                            ->leftJoin('lembaga as l','l.id','=','wali_kelas.lembaga_id')
                            ->whereNull('wali_kelas.deleted_at')
                            ->select(
                                'pegawai.biodata_id as biodata_uuid',
                                'wali_kelas.id as id',
                                'b.nama',
                                'wp.niup',
                                DB::raw("COALESCE(b.nik, b.no_passport) as identitas"),
                                'b.jenis_kelamin',
                                'l.nama_lembaga',
                                'j.nama_jurusan',
                                'k.nama_kelas',
                                'r.gender_rombel',
                                DB::raw("CONCAT(wali_kelas.jumlah_murid, ' pelajar') as jumlah_murid"),
                                'r.nama_rombel',
                                DB::raw("DATE_FORMAT(wali_kelas.updated_at, '%Y-%m-%d %H:%i:%s') AS tgl_update"),
                                DB::raw("DATE_FORMAT(wali_kelas.created_at, '%Y-%m-%d %H:%i:%s') AS tgl_input"),
                                DB::raw("COALESCE(MAX(br.file_path), 'default.jpg') as foto_profil")
                            )->groupBy(
                                'pegawai.biodata_id', 
                                'wali_kelas.id',
                                'b.nama', 
                                'wp.niup', 
                                'l.nama_lembaga',
                                'j.nama_jurusan', 
                                'k.nama_kelas', 
                                'r.nama_rombel',
                                'b.nik',
                                'b.no_passport',
                                'r.gender_rombel',
                                'b.jenis_kelamin',
                                'wali_kelas.jumlah_murid',
                                'wali_kelas.updated_at',
                                'wali_kelas.created_at',
                            );
                        }
                        catch (\Exception $e) {
                            Log::error('Error fetching data Wali Kelas: ' . $e->getMessage());
                            return response()->json([
                                "status" => "error",
                                "message" => "Terjadi kesalahan saat mengambil data Wali Kelas",
                                "code" => 500
                            ], 500);
                        }
    }
    public function formatData($results)
    {
        return collect($results->items())->map(fn($item)=>[
            "id" => $item->biodata_uuid,
            "nama" => $item->nama,
            "niup" => $item->niup ?? "-",
            "nik_or_Passport" => $item->identitas,
            "JenisKelamin" => $item->jenis_kelamin === 'l' ? 'Laki-laki' : ($item->jenis_kelamin === 'p' ? 'Perempuan' : 'Tidak Diketahui'),
            "lembaga" => $item->nama_lembaga,
            "jurusan" => $item->nama_jurusan,
            "kelas" => $item->nama_kelas,
            "GenderRombel" => $item->gender_rombel,
            "JumlahMurid" => $item->jumlah_murid,
            "rombel" => $item->nama_rombel,
            "tgl_update" => $item->tgl_update ?? "-",
            "tgl_input" => $item->tgl_input,
            "foto_profil" => url($item->foto_profil)
        ]);
    }
}