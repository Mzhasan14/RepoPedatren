<?php

namespace App\Services\Kewaliasuhan;

use App\Models\Kewaliasuhan\Anak_asuh;
use App\Models\Kewaliasuhan\Grup_WaliAsuh;
use App\Models\Kewaliasuhan\Kewaliasuhan;
use App\Models\Kewaliasuhan\Wali_asuh;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KewaliasuhanService
{
    // public function createGrup(array $data)
    // {
    //     $now = Carbon::now();
    //     $userId = Auth::id();

    //     $waliId   = $data['wali_santri_id'];          // 1 wali asuh
    //     $anakIds  = array_unique($data['anak_santri_ids']); // banyak anak
    //     $idWilayah = $data['id_wilayah'];
    //     $namaGrup  = $data['nama_grup'];

    //     $dataBaru = [
    //         'wali_asuh' => null,
    //         'grup'      => null,
    //         'anak_asuh' => [],
    //     ];
    //     $dataGagal = [];

    //     DB::beginTransaction();
    //     try {
    //         // ✅ Prefetch semua santri (wali + anak-anak)
    //         $allIds = array_merge([$waliId], $anakIds);
    //         $profilSantri = DB::table('santri as s')
    //             ->join('biodata as b', 'b.id', '=', 's.biodata_id')
    //             ->leftJoin('domisili_santri as ds', function ($join) {
    //                 $join->on('ds.santri_id', '=', 's.id')
    //                     ->where('ds.status', '=', 'aktif');
    //             })
    //             ->whereIn('s.id', $allIds)
    //             ->select(
    //                 's.id',
    //                 's.status as status_santri',
    //                 'b.nama',
    //                 'b.jenis_kelamin',
    //                 'ds.wilayah_id as domisili_wilayah'
    //             )
    //             ->get()
    //             ->keyBy('id');

    //         // ✅ Cek wali asuh valid
    //         $wali = $profilSantri->get($waliId);
    //         if (!$wali) {
    //             return [
    //                 'success' => false,
    //                 'message' => "Santri wali dengan ID {$waliId} tidak ditemukan.",
    //                 'data'    => [],
    //             ];
    //         }

    //         if (strtolower($wali->status_santri) !== 'aktif') {
    //             return [
    //                 'success' => false,
    //                 'message' => "Santri {$wali->nama} tidak aktif, tidak bisa jadi wali asuh.",
    //                 'data'    => [],
    //             ];
    //         }

    //         if (!$wali->domisili_wilayah) {
    //             return [
    //                 'success' => false,
    //                 'message' => "Santri {$wali->nama} belum memiliki wilayah aktif.",
    //                 'data'    => [],
    //             ];
    //         }

    //         if ($wali->domisili_wilayah != $idWilayah) {
    //             return [
    //                 'success' => false,
    //                 'message' => "Wilayah wali asuh {$wali->nama} tidak sesuai dengan wilayah grup.",
    //                 'data'    => [],
    //             ];
    //         }

    //         // 🚫 Cek apakah sudah aktif jadi wali/anak
    //         $waliSudahAnak = DB::table('anak_asuh')
    //             ->where('id_santri', $waliId)
    //             ->where('status', true)
    //             ->exists();

    //         $waliSudahWali = DB::table('wali_asuh')
    //             ->where('id_santri', $waliId)
    //             ->where('status', true)
    //             ->exists();

    //         if ($waliSudahAnak || $waliSudahWali) {
    //             return [
    //                 'success' => false,
    //                 'message' => "Santri {$wali->nama} sudah terdaftar sebagai wali/anak asuh aktif.",
    //                 'data'    => [],
    //             ];
    //         }

    //         // ✅ Buat wali_asuh baru
    //         $waliAsuhId = DB::table('wali_asuh')->insertGetId([
    //             'id_santri'      => $waliId,
    //             'tanggal_mulai'  => $now->toDateString(),
    //             'status'         => true,
    //             'created_by'     => $userId,
    //             'created_at'     => $now,
    //             'updated_at'     => $now,
    //         ]);

    //         // ✅ Buat grup baru
    //         $jenisKelaminGrup = strtolower($wali->jenis_kelamin);
    //         $grupId = DB::table('grup_wali_asuh')->insertGetId([
    //             'id_wilayah'    => $idWilayah,
    //             'wali_asuh_id'  => $waliAsuhId,
    //             'nama_grup'     => $namaGrup,
    //             'jenis_kelamin' => $jenisKelaminGrup,
    //             'status'        => true,
    //             'created_by'    => $userId,
    //             'created_at'    => $now,
    //             'updated_at'    => $now,
    //         ]);

    //         $dataBaru['wali_asuh'] = [
    //             'id'   => $waliAsuhId,
    //             'nama' => $wali->nama,
    //         ];
    //         $dataBaru['grup'] = [
    //             'id'   => $grupId,
    //             'nama' => $namaGrup,
    //         ];

    //         // ✅ Insert anak-anak asuh
    //         foreach ($anakIds as $anakId) {
    //             $anak = $profilSantri->get($anakId);

    //             if (!$anak) {
    //                 $dataGagal[] = [
    //                     'santri_id' => $anakId,
    //                     'message'   => "Santri dengan ID {$anakId} tidak ditemukan.",
    //                 ];
    //                 continue;
    //             }

    //             if (strtolower($anak->status_santri) !== 'aktif') {
    //                 $dataGagal[] = [
    //                     'santri_id' => $anakId,
    //                     'message'   => "Santri {$anak->nama} sudah tidak aktif.",
    //                 ];
    //                 continue;
    //             }

    //             if (!$anak->domisili_wilayah) {
    //                 $dataGagal[] = [
    //                     'santri_id' => $anakId,
    //                     'message'   => "Santri {$anak->nama} belum memiliki wilayah aktif.",
    //                 ];
    //                 continue;
    //             }

    //             if ($anak->domisili_wilayah != $idWilayah) {
    //                 $dataGagal[] = [
    //                     'santri_id' => $anakId,
    //                     'message'   => "Wilayah santri {$anak->nama} tidak sesuai dengan grup.",
    //                 ];
    //                 continue;
    //             }

    //             if (strtolower($anak->jenis_kelamin) !== $jenisKelaminGrup) {
    //                 $dataGagal[] = [
    //                     'santri_id' => $anakId,
    //                     'message'   => "Santri {$anak->nama} tidak sesuai jenis kelamin grup.",
    //                 ];
    //                 continue;
    //             }

    //             // 🚫 Cek duplikat aktif
    //             $anakSudahAnak = DB::table('anak_asuh')
    //                 ->where('id_santri', $anakId)
    //                 ->where('status', true)
    //                 ->exists();

    //             $anakSudahWali = DB::table('wali_asuh')
    //                 ->where('id_santri', $anakId)
    //                 ->where('status', true)
    //                 ->exists();

    //             if ($anakSudahAnak || $anakSudahWali) {
    //                 $dataGagal[] = [
    //                     'santri_id' => $anakId,
    //                     'message'   => "Santri {$anak->nama} sudah terdaftar sebagai anak/wali asuh aktif.",
    //                 ];
    //                 continue;
    //             }

    //             DB::table('anak_asuh')->insert([
    //                 'id_santri'         => $anakId,
    //                 'grup_wali_asuh_id' => $grupId,
    //                 'status'            => true,
    //                 'created_by'        => $userId,
    //                 'created_at'        => $now,
    //                 'updated_at'        => $now,
    //             ]);

    //             $dataBaru['anak_asuh'][] = [
    //                 'id'   => $anakId,
    //                 'nama' => $anak->nama,
    //             ];
    //         }

    //         DB::commit();

    //         return [
    //             'success'    => true,
    //             'message'    => count($dataBaru['anak_asuh']) . " anak berhasil ditambahkan, " . count($dataGagal) . " gagal.",
    //             'data_baru'  => $dataBaru,
    //             'data_gagal' => $dataGagal,
    //         ];
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return [
    //             'success' => false,
    //             'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage(),
    //             'data'    => [],
    //         ];
    //     }
    // }
    public function createGrup(array $data): array
    {
        $now = Carbon::now();
        $userId = Auth::id();

        $waliId           = $data['wali_santri_id'];                // 1 wali asuh
        $anakIds          = array_unique($data['anak_santri_ids']); // banyak anak
        $idWilayah        = $data['id_wilayah'];
        $namaGrup         = $data['nama_grup'];
        $jenisKelaminGrup = $data['jenis_kelamin']; // l/p

        $dataBaru = [
            'wali_asuh' => null,
            'grup'      => null,
            'anak_asuh' => [],
        ];
        $dataGagal = [];

        DB::beginTransaction();
        try {
            // ✅ Ambil data wilayah
            $wilayah = DB::table('wilayah')
                ->select('id', 'nama_wilayah', 'kategori')
                ->where('id', $idWilayah)
                ->where('status', true)
                ->first();

            if (!$wilayah) {
                return [
                    'status'  => false,
                    'message' => "Wilayah dengan ID {$idWilayah} tidak ditemukan atau tidak aktif.",
                ];
            }

            // ✅ Cek kecocokan kategori wilayah dengan jenis kelamin grup
            $mapping = [
                'l' => 'putra',
                'p' => 'putri',
            ];

            if (!isset($mapping[$jenisKelaminGrup]) || $wilayah->kategori !== $mapping[$jenisKelaminGrup]) {
                return [
                    'status'  => false,
                    'message' => "Kategori wilayah ({$wilayah->kategori}) tidak sesuai dengan jenis kelamin grup ({$jenisKelaminGrup}).",
                ];
            }

            // ✅ Prefetch semua santri (wali + anak-anak)
            $allIds = array_merge([$waliId], $anakIds);
            $profilSantri = DB::table('santri as s')
                ->join('biodata as b', 'b.id', '=', 's.biodata_id')
                ->leftJoin('domisili_santri as ds', function ($join) {
                    $join->on('ds.santri_id', '=', 's.id')
                        ->where('ds.status', '=', 'aktif');
                })
                ->whereIn('s.id', $allIds)
                ->select(
                    's.id',
                    's.status as status_santri',
                    'b.nama',
                    'b.jenis_kelamin',
                    'ds.wilayah_id as domisili_wilayah'
                )
                ->get()
                ->keyBy('id');

            // ✅ Validasi wali asuh
            $wali = $profilSantri->get($waliId);
            if (!$wali) {
                return [
                    'status'  => false,
                    'message' => "Santri wali dengan ID {$waliId} tidak ditemukan.",
                ];
            }

            if (strtolower($wali->status_santri) !== 'aktif') {
                return [
                    'status'  => false,
                    'message' => "Santri {$wali->nama} sudah tidak aktif, tidak bisa jadi wali asuh.",
                ];
            }

            if (!$wali->domisili_wilayah) {
                return [
                    'status'  => false,
                    'message' => "Santri {$wali->nama} belum memiliki wilayah aktif, tidak bisa jadi wali asuh.",
                ];
            }

            if ($wali->domisili_wilayah != $idWilayah) {
                return [
                    'status'  => false,
                    'message' => "Santri {$wali->nama} tidak bisa jadi wali asuh karena wilayah tidak sesuai dengan wilayah grup.",
                ];
            }

            if ($wali->jenis_kelamin !== $jenisKelaminGrup) {
                return [
                    'status'  => false,
                    'message' => "Santri {$wali->nama} tidak bisa jadi wali asuh karena jenis kelamin tidak sesuai grup.",
                ];
            }

            // 🚫 Cek apakah wali sudah aktif jadi anak asuh
            $waliSudahAnak = DB::table('anak_asuh')
                ->where('id_santri', $waliId)
                ->where('status', true)
                ->exists();

            if ($waliSudahAnak) {
                return [
                    'status'  => false,
                    'message' => "Santri {$wali->nama} yang dipilih sebagai wali asuh, sudah terdaftar sebagai anak asuh aktif.",
                ];
            }

            // 🚫 Cek apakah wali sudah aktif jadi wali asuh
            $waliSudahWali = DB::table('wali_asuh')
                ->where('id_santri', $waliId)
                ->where('status', true)
                ->exists();

            if ($waliSudahWali) {
                return [
                    'status'  => false,
                    'message' => "Santri {$wali->nama} yang dipilih sebagai wali asuh, sudah terdaftar sebagai wali asuh aktif.",
                ];
            }

            // ✅ Validasi anak-anak dulu (tanpa insert)
            foreach ($anakIds as $anakId) {
                $anak = $profilSantri->get($anakId);

                if (!$anak) {
                    $dataGagal[] = [
                        'santri_id' => $anakId,
                        'message'   => "Santri dengan ID {$anakId} tidak ditemukan.",
                    ];
                    continue;
                }

                if (strtolower($anak->status_santri) !== 'aktif') {
                    $dataGagal[] = [
                        'santri_id' => $anakId,
                        'message'   => "Santri {$anak->nama} sudah tidak aktif.",
                    ];
                    continue;
                }

                if (!$anak->domisili_wilayah) {
                    $dataGagal[] = [
                        'santri_id' => $anakId,
                        'message'   => "Santri {$anak->nama} belum memiliki wilayah aktif.",
                    ];
                    continue;
                }

                if ($anak->domisili_wilayah != $idWilayah) {
                    $dataGagal[] = [
                        'santri_id' => $anakId,
                        'message'   => "Wilayah santri {$anak->nama} tidak sesuai dengan grup.",
                    ];
                    continue;
                }

                if ($anak->jenis_kelamin !== $jenisKelaminGrup) {
                    $dataGagal[] = [
                        'santri_id' => $anakId,
                        'message'   => "Santri {$anak->nama} tidak sesuai jenis kelamin grup.",
                    ];
                    continue;
                }

                // 🚫 Cek apakah anak sudah aktif jadi anak asuh
                $anakSudahAnak = DB::table('anak_asuh')
                    ->where('id_santri', $anakId)
                    ->where('status', true)
                    ->exists();

                if ($anakSudahAnak) {
                    $dataGagal[] = [
                        'santri_id' => $anakId,
                        'message'   => "Santri {$anak->nama} sudah terdaftar sebagai anak asuh aktif.",
                    ];
                    continue;
                }

                // 🚫 Cek apakah anak sudah aktif jadi wali asuh
                $anakSudahWali = DB::table('wali_asuh')
                    ->where('id_santri', $anakId)
                    ->where('status', true)
                    ->exists();

                if ($anakSudahWali) {
                    $dataGagal[] = [
                        'santri_id' => $anakId,
                        'message'   => "Santri {$anak->nama} sudah terdaftar sebagai wali asuh aktif.",
                    ];
                    continue;
                }

                // ✅ Lolos validasi, tambahkan ke calon anak_asuh
                $dataBaru['anak_asuh'][] = [
                    'id'   => $anakId,
                    'nama' => $anak->nama,
                ];
            }

            // 🚨 Kalau tidak ada satupun anak valid → batal
            if (count($dataBaru['anak_asuh']) === 0) {
                DB::rollBack();
                return [
                    'status'  => false,
                    'message' => "Tidak ada anak asuh yang valid, pembuatan grup dibatalkan.",
                    'gagal'   => $dataGagal,
                ];
            }

            // ✅ Insert wali_asuh
            $waliAsuhId = DB::table('wali_asuh')->insertGetId([
                'id_santri'      => $waliId,
                'tanggal_mulai'  => $now->toDateString(),
                'status'         => true,
                'created_by'     => $userId,
                'created_at'     => $now,
                'updated_at'     => $now,
            ]);

            // ✅ Insert grup
            $grupId = DB::table('grup_wali_asuh')->insertGetId([
                'id_wilayah'    => $idWilayah,
                'wali_asuh_id'  => $waliAsuhId,
                'nama_grup'     => $namaGrup,
                'jenis_kelamin' => $jenisKelaminGrup,
                'status'        => true,
                'created_by'    => $userId,
                'created_at'    => $now,
                'updated_at'    => $now,
            ]);

            $dataBaru['wali_asuh'] = [
                'id'   => $waliAsuhId,
                'nama' => $wali->nama,
            ];
            $dataBaru['grup'] = [
                'id'   => $grupId,
                'nama' => $namaGrup,
            ];

            // ✅ Insert anak-anak asuh
            foreach ($dataBaru['anak_asuh'] as $anak) {
                DB::table('anak_asuh')->insert([
                    'id_santri'         => $anak['id'],
                    'grup_wali_asuh_id' => $grupId,
                    'status'            => true,
                    'created_by'        => $userId,
                    'created_at'        => $now,
                    'updated_at'        => $now,
                ]);
            }

            DB::commit();

            return [
                'status'     => true,
                'message'    => count($dataBaru['anak_asuh']) . " anak berhasil ditambahkan, " . count($dataGagal) . " gagal.",
                'grup'       => $dataBaru['grup'],
                'wali_asuh'  => $dataBaru['wali_asuh'],
                'anak_asuh'  => $dataBaru['anak_asuh'],
                'gagal'      => $dataGagal,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'status'  => false,
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage(),
            ];
        }
    }
    public function update(array $data)
    {
        $userId = Auth::id();
        $relasi = Kewaliasuhan::findOrFail($data['id']);

        DB::beginTransaction();
        try {
            $relasi->update([
                'tanggal_berakhir' => $data['tanggal_berakhir'] ?? $relasi->tanggal_berakhir,
                'status' => $data['status'] ?? $relasi->status,
                'updated_by' => $userId,
                'updated_at' => Carbon::now(),
            ]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Relasi anak asuh berhasil diperbarui.',
                'data' => $relasi,
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            return [
                'success' => false,
                'message' => 'Gagal memperbarui relasi anak asuh.',
                'error' => $e->getMessage(),
            ];
        }
    }

    public function delete($id)
    {
        $userId = Auth::id();
        $relasi = Kewaliasuhan::findOrFail($id);

        DB::beginTransaction();
        try {
            $relasi->update([
                'deleted_by' => $userId,
            ]);
            $relasi->delete();

            DB::commit();

            return [
                'success' => true,
                'message' => 'Relasi anak asuh berhasil dihapus.',
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            return [
                'success' => false,
                'message' => 'Gagal menghapus relasi anak asuh.',
                'error' => $e->getMessage(),
            ];
        }
    }
}
