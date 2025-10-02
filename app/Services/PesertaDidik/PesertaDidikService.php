<?php

namespace App\Services\PesertaDidik;

use Exception;
use App\Models\Santri;
use App\Models\Biodata;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\ImageManager;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Validation\ValidationException;
use Spatie\ImageOptimizer\OptimizerChainFactory;

class PesertaDidikService
{
    // Query builder utama, hanya berisi JOIN dan subquery, tanpa select
    public function basePesertaDidikQuery(Request $request)
    {
        $pasFotoId = DB::table('jenis_berkas')
            ->where('nama_jenis_berkas', 'Pas foto')
            ->value('id');

        $fotoLast = DB::table('berkas')
            ->select('biodata_id', DB::raw('MAX(id) AS last_id'))
            ->where('jenis_berkas_id', $pasFotoId)
            ->groupBy('biodata_id');

        $wpLast = DB::table('warga_pesantren')
            ->select('biodata_id', DB::raw('MAX(id) AS last_id'))
            ->where('status', true)
            ->groupBy('biodata_id');

        $keluargaLast = DB::table('keluarga')
            ->select('id_biodata', DB::raw('MAX(id) AS last_id'))
            ->groupBy('id_biodata');

        $query = DB::table('biodata as b')
            ->leftJoin('santri AS s', fn($j) => $j->on('b.id', '=', 's.biodata_id')->where('s.status', 'aktif'))
            ->leftJoin('pendidikan AS pd', fn($j) => $j->on('b.id', '=', 'pd.biodata_id')->where('pd.status', 'aktif'))
            ->leftJoin('lembaga AS l', 'pd.lembaga_id', '=', 'l.id')
            ->leftJoin('domisili_santri AS ds', fn($join) => $join->on('s.id', '=', 'ds.santri_id')->where('ds.status', 'aktif'))
            ->leftJoin('wilayah AS w', 'ds.wilayah_id', '=', 'w.id')
            ->leftJoinSub($fotoLast, 'fl', fn($j) => $j->on('b.id', '=', 'fl.biodata_id'))
            ->leftJoin('berkas AS br', 'br.id', '=', 'fl.last_id')
            ->leftJoinSub($wpLast, 'wl', fn($j) => $j->on('b.id', '=', 'wl.biodata_id'))
            ->leftJoin('warga_pesantren AS wp', 'wp.id', '=', 'wl.last_id')
            ->leftJoin('kabupaten AS kb', 'kb.id', '=', 'b.kabupaten_id')
            ->leftJoinSub($keluargaLast, 'kl', fn($j) => $j->on('b.id', '=', 'kl.id_biodata'))
            ->leftJoin('keluarga as k', 'k.id', '=', 'kl.last_id')
            // Tambahkan default join lain jika dibutuhkan oleh kebanyakan fitur
            ->where(fn($q) => $q->where('s.status', 'aktif')
                ->orWhere('pd.status', '=', 'aktif'))
            ->where('b.status', true)
            ->where(fn($q) => $q->whereNull('b.deleted_at')
                ->whereNull('s.deleted_at'));

        return $query;
    }

    // Query untuk LIST (select default)
    public function getAllPesertaDidik(Request $request, $fields = null)
    {
        $query = $this->basePesertaDidikQuery($request);

        // SELECT default jika tidak dikasih field custom
        $fields = $fields ?? [
            'b.id as biodata_id',
            DB::raw('COALESCE(b.nik, b.no_passport) AS identitas'),
            'b.nama',
            'wp.niup',
            'l.nama_lembaga',
            'w.nama_wilayah',
            'kb.nama_kabupaten AS kota_asal',
            's.created_at',
            DB::raw('GREATEST(
                s.updated_at,
                COALESCE(pd.updated_at, s.updated_at),
                COALESCE(ds.updated_at, s.updated_at)
            ) AS updated_at'),
            DB::raw("COALESCE(br.file_path, 'default.jpg') AS foto_profil"),
        ];

        return $query->select($fields);
    }

