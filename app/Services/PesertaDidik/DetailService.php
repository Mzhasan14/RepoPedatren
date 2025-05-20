<?php

namespace App\Services\PesertaDidik;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

class DetailService
{
    public function getDetail(string $biodataId): array
    {
        $data = [];

        // --- Ambil No KK (jika ada) ---
        $noKk = DB::table('keluarga')
            ->where('id_biodata', $biodataId)
            ->value('no_kk');

        // --- Biodata Utama ---
        $biodata = DB::table('biodata as b')
            ->leftJoin('warga_pesantren as wp', function ($j) {
                $j->on('b.id', 'wp.biodata_id')
                    ->where('wp.status', true);
            })
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
            ->where('b.id', $biodataId)
            ->selectRaw(implode(', ', [
                'b.id',
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
                "COALESCE(br.file_path,'default.jpg') as foto"
            ]))
            ->first();

        if ($biodata) {
            $data['Biodata'] = [
                'id'                   => $biodata->id,
                'nokk'                 => $noKk ?? '-',
                'nik_nopassport'       => $biodata->identitas,
                'niup'                 => $biodata->niup ?? '-',
                'nama'                 => $biodata->nama,
                'jenis_kelamin'        => $biodata->jenis_kelamin,
                'tempat_tanggal_lahir' => $biodata->ttl,
                'anak_ke'              => $biodata->anak_ke,
                'umur'                 => $biodata->umur,
                'kecamatan'            => $biodata->nama_kecamatan ?? '-',
                'kabupaten'            => $biodata->nama_kabupaten ?? '-',
                'provinsi'             => $biodata->nama_provinsi ?? '-',
                'warganegara'          => $biodata->nama_negara ?? '-',
                'foto_profil'          => URL::to($biodata->foto),
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
                ->select(['bo.nama', 'bo.nik', DB::raw("hk.nama_status as status"), 'ow.wali'])
                ->get();

            $excluded = DB::table('orang_tua_wali')->pluck('id_biodata')->toArray();

            $saudara = DB::table('keluarga as k')
                ->where('k.no_kk', $noKk)
                ->whereNotIn('k.id_biodata', $excluded)
                ->where('k.id_biodata', '!=', $biodataId)
                ->join('biodata as bs', 'k.id_biodata', '=', 'bs.id')
                ->select([
                    'bs.nama',
                    'bs.nik',
                    DB::raw("'Saudara Kandung' as status"),
                    DB::raw("NULL as wali")
                ])
                ->get();
        }

        $keluarga = $ortu->merge($saudara);
        $data['Keluarga'] = $keluarga->map(fn($i) => [
            'nama'   => $i->nama,
            'nik'    => $i->nik,
            'status' => $i->status,
            'wali'   => $i->wali,
        ])->toArray();

        // --- Ambil ID Santri (jika ada) ---
        $santriId = DB::table('santri')->where('biodata_id', $biodataId)->value('id');

        // --- Status Santri: Santri ---
        $santriInfo = collect();
        if ($santriId) {
            $santriInfo = DB::table('santri')
                ->where('biodata_id', $biodataId)
                ->select('nis', 'tanggal_masuk', 'tanggal_keluar')
                ->get();
        }

        $data['Status_Santri']['Santri'] = $santriInfo->map(fn($s) => [
            'NIS'           => $s->nis,
            'Tanggal_Mulai' => $s->tanggal_masuk,
            'Tanggal_Akhir' => $s->tanggal_keluar ?? '-',
        ])->toArray();

        // --- Kewaliasuhan (jika ada) ---
        $kew = null;
        if ($santriId) {
            $kew = DB::table('kewaliasuhan as kw')
                ->leftJoin('wali_asuh as wa', 'kw.id_wali_asuh', '=', 'wa.id')
                ->leftJoin('anak_asuh as aa', 'kw.id_anak_asuh', '=', 'aa.id')
                ->leftJoin('biodata as bio_wali', 'bio_wali.id', '=', 'kw.id_wali_asuh')
                ->leftJoin('biodata as bio_anak', 'bio_anak.id', '=', 'kw.id_anak_asuh')
                ->where(function ($q) use ($santriId) {
                    $q->where('wa.id_santri', $santriId)
                        ->orWhere('aa.id_santri', $santriId);
                })
                ->where('kw.status', true)
                ->select(
                    'kw.tanggal_mulai',
                    'kw.tanggal_berakhir',
                    'kw.status',
                    'bio_wali.nama as nama_wali',
                    'bio_anak.nama as nama_anak',
                    DB::raw("
                    CASE 
                        WHEN kw.id_anak_asuh IS NOT NULL THEN 'Anak Asuh'
                        WHEN kw.id_wali_asuh IS NOT NULL THEN 'Wali Asuh'
                        ELSE 'Tidak Diketahui'
                    END as role
                ")
                )
                ->first();
        }

        $data['Status_Santri']['Kewaliasuhan'] = $kew ? [[
            'group'   => '-',
            'sebagai' => $kew->role,
            $kew->role === 'Anak Asuh' ? 'Nama Wali Asuh' : 'Nama Anak Asuh' =>
            $kew->role === 'Anak Asuh' ? ($kew->nama_wali ?? '-') : ($kew->nama_anak ?? '-'),
            'tanggal_mulai' => $kew->tanggal_mulai,
            'tanggal_berakhir' => $kew->tanggal_berakhir ?? '-',
            'status' => $kew->status ? 'Aktif' : 'Tidak Aktif',
        ]] : [];

        // --- Perizinan ---
        $izin = DB::table('perizinan as pr')
            ->join('santri as s', 'pr.santri_id', '=', 's.id')
            ->join('biodata as b', 's.biodata_id', '=', 'b.id')
            ->where('b.id', $biodataId)
            ->select([
                DB::raw("CONCAT(tanggal_mulai,' s/d ',tanggal_akhir) as tanggal"),
                'keterangan',
                DB::raw("CASE WHEN TIMESTAMPDIFF(SECOND,tanggal_mulai,tanggal_akhir)>=86400
                            THEN CONCAT(FLOOR(TIMESTAMPDIFF(SECOND,tanggal_mulai,tanggal_akhir)/86400),' Hari | Bermalam')
                            ELSE CONCAT(FLOOR(TIMESTAMPDIFF(SECOND,tanggal_mulai,tanggal_akhir)/3600),' Jam')
                     END as lama_waktu"),
                'pr.status'
            ])
            ->get();

        $data['Status_Santri']['Info_Perizinan'] = $izin->isNotEmpty()
            ? $izin->map(fn($z) => [
                'tanggal'        => $z->tanggal,
                'keterangan'     => $z->keterangan,
                'lama_waktu'     => $z->lama_waktu,
                'status' => $z->status,
            ])
            : [];

        // --- Domisili ---
        $dom = DB::table('riwayat_domisili as rd')
            ->where('b.id', $biodataId)
            ->join('santri as s', 'rd.santri_id', '=', 's.id')
            ->join('biodata as b', 's.biodata_id', '=', 'b.id')
            ->join('wilayah as w', 'rd.wilayah_id', '=', 'w.id')
            ->join('blok as bl', 'rd.blok_id', '=', 'bl.id')
            ->join('kamar as km', 'rd.kamar_id', '=', 'km.id')
            ->select([
                'rd.id',
                'km.nama_kamar',
                'bl.nama_blok',
                'w.nama_wilayah',
                'rd.tanggal_masuk',
                'rd.tanggal_keluar',
                'rd.status'
            ])
            ->get();

        $data['Domisili'] = $dom->isNotEmpty()
            ? $dom->map(fn($d) => [
                'id'           => $d->id,
                'wilayah'           => $d->nama_wilayah,
                'blok'              => $d->nama_blok,
                'kamar'             => $d->nama_kamar,
                'tanggal_ditempati' => $d->tanggal_masuk,
                'tanggal_pindah'    => $d->tanggal_keluar ?? '-',
                'status'    => $d->status,
            ])
            : [];

        // --- Pendidikan ---
        $pend = DB::table('riwayat_pendidikan as rp')
            ->join('santri as s', 'rp.santri_id', '=', 's.id')
            ->join('biodata as b', 's.biodata_id', '=', 'b.id')
            ->join('lembaga as l', 'rp.lembaga_id', '=', 'l.id')
            ->leftJoin('jurusan as j', 'rp.jurusan_id', '=', 'j.id')
            ->leftJoin('kelas as k', 'rp.kelas_id', '=', 'k.id')
            ->leftJoin('rombel as r', 'rp.rombel_id', '=', 'r.id')
            ->where('b.id', $biodataId)
            ->select([
                'rp.id',
                'rp.no_induk',
                'l.nama_lembaga',
                'j.nama_jurusan',
                'k.nama_kelas',
                'r.nama_rombel',
                'rp.tanggal_masuk',
                'rp.tanggal_keluar'
            ])
            ->get();

        $data['Pendidikan'] = $pend->isNotEmpty()
            ? $pend->map(fn($p) => [
                'id'     => $p->id,
                'no_induk'     => $p->no_induk,
                'nama_lembaga' => $p->nama_lembaga,
                'nama_jurusan' => $p->nama_jurusan,
                'nama_kelas'   => $p->nama_kelas ?? '-',
                'nama_rombel'  => $p->nama_rombel ?? '-',
                'tahun_masuk'  => $p->tanggal_masuk,
                'tahun_lulus'  => $p->tanggal_keluar ?? '-',
            ])
            : [];

        // --- Catatan Afektif ---
        $af = DB::table('catatan_afektif as ca')
            ->join('santri as s', 'ca.id_santri', '=', 's.id')
            ->join('biodata as b', 's.biodata_id', '=', 'b.id')
            ->where('b.id', $biodataId)
            ->latest('ca.created_at')
            ->first();

        $data['Catatan_Progress']['Afektif'] = $af
            ? [
                'kebersihan'               => $af->kebersihan_nilai ?? '-',
                'tindak_lanjut_kebersihan' => $af->kebersihan_tindak_lanjut ?? '-',
                'kepedulian'               => $af->kepedulian_nilai ?? '-',
                'tindak_lanjut_kepedulian' => $af->kepedulian_tindak_lanjut ?? '-',
                'akhlak'                   => $af->akhlak_nilai ?? '-',
                'tindak_lanjut_akhlak'     => $af->akhlak_tindak_lanjut ?? '-',
            ]
            : [];

        // --- Catatan Kognitif ---
        $kg = DB::table('catatan_kognitif as ck')
            ->where('b.id', $biodataId)
            ->join('santri as s', 'ck.id_santri', '=', 's.id')
            ->join('biodata as b', 's.biodata_id', '=', 'b.id')
            ->latest('ck.created_at')
            ->first();

        $data['Catatan_Progress']['Kognitif'] = $kg
            ? [
                'kebahasaan'                      => $kg->kebahasaan_nilai ?? '-',
                'tindak_lanjut_kebahasaan'        => $kg->kebahasaan_tindak_lanjut ?? '-',
                'baca_kitab_kuning'               => $kg->baca_kitab_kuning_nilai ?? '-',
                'tindak_lanjut_baca_kitab_kuning' => $kg->baca_kitab_kuning_tindak_lanjut ?? '-',
                'hafalan_tahfidz'                 => $kg->hafalan_tahfidz_nilai ?? '-',
                'tindak_lanjut_hafalan_tahfidz'   => $kg->hafalan_tahfidz_tindak_lanjut ?? '-',
                'furudul_ainiyah'                 => $kg->furudul_ainiyah_nilai ?? '-',
                'tindak_lanjut_furudul_ainiyah'   => $kg->furudul_ainiyah_tindak_lanjut ?? '-',
                'tulis_alquran'                   => $kg->tulis_alquran_nilai ?? '-',
                'tindak_lanjut_tulis_alquran'     => $kg->tindak_lanjut_tulis_alquran ?? '-',
                'baca_alquran'                    => $kg->baca_alquran_nilai ?? '-',
                'tindak_lanjut_baca_alquran'      => $kg->baca_alquran_tindak_lanjut ?? '-',
            ]
            : [];

        // --- Kunjungan Mahrom ---
        $kun = DB::table('pengunjung_mahrom as pm')
            ->join('biodata as bp', 'pm.biodata_id', '=', 'bp.id')
            ->join('santri as s', 'pm.santri_id', '=', 's.id')
            ->join('biodata as b', 's.biodata_id', '=', 'b.id')
            ->join('hubungan_keluarga as hk', 'pm.hubungan_id', '=', 'hk.id')
            ->where('b.id', $biodataId)
            ->select(['bp.nama', 'hk.nama_status', 'pm.tanggal_kunjungan'])
            ->get();

        $data['Kunjungan_Mahrom'] = $kun->isNotEmpty()
            ? $kun->map(fn($k) => [
                'nama_pengunjung'    => $k->nama,
                'status'    => $k->nama_status,
                'tanggal_kunjungan' => $k->tanggal_kunjungan,
            ])
            : [];

        // --- Khadam ---
        $kh = DB::table('khadam as kh')
            ->where('kh.biodata_id', $biodataId)
            ->select(['kh.keterangan', 'kh.tanggal_mulai', 'kh.tanggal_akhir'])
            ->get();

        $data['Khadam'] = $kh->isNotEmpty()
            ? $kh->map(fn($kh) => [
                'keterangan'    => $kh->keterangan,
                'tanggal_mulai' => $kh->tanggal_mulai,
                'tanggal_akhir' => $kh->tanggal_akhir ?? "-",
            ])
            : [];

                // --- Riwayat Karyawan ---
        $karyawan = DB::table('karyawan')
            ->join('pegawai', 'pegawai.id', '=', 'karyawan.pegawai_id')
            ->join('biodata', 'pegawai.biodata_id', '=', 'biodata.id')
            ->leftJoin('lembaga', 'karyawan.lembaga_id', '=', 'lembaga.id')
            ->where('pegawai.biodata_id', $biodataId)
            ->select(
                'lembaga.nama_lembaga',
                'karyawan.keterangan_jabatan',
                'karyawan.tanggal_mulai',
                'karyawan.tanggal_selesai'
            )
            ->orderBy('karyawan.tanggal_mulai', 'asc')
            ->get();

        $data['Karyawan'] = $karyawan->isNotEmpty()
            ? $karyawan->map(fn($item) => [
                'lembaga'            => $item->nama_lembaga ?? '-',
                'keterangan_jabatan' => $item->keterangan_jabatan ?? '-',
                'tanggal_mulai'      => $item->tanggal_mulai ? date('d-m-Y', strtotime($item->tanggal_mulai)) : '-',
                'tanggal_selesai'    => $item->tanggal_selesai ? date('d-m-Y', strtotime($item->tanggal_selesai)) : '-',
            ])
            : [];


        // --- Ambil data pengajar dan riwayat materi ---
        $pengajar = DB::table('pengajar')
            ->join('pegawai', 'pegawai.id', '=', 'pengajar.pegawai_id')
            ->leftJoin('lembaga', 'lembaga.id', '=', 'pengajar.lembaga_id')
            ->join('biodata', 'pegawai.biodata_id', '=', 'biodata.id')
            ->leftJoin('golongan', 'golongan.id', '=', 'pengajar.golongan_id')
            ->leftJoin('kategori_golongan', 'kategori_golongan.id', '=', 'golongan.kategori_golongan_id')
            ->leftJoin('materi_ajar', 'materi_ajar.pengajar_id', '=', 'pengajar.id')
            ->where('pegawai.biodata_id', $biodataId)
            ->select(
                'lembaga.nama_lembaga',
                'pengajar.jabatan as pekerjaan_kontrak',  // gunakan snake_case agar konsisten
                'kategori_golongan.nama_kategori_golongan',
                'golongan.nama_golongan',
                'pengajar.tahun_masuk',
                'pengajar.tahun_akhir',
                'materi_ajar.tahun_masuk as tanggal_masuk',
                'materi_ajar.tahun_akhir as tanggal_akhir',
                DB::raw("DATE_FORMAT(pengajar.tahun_masuk, '%e %M %Y') AS sejak"),
                DB::raw("
                    CONCAT(
                        TIMESTAMPDIFF(YEAR, pengajar.tahun_masuk, IFNULL(pengajar.tahun_akhir, NOW())),
                        ' Tahun'
                    ) AS masa_kerja
                "),
                DB::raw("
                    CONCAT(
                        GROUP_CONCAT(DISTINCT materi_ajar.nama_materi SEPARATOR ', '),
                        ' (',
                        FLOOR(SUM(materi_ajar.jumlah_menit) / 60), ' jam ',
                        MOD(SUM(materi_ajar.jumlah_menit), 60), ' menit setiap pertemuan)'
                    ) AS daftar_materi_dengan_waktu
                "),
                DB::raw('COUNT(DISTINCT materi_ajar.id) as total_materi')
            )
            ->groupBy(
                'lembaga.nama_lembaga',
                'pengajar.jabatan',
                'kategori_golongan.nama_kategori_golongan',
                'golongan.nama_golongan',
                'pengajar.tahun_masuk',
                'pengajar.tahun_akhir',
                'materi_ajar.tahun_masuk',
                'materi_ajar.tahun_akhir',
            )
            ->get();

        if ($pengajar->isNotEmpty()) {
            $pengajarMap = $pengajar->map(function ($item) {
                return [
                    'pangkalan' => [
                        'lembaga'           => $item->nama_lembaga ?? '-',
                        'pekerjaan_kontrak' => $item->pekerjaan_kontrak ?? '-',
                        'kategori_golongan' => $item->nama_kategori_golongan ?? '-',
                        'nama_golongan'     => $item->nama_golongan ?? '-',
                        'sejak'             => $item->sejak ?? '-',
                        'masa_kerja'        => $item->masa_kerja ?? '-',
                    ],
                    'materi' => [
                        'lembaga'           => $item->nama_lembaga ?? '-',
                        'daftar_materi'     => $item->daftar_materi_dengan_waktu ?? '-',
                        'tanggal_mulai'     => $item->tanggal_masuk ?? null,
                        'tanggal_akhir'     => $item->tanggal_akhir ?? null,
                    ]
                ];
            });

            $data['Pengajar'] = [
                'Pangkalan'    => $pengajarMap->pluck('pangkalan')->unique()->values()->all(),
                'Materi_Ajar'  => $pengajarMap->pluck('materi')->filter()->values()->all(),
            ];
        } else {
            $data['Pengajar'] = [
                'Pangkalan'    => [],
                'Materi_Ajar'  => [],
            ];
        }


        // --- Ambil data pengurus dan riwayat jabatan ---
        $pengurus = DB::table('pengurus')
            ->join('pegawai', 'pegawai.id', '=', 'pengurus.pegawai_id')
            ->join('biodata', 'pegawai.biodata_id', '=', 'biodata.id')
            ->where('pegawai.biodata_id', $biodataId)
            ->select(
                'pengurus.keterangan_jabatan',
                'pengurus.tanggal_mulai',
                'pengurus.tanggal_akhir',
            )
            ->orderBy('pengurus.tanggal_mulai', 'asc')
            ->get();
            $data['Pengurus'] = $pengurus->isNotEmpty()
            ? $pengurus->map(fn($item) => [
                'keterangan_jabatan' => $item->keterangan_jabatan ?? '-',
                'tanggal_mulai'       => $item->tanggal_mulai ?? '-',
                'tanggal_akhir'       => $item->tanggal_akhir ?? '-',
            ])
            : [];
            // ambil data wali kelas dan riwayatnya
            $walikelas = DB::table('wali_kelas') 
                ->join('pegawai', 'pegawai.id', '=', 'wali_kelas.pegawai_id')
                ->join('biodata', 'pegawai.biodata_id', '=', 'biodata.id')
                ->leftJoin('rombel as r', 'r.id', '=', 'wali_kelas.rombel_id')
                ->leftJoin('kelas as k', 'k.id', '=', 'wali_kelas.kelas_id')
                ->leftJoin('jurusan as j', 'j.id', '=', 'wali_kelas.jurusan_id')
                ->leftJoin('lembaga as l', 'l.id', '=', 'wali_kelas.lembaga_id')
                ->where('pegawai.biodata_id', $biodataId)
                ->select([
                    'l.nama_lembaga',
                    'j.nama_jurusan',
                    'k.nama_kelas',
                    'r.nama_rombel',
                    'wali_kelas.periode_awal',
                    'wali_kelas.periode_akhir',
                ])
                ->orderBy('wali_kelas.periode_awal', 'desc') // opsional, jika ingin data terbaru dulu
                ->get();

            $data['Wali_Kelas'] = $walikelas->isNotEmpty()
                ? $walikelas->map(fn($item) => [
                    'Lembaga'       => $item->nama_lembaga ?? '-',
                    'Jurusan'       => $item->nama_jurusan ?? '-',
                    'Kelas'         => $item->nama_kelas ?? '-',
                    'Rombel'        => $item->nama_rombel ?? '-',
                    'Periode_awal'  => $item->periode_awal ?? '-',
                    'Periode_akhir' => $item->periode_akhir ?? '-',
                ])
                : [];

        if (!isset($data['Biodata'])) {
            throw new \Exception("ID biodata tidak valid atau tidak memiliki data terkait");
        }

        return $data;
    }
}
