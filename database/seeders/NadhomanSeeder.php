<?php

namespace Database\Seeders;

use App\Models\Kitab;
use App\Models\Nadhoman;
use App\Models\RekapNadhoman;
use App\Models\Santri;
use App\Models\TahunAjaran;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class NadhomanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $santriAktif = Santri::where('status', 'aktif')->get();
        $kitabList = Kitab::all();
        $tahunAjaranId = TahunAjaran::inRandomOrder()->value('id') ?? 1;

        foreach ($santriAktif as $santri) {
            foreach ($kitabList as $kitab) {

                // Cegah duplikat Nadhoman (ambil yang terakhir kalau sudah ada)
                $existing = Nadhoman::where([
                    'santri_id' => $santri->id,
                    'kitab_id' => $kitab->id,
                    'tahun_ajaran_id' => $tahunAjaranId
                ])->exists();

                if (! $existing) {
                    $baitMulai = rand(1, max(1, $kitab->total_bait - 5));
                    $baitSelesai = min($kitab->total_bait, $baitMulai + rand(1, 5));

                    Nadhoman::create([
                        'santri_id'       => $santri->id,
                        'kitab_id'        => $kitab->id,
                        'tahun_ajaran_id' => $tahunAjaranId,
                        'tanggal'         => now()->subDays(rand(0, 30))->format('Y-m-d'),
                        'jenis_setoran'   => ['baru', 'murojaah'][rand(0, 1)],
                        'bait_mulai'      => $baitMulai,
                        'bait_selesai'    => $baitSelesai,
                        'nilai'           => ['lancar', 'cukup', 'kurang'][rand(0, 2)],
                        'catatan'         => rand(0, 1) ? 'Setoran lancar' : null,
                        'status'          => ['proses', 'tuntas'][rand(0, 1)],
                        'created_by'      => 1,
                    ]);

                    $totalBaitSetoran = max(1, round($kitab->total_bait * 0.1));
                    $persentase = 10; 

                    RekapNadhoman::updateOrCreate(
                        [
                            'santri_id' => $santri->id,
                            'kitab_id'  => $kitab->id,
                        ],
                        [
                            'total_bait' => $totalBaitSetoran,
                            'persentase_selesai' => number_format($persentase, 2, '.', ''),
                            'created_by' => 1
                        ]
                    );
                }
            }
        }
    }
}
