<?php

namespace App\Services\PesertaDidik\Fitur;

use App\Models\Santri;
use App\Models\DomisiliSantri;
use App\Models\RiwayatDomisili;
use App\Models\Kewilayahan\Blok;
use App\Models\Kewilayahan\Kamar;
use Illuminate\Support\Facades\DB;
use App\Models\Kewilayahan\Wilayah;
use Illuminate\Support\Facades\Auth;

class PindahKamarService
{
    public function pindah(array $data)
    {
        $now = now();
        $userId = Auth::id();
        $santriIds = $data['santri_id'];

        $namaSantriList = Santri::whereIn('id', $santriIds)
            ->with('biodata:id,nama,jenis_kelamin')
            ->get()
            ->keyBy('id');

        $domisiliAktif = DomisiliSantri::whereIn('santri_id', $santriIds)
            ->get()
            ->keyBy('santri_id');

        // Ambil data wilayah, blok, kamar beserta kategori
        $wilayahBaru = Wilayah::find($data['wilayah_id']);
        $blokBaru = Blok::find($data['blok_id']);
        $kamarBaru = Kamar::find($data['kamar_id']);

        // ==== VALIDASI SEMUA DATA DULU ====
        foreach ($santriIds as $santriId) {
            $santri = $namaSantriList->get($santriId);
            $domisili = $domisiliAktif->get($santriId);
            $nama = $santri ? $santri->biodata->nama : 'Tidak diketahui';
            $jenisKelamin = strtolower($santri->biodata->jenis_kelamin ?? '');

            // 1. Domisili aktif harus ada
            if (is_null($domisili)) {
                return [
                    'success' => false,
                    'message' => "Proses pindah domisili dibatalkan. Santri <b>$nama</b> tidak memiliki domisili aktif.<br>
                Silakan periksa kembali data domisili.",
                    'data_baru' => [],
                ];
            }

            // ✅ Tambahan: jika wilayah berubah, cek wali asuh & grup aktif
            if ($domisili->wilayah_id != $wilayahBaru->id) {
                $waliAsuh = DB::table('wali_asuh')
                    ->where('id_santri', $domisili->santri_id)
                    ->where('status', true)
                    ->first();

                if ($waliAsuh) {
                    $punyaGrupAktif = DB::table('grup_wali_asuh')
                        ->where('wali_asuh_id', $waliAsuh->id)
                        ->where('status', true)
                        ->exists();

                    if ($punyaGrupAktif) {
                        return [
                            'success' => false,
                            'message' => "Proses pindah domisili dibatalkan. Santri <b>$nama</b> masih terdaftar sebagai wali asuh di grup aktif.<br>
                       Mohon keluarkan dari grup lalu aktifkan kembali di wilayah yang sesuai.",
                            'data_baru' => [],
                        ];
                    }
                }
            }

            // 2. Validasi kategori wilayah
            $kategoriWilayah = strtolower($wilayahBaru->kategori ?? '');
            $genderSantri = $jenisKelamin === 'l' ? 'putra' : ($jenisKelamin === 'p' ? 'putri' : '');
            if ($genderSantri && $kategoriWilayah && $genderSantri !== $kategoriWilayah) {
                return [
                    'success' => false,
                    'message' => "Proses pindah domisili dibatalkan. Santri <b>$nama</b> dengan jenis kelamin <b>$genderSantri</b> tidak sesuai dengan kategori wilayah <b>$kategoriWilayah</b>.<br>
                Silakan pilih wilayah yang sesuai.",
                    'data_baru' => [],
                ];
            }

            // 3. Validasi blok sesuai wilayah
            if ($blokBaru->wilayah_id != $wilayahBaru->id) {
                return [
                    'success' => false,
                    'message' => "Proses pindah domisili dibatalkan. Blok yang dipilih tidak sesuai dengan wilayah <b>$nama</b>.<br>
                Silakan pilih blok yang sesuai dengan wilayah.",
                    'data_baru' => [],
                ];
            }

            // 4. Validasi kamar sesuai blok
            if ($kamarBaru->blok_id != $blokBaru->id) {
                return [
                    'success' => false,
                    'message' => "Proses pindah domisili dibatalkan. Kamar yang dipilih tidak sesuai dengan blok pada santri <b>$nama</b>.<br>
                Silakan pilih kamar yang sesuai dengan blok.",
                    'data_baru' => [],
                ];
            }

            // 5. Validasi kapasitas kamar
            $jumlahPenghuni = DomisiliSantri::where('kamar_id', $kamarBaru->id)
                ->where('status', 'aktif')
                ->count();
            $kapasitasKamar = $kamarBaru->kapasitas ?? 0;
            if ($kapasitasKamar > 0 && $jumlahPenghuni >= $kapasitasKamar) {
                return [
                    'success' => false,
                    'message' => "Proses pindah domisili dibatalkan. Kamar untuk santri <b>$nama</b> sudah penuh.<br>
                Silakan pilih kamar lain yang masih tersedia.",
                    'data_baru' => [],
                ];
            }
        }

        // ==== SEMUA VALID, LAKUKAN PINDAH ====
        $dataBaruNama = [];

        DB::beginTransaction();
        try {
            foreach ($santriIds as $santriId) {
                $santri = $namaSantriList->get($santriId);
                $domisili = $domisiliAktif->get($santriId);
                $nama = $santri ? $santri->biodata->nama : 'Tidak diketahui';

                // Simpan riwayat lama
                RiwayatDomisili::create([
                    'santri_id' => $domisili->santri_id,
                    'wilayah_id' => $domisili->wilayah_id,
                    'blok_id' => $domisili->blok_id,
                    'kamar_id' => $domisili->kamar_id,
                    'status' => 'pindah',
                    'tanggal_masuk' => $domisili->tanggal_masuk,
                    'tanggal_keluar' => $now,
                    'created_by' => $domisili->created_by,
                    'created_at' => $domisili->created_at,
                    'updated_by' => $userId,
                    'updated_at' => $now,
                ]);

                // Update domisili aktif
                $domisili->update([
                    'wilayah_id' => $wilayahBaru->id,
                    'blok_id' => $blokBaru->id,
                    'kamar_id' => $kamarBaru->id,
                    'tanggal_masuk' => $now,
                    'updated_by' => $userId,
                    'updated_at' => $now,
                ]);

                $dataBaruNama[] = [
                    'nama' => $nama,
                    'message' => 'Berhasil dipindahkan.',
                ];
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan saat memindahkan domisili.',
                'data_baru' => [],
            ];
        }

        return [
            'success' => true,
            'message' => 'Santri berhasil dipindahkan ke domisili baru.',
            'data_baru' => $dataBaruNama,
        ];
    }
    // public function pindah(array $data)
    // {
    //     $now = now();
    //     $userId = Auth::id();
    //     $santriIds = $data['santri_id'];

