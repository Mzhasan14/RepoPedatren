<?php

namespace App\Services\Keluarga;

use App\Models\Biodata;
use App\Models\Keluarga;
use App\Models\OrangTuaWali;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WaliService
{
    public function getAllWali(Request $request)
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

        // 3) Query utama: data orang_tua all
        return DB::table('orang_tua_wali AS o')
            ->join('biodata AS b', 'o.id_biodata', '=', 'b.id')
            // join berkas pas foto terakhir
            ->leftJoinSub($fotoLast, 'fl', fn($j) => $j->on('b.id', '=', 'fl.biodata_id'))
            ->leftJoin('berkas AS br', 'br.id', '=', 'fl.last_id')
            ->join('hubungan_keluarga AS hk', 'hk.id', '=', 'o.id_hubungan_keluarga')
            ->join('keluarga AS kel', 'b.id', '=', 'kel.id_biodata') // dari orangtua ke tabel keluarga
            ->join('keluarga as ka', 'kel.no_kk', '=', 'ka.no_kk') // dari keluarga ke keluarga lainnya
            ->join('biodata as ba', 'ka.id_biodata', '=', 'ba.id') // dari keluarga ke anak
            ->leftJoin('kabupaten AS kb', 'kb.id', '=', 'b.kabupaten_id')
            // hanya yang berstatus aktif
            ->where(fn($q) => $q->where('o.status', true))
            ->where(fn($q) => $q->where('o.wali', true))
            ->select([
                'o.id_biodata AS biodata_id',
                'o.id',
                DB::raw('COALESCE(b.nik, b.no_passport) AS identitas'),
                'b.nama',
                'b.no_telepon AS telepon_1',
                'b.no_telepon_2 AS telepon_2',
                'kb.nama_kabupaten AS kota_asal',
                'o.created_at',
                // ambil updated_at terbaru antar s, rp, rd
                DB::raw('
                        GREATEST(
                            o.updated_at,
                            hk.updated_at,
                            kel.updated_at
                        ) AS updated_at
                    '),
                DB::raw("COALESCE(br.file_path, 'default.jpg') AS foto_profil"),
            ])
            ->groupBy([
                'o.id_biodata',
                'o.id',
                'b.nik',
                'b.no_passport',
                'b.nama',
                'b.no_telepon',
                'b.no_telepon_2',
                'kb.nama_kabupaten',
                'o.created_at',
                'o.updated_at',
                'hk.updated_at',
                'kel.updated_at',
                'br.file_path',
            ])
            ->latest('b.created_at');
    }

    public function formatData($results)
    {
        return collect($results->items())->map(fn($item) => [
            'biodata_id' => $item->biodata_id,
            'id' => $item->id,
            'nik' => $item->identitas,
            'nama' => $item->nama,
            'telepon_1' => $item->telepon_1,
            'telepon_2' => $item->telepon_2,
            'kota_asal' => $item->kota_asal,
            'tgl_update' => Carbon::parse($item->updated_at)->translatedFormat('d F Y H:i:s') ?? '-',
            'tgl_input' => Carbon::parse($item->created_at)->translatedFormat('d F Y H:i:s'),
            'foto_profil' => url($item->foto_profil),
        ]);
    }

    // public function store(array $data)
    // {
    //     return DB::transaction(function () use ($data) {
    //         if (!isset($data['id_biodata'], $data['id_hubungan_keluarga']) || !Auth::id()) {
    //             return [
    //                 'status' => false,
    //                 'message' => 'Data tidak lengkap atau pengguna tidak terautentikasi',
    //                 'data' => null
    //             ];
    //         }

    //         $ortu = OrangTuaWali::create([
    //             'id_biodata' => $data['id_biodata'],
    //             'id_hubungan_keluarga' => $data['id_hubungan_keluarga'],
    //             'wali' => $data['wali'] ?? false,
    //             'pekerjaan' => $data['pekerjaan'] ?? null,
    //             'penghasilan' => $data['penghasilan'] ?? null,
    //             'wafat' => $data['wafat'] ?? false,
    //             'status' => true,
    //             'created_by' => Auth::id(),
    //             'created_at' => now(),
    //             'updated_at' => now(),
    //         ]);

    //         activity('ortu_create')
    //             ->performedOn($ortu)
    //             ->withProperties(['new' => $ortu->getAttributes()])
    //             ->event('create_ortu')
    //             ->log('Data orang tua baru disimpan');

    //         return ['status' => true, 'data' => $ortu];
    //     });
    // }
}
