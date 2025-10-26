<?php

namespace App\Imports;

use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SantriImport implements ToCollection, WithHeadingRow
{
    protected $userId;

    public function headingRow(): int
    {
        return 2; // header ada di baris 2
    }

    public function __construct($userId)
    {
        $this->userId = $userId;
    }

    protected function parseDate($value)
    {
        // 🔹 Jika kosong, null, atau hanya berisi spasi
        if (empty($value) || trim($value) === '') {
            return null;
        }

        try {
            // 🔹 Jika berupa angka (format tanggal Excel, misal 40461)
            if (is_numeric($value)) {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)
                    ->format('Y-m-d');
            }

            // 🔹 Jika string (misalnya "20/09/2024" atau "2024-09-20")
            $value = trim((string)$value);

            // 🔹 Coba parse ke tanggal menggunakan Carbon
            $date = \Carbon\Carbon::parse($value);

            // Validasi hasil — pastikan bukan tanggal default Carbon
            return $date->format('Y-m-d');
        } catch (\Exception $e) {
            // 🔹 Kalau gagal parse (format aneh, teks, dll) -> null
            return null;
        }
    }


    protected function normalizeNumberString($value)
    {
        if ($value === null || $value === '') {
            return null;
        }

        // Hapus spasi dan karakter non-digit (kecuali kalau ada 'WNA' di depan)
        $str = trim((string)$value);

        // Kalau format numeric (misal 3.51544E+15), ubah ke string tanpa notasi ilmiah
        if (is_numeric($str) && strpos($str, 'E') !== false) {
            $str = number_format($str, 0, '', '');
        }

        // Hapus karakter non-digit
        $str = preg_replace('/\D/', '', $str);

        // Kembalikan string penuh tanpa pemotongan angka
        return ltrim($str, '0') === '' ? '0' : $str;
    }

    public function collection(Collection $rows)
    {
        if ($rows->isEmpty()) {
            return;
        }

        // Filter hanya row yang punya minimal 1 kolom terisi
        $rows = $rows->filter(function ($row) {
            // Cek apakah ada kolom yang tidak kosong
            foreach ($row as $value) {
                if (trim((string) $value) !== '') {
                    return true; // ada data
                }
            }
            return false; // semua kolom kosong
        });

        if ($rows->isEmpty()) {
            return; // Tidak ada data valid
        }

        DB::beginTransaction();

        try {
            foreach ($rows as $index => $rawRow) {
                $excelRow = $index + 3; // data mulai dari baris 3 (baris 1 kosong, baris 2 header)
                $row = $this->normalizeRow($rawRow->toArray());

                $excelRow = $index + 3; // data mulai dari baris 3 (baris 1 kosong, baris 2 header)
                $row = $this->normalizeRow($rawRow->toArray());

                // --- CEK KEWARGANEGARAAN ---
                $kewarganegaraan = strtoupper(trim((string)($row['kewarganegaraan'] ?? '')));
                if (empty($kewarganegaraan)) {
                    throw new \Exception("Kolom 'Kewarganegaraan' wajib diisi di baris {$excelRow}");
                }

                if (!in_array($kewarganegaraan, ['WNI', 'WNA'])) {
                    throw new \Exception("Kolom 'Kewarganegaraan' hanya boleh berisi 'WNI' atau 'WNA' di baris {$excelRow}");
                }

                // --- CEK NIK SANTRI PALING AWAL ---
                $kewarganegaraan = strtoupper(trim((string)($row['kewarganegaraan'] ?? '')));
                if ($kewarganegaraan === 'WNI') {
                    $nik = preg_replace('/\D/', '', (string)($row['nik'] ?? ''));
                    if (empty($nik)) {
                        throw new \Exception("Kolom 'NIK' wajib diisi untuk WNI di baris {$excelRow}, kolom 'nik'.");
                    }

                    $existsNik = DB::table('biodata')
                        ->where('nik', $nik)
                        ->exists();

                    if ($existsNik) {
                        throw new \Exception("NIK '{$nik}' sudah terdaftar di baris {$excelRow}, kolom 'nik' pada file Excel.");
                    }
                }
                // --- AKHIR CEK NIK SANTRI ---

                if (empty($row['nama_lengkap'])) {
                    throw new \Exception("Kolom 'Nama Lengkap' wajib diisi di baris {$excelRow}");
                }
                if (empty($row['kewarganegaraan'])) {
                    throw new \Exception("Kolom 'Kewarganegaraan' wajib diisi di baris {$excelRow}");
                }

                $nik = $this->normalizeNumberString($row['nik'] ?? null);

                $biodataId = Str::uuid()->toString();

                $kewarganegaraan = strtoupper(trim((string)($row['kewarganegaraan'] ?? '')));
                $nik = null;
                $noPassport = null;

                if ($kewarganegaraan === 'WNI') {
                    $nik = $row['nik'] ?? null;
                } elseif ($kewarganegaraan === 'WNA') {
                    $noPassport = $row['no_passport'] ?? null;
                }

                // Sebelum insert, bersihkan dan validasi nomor telepon
                $noTelp1 = $row['no_telp_1'] ?? null;
                $noTelp2 = $row['no_telp_2'] ?? null;

                // Fungsi helper sederhana
                $formatNomor = function ($nomor) {
                    if (!$nomor) return null;

                    // Hapus spasi dan karakter non-digit kecuali '+'
                    $nomor = preg_replace('/[^0-9+]/', '', trim($nomor));

                    // Jika karakter pertama bukan +, 0, atau 6 maka tambahkan 0 di depan
                    if (!preg_match('/^[+06]/', $nomor)) {
                        $nomor = '0' . $nomor;
                    }

                    return $nomor;
                };

                $noTelp1 = $formatNomor($noTelp1);
                $noTelp2 = $formatNomor($noTelp2);

                $jenisKelaminInput = strtolower(trim($row['jenis_kelamin'] ?? ''));

                if (in_array($jenisKelaminInput, ['l', 'laki-laki', 'laki laki'])) {
                    $jenisKelamin = 'l';
                } elseif (in_array($jenisKelaminInput, ['p', 'perempuan'])) {
                    $jenisKelamin = 'p';
                } else {
                    $jenisKelamin = null; // jika tidak dikenali, biarkan null
                }

                // Baru lakukan insert
                DB::table('biodata')->insert([
                    'id' => $biodataId,
                    'nama' => $row['nama_lengkap'] ?? null,
                    'nik' => $nik,
                    'no_passport' => $noPassport,
                    'jenis_kelamin' => $jenisKelamin,
                    'tempat_lahir' => $row['tempat_lahir'] ?? null,
                    'tanggal_lahir' => $this->parseDate($row['tanggal_lahir'] ?? null),
                    'anak_keberapa' => $row['anak_keberapa'] ?? null,
                    'dari_saudara' => $row['dari_berapa_saudara'] ?? null,
                    'tinggal_bersama' => $row['tinggal_bersama'] ?? null,
                    'jenjang_pendidikan_terakhir' => $row['jenjang_pendidikan_terakhir'] ?? null,
                    'nama_pendidikan_terakhir' => $row['nama_pendidikan_terakhir'] ?? null,
                    'email' => $row['email'] ?? null,
                    'no_telepon' => $noTelp1,
                    'no_telepon_2' => $noTelp2,
                    'negara_id' => $this->findId('negara', $row['negara'] ?? null, $excelRow, true),
                    'provinsi_id' => $this->findId('provinsi', $row['provinsi'] ?? null, $excelRow, false),
                    'kabupaten_id' => $this->findId('kabupaten', $row['kabupaten'] ?? null, $excelRow, false),
                    'kecamatan_id' => $this->findId('kecamatan', $row['kecamatan'] ?? null, $excelRow, false),
                    'jalan' => $row['jalan'] ?? null,
                    'kode_pos' => $row['kode_pos'] ?? null,
                    'status' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                    'created_by' => $this->userId ?? 1
                ]);

                // --- di dalam foreach($rows as $index => $rawRow) setelah insert ayah & ibu ---
                $ayahBiodataId = $this->upsertOrangTuaBiodata($row, 'ayah', $excelRow);
                $ibuBiodataId  = $this->upsertOrangTuaBiodata($row, 'ibu', $excelRow);


                $this->insertOrangTuaRelation($biodataId, $ayahBiodataId, 'ayah', $row, $excelRow, false);
                $this->insertOrangTuaRelation($biodataId, $ibuBiodataId, 'ibu', $row, $excelRow, false);

                // === CEK WALi ===
                // Jika wali kosong → otomatis set ayah atau ibu sebagai wali
                if (empty($row['nik_wali']) && empty($row['nama_wali'])) {
                    if (!empty($ayahBiodataId)) {
                        // ✅ Ayah tersedia → jadikan wali
                        $waliBiodataId = $ayahBiodataId;

                        DB::table('orang_tua_wali')
                            ->where('id_biodata', $ayahBiodataId)
                            ->update(['wali' => true]);
                    } elseif (!empty($ibuBiodataId)) {
                        // ✅ Ayah tidak ada, ibu tersedia → jadikan wali
                        $waliBiodataId = $ibuBiodataId;

                        DB::table('orang_tua_wali')
                            ->where('id_biodata', $ibuBiodataId)
                            ->update(['wali' => true]);
                    } else {
                        // ❌ Ayah dan ibu dua-duanya tidak ada → biarkan kosong tanpa error
                        $waliBiodataId = null;
                    }
                }


                // ==== LOGIKA WALI ====
                $waliBiodataId = null;
                $namaAyah  = trim(strtolower($row['nama_ayah'] ?? ''));
                $namaIbu   = trim(strtolower($row['nama_ibu'] ?? ''));
                $namaWali  = trim(strtolower($row['nama_wali'] ?? ''));

                $nikAyah   = preg_replace('/\D/', '', (string)($row['nik_ayah'] ?? ''));
                $nikIbu    = preg_replace('/\D/', '', (string)($row['nik_ibu'] ?? ''));
                $nikWali   = preg_replace('/\D/', '', (string)($row['nik_wali'] ?? ''));

                // Cek kesamaan berdasarkan NIK atau nama
                $samaDenganAyah = ($nikWali && $nikAyah && $nikWali === $nikAyah) ||
                    (!$nikWali && !$nikAyah && $namaWali && $namaWali === $namaAyah);

                $samaDenganIbu  = ($nikWali && $nikIbu && $nikWali === $nikIbu) ||
                    (!$nikWali && !$nikIbu && $namaWali && $namaWali === $namaIbu);

                if ($samaDenganAyah) {
                    $waliBiodataId = $ayahBiodataId;
                    // update kolom wali = true di orang_tua_wali ayah
                    DB::table('orang_tua_wali')
                        ->where('id_biodata', $ayahBiodataId)
                        ->update(['wali' => true]);
                } elseif ($samaDenganIbu) {
                    $waliBiodataId = $ibuBiodataId;
                    // update kolom wali = true di orang_tua_wali ibu
                    DB::table('orang_tua_wali')
                        ->where('id_biodata', $ibuBiodataId)
                        ->update(['wali' => true]);
                } else {
                    // Kalau wali berbeda dari ayah & ibu, cek apakah sudah ada
                    if (!empty($nikWali)) {
                        $existing = DB::table('biodata')->where('nik', $nikWali)->select('id')->first();
                        if ($existing) {
                            $waliBiodataId = $existing->id;
                        }
                    }
                    if (!$waliBiodataId && !empty($namaWali)) {
                        $existing = DB::table('biodata')
                            ->whereRaw("LOWER(nama) = ?", [mb_strtolower($namaWali)])
                            ->select('id')
                            ->first();
                        if ($existing) {
                            $waliBiodataId = $existing->id;
                        }
                    }
                    // Jika belum ada, buat baru
                    if (!$waliBiodataId && (!empty($nikWali) || !empty($namaWali))) {
                        $waliBiodataId = $this->createBiodataForParent($row, 'wali');
                    }

                    // Insert relasi wali
                    if ($waliBiodataId) {
                        $this->insertOrangTuaRelation($biodataId, $waliBiodataId, 'wali', $row, $excelRow, true);
                    }
                }

                $noKk = $this->normalizeNumberString($row['no_kk'] ?? null);


                // --- Insert keluarga ---
                if ($kewarganegaraan === 'WNA') {
                    $angka13Digit = (string) random_int(1000000000000, 9999999999999);
                    $generatedNoKK = 'WNA' . $angka13Digit;
                } else {
                    $generatedNoKK = $noKk ?? null;
                }

                // Santri
                if (empty($generatedNoKK)) {
                    throw new \Exception("Kolom 'Nomor KK' wajib diisi untuk WNI di baris {$excelRow}");
                }

                // $existsKeluarga = DB::table('keluarga')
                //     ->where('no_kk', $generatedNoKK)
                //     ->where('id_biodata', $biodataId)
                //     ->exists();

                // if ($existsKeluarga) {
                // }

                DB::table('keluarga')->insert([
                    'no_kk' => $generatedNoKK,
                    'id_biodata' => $biodataId,
                    'status' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                    'created_by' => $this->userId ?? 1
                ]);

                // Ayah
                // Ayah
                if (!empty($row['nik_ayah'])) {
                    // Cari berdasarkan NIK
                    $ayahBiodataId = DB::table('biodata')->where('nik', $row['nik_ayah'])->value('id');
                } elseif (!empty($row['nama_ayah'])) {
                    // Cari berdasarkan nama
                    $ayahBiodataId = DB::table('biodata')
                        ->whereRaw('LOWER(nama) = ?', [mb_strtolower($row['nama_ayah'])])
                        ->value('id');
                }

                if ($ayahBiodataId) {
                    $existsAyah = DB::table('keluarga as k')
                        ->join('biodata as b', 'b.id', '=', 'k.id_biodata')
                        ->where('k.no_kk', $generatedNoKK)
                        ->where(function ($q) use ($row) {
                            if (!empty($row['nik_ayah'])) {
                                $q->where('b.nik', $row['nik_ayah']);
                            } else {
                                $q->whereRaw('LOWER(b.nama) = ?', [mb_strtolower($row['nama_ayah'])]);
                            }
                        })
                        ->exists();

                    if (!$existsAyah) {
                        DB::table('keluarga')->insertOrIgnore([
                            'no_kk' => $generatedNoKK,
                            'id_biodata' => $ayahBiodataId,
                            'status' => true,
                            'created_at' => now(),
                            'updated_at' => now(),
                            'created_by' => $this->userId ?? 1
                        ]);
                    }
                }

                // Ibu
                if (!empty($row['nik_ibu'])) {
                    $ibuBiodataId = DB::table('biodata')->where('nik', $row['nik_ibu'])->value('id');
                } elseif (!empty($row['nama_ibu'])) {
                    $ibuBiodataId = DB::table('biodata')
                        ->whereRaw('LOWER(nama) = ?', [mb_strtolower($row['nama_ibu'])])
                        ->value('id');
                }

                if ($ibuBiodataId) {
                    $existsIbu = DB::table('keluarga as k')
                        ->join('biodata as b', 'b.id', '=', 'k.id_biodata')
                        ->where('k.no_kk', $generatedNoKK)
                        ->where(function ($q) use ($row) {
                            if (!empty($row['nik_ibu'])) {
                                $q->where('b.nik', $row['nik_ibu']);
                            } else {
                                $q->whereRaw('LOWER(b.nama) = ?', [mb_strtolower($row['nama_ibu'])]);
                            }
                        })
                        ->exists();

                    if (!$existsIbu) {
                        DB::table('keluarga')->insertOrIgnore([
                            'no_kk' => $generatedNoKK,
                            'id_biodata' => $ibuBiodataId,
                            'status' => true,
                            'created_at' => now(),
                            'updated_at' => now(),
                            'created_by' => $this->userId ?? 1
                        ]);
                    }
                }

                // Wali
                if (!empty($row['nik_wali'])) {
                    $waliBiodataId = DB::table('biodata')->where('nik', $row['nik_wali'])->value('id');
                } elseif (!empty($row['nama_wali'])) {
                    $waliBiodataId = DB::table('biodata')
                        ->whereRaw('LOWER(nama) = ?', [mb_strtolower($row['nama_wali'])])
                        ->value('id');
                }

                if ($waliBiodataId && $waliBiodataId !== ($ayahBiodataId ?? null) && $waliBiodataId !== ($ibuBiodataId ?? null)) {
                    $existsWali = DB::table('keluarga as k')
                        ->join('biodata as b', 'b.id', '=', 'k.id_biodata')
                        ->where('k.no_kk', $generatedNoKK)
                        ->where(function ($q) use ($row) {
                            if (!empty($row['nik_wali'])) {
                                $q->where('b.nik', $row['nik_wali']);
                            } else {
                                $q->whereRaw('LOWER(b.nama) = ?', [mb_strtolower($row['nama_wali'])]);
                            }
                        })
                        ->exists();

                    if (!$existsWali) {
                        DB::table('keluarga')->insertOrIgnore([
                            'no_kk' => $generatedNoKK,
                            'id_biodata' => $waliBiodataId,
                            'status' => true,
                            'created_at' => now(),
                            'updated_at' => now(),
                            'created_by' => $this->userId ?? 1
                        ]);
                    }
                }

                $mondokVal = strtolower((string)($row['status_mondok'] ?? ''));

                if ($mondokVal === '' || $mondokVal === 'iya') {
                    $angkatanId   = $this->findAngkatanId($row['angkatan_santri'] ?? null, 'santri', $excelRow);

                    // cari tahun dari tabel angkatan
                    $tahunAngkatan = null;
                    if ($angkatanId) {
                        $tahunAngkatan = DB::table('angkatan')->where('kategori', 'santri')->where('id', $angkatanId)->value('angkatan');
                    }

                    // Tentukan tanggal masuk santri
                    if (!empty($row['tanggal_masuk_santri'])) {
                        $tanggalMasukSantri = $row['tanggal_masuk_santri'];
                    } elseif ($tahunAngkatan) {
                        // default tanggal masuk pesantren → awal tahun ajaran
                        $tanggalMasukSantri = $tahunAngkatan . '-07-01';
                    } else {
                        // fallback terakhir kalau angkatan tidak ditemukan
                        $tanggalMasukSantri = now();
                    }

                    // 🔹 Ambil 2 digit terakhir tahun
                    $tahunMasuk2Digit = $tahunAngkatan ? substr($tahunAngkatan, -2) : date('y');

                    // 🔹 Cari nomor urut terakhir untuk tahun ini (berdasarkan 2 digit awal NIS)
                    $lastUrut = DB::table('santri')
                        ->whereRaw("LEFT(nis, 2) = ?", [$tahunMasuk2Digit])
                        ->select(DB::raw("MAX(RIGHT(nis, 4)) as last_urut"))
                        ->value('last_urut');

                    $nextUrut = str_pad(((int) $lastUrut) + 1, 4, '0', STR_PAD_LEFT);

                    // 🔹 Generate NIS unik
                    do {
                        $random = str_pad(rand(0, 99), 2, '0', STR_PAD_LEFT); // 2 digit random
                        $nis = $tahunMasuk2Digit . '03' . $random . $nextUrut;
                    } while (
                        DB::table('santri')->where('nis', $nis)->exists()
                    );

                    // 🔹 Insert santri
                    $santriId = DB::table('santri')->insertGetId([
                        'biodata_id'    => $biodataId,
                        'nis'           => $nis,
                        'angkatan_id'   => $angkatanId,
                        'tanggal_masuk' => $tanggalMasukSantri,
                        'status'        => 'aktif',
                        'created_at'    => now(),
                        'updated_at'    => now(),
                        'created_by'    => $this->userId ?? 1
                    ]);

                    // --- Insert Domisili Santri ---
                    if (!empty(trim($row['wilayah'] ?? ''))) {
                        try {
                            $wilayahId = $this->findId('wilayah', $row['wilayah'] ?? null, $excelRow, false);
                            $blokId    = $this->findId('blok', $row['blok'] ?? null, $excelRow, false);
                            $kamarId   = $this->findId('kamar', $row['kamar'] ?? null, $excelRow, false);

                            // Jika wilayah wajib diisi tapi tidak ditemukan
                            if (empty($wilayahId)) {
                                throw new \Exception("Data 'wilayah' tidak ditemukan di baris {$excelRow}. Nilai dari Excel: '{$row['wilayah']}'. Pastikan nama wilayah valid.");
                            }

                            // Jika blok diisi tapi tidak ditemukan
                            if (!empty($row['blok']) && empty($blokId)) {
                                throw new \Exception("Data 'blok' tidak ditemukan di baris {$excelRow}. Nilai dari Excel: '{$row['blok']}'. Pastikan nama blok valid.");
                            }

                            // Jika kamar diisi tapi tidak ditemukan
                            if (!empty($row['kamar']) && empty($kamarId)) {
                                throw new \Exception("Data 'kamar' tidak ditemukan di baris {$excelRow}. Nilai dari Excel: '{$row['kamar']}'. Pastikan nama kamar valid.");
                            }

                            DB::table('domisili_santri')->insert([
                                'santri_id'      => $santriId,
                                'wilayah_id'     => $wilayahId,
                                'blok_id'        => $blokId,
                                'kamar_id'       => $kamarId,
                                'tanggal_masuk'  => $this->parseDate($row['tanggal_masuk_domisili'] ?? now()),
                                'status'         => 'aktif',
                                'created_at'     => now(),
                                'updated_at'     => now(),
                                'created_by'     => $this->userId ?? 1,
                            ]);
                        } catch (\Exception $e) {
                            throw new \Exception("Gagal menyimpan domisili santri di baris {$excelRow}: " . $e->getMessage());
                        }
                    }
                }

                if (!empty(trim($row['lembaga'] ?? ''))) {
                    try {
                        // 🔹 Ambil ID dari master data dengan hierarki yang benar
                        $lembagaId = $this->findId('lembaga', $row['lembaga'] ?? null, $excelRow, false);

                        $jurusanId = $this->findId('jurusan', $row['jurusan'] ?? null, $excelRow, false, null, [
                            'lembaga_id' => $lembagaId,
                        ]);

                        $kelasId = $this->findId('kelas', $row['kelas'] ?? null, $excelRow, false, null, [
                            'jurusan_id' => $jurusanId,
                        ]);

                        $rombelId = $this->findId('rombel', $row['rombel'] ?? null, $excelRow, false, null, [
                            'kelas_id' => $kelasId,
                        ]);

                        $angkatanPelajarId = $this->findAngkatanId($row['angkatan_pelajar'] ?? null, 'pelajar', $excelRow, false);

                        // 🔹 Validasi ID tidak ditemukan
                        if (empty($lembagaId)) {
                            throw new \Exception("Data 'lembaga' tidak ditemukan di baris {$excelRow}. Nilai dari Excel: '{$row['lembaga']}'. Pastikan nama lembaga sesuai master data.");
                        }

                        if (!empty($row['jurusan']) && empty($jurusanId)) {
                            throw new \Exception("Data 'jurusan' tidak ditemukan di baris {$excelRow}. Nilai dari Excel: '{$row['jurusan']}' (mungkin tidak terdaftar di lembaga '{$row['lembaga']}').");
                        }

                        if (!empty($row['kelas']) && empty($kelasId)) {
                            throw new \Exception("Data 'kelas' tidak ditemukan di baris {$excelRow}. Nilai dari Excel: '{$row['kelas']}' (mungkin tidak terdaftar di jurusan '{$row['jurusan']}').");
                        }

                        if (!empty($row['rombel']) && empty($rombelId)) {
                            throw new \Exception("Data 'rombel' tidak ditemukan di baris {$excelRow}. Nilai dari Excel: '{$row['rombel']}' (mungkin tidak terdaftar di kelas '{$row['kelas']}').");
                        }

                        // 🔹 Validasi hubungan antar entitas
                        if (!empty($jurusanId)) {
                            $jurusan = DB::table('jurusan')->where('id', $jurusanId)->first();
                            if ($jurusan && $jurusan->lembaga_id != $lembagaId) {
                                throw new \Exception("Jurusan '{$row['jurusan']}' tidak termasuk dalam lembaga '{$row['lembaga']}' di baris {$excelRow}.");
                            }
                        }

                        if (!empty($kelasId)) {
                            $kelas = DB::table('kelas')->where('id', $kelasId)->first();
                            if ($kelas && $kelas->jurusan_id != $jurusanId) {
                                throw new \Exception("Kelas '{$row['kelas']}' tidak termasuk dalam jurusan '{$row['jurusan']}' di baris {$excelRow}.");
                            }
                        }

                        if (!empty($rombelId)) {
                            $rombel = DB::table('rombel')->where('id', $rombelId)->first();
                            if ($rombel && $rombel->kelas_id != $kelasId) {
                                throw new \Exception("Rombel '{$row['rombel']}' tidak termasuk dalam kelas '{$row['kelas']}' di baris {$excelRow}.");
                            }
                        }

                        // 🔹 Simpan data pendidikan
                        DB::table('pendidikan')->insert([
                            'biodata_id'    => $biodataId,
                            'no_induk'      => $row['no_induk_pendidikan'] ?? null,
                            'lembaga_id'    => $lembagaId,
                            'jurusan_id'    => $jurusanId,
                            'kelas_id'      => $kelasId,
                            'rombel_id'     => $rombelId,
                            'angkatan_id'   => $angkatanPelajarId,
                            'tanggal_masuk' => $this->parseDate($row['tanggal_masuk_pendidikan'] ?? now()),
                            'status'        => 'aktif',
                            'created_at'    => now(),
                            'updated_at'    => now(),
                            'created_by'    => $this->userId ?? 1,
                        ]);
                    } catch (\Exception $e) {
                        throw new \Exception("Gagal menyimpan pendidikan di baris {$excelRow}: " . $e->getMessage());
                    }
                }
            }

            $totalSantri = count($rows);

            $user = Auth::user();

            activity('import_santri')
                ->causedBy($user)
                ->withProperties([
                    'total_santri' => $totalSantri,
                    'ip'           => request()->ip(),
                    'user_agent'   => request()->userAgent(),
                    'timestamp'    => now(),
                ])
                ->event('import_santri')
                ->log("Import data santri dari Excel berhasil. Total {$totalSantri} santri berhasil disimpan ke sistem.");

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            $line = $excelRow ?? 'unknown';
            $msg = $e->getMessage();

            if (strpos($msg, 'Integrity constraint violation') !== false && strpos($msg, 'Duplicate entry') !== false) {
                preg_match("/Duplicate entry '([^']+)' for key '([^']+)'/", $msg, $matches);
                $duplicateValue = $matches[1] ?? 'data';
                $keyName = $matches[2] ?? 'unique key';

                if (stripos($keyName, 'santri_nis_unique') !== false) {
                    $friendlyMsg = "NIS '{$duplicateValue}' sudah terdaftar. Silakan periksa data pada baris {$line}.";
                } elseif (stripos($keyName, 'biodata_nik_unique') !== false) {
                    $friendlyMsg = "NIK '{$duplicateValue}' sudah terdaftar. Silakan periksa data pada baris {$line}.";
                } else {
                    $friendlyMsg = "Data dengan nilai '{$duplicateValue}' pada kolom unik '{$keyName}' sudah ada. Silakan cek baris {$line}.";
                }

                throw new \Exception($friendlyMsg);
            }

            if (strpos($msg, 'Cannot add or update a child row') !== false) {
                throw new \Exception("Referensi data terkait tidak ditemukan atau tidak valid di baris {$line}. Pastikan semua data referensi sudah benar.");
            }

            if (strpos($msg, 'Column') !== false && strpos($msg, 'cannot be null') !== false) {
                preg_match("/Column '([^']+)' cannot be null/", $msg, $matches);
                $col = $matches[1] ?? 'kolom penting';
                throw new \Exception("Kolom '{$col}' tidak boleh kosong. Silakan periksa data pada baris {$line}.");
            }

            throw new \Exception("Error di baris Excel: {$line} → " . $msg);
        }
    }

    protected function normalizeRow(array $raw): array
    {
        $normalized = [];
        foreach ($raw as $k => $v) {
            $key = (string)$k;
            $key = trim($key);
            $key = mb_strtolower($key);
            $key = str_replace(['*', '.', ' '], ['', '_', '_'], $key);
            $key = preg_replace('/[^a-z0-9_]/u', '', $key);
            $key = preg_replace('/_+/', '_', $key);
            $key = trim($key, '_');

            $normalized[$key] = $v;
        }
        return $normalized;
    }

    protected function upsertOrangTuaBiodata(array $row, string $tipe, int $excelRow)
    {
        $tipeLower = strtolower($tipe);
        $nikKey = "nik_{$tipeLower}";
        $namaKey = "nama_{$tipeLower}";

        $nik = !empty($row[$nikKey]) ? trim($row[$nikKey]) : null;
        $nama = !empty($row[$namaKey]) ? trim($row[$namaKey]) : null;

        if (empty($nik) && empty($nama)) {
            return null;
        }

        // ✅ Cegah ibu tertimpa ayah
        $nikAyah = preg_replace('/\D/', '', (string)($row['nik_ayah'] ?? ''));
        $nikIbu  = preg_replace('/\D/', '', (string)($row['nik_ibu'] ?? ''));
        $namaAyah = mb_strtolower(trim($row['nama_ayah'] ?? ''));
        $namaIbu  = mb_strtolower(trim($row['nama_ibu'] ?? ''));

        if ($tipeLower === 'ibu') {
            // Kalau NIK atau nama sama dengan ayah → buat baru, jangan gabung
            if (($nik && $nik === $nikAyah) || ($nama && $namaIbu === $namaAyah)) {
                $nik = $nik ? $nik . '_IBU' : null; // beri pembeda kecil
            }
        }

        // Cek apakah sudah ada berdasarkan NIK (jika unik)
        if (!empty($nik)) {
            $existing = DB::table('biodata')->where('nik', $nik)->select('id')->first();
            if ($existing) {
                return $existing->id;
            }
        }

        // Jika belum ada, buat baru
        return $this->createBiodataForParent($row, $tipeLower);
    }


    protected function createBiodataForParent(array $row, string $tipeLower)
    {
        $nikKey = "nik_{$tipeLower}";
        $namaKey = "nama_{$tipeLower}";
        $tempatKey = "tempat_lahir_{$tipeLower}";
        $tanggalKey = "tanggal_lahir_{$tipeLower}";
        $noTelpKey = "no_telp_{$tipeLower}";
        $noTelp2Key = "no_telp_2_{$tipeLower}";
        $pendidikanKey = "jenjang_pendidikan_terakhir_{$tipeLower}";

        $parentId = Str::uuid()->toString();

        DB::table('biodata')->insert([
            'id' => $parentId,
            'nama' => $row[$namaKey] ?? null,
            'nik' => $row[$nikKey] ?? null,
            'tempat_lahir' => $row[$tempatKey] ?? null,
            'tanggal_lahir' => $this->parseDate($row[$tanggalKey] ?? null),
            'no_telepon' => $row[$noTelpKey] ?? null,
            'no_telepon_2' => $row[$noTelp2Key] ?? null,
            'jenjang_pendidikan_terakhir' => $row[$pendidikanKey] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
            'created_by' => $this->userId ?? 1
        ]);

        return $parentId;
    }

    protected function insertKeluargaIfNotExists($noKK, $biodataId)
    {
        if (!$noKK || !$biodataId) {
            return;
        }

        $exists = DB::table('keluarga')
            ->where('no_kk', $noKK)
            ->where('id_biodata', $biodataId)
            ->exists();

        if (!$exists) {
            DB::table('keluarga')->insert([
                'no_kk' => $noKK,
                'id_biodata' => $biodataId,
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
                'created_by' => $this->userId ?? 1
            ]);
        }
    }


    protected function insertOrangTuaRelation(string $childBiodataId, ?string $parentBiodataId, string $tipe, array $row, int $excelRow, bool $isWali = false)
    {
        if (!$parentBiodataId) {
            return;
        }

        $namaStatus = ucfirst($tipe);
        if ($tipe !== 'wali') {
            $namaStatus .= ' kandung';
        }

        $idHubungan = DB::table('hubungan_keluarga')
            ->where('nama_status', $namaStatus)
            ->value('id');

        if (!$idHubungan) {
            throw new \Exception("Hubungan keluarga '{$tipe}' tidak ditemukan di baris {$excelRow}");
        }

        $pekerjaanKey = "pekerjaan_{$tipe}";
        $penghasilanKey = "penghasilan_{$tipe}";
        $statusWafatKey = "status_wafat_{$tipe}";

        // ✅ Cek apakah sudah ada data orang_tua_wali dengan id_biodata + id_hubungan_keluarga yang sama
        $exists = DB::table('orang_tua_wali')
            ->where('id_biodata', $parentBiodataId)
            ->where('id_hubungan_keluarga', $idHubungan)
            ->exists();

        if ($exists) {
            // Kalau sudah ada, tapi ini wali, pastikan kolom wali = true
            if ($isWali) {
                DB::table('orang_tua_wali')
                    ->where('id_biodata', $parentBiodataId)
                    ->where('id_hubungan_keluarga', $idHubungan)
                    ->update(['wali' => true, 'updated_at' => now()]);
            }
            return; // ❌ Tidak insert ulang
        }

        // ✅ Insert baru kalau belum ada
        DB::table('orang_tua_wali')->insert([
            'id_biodata' => $parentBiodataId,
            'id_hubungan_keluarga' => $idHubungan,
            'wali' => $isWali ? 1 : 0,
            'pekerjaan' => $row[$pekerjaanKey] ?? null,
            'penghasilan' => $row[$penghasilanKey] ?? null,
            'status' => isset($row[$statusWafatKey])
                ? !(in_array(strtolower((string)$row[$statusWafatKey]), ['iya', 'true', 'wafat']))
                : true,
            'created_at' => now(),
            'updated_at' => now(),
            'created_by' => $this->userId ?? 1
        ]);
    }

    protected function findId(
        string $table,
        $value,
        int $excelRow,
        bool $required = true,
        string $columnName = null,
        array $extraWhere = []
    ) {
        if (!isset($value) || trim((string)$value) === '') {
            if ($required) {
                throw new \Exception("Referensi untuk tabel '{$table}' kosong di baris {$excelRow}.");
            }
            return null;
        }

        $value = trim(mb_strtolower((string)$value));

        $columnMap = [
            'negara'    => 'nama_negara',
            'provinsi'  => 'nama_provinsi',
            'kabupaten' => 'nama_kabupaten',
            'kecamatan' => 'nama_kecamatan',
            'wilayah'   => 'nama_wilayah',
            'blok'      => 'nama_blok',
            'kamar'     => 'nama_kamar',
            'lembaga'   => 'nama_lembaga',
            'jurusan'   => 'nama_jurusan',
            'kelas'     => 'nama_kelas',
            'rombel'    => 'nama_rombel',
        ];

        $column = $columnName ?? ($columnMap[$table] ?? 'nama');

        // 🔹 Bangun query dasar
        $query = DB::table($table)->whereRaw("LOWER({$column}) = ?", [$value]);

        // 🔹 Tambahkan filter tambahan (misal: lembaga_id, jurusan_id, kelas_id)
        foreach ($extraWhere as $key => $val) {
            if (!is_null($val)) {
                $query->where($key, $val);
            }
        }

        $record = $query->first();

        if (!$record) {
            if ($required) {
                $context = collect($extraWhere)
                    ->map(fn($v, $k) => "{$k}={$v}")
                    ->implode(', ');
                $contextInfo = $context ? " (konteks: {$context})" : '';
                throw new \Exception("Data '{$value}' tidak ditemukan pada tabel '{$table}' di baris {$excelRow}{$contextInfo}.");
            }
            return null;
        }

        return $record->id ?? null;
    }


    protected function findAngkatanId($value, $kategori, int $excelRow, bool $required = true)
    {
        if (!isset($value) || trim((string)$value) === '') {
            return null;
        }

        $search = trim((string)$value);
        $record = DB::table('angkatan')
            ->whereRaw("LOWER(angkatan) = ?", [mb_strtolower($search)])
            ->where('kategori', $kategori)
            ->select('id')
            ->first();

        if (!$record) {
            return null;
        }

        return $record->id;
    }
}
