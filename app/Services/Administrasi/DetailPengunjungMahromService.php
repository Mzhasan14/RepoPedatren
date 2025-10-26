<?php

namespace App\Services\Administrasi;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

class DetailPengunjungMahromService
{
    public function getDetailPengunjung($id)
    {
        $data = [];

        // --- Ambil No KK (jika ada) ---
        $noKk = DB::table('keluarga as k')
            ->join('biodata as b', 'k.id_biodata', 'b.id')
            ->join('pengunjung_mahrom as pm', 'pm.biodata_id', 'b.id')
            ->where('pm.id', $id)
            ->value('no_kk');

        // --- Biodata Utama ---
        $biodata = DB::table('biodata as b')
            ->join('pengunjung_mahrom as pm', 'pm.biodata_id', 'b.id')
            ->leftJoin('berkas as br', function ($j) {
                $j->on('b.id', 'br.biodata_id')
                    ->whereIn('br.id', function ($sub) {
                        $sub->selectRaw('MAX(id)')
                            ->from('berkas')
                            ->whereColumn('biodata_id', 'b.id')
                            ->where('jenis_berkas_id', function ($q) {
                                $q->select('id')->from('jenis_berkas')->where('nama_jenis_berkas', 'Pas foto');
                            });
                    });
            })
            ->leftJoin('kecamatan as kc', 'b.kecamatan_id', '=', 'kc.id')
            ->leftJoin('kabupaten as kb', 'b.kabupaten_id', '=', 'kb.id')
            ->leftJoin('provinsi as pv', 'b.provinsi_id', '=', 'pv.id')
            ->leftJoin('negara as ng', 'b.negara_id', '=', 'ng.id')
            ->where('pm.id', $id)
            ->selectRaw(implode(', ', [
                'b.id',
                'COALESCE(b.nik, b.no_passport) as identitas',
                'b.nama',
                'b.jenis_kelamin',
                "CONCAT(b.tempat_lahir, ', ', DATE_FORMAT(b.tanggal_lahir, '%e %M %Y')) as ttl",
                "CONCAT(b.anak_keberapa, ' dari ', b.dari_saudara, ' bersaudara') as anak_ke",
                "CONCAT(TIMESTAMPDIFF(YEAR, b.tanggal_lahir, CURDATE()), ' tahun') as umur",
                'kc.nama_kecamatan',
                'kb.nama_kabupaten',
                'pv.nama_provinsi',
                'ng.nama_negara',
                "COALESCE(br.file_path,'default.jpg') as foto",
            ]))
            ->first();

        if ($biodata) {
            $data['Biodata'] = [
                'id' => $biodata->id,
                'nokk' => $noKk ?? '-',
                'nik_nopassport' => $biodata->identitas,
                'nama' => $biodata->nama,
                'jenis_kelamin' => $biodata->jenis_kelamin,
                'tempat_tanggal_lahir' => $biodata->ttl,
                'anak_ke' => $biodata->anak_ke,
                'umur' => $biodata->umur,
                'kecamatan' => $biodata->nama_kecamatan ?? '-',
                'kabupaten' => $biodata->nama_kabupaten ?? '-',
                'provinsi' => $biodata->nama_provinsi ?? '-',
                'warganegara' => $biodata->nama_negara ?? '-',
                'foto_profil' => URL::to($biodata->foto),
            ];
        }

        // --- Keluarga (Ortu & Saudara) ---
        $ortu = collect();
        $saudara = collect();

        if ($noKk) {
            $ortu = DB::table('keluarga as k')
                ->where('k.no_kk', $noKk)
                ->join('orang_tua_wali as ow', 'k.id_biodata', '=', 'ow.id_biodata')
                ->join('biodata as bo', 'ow.id_biodata', '=', 'bo.id')
                ->join('hubungan_keluarga as hk', 'ow.id_hubungan_keluarga', '=', 'hk.id')
                ->select(['bo.nama', 'bo.nik', DB::raw('hk.nama_status as status'), 'ow.wali'])
                ->get();

            $excluded = DB::table('orang_tua_wali')->pluck('id_biodata')->toArray();

            $saudara = DB::table('keluarga as k')
                ->where('k.no_kk', $noKk)
                ->whereNotIn('k.id_biodata', $excluded)
                ->where('k.id_biodata', '!=', $id)
                ->join('biodata as bs', 'k.id_biodata', '=', 'bs.id')
                ->select([
                    'bs.nama',
                    'bs.nik',
                    DB::raw("'Saudara Kandung' as status"),
                    DB::raw('NULL as wali'),
                ])
                ->get();
        }

        $keluarga = $ortu->merge($saudara);
        $data['Keluarga'] = $keluarga->map(fn ($i) => [
            'nama' => $i->nama,
            'nik' => $i->nik,
            'status' => $i->status,
            'wali' => $i->wali,
        ])->toArray();

        // --- Kunjungan Mahrom ---
        $kun = DB::table('pengunjung_mahrom as pm')
            ->join('santri as s', 'pm.santri_id', '=', 's.id')
            ->join('biodata as b', 's.biodata_id', '=', 'b.id')
            ->join('hubungan_keluarga as hk', 'pm.hubungan_id', '=', 'hk.id')
            ->join('biodata as bp', 'pm.biodata_id', '=', 'bp.id')
            ->where('pm.id', $id)
            ->select(['bp.nama', 'hk.nama_status', 'pm.tanggal_kunjungan'])
            ->get();

        $data['Kunjungan_Mahrom'] = $kun->isNotEmpty()
            ? $kun->map(function ($k) {
                $statusMap = [
                    'ayah kandung' => 'anak kandung',
                    'ibu kandung' => 'anak kandung',
                    'ayah sambung' => 'anak tiri',
                    'ibu sambung' => 'anak tiri',
                    'kakek kandung' => 'cucu kandung',
                    'nenek kandung' => 'cucu kandung',
                    'wali' => 'anak asuh',
                ];

                $normalizedStatus = strtolower(trim($k->nama_status));
                $status = $statusMap[$normalizedStatus] ?? 'saudara/sepupu';

                return [
                    'nama_pengunjung' => $k->nama,
                    'status' => $status,
                    'tanggal_kunjungan' => $k->tanggal_kunjungan,
                ];
            })
            : [];

        return $data;
    }
}
