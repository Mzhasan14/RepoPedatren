<?php

namespace App\Services\PesertaDidik;

use App\Models\Biodata;
use App\Models\Santri;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AnakPegawaiService
{
    public function baseAnakPegawaiQuery(Request $request)
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

        $keluargaLast = DB::table('keluarga')
            ->select('id_biodata', DB::raw('MAX(id) AS last_id'))
            ->groupBy('id_biodata');

        $query = DB::table('anak_pegawai as ap')
            ->join('biodata AS b', 'ap.biodata_id', '=', 'b.id')
            ->leftJoin('santri AS s', fn($j) => $j->on('b.id', '=', 's.biodata_id')->where('s.status', 'aktif'))
            ->leftJoin('pendidikan AS pd', function ($j) {
                $j->on('b.id', '=', 'pd.biodata_id')
                    ->where('pd.status', 'aktif');
            })
            ->join('pegawai as p', 'ap.pegawai_id', '=', 'p.id')
            ->join('biodata as bp', 'p.biodata_id', '=', 'bp.id')
            ->leftJoin('lembaga AS l', 'pd.lembaga_id', '=', 'l.id')
            ->leftJoin('jurusan AS j', 'pd.jurusan_id', '=', 'j.id')
            ->leftJoin('kelas AS kls', 'pd.kelas_id', '=', 'kls.id')
            ->leftJoin('domisili_santri AS ds', function ($j) {
                $j->on('s.id', '=', 'ds.santri_id')
                    ->where('ds.status', 'aktif');
            })
            ->leftJoin('wilayah AS w', 'ds.wilayah_id', '=', 'w.id')
            ->leftJoin('blok AS bl', 'ds.blok_id', '=', 'bl.id')
            ->leftJoin('kamar AS km', 'ds.kamar_id', '=', 'km.id')
            ->leftJoinSub($fotoLast, 'fl', fn($j) => $j->on('b.id', '=', 'fl.biodata_id'))
            ->leftJoin('berkas AS br', 'br.id', '=', 'fl.last_id')
            ->leftJoinSub($wpLast, 'wl', fn($j) => $j->on('b.id', '=', 'wl.biodata_id'))
            ->leftJoin('warga_pesantren AS wp', 'wp.id', '=', 'wl.last_id')
            ->leftJoin('kabupaten AS kb', 'kb.id', '=', 'b.kabupaten_id')
            ->leftJoinSub($keluargaLast, 'kl', fn($j) => $j->on('b.id', '=', 'kl.id_biodata'))
            ->leftJoin('keluarga as k', 'k.id', '=', 'kl.last_id')
            ->where('ap.status', true)
            ->where(function ($q) {
                $q->where('s.status', 'aktif')
                    ->orWhere('pd.status', 'aktif');
            })
            ->where(fn($q) => $q->whereNull('b.deleted_at')
                ->whereNull('s.deleted_at'));

        return $query;
    }

    public function getAllAnakPegawai(Request $request, $fields = null)
    {
        $query = $this->baseAnakPegawaiQuery($request);

        $fields = $fields ?? [
            'b.id as biodata_id',
            DB::raw('COALESCE(b.nik, b.no_passport) AS identitas'),
            's.nis',
            'b.nama',
            'wp.niup',
            'l.nama_lembaga',
            'j.nama_jurusan',
            'kls.nama_kelas',
            'w.nama_wilayah',
            'km.nama_kamar',
            'bl.nama_blok',
            DB::raw("GROUP_CONCAT(DISTINCT bp.nama SEPARATOR '/ ') AS nama_ortu"),
            'kb.nama_kabupaten AS kota_asal',
            's.created_at',
            DB::raw('GREATEST(
                    s.updated_at,
                    COALESCE(pd.updated_at, s.updated_at),
                    COALESCE(ds.updated_at, s.updated_at)
                ) AS updated_at'),
            DB::raw("COALESCE(br.file_path, 'default.jpg') AS foto_profil"),
        ];

        $groupBy = [
            'b.id',
            DB::raw('COALESCE(b.nik, b.no_passport)'),
            's.nis',
            'b.nama',
            'wp.niup',
            'l.nama_lembaga',
            'j.nama_jurusan',
            'kls.nama_kelas',
            'w.nama_wilayah',
            'km.nama_kamar',
            'bl.nama_blok',
            'kb.nama_kabupaten',
            's.created_at',
            DB::raw('GREATEST(s.updated_at, COALESCE(pd.updated_at, s.updated_at), COALESCE(ds.updated_at, s.updated_at))'),
            DB::raw("COALESCE(br.file_path, 'default.jpg')"),
        ];

        return $query->select($fields)->groupBy($groupBy);
    }

    public function formatData($results)
    {
        return collect($results->items())->map(fn($item) => [
            'biodata_id' => $item->biodata_id,
            'nik_or_passport' => $item->identitas,
            'nis' => $item->nis ?? '-',
            'nama' => $item->nama,
            'niup' => $item->niup ?? '-',
            'lembaga' => $item->nama_lembaga ?? '-',
            'jurusan' => $item->nama_jurusan ?? '-',
            'kelas' => $item->nama_kelas ?? '-',
            'wilayah' => $item->nama_wilayah ?? '-',
            'kamar' => $item->nama_kamar ?? '-',
            'blok' => $item->nama_blok ?? '-',
            'nama_ortu' => $item->nama_ortu ?? '-',
            'kota_asal' => $item->kota_asal,
            'tgl_update' => Carbon::parse($item->updated_at)->translatedFormat('d F Y H:i:s') ?? '-',
            'tgl_input' => Carbon::parse($item->created_at)->translatedFormat('d F Y H:i:s'),
            'foto_profil' => url($item->foto_profil),
        ]);
    }

    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {
            $userId = Auth::id();
            $now = now();

            // --- Validasi jika paspor diisi, maka negara bukan Indonesia ---
            if (! empty($data['passport'])) {
                $negara = DB::table('negara')->where('id', $data['negara_id'])->first();

                if (! $negara) {
                    throw ValidationException::withMessages([
                        'negara_id' => ['Negara tidak ditemukan.'],
                    ]);
                }

                if (strtolower($negara->nama_negara) === 'indonesia') {
                    throw ValidationException::withMessages([
                        'passport' => ['Jika mengisi nomor paspor, negara asal tidak boleh Indonesia.'],
                    ]);
                }
            }


            // --- 1. Validasi minimal salah satu orang tua adalah pegawai aktif ---
            $pegawaiNikList = DB::table('pegawai')
                ->join('biodata', 'pegawai.biodata_id', '=', 'biodata.id')
                ->where('pegawai.status_aktif', 'aktif')
                ->pluck('biodata.nik')
                ->toArray();

            $ayahIsPegawai = ! empty($data['nik_ayah']) && in_array($data['nik_ayah'], $pegawaiNikList);
            $ibuIsPegawai = ! empty($data['nik_ibu']) && in_array($data['nik_ibu'], $pegawaiNikList);

            if (! $ayahIsPegawai && ! $ibuIsPegawai) {
                throw ValidationException::withMessages([
                    'orang_tua' => ['Minimal salah satu orang tua harus berstatus pegawai aktif.'],
                ]);
            }

            // --- 2. Validasi Biodata, Santri Aktif, Pendidikan, Domisili ---
            $nik = $data['nik'] ?? null;
            $existingBiodata = $nik ? DB::table('biodata')->where('nik', $nik)->first() : null;

            if ($existingBiodata) {
                $santriAktif = DB::table('santri')
                    ->where('biodata_id', $existingBiodata->id)
                    ->where('status', 'aktif')
                    ->first();

                if ($santriAktif) {
                    $hasActivePendidikan = DB::table('pendidikan')
                        ->where('biodata_id', $existingBiodata->id)
                        ->whereNull('deleted_at')
                        ->exists();

                    if ($hasActivePendidikan) {
                        throw ValidationException::withMessages([
                            'pendidikan' => ['Data dengan NIK ini masih memiliki pendidikan aktif. Tidak bisa tambah data baru.'],
                        ]);
                    }

                    $hasActiveDomisili = DB::table('domisili_santri')
                        ->where('santri_id', $santriAktif->id)
                        ->whereNull('deleted_at')
                        ->exists();

                    if ($hasActiveDomisili) {
                        throw ValidationException::withMessages([
                            'domisili_santri' => ['Data dengan NIK ini masih punya domisili aktif. Tidak bisa tambah data baru.'],
                        ]);
                    }

                    throw ValidationException::withMessages([
                        'santri' => ['Data dengan NIK ini masih punya status santri aktif. Tidak bisa tambah data baru.'],
                    ]);
                }
            }

            // --- 3. Insert atau update data biodata ---
            $biodataData = [
                'nama' => $data['nama'],
                'negara_id' => $data['negara_id'],
                'provinsi_id' => $data['provinsi_id'] ?? null,
                'kabupaten_id' => $data['kabupaten_id'] ?? null,
                'kecamatan_id' => $data['kecamatan_id'] ?? null,
                'jalan' => $data['jalan'] ?? null,
                'kode_pos' => $data['kode_pos'] ?? null,
                'no_passport' => $data['passport'] ?? null,
                'jenis_kelamin' => $data['jenis_kelamin'],
                'tanggal_lahir' => $data['tanggal_lahir'],
                'tempat_lahir' => $data['tempat_lahir'],
                'nik' => $nik,
                'no_telepon' => $data['no_telepon'],
                'no_telepon_2' => $data['no_telepon_2'] ?? null,
                'email' => $data['email'],
                'jenjang_pendidikan_terakhir' => $data['jenjang_pendidikan_terakhir'] ?? null,
                'nama_pendidikan_terakhir' => $data['nama_pendidikan_terakhir'] ?? null,
                'anak_keberapa' => $data['anak_keberapa'] ?? null,
                'dari_saudara' => $data['dari_saudara'] ?? null,
                'tinggal_bersama' => $data['tinggal_bersama'] ?? null,
                'smardcard' => $data['smardcard'] ?? null,
                'updated_by' => $userId,
                'updated_at' => $now,
            ];

            if ($existingBiodata) {
                DB::table('biodata')->where('id', $existingBiodata->id)->update($biodataData);
                $biodataId = $existingBiodata->id;
            } else {
                // do {
                //     $smartcard = 'SC-' . strtoupper(Str::random(10));
                // } while (DB::table('biodata')->where('smartcard', $smartcard)->exists());

                do {
                    $biodataId = Str::uuid()->toString();
                } while (DB::table('biodata')->where('id', $biodataId)->exists());

                DB::table('biodata')->insert(array_merge($biodataData, [
                    'id' => $biodataId,
                    // 'smartcard' => $smartcard,
                    'status' => true,
                    'created_by' => $userId,
                    'created_at' => $now,
                ]));
            }

            // --- 4. Validasi KK tidak boleh beda kombinasi ortu ---
            if (! isset($data['no_kk']) || empty($data['no_kk'])) {
                if (! empty($data['passport'])) {
                    do {
                        // WNA + 13 digit angka = 16 karakter
                        $generatedNoKK = 'WNA' . str_pad((string)random_int(0, 9999999999999), 13, '0', STR_PAD_LEFT);
                    } while (DB::table('keluarga')->where('no_kk', $generatedNoKK)->exists());

                    $data['no_kk'] = $generatedNoKK;
                } else {
                    throw ValidationException::withMessages([
                        'no_kk' => ['No KK wajib diisi jika tidak mengisi paspor.'],
                    ]);
                }
            }

            $existingParents = DB::table('keluarga')->where('no_kk', $data['no_kk'])->pluck('id_biodata');
            if ($existingParents->isNotEmpty()) {
                $registeredNiks = DB::table('biodata')->whereIn('id', $existingParents)->pluck('nik');
                foreach (['nik_ayah', 'nik_ibu'] as $k) {
                    if (! empty($data[$k]) && ! $registeredNiks->contains($data[$k])) {
                        throw ValidationException::withMessages([
                            'no_kk' => ['No KK ini sudah digunakan oleh kombinasi orang tua yang berbeda.'],
                        ]);
                    }
                }
            }

            // Insert keluarga jika belum ada
            if (! DB::table('keluarga')->where('id_biodata', $biodataId)->where('no_kk', $data['no_kk'])->exists()) {
                DB::table('keluarga')->insert([
                    'id_biodata' => $biodataId,
                    'no_kk' => $data['no_kk'],
                    'status' => true,
                    'created_by' => $userId,
                    'created_at' => $now,
                ]);
            }

            // --- 5. Proses data Ayah dan Ibu ---
            $hubungan = DB::table('hubungan_keluarga')->pluck('id', 'nama_status');
            foreach (['ayah', 'ibu'] as $role) {
                $nikKey = "nik_$role";
                $nameKey = "nama_$role";
                if (empty($data[$nameKey])) {
                    continue;
                }

                $parent = ! empty($data[$nikKey]) ? DB::table('biodata')->where('nik', $data[$nikKey])->first() : null;
                $parentId = $parent->id ?? Str::uuid()->toString();
                $jenisKelamin = $role === 'ayah' ? 'l' : 'p';
                $wafat = ! empty($data["wafat_$role"]) ? true : false;

                if ($parent) {
                    DB::table('biodata')->where('id', $parentId)->update([
                        'nama' => $data[$nameKey],
                        'tempat_lahir' => $data["tempat_lahir_$role"] ?? null,
                        'tanggal_lahir' => $data["tanggal_lahir_$role"] ?? null,
                        'no_telepon' => $data["no_telepon_$role"] ?? null,
                        'wafat' => $wafat,
                        'status' => true,
                        'updated_by' => $userId,
                        'updated_at' => $now,
                    ]);
                } else {
                    DB::table('biodata')->insert([
                        'id' => $parentId,
                        'nama' => $data[$nameKey],
                        'nik' => $data[$nikKey] ?? null,
                        'jenis_kelamin' => $jenisKelamin,
                        'tempat_lahir' => $data["tempat_lahir_$role"] ?? null,
                        'tanggal_lahir' => $data["tanggal_lahir_$role"] ?? null,
                        'no_telepon' => $data["no_telepon_$role"] ?? null,
                        'wafat' => $wafat,
                        'status' => true,
                        'created_by' => $userId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }

                // Insert/update tabel orang tua wali
                $roleKandung = "$role kandung";
                $ortu = DB::table('orang_tua_wali')
                    ->where('id_biodata', $parentId)
                    ->where('id_hubungan_keluarga', $hubungan[$roleKandung])
                    ->first();

                $ortuData = [
                    'pekerjaan' => $data["pekerjaan_$role"] ?? null,
                    'penghasilan' => $data["penghasilan_$role"] ?? null,
                    'wali' => false,
                    'status' => true,
                    'updated_by' => $userId,
                    'updated_at' => $now,
                ];

                if ($ortu) {
                    DB::table('orang_tua_wali')->where('id', $ortu->id)->update($ortuData);
                } else {
                    DB::table('orang_tua_wali')->insert(array_merge($ortuData, [
                        'id_biodata' => $parentId,
                        'id_hubungan_keluarga' => $hubungan[$roleKandung],
                        'created_by' => $userId,
                        'created_at' => $now,
                    ]));
                }

                // Pastikan ayah/ibu terdaftar di keluarga
                if (! DB::table('keluarga')->where('no_kk', $data['no_kk'])->where('id_biodata', $parentId)->exists()) {
                    DB::table('keluarga')->insert([
                        'id_biodata' => $parentId,
                        'no_kk' => $data['no_kk'],
                        'status' => true,
                        'created_by' => $userId,
                        'created_at' => $now,
                    ]);
                }

                // Jika orang tua adalah pegawai aktif, masukkan ke anak_pegawai
                if (in_array($data[$nikKey] ?? '', $pegawaiNikList)) {
                    $pegawaiId = DB::table('pegawai')
                        ->join('biodata', 'pegawai.biodata_id', '=', 'biodata.id')
                        ->where('biodata.nik', $data[$nikKey])
                        ->where('pegawai.status_aktif', 'aktif')
                        ->value('pegawai.id');

                    if ($pegawaiId) {
                        DB::table('anak_pegawai')->updateOrInsert([
                            'biodata_id' => $biodataId,
                            'pegawai_id' => $pegawaiId,
                        ], [
                            'status_hubungan' => $role,
                            'status' => true,
                            'created_by' => $userId,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);
                    }
                }
            }

            // --- 4. PROSES WALI (JIKA ADA) ---
            if (! empty($data['nama_wali'])) {
                $waliNik = $data['nik_wali'] ?? null;
                $waliIsAyahIbu = false;

                // Cek apakah wali adalah ayah atau ibu
                foreach (['ayah', 'ibu'] as $role) {
                    if ($waliNik && $waliNik === ($data["nik_$role"] ?? null)) {
                        // Wali adalah ayah/ibu kandung
                        $parentId = DB::table('biodata')->where('nik', $waliNik)->value('id');
                        $roleKandung = "$role kandung";
                        DB::table('orang_tua_wali')
                            ->where('id_biodata', $parentId)
                            ->where('id_hubungan_keluarga', $hubungan[$roleKandung])
                            ->update([
                                'wali' => true,
                                'updated_by' => $userId,
                                'updated_at' => $now,
                            ]);
                        $waliIsAyahIbu = true;
                        break;
                    }
                }

                // Jika wali BUKAN ayah/ibu
                if (! $waliIsAyahIbu) {
                    $parent = $waliNik ? DB::table('biodata')->where('nik', $waliNik)->first() : null;
                    $parentId = $parent->id ?? Str::uuid()->toString();

                    // Insert/update biodata wali
                    if ($parent) {
                        DB::table('biodata')->where('id', $parentId)->update([
                            'nama' => $data['nama_wali'],
                            'tempat_lahir' => $data['tempat_lahir_wali'] ?? null,
                            'tanggal_lahir' => $data['tanggal_lahir_wali'] ?? null,
                            'no_telepon' => $data['no_telepon_wali'] ?? null,
                            'status' => true,
                            'updated_by' => $userId,
                            'updated_at' => $now,
                        ]);
                    } else {
                        DB::table('biodata')->insert([
                            'id' => $parentId,
                            'nama' => $data['nama_wali'],
                            'nik' => $waliNik,
                            'tempat_lahir' => $data['tempat_lahir_wali'] ?? null,
                            'tanggal_lahir' => $data['tanggal_lahir_wali'] ?? null,
                            'no_telepon' => $data['no_telepon_wali'] ?? null,
                            'status' => true,
                            'created_by' => $userId,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);
                    }

                    $idHubunganKeluarga = $hubungan['wali'];
                    $wali = DB::table('orang_tua_wali')
                        ->where('id_biodata', $parentId)
                        ->where('id_hubungan_keluarga', $idHubunganKeluarga)
                        ->first();

                    $waliData = [
                        'pekerjaan' => $data['pekerjaan_wali'] ?? null,
                        'penghasilan' => $data['penghasilan_wali'] ?? null,
                        'wali' => true,
                        'status' => true,
                        'updated_by' => $userId,
                        'updated_at' => $now,
                    ];

                    if ($wali) {
                        DB::table('orang_tua_wali')->where('id', $wali->id)->update($waliData);
                    } else {
                        DB::table('orang_tua_wali')->insert(array_merge($waliData, [
                            'id_biodata' => $parentId,
                            'id_hubungan_keluarga' => $idHubunganKeluarga,
                            'created_by' => $userId,
                            'created_at' => $now,
                        ]));
                    }

                    // Tambah ke keluarga jika belum ada
                    if (! DB::table('keluarga')->where('no_kk', $data['no_kk'])->where('id_biodata', $parentId)->exists()) {
                        DB::table('keluarga')->insert([
                            'id_biodata' => $parentId,
                            'no_kk' => $data['no_kk'],
                            'status' => true,
                            'created_by' => $userId,
                            'created_at' => $now,
                        ]);
                    }
                }
            }

            // --- 7. Proses pendidikan jika diisi ---
            if (! empty($data['lembaga_id'])) {
                DB::table('pendidikan')->insert([
                    'biodata_id' => $biodataId,
                    'no_induk' => $data['no_induk'],
                    'lembaga_id' => $data['lembaga_id'],
                    'jurusan_id' => $data['jurusan_id'] ?? null,
                    'kelas_id' => $data['kelas_id'] ?? null,
                    'rombel_id' => $data['rombel_id'] ?? null,
                    'angkatan_id' => $data['angkatan_pelajar_id'],
                    'tanggal_masuk' => $data['tanggal_masuk_pendidikan'],
                    'status' => 'aktif',
                    'created_by' => $userId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            // --- 8. Proses santri & domisili ---
            $santriId = null;
            if (! empty($data['wilayah_id']) || (! empty($data['mondok']))) {
                $santriId = DB::table('santri')->insertGetId([
                    'biodata_id' => $biodataId,
                    'nis' => $data['nis'],
                    'tanggal_masuk' => $data['tanggal_masuk_santri'] ?? $now,
                    'angkatan_id' => $data['angkatan_santri_id'],
                    'status' => 'aktif',
                    'created_by' => $userId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            if (! empty($data['wilayah_id'])) {
                DB::table('domisili_santri')->insert([
                    'santri_id' => $santriId,
                    'wilayah_id' => $data['wilayah_id'],
                    'blok_id' => $data['blok_id'],
                    'kamar_id' => $data['kamar_id'],
                    'tanggal_masuk' => $data['tanggal_masuk_domisili'],
                    'status' => 'aktif',
                    'created_by' => $userId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            // --- 9. Proses berkas (multi file) ---
            if (! empty($data['berkas']) && is_array($data['berkas'])) {
                foreach ($data['berkas'] as $item) {
                    if (! ($item['file_path'] instanceof UploadedFile)) {
                        throw new \Exception('Berkas tidak valid');
                    }
                    $url = Storage::url($item['file_path']->store('PesertaDidik', 'public'));
                    DB::table('berkas')->insert([
                        'biodata_id' => $biodataId,
                        'jenis_berkas_id' => (int) $item['jenis_berkas_id'],
                        'file_path' => $url,
                        'status' => true,
                        'created_by' => $userId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }

            // --- 10. Catat log aktivitas (audit trail) ---
            activity('santri_registration')
                ->causedBy(Auth::user())
                ->performedOn(Biodata::find($biodataId))
                ->withProperties([
                    'biodata_id' => $biodataId,
                    'santri_id' => $santriId ?? null,
                    'no_kk' => $data['no_kk'],
                    'nik' => $data['nik'],
                    'orang_tua' => [
                        'ayah' => $data['nik_ayah'] ?? null,
                        'ibu' => $data['nik_ibu'] ?? null,
                        'wali' => $data['nik_wali'] ?? null,
                    ],
                    'pendidikan' => ! empty($data['lembaga_id']) ? [
                        'lembaga_id' => $data['lembaga_id'],
                        'jurusan_id' => $data['jurusan_id'] ?? null,
                        'kelas_id' => $data['kelas_id'] ?? null,
                        'rombel_id' => $data['rombel_id'] ?? null,
                        'tanggal_masuk' => $data['tanggal_masuk_pendidikan'],
                    ] : null,
                    'domisili_santri' => ! empty($data['wilayah_id']) ? [
                        'wilayah_id' => $data['wilayah_id'],
                        'blok_id' => $data['blok_id'],
                        'kamar_id' => $data['kamar_id'],
                        'tanggal_masuk' => $data['tanggal_masuk_domisili'],
                    ] : null,
                    'berkas' => collect($data['berkas'] ?? [])->pluck('jenis_berkas_id'),
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ])
                ->event('create_anak_pegawai')
                ->log('Pendaftaran anak pegawai baru beserta orang tua, wali, keluarga, dan berkas berhasil disimpan.');

            // --- 11. Return hasil ---
            return [
                'biodata_diri' => $biodataId,
                'santri_id' => $santriId ?? null,
            ];
        });
    }

    public function getExportAnakPegawaiQuery($fields, $request)
    {
        $query = $this->baseAnakPegawaiQuery($request);

        // Join dinamis: tambah join sesuai field export, alias dikasih angka semua
        if (in_array('alamat', $fields)) {
            $query->leftJoin('kecamatan as kc2', 'b.kecamatan_id', '=', 'kc2.id');
            $query->leftJoin('kabupaten as kb2', 'b.kabupaten_id', '=', 'kb2.id');
            $query->leftJoin('provinsi as pv2', 'b.provinsi_id', '=', 'pv2.id');
            $query->leftJoin('negara as ng2', 'b.negara_id', '=', 'ng2.id');
        }
        if (in_array('domisili_santri', $fields)) {
            $query->leftJoin('blok as bl2', 'ds.blok_id', '=', 'bl2.id');
            $query->leftJoin('kamar as km2', 'ds.kamar_id', '=', 'km2.id');
        }
        if (in_array('angkatan_santri', $fields)) {
            $query->leftJoin('angkatan as as2', 's.angkatan_id', '=', 'as2.id');
        }
        if (in_array('angkatan_pelajar', $fields)) {
            $query->leftJoin('angkatan as ap2', 'pd.angkatan_id', '=', 'ap2.id');
        }
        if (in_array('jurusan', $fields)) {
            $query->leftJoin('jurusan AS j2', 'pd.jurusan_id', '=', 'j2.id');
        }
        if (in_array('kelas', $fields)) {
            $query->leftJoin('kelas AS kls2', 'pd.kelas_id', '=', 'kls2.id');
        }
        if (in_array('rombel', $fields)) {
            $query->leftJoin('rombel AS r2', 'pd.rombel_id', '=', 'r2.id');
        }
        if (in_array('ibu_kandung', $fields)) {
            $subIbu = DB::table('keluarga as k1')
                ->select('k1.no_kk', 'otw2.id_biodata as id_biodata_ibu')
                ->join('orang_tua_wali as otw2', 'otw2.id_biodata', '=', 'k1.id_biodata')
                ->join('hubungan_keluarga as hk2', function ($join) {
                    $join->on('otw2.id_hubungan_keluarga', '=', 'hk2.id')
                        ->where('hk2.nama_status', '=', 'ibu kandung');
                });
            $query->leftJoinSub($subIbu, 'ibu2', function ($join) {
                $join->on('k.no_kk', '=', 'ibu2.no_kk');
            });
            $query->leftJoin('biodata as b_ibu2', 'ibu2.id_biodata_ibu', '=', 'b_ibu2.id');
        }

        if (in_array('ayah_kandung', $fields)) {
            $subAyah = DB::table('keluarga as k1')
                ->select('k1.no_kk', 'otw2.id_biodata as id_biodata_ayah')
                ->join('orang_tua_wali as otw2', 'otw2.id_biodata', '=', 'k1.id_biodata')
                ->join('hubungan_keluarga as hk2', function ($join) {
                    $join->on('otw2.id_hubungan_keluarga', '=', 'hk2.id')
                        ->where('hk2.nama_status', '=', 'ayah kandung');
                });
            $query->leftJoinSub($subAyah, 'ayah2', function ($join) {
                $join->on('k.no_kk', '=', 'ayah2.no_kk');
            });
            $query->leftJoin('biodata as b_ayah2', 'ayah2.id_biodata_ayah', '=', 'b_ayah2.id');
        }

        // Mapping select sesuai $fields, sesuaikan alias yang baru!
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
                    $select[] = 'j2.nama_jurusan as jurusan';
                    break;
                case 'kelas':
                    $select[] = 'kls2.nama_kelas as kelas';
                    break;
                case 'rombel':
                    $select[] = 'r2.nama_rombel as rombel';
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
                    $select[] = 'b.anak_keberapa';
                    break;
                case 'jumlah_saudara':
                    $select[] = 'b.dari_saudara';
                    break;
                case 'alamat':
                    $select[] = 'b.jalan';
                    $select[] = 'kc2.nama_kecamatan';
                    $select[] = 'kb2.nama_kabupaten';
                    $select[] = 'pv2.nama_provinsi';
                    $select[] = 'ng2.nama_negara';
                    break;
                case 'domisili_santri':
                    $select[] = 'w.nama_wilayah as dom_wilayah';
                    $select[] = 'bl2.nama_blok as dom_blok';
                    $select[] = 'km2.nama_kamar as dom_kamar';
                    break;
                case 'angkatan_santri':
                    $select[] = 'as2.angkatan as angkatan_santri';
                    break;
                case 'angkatan_pelajar':
                    $select[] = 'ap2.angkatan as angkatan_pelajar';
                    break;
                case 'status':
                    $select[] = DB::raw(
                        "CASE 
                            WHEN s.status = 'aktif' AND pd.status = 'aktif' THEN 'santri-pelajar'
                            WHEN s.status = 'aktif' THEN 'santri'
                            WHEN pd.status = 'aktif' THEN 'pelajar'
                            ELSE ''
                        END as status"
                    );
                    break;
                case 'ibu_kandung':
                    $select[] = 'b_ibu2.nama as nama_ibu';
                    break;
                case 'ayah_kandung':
                    $select[] = 'b_ayah2.nama as nama_ayah';
                    break;
            }
        }

        $groupBy = ['b.id']; // selalu group by id utama

        foreach ($fields as $field) {
            switch ($field) {
                case 'nama':
                    $groupBy[] = 'b.nama';
                    break;
                case 'tempat_lahir':
                    $groupBy[] = 'b.tempat_lahir';
                    break;
                case 'tanggal_lahir':
                    $groupBy[] = 'b.tanggal_lahir';
                    break;
                case 'jenis_kelamin':
                    $groupBy[] = 'b.jenis_kelamin';
                    break;
                case 'nis':
                    $groupBy[] = 's.nis';
                    break;
                case 'no_induk':
                    $groupBy[] = 'pd.no_induk';
                    break;
                case 'lembaga':
                    $groupBy[] = 'l.nama_lembaga';
                    break;
                case 'jurusan':
                    $groupBy[] = 'j2.nama_jurusan';
                    break;
                case 'kelas':
                    $groupBy[] = 'kls2.nama_kelas';
                    break;
                case 'rombel':
                    $groupBy[] = 'r2.nama_rombel';
                    break;
                case 'no_kk':
                    $groupBy[] = 'k.no_kk';
                    break;
                // case 'nik' tidak bisa di-group karena COALESCE/alias, skip
                case 'niup':
                    $groupBy[] = 'wp.niup';
                    break;
                case 'anak_ke':
                    $groupBy[] = 'b.anak_keberapa';
                    break;
                case 'jumlah_saudara':
                    $groupBy[] = 'b.dari_saudara';
                    break;
                case 'alamat':
                    $groupBy[] = 'b.jalan';
                    $groupBy[] = 'kc2.nama_kecamatan';
                    $groupBy[] = 'kb2.nama_kabupaten';
                    $groupBy[] = 'pv2.nama_provinsi';
                    $groupBy[] = 'ng2.nama_negara';
                    break;
                case 'domisili_santri':
                    $groupBy[] = 'w.nama_wilayah';
                    $groupBy[] = 'bl2.nama_blok';
                    $groupBy[] = 'km2.nama_kamar';
                    break;
                case 'angkatan_santri':
                    $groupBy[] = 'as2.angkatan';
                    break;
                case 'angkatan_pelajar':
                    $groupBy[] = 'ap2.angkatan';
                    break;
                case 'status':
                    $groupBy[] = 's.status';
                    $groupBy[] = 'pd.status';
                    break;
                // 'status' dan field yang pakai CASE/alias, skip group by (SQL tidak butuh)
                case 'ibu_kandung':
                    $groupBy[] = 'b_ibu2.nama';
                    break;
                case 'ayah_kandung':
                    $groupBy[] = 'b_ayah2.nama';
                    break;
            }
        }

        // Pastikan tidak ada duplikat kolom di group by
        $groupBy = array_unique($groupBy);

        return $query->select($select)->groupBy($groupBy);
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
                        if (strtolower($jk) === 'l') {
                            $data['Jenis Kelamin'] = 'Laki-laki';
                        } elseif (strtolower($jk) === 'p') {
                            $data['Jenis Kelamin'] = 'Perempuan';
                        } else {
                            $data['Jenis Kelamin'] = '';
                        }
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
                        $data['Jalan'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        $data['Kecamatan'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        $data['Kabupaten'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        $data['Provinsi'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        $data['Negara'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        break;
                    case 'domisili_santri':
                        $data['Wilayah Domisili'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        $data['Blok Domisili'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
                        $data['Kamar Domisili'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
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
                    case 'ayah_kandung':
                        $data['Ayah Kandung'] = $itemArr[array_keys($itemArr)[$i++]] ?? '';
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
            'alamat' => ['Jalan', 'Kecamatan', 'Kabupaten', 'Provinsi', 'Negara'],
            'domisili_santri' => ['Wilayah Domisili', 'Blok Domisili', 'Kamar Domisili'],
            'angkatan_santri' => 'Angkatan Santri',
            'angkatan_pelajar' => 'Angkatan Pelajar',
            'status' => 'Status',
            'ibu_kandung' => 'Ibu Kandung',
            'ayah_kandung' => 'Ayah Kandung',
        ];
        $headings = [];
        foreach ($fields as $field) {
            if (isset($map[$field])) {
                if (is_array($map[$field])) {
                    foreach ($map[$field] as $h) {
                        $headings[] = $h;
                    }
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
