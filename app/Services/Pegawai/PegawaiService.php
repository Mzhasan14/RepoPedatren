<?php

namespace App\Services\Pegawai;

use App\Models\Pegawai\Pegawai;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PegawaiService
{
    public function getAllPegawai(Request $request)
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
            return Pegawai::Active()
                            ->join('biodata as b','b.id','pegawai.biodata_id')
                            // join warga pesantren terakhir true (NIUP)
                            ->leftJoinSub($wpLast, 'wl', fn($j) => $j->on('b.id', '=', 'wl.biodata_id'))
                            ->leftJoin('warga_pesantren AS wp', 'wp.id', '=', 'wl.last_id') 
                            // join pengajar yang hanya berstatus aktif                    
                            ->leftJoin('pengajar', function($join) {
                                $join->on('pengajar.pegawai_id', '=', 'pegawai.id')
                                     ->where('pengajar.status_aktif', 'aktif');
                            })
                            // join pengurus yang hanya berstatus aktif
                            ->leftJoin('pengurus', function($join) {
                                $join->on('pengurus.pegawai_id', '=', 'pegawai.id')
                                     ->where('pengurus.status_aktif', 'aktif');
                            })
                            // join karyawan yang hanya berstatus aktif
                            ->leftJoin('karyawan', function($join) {
                                $join->on('karyawan.pegawai_id', '=', 'pegawai.id')
                                     ->where('karyawan.status_aktif', 'aktif');
                            })
                            
                            // join berkas pas foto terakhir
                            ->leftJoinSub($fotoLast, 'fl', fn($j) => $j->on('b.id', '=', 'fl.biodata_id'))
                            ->leftJoin('berkas AS br', 'br.id', '=', 'fl.last_id')
                            ->select(
                                'pegawai.id as id',
                                'b.nama as nama',
                                'wp.niup',
                                'pengurus.id as pengurus',
                                'karyawan.id as karyawan',
                                'pengajar.id as pengajar',
                                DB::raw("TIMESTAMPDIFF(YEAR, b.tanggal_lahir, CURDATE()) AS umur"),
                                DB::raw("TRIM(BOTH ', ' FROM CONCAT_WS(', ', 
                                GROUP_CONCAT(DISTINCT CASE WHEN pengajar.id IS NOT NULL THEN 'Pengajar' END SEPARATOR ', '),
                                GROUP_CONCAT(DISTINCT CASE WHEN karyawan.id IS NOT NULL THEN 'Karyawan' END SEPARATOR ', '),
                                GROUP_CONCAT(DISTINCT CASE WHEN pengurus.id IS NOT NULL THEN 'Pengurus' END SEPARATOR ', ')
                            )) as status"),
                                'b.nama_pendidikan_terakhir as pendidikanTerkahir',
                                DB::raw("COALESCE(MAX(br.file_path), 'default.jpg') as foto_profil")
                                )->groupBy(
                                    'pegawai.id', 
                                    'b.nama',
                                    'wp.niup',
                                    'pengurus.id',
                                    'karyawan.id',
                                    'pengajar.id',
                                    'b.tanggal_lahir',
                                    'b.nama_pendidikan_terakhir'
                                );
    }
        catch (\Exception $e) {
            Log::error('Error fetching data pegawai: ' . $e->getMessage());
            return response()->json([
                "status" => "error",
                "message" => "Terjadi kesalahan saat mengambil data pegawai",
                "code" => 500
            ], 500);
        }
                            
    }

    public function formatData($results)
    {
        return collect($results->items())->map(fn($item) => [
            "id" => $item->id,
            "nama" => $item->nama,
            "niup" => $item->niup ?? '-',
            "umur" => $item->umur,
            "status" => $item->status,
            "pendidikanTerkahir" => $item->pendidikanTerkahir,
            "pengurus" => $item->pengurus ? true : false,
            "karyawan" => $item->karyawan ? true : false,
            "pengajar" => $item->pengajar ? true : false,
            "foto_profil" => url($item->foto_profil)
        ]);
    }
}