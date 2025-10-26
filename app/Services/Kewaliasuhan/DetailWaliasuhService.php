<?php

namespace App\Services\Kewaliasuhan;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

class DetailWaliasuhService
{
    public function getDetailWaliasuh(string $bioId): array
    {
        // --- 1. Ambil basic wali asuh santri + biodata_id + no_kk sekaligus ---
        $base = DB::table('wali_asuh as ws')
            ->join('santri as s', 'ws.id_santri', '=', 's.id')
            ->join('biodata as b', 's.biodata_id', '=', 'b.id')
            ->leftJoin('keluarga as k', 'b.id', '=', 'k.id_biodata')
            ->where('s.biodata_id', $bioId)
            ->select([
                's.id as santri_id',
                'b.id as biodata_id',
                'k.no_kk',
            ])
            ->first();

        if (! $base) {
            return ['error' => 'Wali asuh tidak ditemukan'];
        }

        $santriId = $base->santri_id;
        $bioId = $base->biodata_id;
        $noKk = $base->no_kk;

        // --- 2. Biodata detail ---
        $biodata = DB::table('biodata as b')
            ->leftJoin('warga_pesantren as wp', function ($j) {
                $j->on('b.id', 'wp.biodata_id')
                    ->where('wp.status', true)
                    ->whereRaw('wp.id = (
                    select max(id)
                    from warga_pesantren
                    where biodata_id = b.id and status = true
                )');
            })
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
            ->leftJoin('kecamatan as kc', 'b.kecamatan_id', '=', 'kc.id')
            ->leftJoin('kabupaten as kb', 'b.kabupaten_id', '=', 'kb.id')
            ->leftJoin('provinsi as pv', 'b.provinsi_id', '=', 'pv.id')
            ->leftJoin('negara as ng', 'b.negara_id', '=', 'ng.id')
            ->where('b.id', $bioId)
            ->selectRaw(implode(', ', [
                'COALESCE(b.nik, b.no_passport) as identitas',
                'wp.niup',
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

        $data['Biodata'] = [
            'nokk' => $noKk ?? '-',
            'nik_nopassport' => $biodata->identitas,
            'niup' => $biodata->niup ?? '-',
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
                DB::raw('hk.nama_status as status'),
                'ow.wali',
            ])
            ->get();

        // Saudara kandung
        $excluded = DB::table('orang_tua_wali')->pluck('id_biodata')->toArray();
        $saudara = DB::table('keluarga as k')
            ->where('k.no_kk', $noKk)
            ->whereNotIn('k.id_biodata', $excluded)
            ->where('k.id_biodata', '!=', $bioId)
            ->join('biodata as bs', 'k.id_biodata', '=', 'bs.id')
            ->select([
                'bs.nama',
                'bs.nik',
                DB::raw("'Saudara Kandung' as status"),
                DB::raw('NULL as wali'),
            ])
            ->get();

        $keluarga = $ortu->merge($saudara);
        if ($keluarga->isNotEmpty()) {
            $data['Keluarga'] = $keluarga->map(fn($i) => [
                'nama' => $i->nama,
                'nik' => $i->nik,
                'status' => $i->status,
                'sebagai_wali' => $i->wali,
            ]);
        }

        // --- 4. Informasi Santri ---
        $santriInfo = DB::table('santri as s')
            ->join('biodata as b', 's.biodata_id', '=', 'b.id')
            ->where('s.biodata_id', $bioId)
            ->select('nis', 'tanggal_masuk', 'tanggal_keluar')
            ->get();

        if ($santriInfo) {
            if ($keluarga->isNotEmpty()) {
                $data['Status_Santri']['Santri'] = $santriInfo->map(fn($s) => [
                    'NIS' => $s->nis,
                    'Tanggal_Mulai' => $s->tanggal_masuk,
                    'Tanggal_Akhir' => $s->tanggal_keluar ?? '-',
                ]);
            }
        }

        // // Kewaliasuhan
        // $kew = DB::table('santri as s')
        //     ->where('s.id', $santriId)
        //     ->leftJoin('wali_asuh as wa', 's.id', '=', 'wa.id_santri')
        //     ->leftJoin('anak_asuh as aa', 's.id', '=', 'aa.id_santri')

        //     // Relasi grup jika santri menjadi wali asuh
        //     ->leftJoin('grup_wali_asuh as gw_wali', 'gw_wali.id', '=', 'wa.id_grup_wali_asuh')

        //     // Relasi ke kewaliasuhan jika santri adalah anak asuh
        //     ->leftJoin('kewaliasuhan as kw_anak', function ($join) {
        //         $join->on('kw_anak.id_anak_asuh', '=', 'aa.id')
        //             ->where('kw_anak.status', true);
        //     })

        //     // Wali asuh yang mengasuh santri (anak asuh)
        //     ->leftJoin('wali_asuh as wa3', 'wa3.id', '=', 'kw_anak.id_wali_asuh')
        //     ->leftJoin('grup_wali_asuh as gw_wali_dari', 'gw_wali_dari.id', '=', 'wa3.id_grup_wali_asuh')

        //     ->select([
        //         // Ambil nama grup dari dua sisi
        //         DB::raw("CASE 
        //             WHEN wa.id IS NOT NULL AND wa.status = true THEN gw_wali.nama_grup 
        //             WHEN wa3.id IS NOT NULL AND wa3.status = true THEN gw_wali_dari.nama_grup 
        //             ELSE '-' 
        //         END as nama_grup"),


        //         // Tentukan peran hanya jika wali_asuh masih aktif
        //         DB::raw("CASE 
        //         WHEN wa.id IS NOT NULL AND wa.status = true 
        //         THEN 'Wali Asuh' 
        //         ELSE 'Anak Asuh' 
        //     END as role"),

        //         // Jika wali asuh: ambil nama anak asuhnya (hanya yang status aktif)
        //         DB::raw("(SELECT GROUP_CONCAT(bio.nama SEPARATOR ', ')
        //             FROM biodata bio
        //             JOIN santri s2 ON bio.id = s2.biodata_id
        //             JOIN anak_asuh aa2 ON aa2.id_santri = s2.id
        //             JOIN kewaliasuhan kw2 ON kw2.id_anak_asuh = aa2.id
        //             WHERE kw2.id_wali_asuh = wa.id AND kw2.status = true
        //         ) as anak_asuh_names"),

        //         // Jika anak asuh: ambil nama wali asuhnya (hanya yang status aktif)
        //         DB::raw("(SELECT GROUP_CONCAT(bio.nama SEPARATOR ', ')
        //             FROM biodata bio
        //             JOIN santri s3 ON bio.id = s3.biodata_id
        //             JOIN wali_asuh wa3x ON wa3x.id_santri = s3.id
        //             JOIN kewaliasuhan kw3x ON kw3x.id_wali_asuh = wa3x.id
        //             WHERE kw3x.id_anak_asuh = aa.id AND kw3x.status = true
        //         ) as wali_asuh_names"),
        //     ])
        //     ->get();

        // if ($kew->isNotEmpty()) {
        //     $data['Status_Santri']['Kewaliasuhan'] = $kew->map(function ($k) {
        //         $result = [
        //             'group_kewaliasuhan' => $k->nama_grup ?? '-',
        //             'sebagai' => $k->role,
        //         ];

        //         if ($k->role === 'Wali Asuh') {
        //             $result['Anak Asuh'] = $k->anak_asuh_names ?? '-';
        //         } else {
        //             $result['Nama Wali Asuh'] = $k->wali_asuh_names ?? '-';
        //         }

        //         return $result;
        //     });
        // }
        // Ambil status kewaliasuhan
        $kew = DB::table('santri as s')
            ->where('s.id', $santriId)

            // LEFT JOIN wali_asuh aktif
            ->leftJoin('wali_asuh as wa', function ($join) {
                $join->on('s.id', '=', 'wa.id_santri')
                    ->where('wa.status', true);
            })

            // LEFT JOIN anak_asuh aktif
            ->leftJoin('anak_asuh as aa', function ($join) {
                $join->on('s.id', '=', 'aa.id_santri')
                    ->where('aa.status', true);
            })

            // Grup jika santri adalah wali asuh
            ->leftJoin('grup_wali_asuh as gw_wali', 'gw_wali.wali_asuh_id', '=', 'wa.id')

            // Grup jika santri adalah anak asuh
            ->leftJoin('grup_wali_asuh as gw_wali_dari', 'gw_wali_dari.id', '=', 'aa.grup_wali_asuh_id')

            ->select([
                // Nama grup
                DB::raw("CASE 
            WHEN wa.id IS NOT NULL THEN gw_wali.nama_grup
            WHEN aa.id IS NOT NULL THEN gw_wali_dari.nama_grup
            ELSE '-' 
        END as nama_grup"),

                // Peran
                DB::raw("CASE 
            WHEN wa.id IS NOT NULL THEN 'Wali Asuh'
            WHEN aa.id IS NOT NULL THEN 'Anak Asuh'
            ELSE '-' 
        END as role"),

                // Jika wali asuh, ambil semua anak asuh dalam grup
                DB::raw("(SELECT GROUP_CONCAT(bio.nama SEPARATOR ', ')
            FROM anak_asuh aa2
            JOIN santri s2 ON aa2.id_santri = s2.id
            JOIN biodata bio ON s2.biodata_id = bio.id
            WHERE aa2.grup_wali_asuh_id = gw_wali.id AND aa2.status = true
        ) as anak_asuh_names"),

                // Jika anak asuh, ambil nama wali asuh dari grup
                DB::raw("(SELECT bio.nama
            FROM grup_wali_asuh gw2
            JOIN wali_asuh wa2 ON wa2.id = gw2.wali_asuh_id
            JOIN santri s3 ON wa2.id_santri = s3.id
            JOIN biodata bio ON s3.biodata_id = bio.id
            WHERE gw2.id = aa.grup_wali_asuh_id AND wa2.status = true
            LIMIT 1
        ) as wali_asuh_names"),
            ])
            ->first(); // gunakan first()

        // Mapping hasil
        $data['Status_Santri']['Kewaliasuhan'] = [];

        if ($kew) {
            $result = [
                'group_kewaliasuhan' => $kew->nama_grup ?? '-',
                'sebagai' => $kew->role,
            ];

            if ($kew->role === 'Wali Asuh') {
                $result['Anak Asuh'] = $kew->anak_asuh_names ?? '-';
            } elseif ($kew->role === 'Anak Asuh') {
                $result['Nama Wali Asuh'] = $kew->wali_asuh_names ?? '-';
            }

            $data['Status_Santri']['Kewaliasuhan'][] = $result;
        }


        // --- 6. Perizinan ---
        $izin = DB::table('perizinan')
            ->where('santri_id', $santriId)
            ->select([
                DB::raw("CONCAT(tanggal_mulai,' s/d ',tanggal_akhir) as tanggal"),
                'keterangan',
                DB::raw("CASE WHEN TIMESTAMPDIFF(SECOND,tanggal_mulai,tanggal_akhir)>=86400
                            THEN CONCAT(FLOOR(TIMESTAMPDIFF(SECOND,tanggal_mulai,tanggal_akhir)/86400),' Hari | Bermalam')
                            ELSE CONCAT(FLOOR(TIMESTAMPDIFF(SECOND,tanggal_mulai,tanggal_akhir)/3600),' Jam')
                     END as lama_waktu"),
                'status',
            ])
            ->get();

        if ($izin->isNotEmpty()) {
            $data['Status_Santri']['Info_Perizinan'] = $izin->map(fn($z) => [
                'tanggal' => $z->tanggal,
                'keterangan' => $z->keterangan,
                'lama_waktu' => $z->lama_waktu,
                'status_kembali' => $z->status,
            ]);
        }

        // Gabungkan domisili aktif dan riwayat
        $domisiliAktif = DB::table('domisili_santri as ds')
            ->join('santri AS s', fn($j) => $j->on('s.id', '=', 'ds.santri_id')->where('s.status', 'aktif'))
            ->join('biodata as b', 's.biodata_id', 'b.id')
            ->join('wilayah as w', 'ds.wilayah_id', '=', 'w.id')
            ->join('blok as bl', 'ds.blok_id', '=', 'bl.id')
            ->join('kamar as km', 'ds.kamar_id', '=', 'km.id')
            ->where('b.id', $bioId)
            ->select([
                'ds.id',
                'km.nama_kamar',
                'bl.nama_blok',
                'w.nama_wilayah',
                'ds.tanggal_masuk',
                'ds.tanggal_keluar',
                'ds.status',
            ]);

        $domisiliRiwayat = DB::table('riwayat_domisili as rd')
            ->join('santri as s', 'rd.santri_id', 's.id')
            ->join('biodata as b', 's.biodata_id', 'b.id')
            ->join('wilayah as w', 'rd.wilayah_id', '=', 'w.id')
            ->join('blok as bl', 'rd.blok_id', '=', 'bl.id')
            ->join('kamar as km', 'rd.kamar_id', '=', 'km.id')
            ->where('b.id', $bioId)
            ->select([
                'rd.id',
                'km.nama_kamar',
                'bl.nama_blok',
                'w.nama_wilayah',
                'rd.tanggal_masuk',
                'rd.tanggal_keluar',
                'rd.status',
            ]);

        $domisiliGabungan = $domisiliAktif->unionAll($domisiliRiwayat)->get();

        // Map dan urutkan berdasarkan tanggal masuk desc
        $data['Domisili'] = collect($domisiliGabungan)
            ->map(fn($d) => [
                'id' => $d->id,
                'wilayah' => $d->nama_wilayah,
                'blok' => $d->nama_blok,
                'kamar' => $d->nama_kamar,
                'tanggal_ditempati' => $d->tanggal_masuk,
                'tanggal_pindah' => $d->tanggal_keluar ?? '-',
                'status' => $d->status,
            ])
            ->sortByDesc('tanggal_masuk')
            ->values();

        // Gabungkan pendidikan aktif dan riwayat
        $pendidikanAktif = DB::table('pendidikan as pd')
            ->join('lembaga as l', 'pd.lembaga_id', '=', 'l.id')
            ->leftJoin('jurusan as j', 'pd.jurusan_id', '=', 'j.id')
            ->leftJoin('kelas as k', 'pd.kelas_id', '=', 'k.id')
            ->leftJoin('rombel as r', 'pd.rombel_id', '=', 'r.id')
            ->where('pd.biodata_id', $bioId)
            ->whereIn('pd.status', ['aktif', 'cuti'])
            ->select([
                'pd.id',
                'pd.no_induk',
                'l.nama_lembaga',
                'j.nama_jurusan',
                'k.nama_kelas',
                'r.nama_rombel',
                'pd.tanggal_masuk',
                'pd.tanggal_keluar',
                'pd.status',
            ]);

        $riwayatPendidikan = DB::table('riwayat_pendidikan as rp')
            ->join('lembaga as l', 'rp.lembaga_id', '=', 'l.id')
            ->leftJoin('jurusan as j', 'rp.jurusan_id', '=', 'j.id')
            ->leftJoin('kelas as k', 'rp.kelas_id', '=', 'k.id')
            ->leftJoin('rombel as r', 'rp.rombel_id', '=', 'r.id')
            ->where('rp.biodata_id', $bioId)
            ->select([
                'rp.id',
                'rp.no_induk',
                'l.nama_lembaga',
                'j.nama_jurusan',
                'k.nama_kelas',
                'r.nama_rombel',
                'rp.tanggal_masuk',
                'rp.tanggal_keluar',
                'rp.status',
            ]);

        $pendidikanGabungan = $pendidikanAktif->unionAll($riwayatPendidikan)->get();

        // Map dan urutkan berdasarkan tanggal masuk desc
        $data['Pendidikan'] = collect($pendidikanGabungan)
            ->map(fn($p) => [
                'id' => $p->id,
                'no_induk' => $p->no_induk,
                'nama_lembaga' => $p->nama_lembaga,
                'nama_jurusan' => $p->nama_jurusan ?? '-',
                'nama_kelas' => $p->nama_kelas ?? '-',
                'nama_rombel' => $p->nama_rombel ?? '-',
                'tahun_masuk' => $p->tanggal_masuk,
                'tahun_lulus' => $p->tanggal_keluar ?? '-',
                'status' => $p->status,
            ])
            ->sortByDesc('tanggal_masuk')
            ->values();

        // Wali asuh
        $ks = DB::table('wali_asuh as ws')
            ->where('ws.id_santri', $santriId)
            ->select(['ws.tanggal_mulai', 'ws.tanggal_berakhir'])
            ->get();

        if ($ks->isNotEmpty()) {
            $data['Wali_Asuh'] = $ks->map(fn($k) => [
                'tanggal_mulai' => $k->tanggal_mulai,
                'tanggal_akhir' => $k->tanggal_berakhir,
            ]);
        }

        // --- 9. Catatan Afektif & Kognitif ---
        $af = DB::table('catatan_afektif as ca')
            ->where('ca.id_santri', $santriId)
            ->latest('ca.created_at')
            ->first();

        if ($af) {
            $data['Catatan_Progress']['Afektif'] = [
                'kebersihan' => $af->kebersihan_nilai ?? '-',
                'tindak_lanjut_kebersihan' => $af->kebersihan_tindak_lanjut ?? '-',
                'kepedulian' => $af->kepedulian_nilai ?? '-',
                'tindak_lanjut_kepedulian' => $af->kepedulian_tindak_lanjut ?? '-',
                'akhlak' => $af->akhlak_nilai ?? '-',
                'tindak_lanjut_akhlak' => $af->akhlak_tindak_lanjut ?? '-',
            ];
        }

        $kg = DB::table('catatan_kognitif as ck')
            ->where('ck.id_santri', $santriId)
            ->latest('ck.created_at')
            ->first();

        if ($kg) {
            $data['Catatan_Progress']['Kognitif'] = [
                'kebahasaan' => $kg->kebahasaan_nilai ?? '-',
                'tindak_lanjut_kebahasaan' => $kg->kebahasaan_tindak_lanjut ?? '-',
                'baca_kitab_kuning' => $kg->baca_kitab_kuning_nilai ?? '-',
                'tindak_lanjut_baca_kitab_kuning' => $kg->baca_kitab_kuning_tindak_lanjut ?? '-',
                'hafalan_tahfidz' => $kg->hafalan_tahfidz_nilai ?? '-',
                'tindak_lanjut_hafalan_tahfidz' => $kg->hafalan_tahfidz_tindak_lanjut ?? '-',
                'furudul_ainiyah' => $kg->furudul_ainiyah_nilai ?? '-',
                'tindak_lanjut_furudul_ainiyah' => $kg->furudul_ainiyah_tindak_lanjut ?? '-',
                'tulis_alquran' => $kg->tulis_alquran_nilai ?? '-',
                'tindak_lanjut_tulis_alquran' => $kg->tindak_lanjut_tulis_alquran ?? '-',
                'baca_alquran' => $kg->baca_alquran_nilai ?? '-',
                'tindak_lanjut_baca_alquran' => $kg->baca_alquran_tindak_lanjut ?? '-',
            ];
        }

        // --- 10. Kunjungan Mahrom ---
        $kun = DB::table('pengunjung_mahrom as pm')
            ->join('santri as s', 's.id', '=', 'pm.santri_id')
            ->join('biodata as b', 'b.id', '=', 'pm.biodata_id')
            ->leftjoin('orang_tua_wali as ow', 'ow.id_biodata', '=', 'b.id')
            ->join('hubungan_keluarga as hk', 'pm.hubungan_id', '=', 'hk.id')
            ->where('pm.santri_id', $santriId)
            ->select([
                'b.nama',
                'hk.nama_status as hubungan',
                'pm.tanggal_kunjungan'
            ])
            ->get();

        if ($kun->isNotEmpty()) {
            $data['Kunjungan_Mahrom'] = $kun->map(fn($k) => [
                'nama_pengunjung' => $k->nama,
                'hubungan' => $k->hubungan,
                'tanggal_kunjungan' => $k->tanggal_kunjungan,
            ]);
        }

        return $data;
    }
}
