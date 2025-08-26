<?php

namespace Database\Seeders;

use App\Models\RekapTahfidz;
use App\Models\Santri;
use App\Models\Tahfidz;
use App\Models\TahunAjaran;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TahfidzSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $santriAktif = Santri::where('status', 'aktif')->get();
        $tahunAjaranId = TahunAjaran::inRandomOrder()->value('id') ?? 1;

        // Daftar surat beserta jumlah ayat
        $suratList = [
            'Al-Fatihah' => 7,
            'Al-Baqarah' => 286,
            'Ali Imran'  => 200,
            'An-Nisa'    => 176,
            'Al-Maidah'  => 120,
            'Al-Anam'    => 165,
            'Al-Araf'    => 206,
            'Al-Anfal'   => 75,
            'At-Taubah'  => 129,
            'Yunus'      => 109,
        ];

        foreach ($santriAktif as $santri) {
            $existing = Tahfidz::where([
                'santri_id' => $santri->id,
                'tahun_ajaran_id' => $tahunAjaranId
            ])->exists();

            if (! $existing) {
                // Pilih surat acak
                $surat = array_rand($suratList);
                $ayatMulai = rand(1, $suratList[$surat] - 5);
                $ayatSelesai = min($suratList[$surat], $ayatMulai + rand(1, 5));
                $status = ['proses', 'tuntas'][rand(0, 1)];
                $nilai = ['lancar', 'cukup', 'kurang'][rand(0, 2)];

                // Insert setoran tahfidz
                Tahfidz::create([
                    'santri_id'       => $santri->id,
                    'tahun_ajaran_id' => $tahunAjaranId,
                    'tanggal'         => now()->subDays(rand(0, 30))->format('Y-m-d'),
                    'jenis_setoran'   => ['baru', 'murojaah'][rand(0, 1)],
                    'surat'           => $surat,
                    'ayat_mulai'      => $ayatMulai,
                    'ayat_selesai'    => $ayatSelesai,
                    'nilai'           => $nilai,
                    'catatan'         => rand(0, 1) ? 'Setoran lancar' : null,
                    'status'          => $status,
                    'created_by'      => 1,
                ]);

                // Hitung rekap
                $totalSurat = ($status === 'tuntas') ? 1 : 0;
                $persentase = ($totalSurat / 114) * 100;
                $suratTersisa = 114 - $totalSurat;
                $sisaPersentase = 100 - $persentase;

                $totalAyat = ($status === 'tuntas') ? ($ayatSelesai - $ayatMulai + 1) : 0;
                $persentaseAyat = ($totalAyat / 6236) * 100;

                $jumlahSetoran = 1;
                $rataRataNilai = $this->convertNilaiToNumber($nilai);

                $tanggalMulai = now()->format('Y-m-d');
                $tanggalSelesai = ($totalSurat >= 114) ? now()->format('Y-m-d') : null;

                RekapTahfidz::updateOrCreate(
                    [
                        'santri_id'       => $santri->id,
                    ],
                    [
                        'total_surat'        => $totalSurat,
                        'persentase_khatam'  => number_format($persentase, 2, '.', ''),
                        'surat_tersisa'      => $suratTersisa,
                        'sisa_persentase'    => number_format($sisaPersentase, 2, '.', ''),
                        'jumlah_setoran'     => $jumlahSetoran,
                        'rata_rata_nilai'    => number_format($rataRataNilai, 2, '.', ''),
                        'tanggal_mulai'      => $tanggalMulai,
                        'tanggal_selesai'    => $tanggalSelesai,
                        'created_by'         => 1,
                        'updated_by'         => 1,
                        'updated_at'         => now(),
                    ]
                );
            }
        }
    }

    /**
     * Konversi nilai string ke angka untuk rata-rata.
     */
    private function convertNilaiToNumber($nilai)
    {
        return match ($nilai) {
            'lancar' => 100,
            'cukup'  => 75,
            'kurang' => 50,
            default  => 0,
        };
    }
}
