<?php

namespace App\Services\PesertaDidik;

use App\Models\Santri;
use App\Models\Biodata;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use App\Helpers\StatusPesertaDidikHelper;

class PesertaDidikService
{
    public function getAllPesertaDidik(Request $request)
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

        // 3) Subquery: warga_pesantren terakhir per biodata (status = true)
        $wpLast = DB::table('warga_pesantren')
            ->select('biodata_id', DB::raw('MAX(id) AS last_id'))
            ->where('status', true)
            ->groupBy('biodata_id');

        return DB::table('biodata as b')
            ->leftjoin('santri as s', 's.biodata_id', '=', 'b.id')
            ->leftjoin('pendidikan AS pd', fn($j) => $j->on('b.id', '=', 'pd.biodata_id')->where('pd.status', 'aktif'))
            ->leftJoin('lembaga AS l', 'pd.lembaga_id', '=', 'l.id')
            ->leftjoin('domisili_santri AS ds', fn($join) => $join->on('s.id', '=', 'ds.santri_id')->where('ds.status', 'aktif'))
            ->leftJoin('wilayah AS w', 'ds.wilayah_id', '=', 'w.id')
            ->leftJoinSub($fotoLast, 'fl', fn($j) => $j->on('b.id', '=', 'fl.biodata_id'))
            ->leftJoin('berkas AS br', 'br.id', '=', 'fl.last_id')
            ->leftJoinSub($wpLast, 'wl', fn($j) => $j->on('b.id', '=', 'wl.biodata_id'))
            ->leftJoin('warga_pesantren AS wp', 'wp.id', '=', 'wl.last_id')
            ->leftJoin('kabupaten AS kb', 'kb.id', '=', 'b.kabupaten_id')
            ->where(fn($q) => $q->where('s.status', 'aktif')
                ->orWhere('pd.status', '=', 'aktif'))
            ->where(fn($q) => $q->whereNull('b.deleted_at')
                ->whereNull('s.deleted_at'))
            ->select([
                'b.id as biodata_id',
                DB::raw("COALESCE(b.nik, b.no_passport) AS identitas"),
                'b.nama',
                'wp.niup',
                'l.nama_lembaga',
                'w.nama_wilayah',
                'kb.nama_kabupaten AS kota_asal',
                's.created_at',
                // ambil updated_at terbaru antar s, pd, ds
                DB::raw("
                    GREATEST(
                        s.updated_at,
                        COALESCE(pd.updated_at, s.updated_at),
                        COALESCE(ds.updated_at, s.updated_at)
                    ) AS updated_at
                "),
                DB::raw("COALESCE(br.file_path, 'default.jpg') AS foto_profil"),
            ]);
        return $query;
    }