    public function formatData($results)
    {
        return collect($results->items())->map(fn($item) => [
            'biodata_id' => $item->biodata_id,
            'nik_or_passport' => $item->identitas,
            'nama' => $item->nama,
            'niup' => $item->niup ?? '-',
            'lembaga' => $item->nama_lembaga ?? '-',
            'wilayah' => $item->nama_wilayah ?? '-',
            'kota_asal' => $item->kota_asal,
            'tgl_update' => Carbon::parse($item->updated_at)->translatedFormat('d F Y H:i:s') ?? '-',
            'tgl_input' => Carbon::parse($item->created_at)->translatedFormat('d F Y H:i:s'),
            'foto_profil' => url($item->foto_profil),
        ]);
    }

    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {
            // Inisialisasi user dan waktu
            $userId = Auth::id();
            $now = now();

            // --- Validasi jika paspor diisi, maka negara bukan Indonesia ---
            if (! empty($data['passport'])) {
                // Pastikan negara_id ada dulu
                if (empty($data['negara_id'])) {
                    throw ValidationException::withMessages([
                        'negara_id' => ['Negara wajib dipilih jika mengisi paspor.'],
                    ]);
                }

                // Ambil data negara berdasarkan ID
                $negara = DB::table('negara')->where('id', $data['negara_id'])->first();

                // Cek jika negara tidak ditemukan (mungkin karena data di DB kosong)
                if (! $negara) {
                    throw ValidationException::withMessages([
                        'negara_id' => ['Negara tidak ditemukan di database.'],
                    ]);
                }

                // Jika negara asal adalah Indonesia, tolak pengisian paspor
                if (strtolower(trim($negara->nama_negara)) === 'indonesia') {
                    throw ValidationException::withMessages([
                        'passport' => ['Jika mengisi nomor paspor, negara asal tidak boleh Indonesia.'],
                    ]);
                }
            }


            // --- 1. VALIDASI & PROSES BIODATA ---
            $nik = $data['nik'] ?? null;
            $existingBiodata = $nik ? DB::table('biodata')->where('nik', $nik)->first() : null;

            // Cek duplikasi biodata, santri aktif, pendidikan aktif, dan domisili aktif
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

            // Siapkan data biodata
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
                // 'smartcard' => $data['smarcard'] ?? null,
                'updated_by' => $userId,
                'updated_at' => $now,
            ];

