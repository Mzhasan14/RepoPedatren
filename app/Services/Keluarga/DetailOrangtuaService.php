<?php

namespace App\Services\Keluarga;

use illuminate\Support\Facades\DB;
use illuminate\Support\Facades\URL;

class DetailOrangtuaService
{

    public function getDetailOrangtua(string $OrangtuaId): array
    {
        // --- 1. Ambil basic ortu + biodata_id + no_kk sekaligus ---
        $base = DB::table('orang_tua_wali as ot')
            ->join('biodata as b', 'ot.biodata_id', '=', 'b.id')
            ->leftJoin('keluarga as k', 'b.id', '=', 'k.id_biodata')
            ->where('ot.id', $OrangtuaId)
            ->select([
                'ot.id as ortu_id',
                'b.id as biodata_id',
                'k.no_kk',
            ])
            ->first();

        if (! $base) {
            return ['error' => 'Orang tua tidak ditemukan'];
        }

        $OrangtuaId  = $base->ortu_id;
        $bioId     = $base->biodata_id;
        $noKk      = $base->no_kk;

        // --- 2. Biodata detail ---
        $biodata = DB::table('biodata as b')
            ->leftJoin('berkas as br', function ($j) {
                $j->on('b.id', 'br.biodata_id')
                    ->where('br.jenis_berkas_id', function ($q) {
                        $q->select('id')
                            ->from('jenis_berkas')
                            ->where('nama_jenis_berkas', 'Pas foto')
                            ->limit(1);
                    })
                    ->whereRaw('br.id = (
                    select max(id)
                    from berkas
                    where biodata_id = b.id
                      and jenis_berkas_id = br.jenis_berkas_id
                )');
            })
            ->join('orang_tua_wali as ot', 'ot.id_biodata', '=', 'b.id')
            ->leftJoin('kecamatan as kc', 'b.kecamatan_id', '=', 'kc.id')
            ->leftJoin('kabupaten as kb', 'b.kabupaten_id', '=', 'kb.id')
            ->leftJoin('provinsi as pv', 'b.provinsi_id', '=', 'pv.id')
            ->leftJoin('negara as ng', 'b.negara_id', '=', 'ng.id')
            ->where('b.id', $bioId)
            ->selectRaw(implode(', ', [
                'COALESCE(b.nik, b.no_passport) as identitas',
                'b.nama',
                'b.jenis_kelamin',
                "CONCAT(b.tempat_lahir, ', ', DATE_FORMAT(b.tanggal_lahir, '%e %M %Y')) as ttl",
                "CONCAT(b.anak_keberapa, ' dari ', b.dari_saudara, ' bersaudara') as anak_ke",
                "CONCAT(TIMESTAMPDIFF(YEAR, b.tanggal_lahir, CURDATE()), ' tahun') as umur",
                'b.email',
                'b.no_telepon',
                'b.no_telepon_2',
                'ot.pekerjaan',
                'ot.penghasilan',
                'kc.nama_kecamatan',
                'kb.nama_kabupaten',
                'pv.nama_provinsi',
                'ng.nama_negara',
                "COALESCE(br.file_path,'default.jpg') as foto"
            ]))
            ->first();

        $data['Biodata'] = [
            'nokk'               => $noKk ?? '-',
            'nik_nopassport'     => $biodata->identitas,
            'nama'               => $biodata->nama,
            'jenis_kelamin'      => $biodata->jenis_kelamin,
            'tempat_tanggal_lahir' => $biodata->ttl,
            'anak_ke'            => $biodata->anak_ke,
            'umur'               => $biodata->umur,
            'email' => $biodata->email,
            'telepon_1' => $biodata->no_telepon,
            'telepon_2' => $biodata->no_telepon_2,
            'pekerjaan' => $biodata->pekerjaan,
            'penghasilan' => $biodata->penghasilan,
            'kecamatan'          => $biodata->nama_kecamatan ?? '-',
            'kabupaten'          => $biodata->nama_kabupaten ?? '-',
            'provinsi'           => $biodata->nama_provinsi ?? '-',
            'warganegara'        => $biodata->nama_negara ?? '-',
            'foto_profil'        => URL::to($biodata->foto),
        ];

        // --- 3. Data Keluarga (Orang tua/wali & saudara) ---
        // Orang tua / wali
        $ortu = DB::table('keluarga as k')
            ->where('k.no_kk', $noKk)
            ->join('orang_tua_wali as ow', 'k.id_biodata', '=', 'ow.id_biodata')
            ->join('biodata as bo', 'ow.id_biodata', '=', 'bo.id')
            ->join('hubungan_keluarga as hk', 'ow.id_hubungan_keluarga', '=', 'hk.id')
            ->select([
                'bo.nama',
                'bo.nik',
                DB::raw("hk.nama_status as status"),
                'ow.wali'
            ])
            ->get();

        return $data;
    }
}