    public function formatData($results)
    {
        return collect($results->items())->map(fn($item) => [
            'biodata_id'       => $item->biodata_id,
            'nik_or_passport'  => $item->identitas,
            'nama'             => $item->nama,
            'niup'             => $item->niup ?? '-',
            'lembaga'          => $item->nama_lembaga ?? '-',
            'wilayah'          => $item->nama_wilayah ?? '-',
            'kota_asal'        => $item->kota_asal,
            'tgl_update'       => Carbon::parse($item->updated_at)->translatedFormat('d F Y H:i:s') ?? '-',
            'tgl_input'        => Carbon::parse($item->created_at)->translatedFormat('d F Y H:i:s'),
            'foto_profil'      => url($item->foto_profil),
        ]);
    }

    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {
            $userId = Auth::id();
            $now    = now();

            // Biodata Diri
            $nik = $data['nik'] ?? null;
            $existingBiodata = $nik ? DB::table('biodata')->where('nik', $nik)->first() : null;

            // Validasi: jika sudah ada biodata, cek santri aktif
            if ($existingBiodata) {
                $santriAktif = DB::table('santri')
                    ->where('biodata_id', $existingBiodata->id)
                    ->where('status', 'aktif')
                    ->first();

                if ($santriAktif) {
                    // Cek pendidikan aktif
                    $hasActivePendidikan = DB::table('pendidikan')
                        ->where('biodata_id', $existingBiodata->id)
                        ->whereNull('pendidikan.deleted_at')
                        ->exists();

                    if ($hasActivePendidikan) {
                        throw ValidationException::withMessages([
                            'pendidikan' => ['Data dengan nik ini masih tercatat memiliki pendidikan yang aktif. Tidak dapat menambahkan data baru.'],
                        ]);
                    }

                    // Cek domisili aktif
                    $hasActiveDomisili = DB::table('domisili_santri')
                        ->where('santri_id', $santriAktif->id)
                        ->whereNull('domisili_santri.deleted_at')
                        ->exists();

                    if ($hasActiveDomisili) {
                        throw ValidationException::withMessages([
                            'domisili_santri' => ['Data dengan nik ini masih tercatat memiliki riwayat domisili yang aktif. Tidak dapat menambahkan data baru.'],
                        ]);
                    }

                    // Jika santri aktif tapi tidak ada riwayat aktif, tetap tolak pendaftaran baru
                    throw ValidationException::withMessages([
                        'santri' => ['Data dengan nik ini masih tercatat memiliki status santri aktif. Tidak dapat menambahkan data baru.'],
                    ]);
                }
            }

            $biodataData = [
                'nama'                         => $data['nama'],
                'negara_id'                    => $data['negara_id'],
                'provinsi_id'                  => $data['provinsi_id'] ?? null,
                'kabupaten_id'                 => $data['kabupaten_id'] ?? null,
                'kecamatan_id'                 => $data['kecamatan_id'] ?? null,
                'jalan'                        => $data['jalan'] ?? null,
                'kode_pos'                     => $data['kode_pos'] ?? null,
                'no_passport'                  => $data['no_passport'] ?? null,
                'jenis_kelamin'                => $data['jenis_kelamin'],
                'tanggal_lahir'                => $data['tanggal_lahir'],
                'tempat_lahir'                 => $data['tempat_lahir'],
                'nik'                          => $nik,
                'no_telepon'                   => $data['no_telepon'],
                'no_telepon_2'                 => $data['no_telepon_2'] ?? null,
                'email'                        => $data['email'],
                'jenjang_pendidikan_terakhir'  => $data['jenjang_pendidikan_terakhir'] ?? null,
                'nama_pendidikan_terakhir'     => $data['nama_pendidikan_terakhir'] ?? null,
                'anak_keberapa'                => $data['anak_keberapa'] ?? null,
                'dari_saudara'                 => $data['dari_saudara'] ?? null,
                'tinggal_bersama'              => $data['tinggal_bersama'] ?? null,
                'updated_by'                   => $userId,
                'updated_at'                   => $now,
            ];

            // Cek apakah biodata sudah pernah terdaftar
            if ($existingBiodata) {
                DB::table('biodata')->where('id', $existingBiodata->id)->update($biodataData);
                $biodataId = $existingBiodata->id;
            } else {
                do {
                    $smartcard = 'SC-' . strtoupper(Str::random(10));
                } while (DB::table('biodata')->where('smartcard', $smartcard)->exists());

                do {
                    $biodataId = Str::uuid()->toString();
                } while (DB::table('biodata')->where('id', $biodataId)->exists());

                DB::table('biodata')->insert(array_merge($biodataData, [
                    'id'         => $biodataId,
                    'smartcard'  => $smartcard,
                    'status'     => true,
                    'created_by' => $userId,
                    'created_at' => $now,
                ]));
            }

            // Validasi no kk apakah pernah terdaftar di keluarga
            $existingParents = DB::table('keluarga')->where('no_kk', $data['no_kk'])->pluck('id_biodata');
            if ($existingParents->isNotEmpty()) {
                $registeredNiks = DB::table('biodata')->whereIn('id', $existingParents)->pluck('nik');
                foreach (['nik_ayah', 'nik_ibu'] as $k) {
                    if (!empty($data[$k]) && !$registeredNiks->contains($data[$k])) {
                        throw ValidationException::withMessages([
                            'no_kk' => ['No KK ini sudah digunakan oleh kombinasi orang tua yang berbeda.'],
                        ]);
                    }
                }
            }

            if (!DB::table('keluarga')->where('id_biodata', $biodataId)->where('no_kk', $data['no_kk'])->exists()) {
                // Insert data keluarga
                DB::table('keluarga')->insert([
                    'id_biodata' => $biodataId,
                    'no_kk'      => $data['no_kk'],
                    'status'     => true,
                    'created_by' => $userId,
                    'created_at' => $now,
                ]);
            }

            // Ayah dan Ibu
            $hubungan = DB::table('hubungan_keluarga')
                ->pluck('id', 'nama_status');

            foreach (['ayah', 'ibu'] as $role) {
                $nikKey = "nik_$role";
                $nameKey = "nama_$role";
                if (empty($data[$nameKey])) continue;

                $parent = $data[$nikKey] ? DB::table('biodata')->where('nik', $data[$nikKey])->first() : null;
                $parentId = $parent->id ?? Str::uuid()->toString();
                $jenisKelamin = $role === 'ayah' ? 'l' : 'p'; // Tentukan jenis kelamin


                $wafat = $data["wafat_{$role}"] == 1 ? true : false;
                // Jika data sudah ada berdasarkan NIK, lakukan update (kecuali created_at dan created_by)
                if ($parent) {
                    DB::table('biodata')->where('id', $parentId)->update([
                        'nama'          => $data[$nameKey],
                        'tempat_lahir'  => $data["tempat_lahir_{$role}"] ?? null,
                        'tanggal_lahir' => $data["tanggal_lahir_{$role}"] ?? null,
                        'no_telepon'    => $data["no_telepon_{$role}"] ?? null,
                        'wafat'         => $wafat,
                        'status'        => true,
                        'updated_by'    => $userId,
                        'updated_at'    => $now,
                    ]);
                } else {
                    // Jika belum ada, insert data baru
                    DB::table('biodata')->insert([
                        'id'            => $parentId,
                        'nama'          => $data[$nameKey],
                        'nik'           => $data[$nikKey] ?? null,
                        'jenis_kelamin' => $jenisKelamin,
                        'tempat_lahir'  => $data["tempat_lahir_{$role}"] ?? null,
                        'tanggal_lahir' => $data["tanggal_lahir_{$role}"] ?? null,
                        'no_telepon'    => $data["no_telepon_{$role}"] ?? null,
                        'wafat'         => $wafat,
                        'status'        => true,
                        'created_by'    => $userId,
                        'created_at'    => $now,
                        'updated_at'    => $now,
                    ]);
                }

                // Proses di tabel orang_tua_wali
                $roleKandung = $role . ' kandung'; // hasil: "ayah kandung" atau "ibu kandung"

                $ortu = DB::table('orang_tua_wali')
                    ->where('id_biodata', $parentId)
                    ->where('id_hubungan_keluarga', $hubungan[$roleKandung])
                    ->first();

                $ortuData = [
                    'pekerjaan'   => $data["pekerjaan_{$role}"] ?? null,
                    'penghasilan' => $data["penghasilan_{$role}"] ?? null,
                    'wali'        => false,
                    'status'      => true,
                    'updated_by'  => $userId,
                    'updated_at'  => $now,
                ];

                if ($ortu) {
                    DB::table('orang_tua_wali')->where('id', $ortu->id)->update($ortuData);
                } else {
                    DB::table('orang_tua_wali')->insert(array_merge($ortuData, [
                        'id_biodata'           => $parentId,
                        'id_hubungan_keluarga' => $hubungan[$roleKandung],
                        'created_by'           => $userId,
                        'created_at'           => $now,
                    ]));
                }

                // Pastikan orang tua terdaftar dalam keluarga (no_kk)
                if (!DB::table('keluarga')->where('no_kk', $data['no_kk'])->where('id_biodata', $parentId)->exists()) {
                    DB::table('keluarga')->insert([
                        'id_biodata' => $parentId,
                        'no_kk'      => $data['no_kk'],
                        'status'     => true,
                        'created_by' => $userId,
                        'created_at' => $now,
                    ]);
                }
            }

            // Wali
            if (!empty($data['nama_wali'])) {
                $waliNik = $data['nik_wali'] ?? null;
                $assigned = false;

                // Cek apakah walinya adalah ayah atau ibu
                foreach (['ayah', 'ibu'] as $role) {
                    if ($waliNik && $waliNik === ($data["nik_$role"] ?? null)) {
                        $parentId = DB::table('biodata')->where('nik', $waliNik)->value('id');
                        $roleKandung = $role . ' kandung';
                        DB::table('orang_tua_wali')
                            ->where('id_biodata', $parentId)
                            ->where('id_hubungan_keluarga', $hubungan[$roleKandung])
                            ->update([
                                'wali'       => true,
                                'updated_by' => $userId,
                                'updated_at' => $now,
                            ]);
                        $assigned = true;
                        break;
                    }
                }

                if (!$assigned) {
                    $parent = $waliNik ? DB::table('biodata')->where('nik', $waliNik)->first() : null;
                    $parentId = $parent->id ?? Str::uuid()->toString();

                    // Jika wali sudah ada di tabel biodata, update datanya kecuali created_at & created_by
                    if ($parent) {
                        DB::table('biodata')->where('id', $parentId)->update([
                            'nama'          => $data['nama_wali'],
                            'tempat_lahir'  => $data['tempat_lahir_wali'] ?? null,
                            'tanggal_lahir' => $data['tanggal_lahir_wali'] ?? null,
                            'no_telepon'    => $data['no_telepon_wali'] ?? null,
                            'status'        => true,
                            'updated_by'    => $userId,
                            'updated_at'    => $now,
                        ]);
                    } else {
                        DB::table('biodata')->insert([
                            'id'            => $parentId,
                            'nama'          => $data['nama_wali'],
                            'nik'           => $waliNik,
                            'tempat_lahir'  => $data['tempat_lahir_wali'] ?? null,
                            'tanggal_lahir' => $data['tanggal_lahir_wali'] ?? null,
                            'no_telepon'    => $data['no_telepon_wali'] ?? null,
                            'status'        => true,
                            'created_by'    => $userId,
                            'created_at'    => $now,
                            'updated_at'    => $now,
                        ]);
                    }

                    $wali = DB::table('orang_tua_wali')
                        ->where('id_biodata', $parentId)
                        ->where('id_hubungan_keluarga', $hubungan[$data['hubungan']])
                        ->first();

                    $waliData = [
                        'pekerjaan'   => $data['pekerjaan_wali'] ?? null,
                        'penghasilan' => $data['penghasilan_wali'] ?? null,
                        'wali'        => true,
                        'status'      => true,
                        'updated_by'  => $userId,
                        'updated_at'  => $now,
                    ];

                    if ($wali) {
                        DB::table('orang_tua_wali')->where('id', $wali->id)->update($waliData);
                    } else {
                        DB::table('orang_tua_wali')->insert(array_merge($waliData, [
                            'id_biodata'           => $parentId,
                            'id_hubungan_keluarga' => $hubungan[$data['hubungan']],
                            'created_by'           => $userId,
                            'created_at'           => $now,
                        ]));
                    }

                    if (!DB::table('keluarga')->where('no_kk', $data['no_kk'])->where('id_biodata', $parentId)->exists()) {
                        DB::table('keluarga')->insert([
                            'id_biodata' => $parentId,
                            'no_kk'      => $data['no_kk'],
                            'status'     => true,
                            'created_by' => $userId,
                            'created_at' => $now,
                        ]);
                    }
                }
            }

            // Tambah Riwayat Pendidikan jika lembaga diisi
            if (!empty($data['lembaga_id'])) {
                DB::table('pendidikan')->insert([
                    'biodata_id'      => $biodataId,
                    'lembaga_id'     => $data['lembaga_id'],
                    'jurusan_id'     => $data['jurusan_id'] ?? null,
                    'kelas_id'       => $data['kelas_id'] ?? null,
                    'rombel_id'      => $data['rombel_id'] ?? null,
                    'angkatan_id'    => $data['angkatan_pelajar_id'],
                    'tanggal_masuk'  => $data['tanggal_masuk_pendidikan'],
                    'status'         => 'aktif',
                    'created_by'     => $userId,
                    'created_at'     => $now,
                    'updated_at'     => $now,
                ]);
            }

            // validasi mondok
            if (!empty($data['wilayah_id']) || $data['mondok'] == 1) {
                // Tambah Santri
                $santriId = DB::table('santri')->insertGetId([
                    'biodata_id'    => $biodataId,
                    'nis'           => $data['nis'],
                    'tanggal_masuk' => $now,
                    'angkatan_id'    => $data['angkatan_santri_id'],
                    'status'        => 'aktif',
                    'created_by'    => $userId,
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ]);
            }

            // Tambah Riwayat Domisili jika wilayah diisi
            if (!empty($data['wilayah_id'])) {
                DB::table('domisili_santri')->insert([
                    'santri_id'     => $santriId,
                    'wilayah_id'    => $data['wilayah_id'],
                    'blok_id'       => $data['blok_id'],
                    'kamar_id'      => $data['kamar_id'],
                    'tanggal_masuk' => $data['tanggal_masuk_domisili'],
                    'status'        => 'aktif',
                    'created_by'    => $userId,
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ]);
            }

            // Berkas
            if (!empty($data['berkas']) && is_array($data['berkas'])) {
                foreach ($data['berkas'] as $item) {
                    if (!($item['file_path'] instanceof UploadedFile)) {
                        throw new \Exception('Berkas tidak valid');
                    }
                    $url = Storage::url($item['file_path']->store('PesertaDidik', 'public'));
                    DB::table('berkas')->insert([
                        'biodata_id'      => $biodataId,
                        'jenis_berkas_id' => (int) $item['jenis_berkas_id'],
                        'file_path'       => $url,
                        'status'          => true,
                        'created_by'      => $userId,
                        'created_at'      => $now,
                        'updated_at'      => $now,
                    ]);
                }
            }

            activity('registrasi_peserta_didik')
                ->causedBy(Auth::user())
                ->performedOn(Biodata::find($biodataId))
                ->withProperties([
                    'biodata_id'    => $biodataId,
                    'santri_id'     => $santriId ?? null,
                    'no_kk'         => $data['no_kk'],
                    'nik'           => $data['nik'],
                    'orang_tua'     => [
                        'ayah' => $data['nik_ayah'] ?? null,
                        'ibu'  => $data['nik_ibu'] ?? null,
                        'wali' => $data['nik_wali'] ?? null,
                    ],
                    'pendidikan' => !empty($data['lembaga_id']) ? [
                        'lembaga_id'     => $data['lembaga_id'],
                        'jurusan_id'     => $data['jurusan_id'] ?? null,
                        'kelas_id'       => $data['kelas_id'] ?? null,
                        'rombel_id'      => $data['rombel_id'] ?? null,
                        'tanggal_masuk'  => $data['tanggal_masuk_pendidikan'],
                    ] : null,
                    'domisili_santri' => !empty($data['wilayah_id']) ? [
                        'wilayah_id'     => $data['wilayah_id'],
                        'blok_id'        => $data['blok_id'],
                        'kamar_id'       => $data['kamar_id'],
                        'tanggal_masuk'  => $data['tanggal_masuk_domisili'],
                    ] : null,
                    'berkas'        => collect($data['berkas'] ?? [])->pluck('jenis_berkas_id'),
                    'ip'            => request()->ip(),
                    'user_agent'    => request()->userAgent(),
                ])
                ->event('create_peserta_didik')
                ->log('Pendaftaran peserta didik baru beserta orang tua, wali, keluarga, dan berkas berhasil disimpan.');

            return [
                'biodata_diri' => $biodataId,
                'santri_id'    => $santriId ?? null,
            ];
        });
    }

    public function getExportPesertaDidikQuery($fields, $request)
    {
        // 1. Subquery
        $wpLast = DB::table('warga_pesantren')
            ->select('biodata_id', DB::raw('MAX(id) AS last_id'))
            ->where('status', true)
            ->groupBy('biodata_id');

        // 2. Query Builder & JOINS (sesuai kebutuhan multi-field)
        $query = DB::table('biodata as b')
            ->leftjoin('santri as s', 's.biodata_id', '=', 'b.id')
            ->leftjoin('angkatan as as', 's.angkatan_id', '=', 'as.id')
            ->leftjoin('pendidikan AS pd', fn($j) => $j->on('b.id', '=', 'pd.biodata_id')->where('pd.status', 'aktif'))
            ->leftjoin('angkatan as ap', 'pd.angkatan_id', '=', 'ap.id')
            ->leftJoin('lembaga AS l', 'pd.lembaga_id', '=', 'l.id')
            ->leftJoin('jurusan AS j', 'pd.jurusan_id', '=', 'j.id')
            ->leftJoin('kelas AS kls', 'pd.kelas_id', '=', 'kls.id')
            ->leftJoin('rombel AS r', 'pd.rombel_id', '=', 'r.id')
            ->leftJoinSub($wpLast, 'wl', fn($j) => $j->on('b.id', '=', 'wl.biodata_id'))
            ->leftJoin('warga_pesantren AS wp', 'wp.id', '=', 'wl.last_id')
            ->leftjoin('keluarga as k', 'k.id_biodata', 'b.id')
            ->where(fn($q) => $q->where('s.status', 'aktif')->orWhere('pd.status', '=', 'aktif'))
            ->where(fn($q) => $q->whereNull('b.deleted_at')->whereNull('s.deleted_at'));

        // -- Multi-field JOINs (di luar loop, agar SELECT tetap clean)
        if (in_array('alamat', $fields)) {
            $query->leftJoin('kecamatan as kc', 'b.kecamatan_id', '=', 'kc.id');
            $query->leftJoin('kabupaten as kb', 'b.kabupaten_id', '=', 'kb.id');
            $query->leftJoin('provinsi as pv', 'b.provinsi_id', '=', 'pv.id');
            $query->leftJoin('negara as ng', 'b.negara_id', '=', 'ng.id');
        }
        if (in_array('domisili_santri', $fields)) {
            $query->leftjoin('domisili_santri AS ds', fn($join) => $join->on('s.id', '=', 'ds.santri_id')->where('ds.status', 'aktif'));
            $query->leftJoin('wilayah as w', 'ds.wilayah_id', '=', 'w.id');
            $query->leftJoin('blok as bl', 'ds.blok_id', '=', 'bl.id');
            $query->leftJoin('kamar as km', 'ds.kamar_id', '=', 'km.id');
        }
        if (in_array('ibu_kandung', $fields)) {
            $subIbu = DB::table('keluarga as k1')
                ->select('k1.no_kk', 'otw.id_biodata as id_biodata_ibu')
                ->join('orang_tua_wali as otw', 'otw.id_biodata', '=', 'k1.id_biodata')
                ->join('hubungan_keluarga as hk', function ($join) {
                    $join->on('otw.id_hubungan_keluarga', '=', 'hk.id')
                        ->where('hk.nama_status', '=', 'ibu kandung');
                });
            $query->leftJoinSub($subIbu, 'ibu', function ($join) {
                $join->on('k.no_kk', '=', 'ibu.no_kk');
            });
            $query->leftJoin('biodata as b_ibu', 'ibu.id_biodata_ibu', '=', 'b_ibu.id');
        }

        // --- Select sesuai urutan fields, konsisten! ---
        $select = [];
        foreach ($fields as $field) {
            switch ($field) {
                case 'nama':
                    $select[] = 'b.nama';
                    break;
                case 'tempat_lahir':
                    $select[] = 'b.tempat_lahir';
                    break;
                case 'tanggal_lahir':
                    $select[] = 'b.tanggal_lahir';
                    break;
                case 'jenis_kelamin':
                    $select[] = 'b.jenis_kelamin';
                    break;
                case 'nis':
                    $select[] = 's.nis';
                    break;
                case 'no_induk':
                    $select[] = 'pd.no_induk';
                    break;
                case 'lembaga':
                    $select[] = 'l.nama_lembaga as lembaga';
                    break;
                case 'jurusan':
                    $select[] = 'j.nama_jurusan as jurusan';
                    break;
                case 'kelas':
                    $select[] = 'kls.nama_kelas as kelas';
                    break;
                case 'rombel':
                    $select[] = 'r.nama_rombel as rombel';
                    break;
                case 'no_kk':
                    $select[] = 'k.no_kk';
                    break;
                case 'nik':
                    $select[] = DB::raw('COALESCE(b.nik, b.no_passport) as nik');
                    break;
                case 'niup':
                    $select[] = 'wp.niup';
                    break;
                case 'anak_ke':
                    $select[] = 'b.anak_ke';
                    break;
                case 'jumlah_saudara':
                    $select[] = 'b.jumlah_saudara';
                    break;
                case 'alamat':
                    $select[] = 'b.jalan';
                    $select[] = 'kc.nama_kecamatan';
                    $select[] = 'kb.nama_kabupaten';
                    $select[] = 'pv.nama_provinsi';
                    $select[] = 'ng.nama_negara';
                    break;
                case 'domisili_santri':
                    $select[] = 'w.nama_wilayah as dom_wilayah';
                    $select[] = 'bl.nama_blok as dom_blok';
                    $select[] = 'km.nama_kamar as dom_kamar';
                    break;
                case 'angkatan_santri':
                    $select[] = 'as.angkatan as angkatan_santri';
                    break;
                case 'angkatan_pelajar':
                    $select[] = 'ap.angkatan as angkatan_pelajar';
                    break;
                case 'status':
                    $select[] = 's.status';
                    break;
                case 'ibu_kandung':
                    $select[] = 'b_ibu.nama as nama_ibu';
                    break;
                // --- tambahkan 'ayah_kandung' jika ingin support export ayah juga
                case 'ayah_kandung':
                    // ... tambahkan join sub & select jika perlu
                    break;
            }
        }
        $query->select($select);

        return $query;
    }


    public function formatDataExport($results, $fields, $addNumber = false)
    {
        return collect($results)->values()->map(function ($item, $idx) use ($fields, $addNumber) {
            $data = [];
            if ($addNumber) {
                $data['No'] = $idx + 1;
            }
            $itemArr = (array) $item;
            $i = 0; // pointer index hasil select (array order)

            foreach ($fields as $field) {
                switch ($field) {
                    case 'nama':
                        $data['Nama'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    case 'tempat_lahir':
                        $data['Tempat Lahir'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    case 'tanggal_lahir':
                        $tgl = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        $data['Tanggal Lahir'] = $tgl ? \Carbon\Carbon::parse($tgl)->translatedFormat('d F Y') : '';
                        break;
                    case 'jenis_kelamin':
                        $jk = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        if (strtolower($jk) === 'l') $data['Jenis Kelamin'] = 'Laki-laki';
                        elseif (strtolower($jk) === 'p') $data['Jenis Kelamin'] = 'Perempuan';
                        else $data['Jenis Kelamin'] = '';
                        break;
                    case 'nis':
                        $data['NIS'] = ' ' . ($itemArr[array_keys($itemArr)[$i++]] ?? '');
                        break;
                    case 'no_induk':
                        $data['No. Induk'] = ' ' . ($itemArr[array_keys($itemArr)[$i++]] ?? '');
                        break;
                    case 'lembaga':
                        $data['Lembaga'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    case 'jurusan':
                        $data['Jurusan'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    case 'kelas':
                        $data['Kelas'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    case 'rombel':
                        $data['Rombel'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    case 'no_kk':
                        $data['No. KK'] = ' ' . ($itemArr[array_keys($itemArr)[$i++]] ?? '');
                        break;
                    case 'nik':
                        $data['NIK'] = ' ' . ($itemArr[array_keys($itemArr)[$i++]] ?? '');
                        break;
                    case 'niup':
                        $data['NIUP'] = ' ' . ($itemArr[array_keys($itemArr)[$i++]] ?? '');
                        break;
                    case 'anak_ke':
                        $data['Anak ke'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    case 'jumlah_saudara':
                        $data['Jumlah Saudara'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    case 'alamat':
                        $data['Jalan']     = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        $data['Kecamatan'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        $data['Kabupaten'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        $data['Provinsi']  = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        $data['Negara']    = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    case 'domisili_santri':
                        $data['Wilayah Domisili'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        $data['Blok Domisili']    = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        $data['Kamar Domisili']   = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    case 'angkatan_santri':
                        $data['Angkatan Santri'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    case 'angkatan_pelajar':
                        $data['Angkatan Pelajar'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    case 'status':
                        $data['Status'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    case 'ibu_kandung':
                        $data['Ibu Kandung'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    default:
                        $data[$field] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                }
            }
            return $data;
        })->values();
    }


    public function getFieldExportHeadings($fields, $addNumber = false)
    {
        $map = [
            'nama' => 'Nama',
            'tempat_lahir' => 'Tempat Lahir',
            'tanggal_lahir' => 'Tanggal Lahir',
            'jenis_kelamin' => 'Jenis Kelamin',
            'nis' => 'NIS',
            'no_induk' => 'No. Induk',
            'lembaga' => 'Lembaga',
            'jurusan' => 'Jurusan',
            'kelas' => 'Kelas',
            'rombel' => 'Rombel',
            'no_kk' => 'No. KK',
            'nik' => 'NIK',
            'niup' => 'NIUP',
            'anak_ke' => 'Anak ke',
            'jumlah_saudara' => 'Jumlah Saudara',
            'alamat'   => ['Jalan', 'Kecamatan', 'Kabupaten', 'Provinsi', 'Negara'],
            'domisili_santri' => ['Wilayah Domisili', 'Blok Domisili', 'Kamar Domisili'],
            'angkatan_santri' => 'Angkatan Santri',
            'angkatan_pelajar' => 'Angkatan Pelajar',
            'status' => 'Status',
            'ibu_kandung' => 'Ibu Kandung',
        ];
        $headings = [];
        foreach ($fields as $field) {
            if (isset($map[$field])) {
                if (is_array($map[$field])) {
                    foreach ($map[$field] as $h) $headings[] = $h;
                } else {
                    $headings[] = $map[$field];
                }
            } else {
                $headings[] = $field;
            }
        }
        if ($addNumber) {
            array_unshift($headings, 'No');
        }
        return $headings;
    }
}
