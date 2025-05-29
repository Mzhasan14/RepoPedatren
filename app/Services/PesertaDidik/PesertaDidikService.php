<?php

namespace App\Services\PesertaDidik;

use App\Models\Santri;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

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

        return DB::table('santri AS s')
            ->join('biodata AS b', 's.biodata_id', '=', 'b.id')
            ->leftjoin('riwayat_pendidikan AS rp', fn($j) => $j->on('s.id', '=', 'rp.santri_id')->where('rp.status', 'aktif'))
            ->leftJoin('lembaga AS l', 'rp.lembaga_id', '=', 'l.id')
            ->leftjoin('riwayat_domisili AS rd', fn($join) => $join->on('s.id', '=', 'rd.santri_id')->where('rd.status', 'aktif'))
            ->leftJoin('wilayah AS w', 'rd.wilayah_id', '=', 'w.id')
            ->leftJoinSub($fotoLast, 'fl', fn($j) => $j->on('b.id', '=', 'fl.biodata_id'))
            ->leftJoin('berkas AS br', 'br.id', '=', 'fl.last_id')
            ->leftJoinSub($wpLast, 'wl', fn($j) => $j->on('b.id', '=', 'wl.biodata_id'))
            ->leftJoin('warga_pesantren AS wp', 'wp.id', '=', 'wl.last_id')
            ->leftJoin('kabupaten AS kb', 'kb.id', '=', 'b.kabupaten_id')
            ->where(fn($q) => $q->where('s.status', 'aktif')
                ->orWhere('rp.status', '=', 'aktif'))
            ->where(fn($q) => $q->whereNull('b.deleted_at')
                ->whereNull('s.deleted_at'))
            ->select([
                'b.id as biodata_id',
                's.id',
                DB::raw("COALESCE(b.nik, b.no_passport) AS identitas"),
                'b.nama',
                'wp.niup',
                'l.nama_lembaga',
                'w.nama_wilayah',
                'kb.nama_kabupaten AS kota_asal',
                's.created_at',
                // ambil updated_at terbaru antar s, rp, rd
                DB::raw("
                    GREATEST(
                        s.updated_at,
                        COALESCE(rp.updated_at, s.updated_at),
                        COALESCE(rd.updated_at, s.updated_at)
                    ) AS updated_at
                "),
                DB::raw("COALESCE(br.file_path, 'default.jpg') AS foto_profil"),
            ])
            ->latest();
        return $query;
    }

    public function formatData($results)
    {
        return collect($results->items())->map(fn($item) => [
            'biodata_id'       => $item->biodata_id,
            'id'               => $item->id,
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
                    $hasActivePendidikan = DB::table('riwayat_pendidikan')
                        ->where('santri_id', $santriAktif->id)
                        ->where('status', 'aktif')
                        ->exists();

                    if ($hasActivePendidikan) {
                        throw ValidationException::withMessages([
                            'riwayat_pendidikan' => ['Santri ini masih memiliki riwayat pendidikan yang aktif.'],
                        ]);
                    }

                    // Cek domisili aktif
                    $hasActiveDomisili = DB::table('riwayat_domisili')
                        ->where('santri_id', $santriAktif->id)
                        ->where('status', 'aktif')
                        ->exists();

                    if ($hasActiveDomisili) {
                        throw ValidationException::withMessages([
                            'riwayat_domisili' => ['Santri ini masih memiliki riwayat domisili yang aktif.'],
                        ]);
                    }

                    // Jika santri aktif tapi tidak ada riwayat aktif, tetap tolak pendaftaran baru
                    throw ValidationException::withMessages([
                        'santri' => ['Santri ini masih memiliki status aktif. Tidak dapat menambahkan santri baru.'],
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

            // Tambah Santri
            do {
                $nis = now()->format('YmdHis') . sprintf("%04d", mt_rand(0, 9999));
            } while (DB::table('santri')->where('nis', $nis)->exists());

            $santriId = DB::table('santri')->insertGetId([
                'biodata_id'    => $biodataId,
                'nis'           => $nis,
                'tanggal_masuk' => $now,
                'status'        => 'aktif',
                'created_by'    => $userId,
                'created_at'    => $now,
                'updated_at'    => $now,
            ]);

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

                // Jika data sudah ada berdasarkan NIK, lakukan update (kecuali created_at dan created_by)
                if ($parent) {
                    DB::table('biodata')->where('id', $parentId)->update([
                        'nama'          => $data[$nameKey],
                        'tempat_lahir'  => $data["tempat_lahir_{$role}"] ?? null,
                        'tanggal_lahir' => $data["tanggal_lahir_{$role}"] ?? null,
                        'no_telepon'    => $data["no_telepon_{$role}"] ?? null,
                        'wafat'         => $data["wafat_{$role}"],
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
                        'wafat'         => $data["wafat_{$role}"],
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
                DB::table('riwayat_pendidikan')->insert([
                    'santri_id'      => $santriId,
                    'lembaga_id'     => $data['lembaga_id'],
                    'jurusan_id'     => $data['jurusan_id'] ?? null,
                    'kelas_id'       => $data['kelas_id'] ?? null,
                    'rombel_id'      => $data['rombel_id'] ?? null,
                    'tanggal_masuk'  => $data['tanggal_masuk_pendidikan'],
                    'status'         => 'aktif',
                    'created_by'     => $userId,
                    'created_at'     => $now,
                    'updated_at'     => $now,
                ]);
            }

            // Tambah Riwayat Domisili jika wilayah diisi
            if (!empty($data['wilayah_id'])) {
                DB::table('riwayat_domisili')->insert([
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

            activity('santri_registration')
                ->causedBy(Auth::user())
                ->performedOn(Santri::find($santriId))
                ->withProperties([
                    'santri_id'     => $santriId,
                    'biodata_id'    => $biodataId,
                    'no_kk'         => $data['no_kk'],
                    'nik'           => $data['nik'],
                    'orang_tua'     => [
                        'ayah' => $data['nik_ayah'] ?? null,
                        'ibu'  => $data['nik_ibu'] ?? null,
                        'wali' => $data['nik_wali'] ?? null,
                    ],
                    'riwayat_pendidikan' => !empty($data['lembaga_id']) ? [
                        'lembaga_id'     => $data['lembaga_id'],
                        'jurusan_id'     => $data['jurusan_id'] ?? null,
                        'kelas_id'       => $data['kelas_id'] ?? null,
                        'rombel_id'      => $data['rombel_id'] ?? null,
                        'tanggal_masuk'  => $data['tanggal_masuk_pendidikan'],
                    ] : null,
                    'riwayat_domisili' => !empty($data['wilayah_id']) ? [
                        'wilayah_id'     => $data['wilayah_id'],
                        'blok_id'        => $data['blok_id'],
                        'kamar_id'       => $data['kamar_id'],
                        'tanggal_masuk'  => $data['tanggal_masuk_domisili'],
                    ] : null,
                    'berkas'        => collect($data['berkas'] ?? [])->pluck('jenis_berkas_id'),
                    'ip'            => request()->ip(),
                    'user_agent'    => request()->userAgent(),
                ])
                ->event('create_santri')
                ->log('Pendaftaran santri baru beserta orang tua, wali, keluarga, dan berkas berhasil disimpan.');

            return [
                'santri_id'    => $santriId,
                'biodata_diri' => $biodataId,
            ];
        });
    }

    public function destroy(string $santriId)
    {
        return DB::transaction(function () use ($santriId) {
            $userId = Auth::id();

            $santri = Santri::with([
                'biodata',
                'riwayatPendidikan',
                'riwayatDomisili',
                'waliAsuh',
                'anakAsuh',
            ])->findOrFail($santriId);

            // Cek relasi yang masih aktif
            $aktifPendidikan = $santri->riwayatPendidikan->where('status', 'aktif')->count();
            $aktifDomisili   = $santri->riwayatDomisili->where('status', 'aktif')->count();
            $aktifWaliAsuh   = $santri->waliAsuh->where('status', true)->count();
            $aktifAnakAsuh   = $santri->anakAsuh->where('status', true)->count();

            if ($aktifPendidikan || $aktifDomisili || $aktifWaliAsuh || $aktifAnakAsuh) {
                throw ValidationException::withMessages([
                    'santri' => 'Penghapusan dibatalkan. Pastikan semua data relasi sudah tidak aktif.',
                    'detail' => [
                        'riwayat_pendidikan_aktif' => $aktifPendidikan,
                        'riwayat_domisili_aktif'   => $aktifDomisili,
                        'wali_asuh_aktif'          => $aktifWaliAsuh,
                        'anak_asuh_aktif'          => $aktifAnakAsuh,
                    ]
                ]);
            }

            // Lakukan soft delete
            if ($santri->biodata) {
                $santri->biodata->deleted_by = $userId;
                $santri->biodata->save();
                $santri->biodata->delete();
            }

            $santri->deleted_by = $userId;
            $santri->save();
            $santri->delete();

            return $santri;
        });
    }


    // public function destroy(string $santriId)
    // {
    //     return DB::transaction(function () use ($santriId) {
    //         $userId = Auth::id();

    //         $santri  = Santri::with('biodata')->findOrFail($santriId);
    //         $biodata = $santri->biodata;

    //         if ($biodata) {
    //             $biodata->deleted_by = $userId;
    //             $biodata->save();
    //             $biodata->delete();
    //         }

    //         $santri->deleted_by = $userId;
    //         $santri->save();
    //         $santri->delete();

    //         return $santri;
    //     });
    // }
}
