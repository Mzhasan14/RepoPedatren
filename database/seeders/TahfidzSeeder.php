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

        // Daftar surat beserta jumlah ayat (biar persentase khatam realistis)
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
            // Cek kalau sudah pernah diinput
            $existing = Tahfidz::where([
                'santri_id' => $santri->id,
                'tahun_ajaran_id' => $tahunAjaranId
            ])->exists();

            if (! $existing) {
                // Pilih surat acak
                $surat = array_rand($suratList);
                $ayatMulai = rand(1, $suratList[$surat] - 5);
                $ayatSelesai = min($suratList[$surat], $ayatMulai + rand(1, 5));

                Tahfidz::create([
                    'santri_id'       => $santri->id,
                    'tahun_ajaran_id' => $tahunAjaranId,
                    'tanggal'         => now()->subDays(rand(0, 30))->format('Y-m-d'),
                    'jenis_setoran'   => ['baru', 'murojaah'][rand(0, 1)],
                    'surat'           => $surat,
                    'ayat_mulai'      => $ayatMulai,
                    'ayat_selesai'    => $ayatSelesai,
                    'nilai'           => ['lancar', 'cukup', 'kurang'][rand(0, 2)],
                    'catatan'         => rand(0, 1) ? 'Setoran lancar' : null,
                    'status'          => ['proses', 'tuntas'][rand(0, 1)],
                    'created_by'      => 1,
                ]);

                // Hitung rekap
                $totalSurat = 1; // karena hanya input satu surat di seeder ini
                $persentase = (1 / 114) * 100; // 114 surat di Al-Qur'an

                RekapTahfidz::updateOrCreate(
                    [
                        'santri_id' => $santri->id,
                        'tahun_ajaran_id' => $tahunAjaranId
                    ],
                    [
                        'total_surat' => $totalSurat,
                        'persentase_khatam' => number_format($persentase, 2, '.', ''),
                        'created_by' => 1
                    ]
                );
            }
        }
    }
}
