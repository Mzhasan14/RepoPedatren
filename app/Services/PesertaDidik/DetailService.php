<?php

namespace App\Services\PesertaDidik;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Database\Eloquent\ModelNotFoundException;

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
                // PERBAIKAN 1: Gunakan 'AND' dalam 'on' agar tidak mengubah LEFT JOIN menjadi INNER JOIN
                $j->on('b.id', '=', 'wp.biodata_id')
                    ->where('wp.status', '=', true);
            })
            ->leftJoin('berkas as br', function ($j) {
                // PERBAIKAN 1: Logika yang sama untuk join berkas
                $j->on('b.id', '=', 'br.biodata_id')
                    ->whereIn('br.id', function ($sub) {
                        $sub->selectRaw('MAX(id)')
                            ->from('berkas as sub_br')
                            ->whereColumn('sub_br.biodata_id', 'b.id')
                            ->where('sub_br.jenis_berkas_id', function ($q) {
                                $q->select('id')->from('jenis_berkas')->where('nama_jenis_berkas', 'Pas foto');
                            });
                    });
            })
            ->leftJoin('kecamatan as kc', 'b.kecamatan_id', '=', 'kc.id')
            ->leftJoin('kabupaten as kb', 'b.kabupaten_id', '=', 'kb.id')
            ->leftJoin('provinsi as pv', 'b.provinsi_id', '=', 'pv.id')
            ->leftJoin('negara as ng', 'b.negara_id', '=', 'ng.id')
            ->where('b.id', $biodataId)
            ->select([
                'b.id',
                DB::raw('COALESCE(b.nik, b.no_passport) as identitas'),
                'wp.niup',
                'b.nama',
                'b.jenis_kelamin',
                'b.tempat_lahir',
                'b.tanggal_lahir',
                DB::raw("CONCAT(b.anak_keberapa, ' dari ', b.dari_saudara, ' bersaudara') as anak_ke"),
                DB::raw("CASE WHEN b.tanggal_lahir IS NULL THEN NULL ELSE TIMESTAMPDIFF(YEAR, b.tanggal_lahir, CURDATE()) END as umur"),
                // DB::raw("TIMESTAMPDIFF(YEAR, b.tanggal_lahir, CURDATE()) as umur"),
                'kc.nama_kecamatan',
                'kb.nama_kabupaten',
                'pv.nama_provinsi',
                'ng.nama_negara',
                DB::raw("COALESCE(br.file_path, 'default.jpg') as foto"),
            ])
            ->first();

        // PERBAIKAN 2: Fail-Fast. Jika biodata utama tidak ada, hentikan eksekusi.
        if (!$biodata) {
            // Opsi 1: Lemparkan Exception (direkomendasikan untuk API)
            throw new ModelNotFoundException("Data biodata dengan ID '{$biodataId}' tidak ditemukan.");

            // Opsi 2: Kembalikan array kosong
            // return [];
        }

        $data = [];

        // --- Format tanggal lahir & siapkan data utama ---
        Carbon::setLocale('id');

        // ✅ Jangan parse tanggal jika null, biarkan null
        if ($biodata->tanggal_lahir) {
            $tanggal_lahir = Carbon::parse($biodata->tanggal_lahir)->translatedFormat('d F Y');
            $ttl = "{$biodata->tempat_lahir}, {$tanggal_lahir}";
        } else {
            $tanggal_lahir = null;
            $ttl = $biodata->tempat_lahir; 
        }

        // Ambil No KK (dipindah ke sini agar hanya dieksekusi jika biodata ada)
        $noKk = DB::table('keluarga')
            ->where('id_biodata', $biodata->id)
            ->value('no_kk');

        $data['Biodata'] = [
            'id' => $biodata->id,
            'nokk' => $noKk ?? '-',
            'nik_nopassport' => $biodata->identitas,
            'niup' => $biodata->niup ?? '-',
            'nama' => $biodata->nama,
            'jenis_kelamin' => $biodata->jenis_kelamin,
            'tempat_tanggal_lahir' => $ttl,
            'anak_ke' => $biodata->anak_ke,
            'umur' => $biodata->umur . ' tahun',
            'kecamatan' => $biodata->nama_kecamatan ?? '-',
            'kabupaten' => $biodata->nama_kabupaten ?? '-',
            'provinsi' => $biodata->nama_provinsi ?? '-',
            'warganegara' => $biodata->nama_negara ?? '-',
            'foto_profil' => URL::to($biodata->foto),
        ];



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
                ->where('k.id_biodata', '!=', $biodataId)
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
        $data['Keluarga'] = $keluarga->map(fn($i) => [
            'nama' => $i->nama,
            'nik' => $i->nik,
            'status' => $i->status,
            'wali' => $i->wali,
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
            'NIS' => $s->nis,
            'Tanggal_Mulai' => $s->tanggal_masuk,
            'Tanggal_Akhir' => $s->tanggal_keluar ?? '-',
        ])->toArray();

        $kew = DB::table('santri as s')
            ->where('s.id', $santriId)
            ->leftJoin('wali_asuh as wa', function ($join) {
                $join->on('s.id', '=', 'wa.id_santri')->where('wa.status', true);
            })
            ->leftJoin('anak_asuh as aa', function ($join) {
                $join->on('s.id', '=', 'aa.id_santri')->where('aa.status', true);
            })
            ->leftJoin('grup_wali_asuh as gw_wali', 'gw_wali.wali_asuh_id', '=', 'wa.id')
            ->leftJoin('grup_wali_asuh as gw_wali_dari', 'gw_wali_dari.id', '=', 'aa.grup_wali_asuh_id')
            ->select([
                DB::raw("CASE WHEN wa.id IS NOT NULL THEN gw_wali.nama_grup WHEN aa.id IS NOT NULL THEN gw_wali_dari.nama_grup ELSE '-' END as nama_grup"),
                DB::raw("CASE WHEN wa.id IS NOT NULL THEN 'Wali Asuh' WHEN aa.id IS NOT NULL THEN 'Anak Asuh' ELSE '-' END as role"),
                DB::raw("(SELECT GROUP_CONCAT(bio.nama SEPARATOR ', ')
            FROM anak_asuh aa2
            JOIN santri s2 ON aa2.id_santri = s2.id
            JOIN biodata bio ON s2.biodata_id = bio.id
            WHERE aa2.grup_wali_asuh_id = gw_wali.id AND aa2.status = true) as anak_asuh_names"),
                DB::raw("(SELECT bio.nama
            FROM grup_wali_asuh gw2
            JOIN wali_asuh wa2 ON wa2.id = gw2.wali_asuh_id
            JOIN santri s3 ON wa2.id_santri = s3.id
            JOIN biodata bio ON s3.biodata_id = bio.id
            WHERE gw2.id = aa.grup_wali_asuh_id AND wa2.status = true
            LIMIT 1) as wali_asuh_names"),
            ])
            ->first();

        $data['Status_Santri']['Kewaliasuhan'] = [];

        if ($kew && $kew->role !== '-') {
            $data['Status_Santri']['Kewaliasuhan'][] = [
                'group_kewaliasuhan' => $kew->nama_grup ?? '-',
                'sebagai' => $kew->role,
                'Anak Asuh' => $kew->role === 'Wali Asuh' ? ($kew->anak_asuh_names ?? '-') : null,
                'Nama Wali Asuh' => $kew->role === 'Anak Asuh' ? ($kew->wali_asuh_names ?? '-') : null,
            ];
        }


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
                'pr.status',
            ])
            ->get();

        $data['Status_Santri']['Info_Perizinan'] = $izin->isNotEmpty()
            ? $izin->map(fn($z) => [
                'tanggal' => $z->tanggal,
                'keterangan' => $z->keterangan,
                'lama_waktu' => $z->lama_waktu,
                'status' => $z->status,
            ])
            : [];

        // Data aktif
        $domisiliAktif = DB::table('domisili_santri as ds')
            ->join('santri AS s', fn($j) => $j->on('s.id', '=', 'ds.santri_id')->where('s.status', 'aktif'))
            ->join('biodata as b', 's.biodata_id', 'b.id')
            ->join('wilayah as w', 'ds.wilayah_id', '=', 'w.id')
            ->leftJoin('blok as bl', 'ds.blok_id', '=', 'bl.id')
            ->leftjoin('kamar as km', 'ds.kamar_id', '=', 'km.id')
            ->where('b.id', $biodataId)
            ->where('ds.status', 'aktif')
            ->select([
                'ds.id',
                'km.nama_kamar',
                'bl.nama_blok',
                'w.nama_wilayah',
                'ds.tanggal_masuk',
                'ds.tanggal_keluar',
                'ds.status',
            ])
            ->first();

        // Data riwayat, urutkan terbaru dulu
        $domisiliRiwayat = DB::table('riwayat_domisili as rd')
            ->join('santri as s', 'rd.santri_id', 's.id')
            ->join('biodata as b', 's.biodata_id', 'b.id')
            ->join('wilayah as w', 'rd.wilayah_id', '=', 'w.id')
            ->leftjoin('blok as bl', 'rd.blok_id', '=', 'bl.id')
            ->leftjoin('kamar as km', 'rd.kamar_id', '=', 'km.id')
            ->where('b.id', $biodataId)
            ->select([
                'rd.id',
                'km.nama_kamar',
                'bl.nama_blok',
                'w.nama_wilayah',
                'rd.tanggal_masuk',
                'rd.tanggal_keluar',
                'rd.status',
            ])
            ->orderByDesc('rd.tanggal_masuk')
            ->get();

        // Gabungkan aktif + riwayat
        $data['Domisili'] = collect();

        if ($domisiliAktif) {
            $data['Domisili']->push([
                'id' => $domisiliAktif->id,
                'wilayah' => $domisiliAktif->nama_wilayah,
                'blok' => $domisiliAktif->nama_blok,
                'kamar' => $domisiliAktif->nama_kamar,
                'tanggal_ditempati' => $domisiliAktif->tanggal_masuk,
                'tanggal_pindah' => $domisiliAktif->tanggal_keluar ?? '-',
                'status' => $domisiliAktif->status,
            ]);
        }

        foreach ($domisiliRiwayat as $d) {
            $data['Domisili']->push([
                'id' => $d->id,
                'wilayah' => $d->nama_wilayah,
                'blok' => $d->nama_blok,
                'kamar' => $d->nama_kamar,
                'tanggal_ditempati' => $d->tanggal_masuk,
                'tanggal_pindah' => $d->tanggal_keluar ?? '-',
                'status' => $d->status,
            ]);
        }

        // Gabungkan pendidikan aktif dan riwayat
        // Pendidikan aktif/cuti (hanya satu)
        $pendidikanAktif = DB::table('pendidikan as pd')
            ->join('lembaga as l', 'pd.lembaga_id', '=', 'l.id')
            ->leftJoin('jurusan as j', 'pd.jurusan_id', '=', 'j.id')
            ->leftJoin('kelas as k', 'pd.kelas_id', '=', 'k.id')
            ->leftJoin('rombel as r', 'pd.rombel_id', '=', 'r.id')
            ->where('pd.biodata_id', $biodataId)
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
            ])
            ->orderByDesc('pd.tanggal_masuk')
            ->first();

        $riwayatPendidikan = DB::table('riwayat_pendidikan as rp')
            ->join('lembaga as l', 'rp.lembaga_id', '=', 'l.id')
            ->leftJoin('jurusan as j', 'rp.jurusan_id', '=', 'j.id')
            ->leftJoin('kelas as k', 'rp.kelas_id', '=', 'k.id')
            ->leftJoin('rombel as r', 'rp.rombel_id', '=', 'r.id')
            ->where('rp.biodata_id', $biodataId)
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
            ])
            ->orderByDesc('rp.tanggal_masuk')
            ->get();

        $data['Pendidikan'] = collect();

        if ($pendidikanAktif) {
            $data['Pendidikan']->push([
                'id' => $pendidikanAktif->id,
                'no_induk' => $pendidikanAktif->no_induk,
                'nama_lembaga' => $pendidikanAktif->nama_lembaga,
                'nama_jurusan' => $pendidikanAktif->nama_jurusan ?? '-',
                'nama_kelas' => $pendidikanAktif->nama_kelas ?? '-',
                'nama_rombel' => $pendidikanAktif->nama_rombel ?? '-',
                'tahun_masuk' => $pendidikanAktif->tanggal_masuk,
                'tahun_lulus' => $pendidikanAktif->tanggal_keluar ?? '-',
                'status' => $pendidikanAktif->status,
            ]);
        }

        foreach ($riwayatPendidikan as $p) {
            $data['Pendidikan']->push([
                'id' => $p->id,
                'no_induk' => $p->no_induk,
                'nama_lembaga' => $p->nama_lembaga,
                'nama_jurusan' => $p->nama_jurusan ?? '-',
                'nama_kelas' => $p->nama_kelas ?? '-',
                'nama_rombel' => $p->nama_rombel ?? '-',
                'tahun_masuk' => $p->tanggal_masuk,
                'tahun_lulus' => $p->tanggal_keluar ?? '-',
                'status' => $p->status,
            ]);
        }

        // --- Catatan Afektif ---
        $af = DB::table('catatan_afektif as ca')
            ->join('santri as s', 'ca.id_santri', '=', 's.id')
            ->join('biodata as b', 's.biodata_id', '=', 'b.id')
            ->where('b.id', $biodataId)
            ->latest('ca.created_at')
            ->first();

        $data['Catatan_Progress']['Afektif'] = $af
            ? [
                'kebersihan' => $af->kebersihan_nilai ?? '-',
                'tindak_lanjut_kebersihan' => $af->kebersihan_tindak_lanjut ?? '-',
                'kepedulian' => $af->kepedulian_nilai ?? '-',
                'tindak_lanjut_kepedulian' => $af->kepedulian_tindak_lanjut ?? '-',
                'akhlak' => $af->akhlak_nilai ?? '-',
                'tindak_lanjut_akhlak' => $af->akhlak_tindak_lanjut ?? '-',
            ]
            : [];

        // --- Catatan Kognitif ---
        $kg = DB::table('catatan_kognitif as ck')
            ->join('santri as s', 'ck.id_santri', '=', 's.id')
            ->join('biodata as b', 's.biodata_id', '=', 'b.id')
            ->where('b.id', $biodataId)
            ->latest('ck.created_at')
            ->first();


        $data['Catatan_Progress']['Kognitif'] = $kg
            ? [
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
                'nama_pengunjung' => $k->nama,
                'status' => $k->nama_status,
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
                'keterangan' => $kh->keterangan,
                'tanggal_mulai' => $kh->tanggal_mulai,
                'tanggal_akhir' => $kh->tanggal_akhir ?? '-',
            ])
            : [];

        // riwayat transaksi
        $riwayatTransaksi = DB::table('santri as s')
            ->join('transaksi_saldo as t', 't.santri_id', '=', 's.id')
            ->leftJoin('outlets as o', 'o.id', '=', 't.outlet_id')
            ->leftJoin('kategori as kk', 'kk.id', '=', 't.kategori_id')
            ->where('s.biodata_id', $biodataId)
            ->select([
                't.uid_kartu',
                'o.nama_outlet',
                'kk.nama_kategori',
                't.tipe',
                't.jumlah',
                't.keterangan',
                't.created_at'
            ])
            ->orderBy('t.created_at', 'desc')
            ->get();

        $data['RiwayatTransaksi'] = $riwayatTransaksi->isNotEmpty()
            ? $riwayatTransaksi->map(fn($trx) => [
                'uid_kartu' => $trx->uid_kartu ?? 'Non-Kartu',
                'nama_outlet' => $trx->nama_outlet ?? 'Sistem/Lainnya',
                'nama_kategori' => $trx->nama_kategori ?? '-',
                'tipe' => $trx->tipe,
                'jumlah' => $trx->jumlah,
                'keterangan' => $trx->keterangan ?? '-',
            ])->values()
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
            ->orderBy('karyawan.tanggal_mulai', 'desc')
            ->get();

        $data['Karyawan'] = $karyawan->isNotEmpty()
            ? $karyawan->map(fn($item) => [
                'lembaga' => $item->nama_lembaga ?? '-',
                'keterangan_jabatan' => $item->keterangan_jabatan ?? '-',
                'tanggal_mulai' => $item->tanggal_mulai ? date('d-m-Y', strtotime($item->tanggal_mulai)) : '-',
                'tanggal_selesai' => $item->tanggal_selesai ? date('d-m-Y', strtotime($item->tanggal_selesai)) : '-',
            ])
            : [];

        // --- Ambil data pengajar dan riwayat materi ---
        $pengajar = DB::table('pengajar')
            ->join('pegawai', 'pegawai.id', '=', 'pengajar.pegawai_id')
            ->leftJoin('lembaga', 'lembaga.id', '=', 'pengajar.lembaga_id')
            ->join('biodata', 'pegawai.biodata_id', '=', 'biodata.id')
            ->leftJoin('golongan', 'golongan.id', '=', 'pengajar.golongan_id')
            ->leftJoin('kategori_golongan', 'kategori_golongan.id', '=', 'golongan.kategori_golongan_id')

            // Mata pelajaran dan jadwal
            ->leftJoin('mata_pelajaran', function ($join) {
                $join->on('mata_pelajaran.pengajar_id', '=', 'pengajar.id')
                    ->where('mata_pelajaran.status', true);
            })
            ->leftJoin('jadwal_pelajaran', 'jadwal_pelajaran.mata_pelajaran_id', '=', 'mata_pelajaran.id')
            ->leftJoin('jam_pelajaran', 'jam_pelajaran.id', '=', 'jadwal_pelajaran.jam_pelajaran_id')

            ->where('pegawai.biodata_id', $biodataId)
            ->select(
                // Pangkalan
                // 'lembaga.nama_lembaga',
                DB::raw("CONCAT(pengajar.keterangan_jabatan, ' - ', lembaga.nama_lembaga) as nama_lembaga"),
                'pengajar.jabatan as pekerjaan_kontrak',
                'kategori_golongan.nama_kategori_golongan',
                'golongan.nama_golongan',
                'pengajar.tahun_masuk',
                'pengajar.tahun_akhir',

                DB::raw("DATE_FORMAT(pengajar.tahun_masuk, '%e %M %Y') AS sejak"),
                DB::raw("CONCAT(
                    TIMESTAMPDIFF(YEAR, pengajar.tahun_masuk, IFNULL(pengajar.tahun_akhir, NOW())),
                    ' Tahun') AS masa_kerja"),

                // Mata Pelajaran
                'mata_pelajaran.id as materi_id',
                'mata_pelajaran.kode_mapel',
                'mata_pelajaran.nama_mapel',
                DB::raw("CONCAT(jam_pelajaran.jam_mulai, ' - ', jam_pelajaran.jam_selesai) AS jam")
            )
            ->orderByRaw("FIELD(jadwal_pelajaran.hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu')")
            ->get();

        if ($pengajar->isNotEmpty()) {
            $pengajarMap = $pengajar->map(function ($item) {
                return [
                    'pangkalan' => [
                        'lembaga' => $item->nama_lembaga ?? '-',
                        'pekerjaan_kontrak' => $item->pekerjaan_kontrak ?? '-',
                        'kategori_golongan' => $item->nama_kategori_golongan ?? '-',
                        'nama_golongan' => $item->nama_golongan ?? '-',
                        'sejak' => $item->sejak ?? '-',
                        'masa_kerja' => $item->masa_kerja ?? '-',
                    ],
                    'mata_pelajaran' => $item->kode_mapel ? [
                        'materi_id' => $item->materi_id,
                        'kode_mapel' => $item->kode_mapel,
                        'nama_mapel' => $item->nama_mapel,
                    ] : null
                ];
            });

            $data['Pengajar'] = [
                'Pangkalan' => $pengajarMap->pluck('pangkalan')->unique()->values()->all(),
                'Mata_Pelajaran' => $pengajarMap->pluck('mata_pelajaran')->filter()->unique('materi_id')->values()->all(),
            ];
        } else {
            $data['Pengajar'] = [
                'Pangkalan' => [],
                'Mata_Pelajaran' => [],
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
            ->orderBy('pengurus.tanggal_mulai', 'desc')
            ->get();
        $data['Pengurus'] = $pengurus->isNotEmpty()
            ? $pengurus->map(fn($item) => [
                'keterangan_jabatan' => $item->keterangan_jabatan ?? '-',
                'tanggal_mulai' => $item->tanggal_mulai ?? '-',
                'tanggal_akhir' => $item->tanggal_akhir ?? '-',
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
                'Lembaga' => $item->nama_lembaga ?? '-',
                'Jurusan' => $item->nama_jurusan ?? '-',
                'Kelas' => $item->nama_kelas ?? '-',
                'Rombel' => $item->nama_rombel ?? '-',
                'Periode_awal' => $item->periode_awal ?? '-',
                'Periode_akhir' => $item->periode_akhir ?? '-',
            ])
            : [];

        // if (!isset($data['Biodata'])) {

        //     throw new \Exception("ID biodata tidak valid atau tidak memiliki data terkait");
        // }

        return $data;
    }
}
