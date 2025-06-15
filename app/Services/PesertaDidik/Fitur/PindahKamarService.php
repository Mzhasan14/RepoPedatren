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

        $dataBaruNama = [];
        $dataGagal = [];

        DB::beginTransaction();
        try {
            foreach ($santriIds as $santriId) {
                $santri = $namaSantriList->get($santriId);
                $domisili = $domisiliAktif->get($santriId);
                $nama = $santri ? $santri->biodata->nama : 'Tidak diketahui';
                $jenisKelamin = strtolower($santri->biodata->jenis_kelamin ?? '');

                if (is_null($domisili)) {
                    $dataGagal[] = [
                        'nama' => $nama,
                        'message' => 'Data domisili aktif tidak ditemukan.',
                    ];
                    continue;
                }

                // ========== VALIDASI JENIS KELAMIN & KATEGORI ==========

                $kategoriWilayah = strtolower($wilayahBaru->kategori ?? '');
                if (
                    ($jenisKelamin === 'l' && $kategoriWilayah !== 'putra') ||
                    ($jenisKelamin === 'p' && $kategoriWilayah !== 'putri')
                ) {
                    $dataGagal[] = [
                        'nama' => $nama,
                        'message' => 'Jenis kelamin santri tidak sesuai dengan kategori wilayah.',
                    ];
                    continue;
                }

                if ($blokBaru->wilayah_id != $wilayahBaru->id) {
                    $dataGagal[] = [
                        'nama' => $nama,
                        'message' => 'Blok tidak sesuai dengan wilayah yang dipilih.',
                    ];
                    continue;
                }

                if ($kamarBaru->blok_id != $blokBaru->id) {
                    $dataGagal[] = [
                        'nama' => $nama,
                        'message' => 'Kamar tidak sesuai dengan blok yang dipilih.',
                    ];
                    continue;
                }

                // cek kapasitas kamar
                $jumlahPenghuni = DomisiliSantri::where('kamar_id', $kamarBaru->id)
                    ->where('status', 'aktif')
                    ->count();
                $kapasitasKamar = $kamarBaru->kapasitas ?? 0;
                if ($kapasitasKamar > 0 && $jumlahPenghuni >= $kapasitasKamar) {
                    $dataGagal[] = [
                        'nama' => $nama,
                        'message' => 'Kamar sudah penuh, kapasitas maksimum telah tercapai.',
                    ];
                    continue;
                }

                // ========== END VALIDASI ==========

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
                'error' => $e->getMessage(),
            ];
        }

        return [
            'success' => true,
            'message' => 'Santri berhasil dipindahkan ke domisili baru.',
            'data_baru' => $dataBaruNama,
            'data_gagal' => $dataGagal,
        ];
    }
}