    //     $namaSantriList = Santri::whereIn('id', $santriIds)
    //         ->with('biodata:id,nama,jenis_kelamin')
    //         ->get()
    //         ->keyBy('id');

    //     $domisiliAktif = DomisiliSantri::whereIn('santri_id', $santriIds)
    //         ->get()
    //         ->keyBy('santri_id');

    //     // Ambil data wilayah, blok, kamar beserta kategori
    //     $wilayahBaru = Wilayah::find($data['wilayah_id']);
    //     $blokBaru = Blok::find($data['blok_id']);
    //     $kamarBaru = Kamar::find($data['kamar_id']);

    //     // ==== VALIDASI SEMUA DATA DULU ====
    //     foreach ($santriIds as $santriId) {
    //         $santri = $namaSantriList->get($santriId);
    //         $domisili = $domisiliAktif->get($santriId);
    //         $nama = $santri ? $santri->biodata->nama : 'Tidak diketahui';
    //         $jenisKelamin = strtolower($santri->biodata->jenis_kelamin ?? '');

    //         // 1. Domisili aktif harus ada
    //         if (is_null($domisili)) {
    //             return [
    //                 'success' => false,
    //                 'message' => "Proses pindah domisili dibatalkan. Santri <b>$nama</b> tidak memiliki domisili aktif.<br>
    //                 Silakan periksa kembali data domisili.",
    //                 'data_baru' => [],
    //             ];
    //         }

