<?php

namespace Database\Seeders;

use App\Models\Catatan_kognitif;
use App\Models\Kewaliasuhan\Wali_asuh;
use App\Models\Santri;
use Database\Factories\CatatanKognitifFactory;
use Illuminate\Database\Seeder;

class CatatanKognitifSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $santriAktif = Santri::where('status', 'aktif')->get();
        $waliAsuhList = Wali_asuh::all();

        if ($santriAktif->isEmpty()) {
            $this->command->warn('Tidak ada santri aktif. Seeder CatatanKognitif dilewati.');
            return;
        }

        foreach ($santriAktif->take(25) as $santri) {
            Catatan_kognitif::factory()->create([
                'id_santri' => $santri->id,
                'id_wali_asuh' => $waliAsuhList->random()->id ?? null,
            ]);
        }
    }
}
