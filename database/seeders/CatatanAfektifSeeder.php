<?php

namespace Database\Seeders;

use App\Models\Catatan_afektif;
use App\Models\Kewaliasuhan\Wali_asuh;
use App\Models\Santri;
use Database\Factories\CatatanAfektifFactory;
use Illuminate\Database\Seeder;

class CatatanAfektifSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $santriAktif = Santri::where('status', 'aktif')->get();

        // Ambil semua wali asuh (opsional bisa difilter aktif juga jika ada fieldnya)
        $waliAsuhList = Wali_asuh::all();

        if ($santriAktif->isEmpty()) {
            $this->command->warn('Tidak ada santri aktif. Seeder CatatanAfektif dilewati.');
            return;
        }

        foreach ($santriAktif->take(25) as $santri) {
            Catatan_afektif::factory()->create([
                'id_santri' => $santri->id,
                'id_wali_asuh' => $waliAsuhList->random()->id ?? null,
            ]);
        }
    }
}
