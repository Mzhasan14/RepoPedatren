<?php

namespace Database\Seeders;

use App\Models\Catatan_afektif;
use App\Models\Kewaliasuhan\Wali_asuh;
use App\Models\Santri;
use Database\Factories\CatatanAfektifFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CatatanAfektifSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $anakAsuhList = DB::table('anak_asuh')
            ->where('status', true)
            ->get();

        if ($anakAsuhList->isEmpty()) {
            $this->command->warn('Tidak ada anak asuh aktif. Seeder CatatanAfektif dilewati.');
            return;
        }

        // Batasi misal 25 catatan
        foreach ($anakAsuhList->take(25) as $anak) {
            $santriId = $anak->id_santri;
            $waliAsuhId = $anak->wali_asuh_id;

            if (! $santriId || ! $waliAsuhId) continue;

            Catatan_afektif::factory()->create([
                'id_santri'   => $santriId,
                'id_wali_asuh' => $waliAsuhId,
            ]);
        }
    }
}
