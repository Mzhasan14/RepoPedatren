<?php

namespace App\Services\Pegawai;

use App\Models\Pegawai\Pegawai;
use App\Models\Pegawai\Pengurus;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PengurusService
{
    public function getAllPengurus(Request $request)
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
       return Pengurus::Active()
                            // relasi ke golongan jabatan yang hanya berstatus true
                            ->leftJoin('golongan_jabatan as g',function ($join) {
                                $join->on('pengurus.golongan_jabatan_id', '=', 'g.id')
                                    ->where('g.status', true);
                            })
                            // Join Pegawai yang Berstatus Aktif
                            ->join('pegawai', function ($join) {
                                $join->on('pengurus.pegawai_id', '=', 'pegawai.id')
                                    ->where('pegawai.status_aktif', 'aktif');
                            })
                            ->join('biodata as b','pegawai.biodata_id','=','b.id')
                            //  Join Warga Pesantren Terakhir Berstatus Aktif
                            ->leftJoinSub($wpLast, 'wl', fn($j) => $j->on('b.id', '=', 'wl.biodata_id'))
                            ->join('warga_pesantren AS wp', 'wp.id', '=', 'wl.last_id')  
                            // join berkas pas foto terakhir
                            ->leftJoinSub($fotoLast, 'fl', fn($j) => $j->on('b.id', '=', 'fl.biodata_id'))
                            ->leftJoin('berkas AS br', 'br.id', '=', 'fl.last_id')
                            ->leftJoin('lembaga as l', 'pegawai.lembaga_id', '=', 'l.id')
                            ->whereNull('pengurus.deleted_at')
                            ->select(
                                'pengurus.pegawai_id as id',
                                'b.nama',
                                'b.nik',
                                'wp.niup',
                                'pengurus.keterangan_jabatan as jabatan',
                                DB::raw("TIMESTAMPDIFF(YEAR, b.tanggal_lahir, CURDATE()) AS umur"),
                                'pengurus.satuan_kerja',
                                'pengurus.jabatan as jenis',
                                'g.nama_golongan_jabatan as nama_golongan',
                                'b.nama_pendidikan_terakhir as pendidikan_terakhir',
                                DB::raw("DATE_FORMAT(pengurus.updated_at, '%Y-%m-%d %H:%i:%s') AS tgl_update"),
                                DB::raw("DATE_FORMAT(pengurus.created_at, '%Y-%m-%d %H:%i:%s') AS tgl_input"),
                                DB::raw("COALESCE(MAX(br.file_path), 'default.jpg') as foto_profil")
                                )    
                                ->groupBy(
                                    'wp.niup',
                                    'pengurus.pegawai_id',
                                    'b.nama',
                                    'b.nik',
                                    'pengurus.keterangan_jabatan',
                                    'b.tanggal_lahir',
                                    'pengurus.satuan_kerja',
                                    'pengurus.jabatan',
                                    'g.nama_golongan_jabatan',
                                    'b.nama_pendidikan_terakhir',
                                    'pengurus.updated_at',
                                    'pengurus.created_at'
                                );
                            }
                            catch (\Exception $e) {
                                Log::error('Error fetching data Pengurus: ' . $e->getMessage());
                                return response()->json([
                                    "status" => "error",
                                    "message" => "Terjadi kesalahan saat mengambil data Pengurus",
                                    "code" => 500
                                ], 500);
                            }
    }
    public function formatData($results)
    {
        return collect($results->items())->map(fn($item) => [
            "id" => $item->id,
            "nama" => $item->nama,
            "nik" => $item->nik,
            "niup" => $item->niup ?? "-",
            "jabatan" => $item->jabatan,
            "umur" => $item->umur,
            "satuan_kerja" => $item->satuan_kerja ?? "-",
            "jenis_jabatan" =>$item->jenis,
            "golongan" => $item->nama_golongan,
            "pendidikan_terakhir" => $item->pendidikan_terakhir,
            "tgl_update" => $item->tgl_update ?? "-",
            "tgl_input" => $item->tgl_input,
            "foto_profil" => url($item->foto_profil)
        ]);
    }
}