            // Simpan atau update biodata
            if ($existingBiodata) {
                DB::table('biodata')->where('id', $existingBiodata->id)->update($biodataData);
                $biodataId = $existingBiodata->id;
            } else {
                // Buat smartcard dan id unik
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

            // --- 2. VALIDASI & PROSES KELUARGA ---

            // --- Generate no_kk otomatis jika no_passport diisi dan no_kk kosong ---
            if (! isset($data['no_kk']) || empty($data['no_kk'])) {
                if (! empty($data['passport'])) {
                    do {
                        // Generate angka antara 1000000000000 (13 digit) s.d. 9999999999999
                        $angka13Digit = (string) random_int(1000000000000, 9999999999999);
                        $generatedNoKK = 'WNA' . $angka13Digit;
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

            // --- 3. PROSES AYAH & IBU SEKALIGUS ---
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

                // Update atau insert biodata ayah/ibu
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

                // Update atau insert orang tua
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

                // Pastikan ayah/ibu sudah di keluarga
                if (! DB::table('keluarga')->where('no_kk', $data['no_kk'])->where('id_biodata', $parentId)->exists()) {
                    DB::table('keluarga')->insert([
                        'id_biodata' => $parentId,
                        'no_kk' => $data['no_kk'],
                        'status' => true,
                        'created_by' => $userId,
                        'created_at' => $now,
                    ]);
                }

                // --- Cek jika orang tua adalah pegawai ---
                $pegawai = DB::table('pegawai')
                    ->select('pegawai.id as pegawai_id') // Ambil ID asli dari tabel pegawai
                    ->leftJoin('biodata as b_pegawai', 'pegawai.biodata_id', '=', 'b_pegawai.id')
                    ->where('b_pegawai.nik', $data[$nikKey] ?? null)
                    ->first();

                if ($pegawai) {
                    // Cek apakah sudah tercatat sebagai anak pegawai
                    $sudahTerdaftar = DB::table('anak_pegawai')
                        ->where('pegawai_id', $pegawai->pegawai_id)
                        ->where('biodata_id', $biodataId)
                        ->exists();

                    if (! $sudahTerdaftar) {
                        DB::table('anak_pegawai')->insert([
                            'pegawai_id' => $pegawai->pegawai_id, // ← Sekarang sudah pasti ID pegawai
                            'biodata_id' => $biodataId,
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

            $jenisKelamin = $data['jenis_kelamin'] ?? null;

            // mapping l → putra, p → putri
            $genderSantri = null;
            if ($jenisKelamin) {
                $genderSantri = strtolower($jenisKelamin) === 'l' ? 'putra' : 'putri';
            }

            // --- 5. PROSES PENDIDIKAN (JIKA ADA) ---
            if (! empty($data['lembaga_id'])) {

                // kalau ada rombel_id → cek gender_rombel
                if (! empty($data['rombel_id'])) {
                    $rombel = DB::table('rombel')
                        ->where('id', $data['rombel_id'])
                        ->first();

                    if (! $rombel) {
                        throw new \Exception('Rombel tidak ditemukan');
                    }

                    if (strtolower($rombel->gender_rombel) !== $genderSantri) {
                        throw new \Exception("Rombel hanya untuk santri {$rombel->gender_rombel}, tidak sesuai dengan jenis kelamin yang dipilih");
                    }
                }

                DB::table('pendidikan')->insert([
                    'biodata_id'    => $biodataId,
                    'no_induk'      => $data['no_induk'],
                    'lembaga_id'    => $data['lembaga_id'],
                    'jurusan_id'    => $data['jurusan_id'] ?? null,
                    'kelas_id'      => $data['kelas_id'] ?? null,
                    'rombel_id'     => $data['rombel_id'] ?? null,
                    'angkatan_id'   => $data['angkatan_pelajar_id'],
                    'tanggal_masuk' => $data['tanggal_masuk_pendidikan'],
                    'status'        => 'aktif',
                    'created_by'    => $userId,
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ]);
            }

            // --- 6. PROSES SANTRI & DOMISILI ---
            $santriId = null;
            if (! empty($data['wilayah_id']) || ! empty($data['mondok'])) {
                $angkatanId   = $data['angkatan_santri_id'] ?? null;

                // cari tahun dari tabel angkatan
                $tahunAngkatan = null;
                if ($angkatanId) {
                    $tahunAngkatan = DB::table('angkatan')->where('id', $angkatanId)->value('angkatan');
                }

                // ambil 2 digit terakhir tahun
                $tahunMasuk2Digit = $tahunAngkatan ? substr($tahunAngkatan, -2) : date('y');

                // generate nomor urut terakhir untuk angkatan ini
                $lastUrut = DB::table('santri')
                    ->where('angkatan_id', $angkatanId)
                    ->select(DB::raw("MAX(RIGHT(nis,3)) as last_urut"))
                    ->value('last_urut');

                $nextUrut = str_pad(((int) $lastUrut) + 1, 3, '0', STR_PAD_LEFT);

                // generate nis unik
                do {
                    $random = str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);
                    $nis = $tahunMasuk2Digit . '11' . $random . $nextUrut;
                } while (
                    DB::table('santri')->where('nis', $nis)->exists()
                );
                $santriId = DB::table('santri')->insertGetId([
                    'biodata_id' => $biodataId,
                    'nis' => $nis,
                    'tanggal_masuk' => $data['tanggal_masuk_santri'] ?? $now,
                    'angkatan_id' => $data['angkatan_santri_id'],
                    'status' => 'aktif',
                    'created_by' => $userId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }



            // --- Validasi WILAYAH ---
            if (! empty($data['wilayah_id'])) {
                $wilayah = DB::table('wilayah')
                    ->where('id', $data['wilayah_id'])
                    ->first();

                if (! $wilayah) {
                    throw new \Exception('Wilayah tidak ditemukan');
                }

                if (strtolower($wilayah->kategori) !== strtolower($genderSantri)) {
                    throw new \Exception("Wilayah hanya untuk santri {$wilayah->kategori}, tidak sesuai dengan jenis kelamin yang dipilih");
                }

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

            // --- Validasi PENDI

            // --- 7. PROSES BERKAS (MULTI FILE) ---
            if (! empty($data['berkas']) && is_array($data['berkas'])) {
                $maxWidth = 1200;
                $quality  = 85;

                // Gunakan ImageManager v3 dengan driver GD
                $manager = new ImageManager(new Driver());

                foreach ($data['berkas'] as $item) {
                    if (! ($item['file_path'] instanceof UploadedFile)) {
                        throw new \Exception('Berkas tidak valid');
                    }

                    try {
                        $uploaded = $item['file_path'];
                        $mime     = $uploaded->getClientMimeType() ?? '';

                        if (str_starts_with($mime, 'image/')) {
                            // Baca dan orientasikan foto
                            $img = $manager->read($uploaded->getRealPath())->orient();

                            // Resize gambar tanpa merusak aspek rasio
                            $img = $img->scale(width: $maxWidth);

                            // Konversi semua gambar ke JPEG agar lebih kecil dan konsisten
                            $encodedImage = $img->toJpeg($quality);

                            // Nama file unik
                            $filename   = time() . '_' . uniqid() . '.jpg';
                            $storedPath = 'PesertaDidik/' . $filename;

                            // Simpan ke storage/public
                            Storage::disk('public')->put($storedPath, (string) $encodedImage);

                            $url = Storage::url($storedPath);
                        } else {
                            // Kalau bukan gambar, simpan langsung tanpa resize
                            $storedPath = $uploaded->store('PesertaDidik', 'public');
                            $url        = Storage::url($storedPath);
                        }

                        // Simpan ke DB
                        DB::table('berkas')->insert([
                            'biodata_id'      => $biodataId,
                            'jenis_berkas_id' => (int) $item['jenis_berkas_id'],
                            'file_path'       => $url,
                            'status'          => true,
                            'created_by'      => $userId,
                            'created_at'      => $now,
                            'updated_at'      => $now,
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Gagal memproses berkas: ' . $e->getMessage(), [
                            'file' => $item['file_path'] ? $item['file_path']->getClientOriginalName() : null,
                        ]);
                        throw $e;
                    }
                }
            }


            // --- 8. CATAT LOG AKTIVITAS (AUDIT TRAIL) ---
            activity('registrasi_peserta_didik')
                ->causedBy(Auth::user())
                ->performedOn(Biodata::find($biodataId))
                ->withProperties([
                    'biodata_id' => $biodataId,
                    'santri_id' => $santriId ?? null,
                    'no_kk' => $data['no_kk'],
                    'nik' => $nik,
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
                ->event('create_peserta_didik')
                ->log('Pendaftaran peserta didik beserta orang tua, wali, keluarga, dan berkas berhasil disimpan.');

            // --- 9. RETURN RESULT ---
            return [
                'biodata_diri' => $biodataId,
                'santri_id' => $santriId ?? null,
            ];
        });
    }

    // Query untuk EXPORT, join dan select dinamis sesuai field
    public function getExportPesertaDidikQuery($fields, $request)
    {
        $query = $this->basePesertaDidikQuery($request);

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
            }
        }

        return $query->select($select);
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
