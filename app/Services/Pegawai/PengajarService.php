<?php
namespace App\Services\Pegawai;

use App\Models\Pegawai\Pengajar;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PengajarService
{
    public function getAllPengajar(Request $request)
    {
        try {
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
            return Pengajar::Active()
                // join pegawai yang hanya berstatus true atau akif
                ->join('pegawai',function ($join){
                    $join->on('pegawai.id','=','pengajar.pegawai_id')
                                ->where('pegawai.status_aktif','aktif');
                })
                // relasi ke biodata
                ->join('biodata as b', 'pegawai.biodata_id', '=', 'b.id')
                // relasi ke warga pesantren terakhir true (NIUP)
                ->leftJoinSub($wpLast, 'wl', fn($j) => $j->on('b.id', '=', 'wl.biodata_id')) 
                ->leftJoin('warga_pesantren AS wp', 'wp.id', '=', 'wl.last_id') 
                // relasi ke lembaga
                ->leftJoin('lembaga as l', 'pengajar.lembaga_id', '=', 'l.id')
                // relasi ke golongan
                ->leftJoin('golongan as g', 'pengajar.golongan_id', '=', 'g.id')
                // relasi ke kategori golongan
                ->leftJoin('kategori_golongan as kg', 'g.kategori_golongan_id', '=', 'kg.id')
                 // relasi ke berkas pas foto terakhir
                ->leftJoinSub($fotoLast, 'fl', fn($j) => $j->on('b.id', '=', 'fl.biodata_id'))
                ->leftJoin('berkas AS br', 'br.id', '=', 'fl.last_id')
                ->leftJoin('materi_ajar', function ($join) {
                    $join->on('materi_ajar.pengajar_id', '=', 'pengajar.id')
                         ->where('materi_ajar.status', 1);
                })
                ->select(
                    'pengajar.id',
                    'b.nama',
                    'wp.niup',
                    DB::raw("TIMESTAMPDIFF(YEAR, b.tanggal_lahir, CURDATE()) AS umur"),
                    DB::raw("
                    GROUP_CONCAT(DISTINCT materi_ajar.nama_materi SEPARATOR ', ') AS daftar_materi"),
                    DB::raw("
                    CONCAT(
                        FLOOR(SUM(DISTINCT materi_ajar.jumlah_menit) / 60), ' jam ',
                        MOD(SUM(DISTINCT materi_ajar.jumlah_menit), 60), ' menit'
                    ) AS total_waktu_materi
                "),     
                    DB::raw("COUNT(DISTINCT materi_ajar.nama_materi) AS total_materi"),
                    DB::raw("
                    CASE 
                        WHEN TIMESTAMPDIFF(YEAR, pengajar.tahun_masuk, COALESCE(pengajar.tahun_akhir, CURDATE())) = 0 
                        THEN CONCAT(
                            'Belum setahun sejak ', DATE_FORMAT(pengajar.tahun_masuk, '%Y-%m-%d'),
                            ' sampai ', 
                            IF(pengajar.tahun_akhir IS NOT NULL, DATE_FORMAT(pengajar.tahun_akhir, '%Y-%m-%d'), 'saat ini')
                        )
                        ELSE CONCAT(
                            TIMESTAMPDIFF(YEAR, pengajar.tahun_masuk, COALESCE(pengajar.tahun_akhir, CURDATE())), 
                            ' Tahun sejak ', DATE_FORMAT(pengajar.tahun_masuk, '%Y-%m-%d'),
                            ' sampai ', 
                            IF(pengajar.tahun_akhir IS NOT NULL, DATE_FORMAT(pengajar.tahun_akhir, '%Y-%m-%d'), 'saat ini')
                        )
                    END AS masa_kerja
                "),            
                    'g.nama_golongan',
                    'b.nama_pendidikan_terakhir',
                    DB::raw("DATE_FORMAT(pengajar.updated_at, '%Y-%m-%d %H:%i:%s') AS tgl_update"),
                    DB::raw("DATE_FORMAT(pengajar.created_at, '%Y-%m-%d %H:%i:%s') AS tgl_input"),
                    'l.nama_lembaga',    
                    DB::raw("COALESCE(MAX(br.file_path), 'default.jpg') as foto_profil")
                    )   
                     ->groupBy(
                        'pengajar.id',
                        'b.nama',
                        'wp.niup',
                        'b.tanggal_lahir',
                        'g.nama_golongan',
                        'b.nama_pendidikan_terakhir',
                        'pengajar.updated_at',
                        'pengajar.created_at',
                        'l.nama_lembaga',
                        'pengajar.tahun_masuk',
                        'pengajar.tahun_akhir'
                    );   
                }
                catch (\Exception $e) {
                    Log::error('Error fetching data Pengajar: ' . $e->getMessage());
                    return response()->json([
                        "status" => "error",
                        "message" => "Terjadi kesalahan saat mengambil data pengajar",
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
            "umur" => $item->umur,
            "daftar_materi" => $item->daftar_materi ?? "-",
            "total_materi" => $item->total_materi ?? 0,
            "total_waktu_materi" => $item->total_waktu_materi ?? "-",
            "masa_kerja" => $item->masa_kerja ?? "-",
            "golongan" => $item->nama_golongan,
            "pendidikan_terakhir" => $item->pendidikan_terakhir,
            "tgl_update" => Carbon::parse($item->tgl_update)->translatedFormat('d F Y H:i:s'),
            "tgl_input" => Carbon::parse($item->tgl_input)->translatedFormat('d F Y H:i:s'),
            "lembaga" => $item->nama_lembaga,
            "foto_profil" => url($item->foto_profil)
        ]);
    }
}