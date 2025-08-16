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
        $kewaliasuhanList = DB::table('kewaliasuhan')->get();

        if ($kewaliasuhanList->isEmpty()) {
            $this->command->warn('Tidak ada relasi kewaliasuhan. Seeder CatatanAfektif dilewati.');
            return;
        }

        // Batasi misal 25 catatan
        foreach ($kewaliasuhanList->take(25) as $relasi) {
            $santriId = DB::table('anak_asuh')
                ->where('id', $relasi->id_anak_asuh)
                ->value('id_santri');

            if (! $santriId) continue;

            Catatan_afektif::factory()->create([
                'id_santri' => $santriId,
                'id_wali_asuh' => $relasi->id_wali_asuh,
            ]);
        }
    }
}