    //         // 2. Validasi kategori wilayah
    //         $kategoriWilayah = strtolower($wilayahBaru->kategori ?? '');
    //         $genderSantri = $jenisKelamin === 'l' ? 'putra' : ($jenisKelamin === 'p' ? 'putri' : '');
    //         if ($genderSantri && $kategoriWilayah && $genderSantri !== $kategoriWilayah) {
    //             return [
    //                 'success' => false,
    //                 'message' => "Proses pindah domisili dibatalkan. Santri <b>$nama</b> dengan jenis kelamin <b>$genderSantri</b> tidak sesuai dengan kategori wilayah <b>$kategoriWilayah</b>.<br>
    //                 Silakan pilih wilayah yang sesuai.",
    //                 'data_baru' => [],
    //             ];
    //         }

    //         // 3. Validasi blok sesuai wilayah
    //         if ($blokBaru->wilayah_id != $wilayahBaru->id) {
    //             return [
    //                 'success' => false,
    //                 'message' => "Proses pindah domisili dibatalkan. Blok yang dipilih tidak sesuai dengan wilayah <b>$nama</b>.<br>
    //                 Silakan pilih blok yang sesuai dengan wilayah.",
    //                 'data_baru' => [],
    //             ];
    //         }

    //         // 4. Validasi kamar sesuai blok
    //         if ($kamarBaru->blok_id != $blokBaru->id) {
    //             return [
    //                 'success' => false,
    //                 'message' => "Proses pindah domisili dibatalkan. Kamar yang dipilih tidak sesuai dengan blok pada santri <b>$nama</b>.<br>
    //                 Silakan pilih kamar yang sesuai dengan blok.",
    //                 'data_baru' => [],
    //             ];
    //         }

    //         // 5. Validasi kapasitas kamar
    //         $jumlahPenghuni = DomisiliSantri::where('kamar_id', $kamarBaru->id)
    //             ->where('status', 'aktif')
    //             ->count();
    //         $kapasitasKamar = $kamarBaru->kapasitas ?? 0;
    //         if ($kapasitasKamar > 0 && $jumlahPenghuni >= $kapasitasKamar) {
    //             return [
    //                 'success' => false,
    //                 'message' => "Proses pindah domisili dibatalkan. Kamar untuk santri <b>$nama</b> sudah penuh.<br>
    //                 Silakan pilih kamar lain yang masih tersedia.",
    //                 'data_baru' => [],
    //             ];
    //         }
    //     }

    //     // ==== SEMUA VALID, LAKUKAN PINDAH ====
    //     $dataBaruNama = [];

    //     DB::beginTransaction();
    //     try {
    //         foreach ($santriIds as $santriId) {
    //             $santri = $namaSantriList->get($santriId);
    //             $domisili = $domisiliAktif->get($santriId);
    //             $nama = $santri ? $santri->biodata->nama : 'Tidak diketahui';

    //             // Simpan riwayat lama
    //             RiwayatDomisili::create([
    //                 'santri_id' => $domisili->santri_id,
    //                 'wilayah_id' => $domisili->wilayah_id,
    //                 'blok_id' => $domisili->blok_id,
    //                 'kamar_id' => $domisili->kamar_id,
    //                 'status' => 'pindah',
    //                 'tanggal_masuk' => $domisili->tanggal_masuk,
    //                 'tanggal_keluar' => $now,
    //                 'created_by' => $domisili->created_by,
    //                 'created_at' => $domisili->created_at,
    //                 'updated_by' => $userId,
    //                 'updated_at' => $now,
    //             ]);

    //             // Update domisili aktif
    //             $domisili->update([
    //                 'wilayah_id' => $wilayahBaru->id,
    //                 'blok_id' => $blokBaru->id,
    //                 'kamar_id' => $kamarBaru->id,
    //                 'tanggal_masuk' => $now,
    //                 'updated_by' => $userId,
    //                 'updated_at' => $now,
    //             ]);

    //             $dataBaruNama[] = [
    //                 'nama' => $nama,
    //                 'message' => 'Berhasil dipindahkan.',
    //             ];
    //         }

    //         DB::commit();
    //     } catch (\Exception $e) {
    //         DB::rollBack();

    //         return [
    //             'success' => false,
    //             'message' => 'Terjadi kesalahan saat memindahkan domisili.',
    //             'data_baru' => [],
    //         ];
    //     }

    //     return [
    //         'success' => true,
    //         'message' => 'Santri berhasil dipindahkan ke domisili baru.',
    //         'data_baru' => $dataBaruNama,
    //     ];
    // }
}
