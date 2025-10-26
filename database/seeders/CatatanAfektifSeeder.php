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
        // Ambil anak asuh aktif yang grupnya punya wali_asuh
        $anakAsuhList = DB::table('anak_asuh as aa')
            ->join('grup_wali_asuh as g', 'aa.grup_wali_asuh_id', '=', 'g.id')
            ->where('aa.status', true)
            ->whereNotNull('g.wali_asuh_id') // grup harus punya wali
            ->select('aa.id_santri', 'g.wali_asuh_id')
            ->get();

        if ($anakAsuhList->isEmpty()) {
            $this->command->warn('Tidak ada anak asuh dengan grup yang punya wali. Seeder CatatanAfektif dilewati.');
            return;
        }

        // Batasi misal 25 catatan
        foreach ($anakAsuhList->take(25) as $anak) {
            Catatan_afektif::factory()->create([
                'id_santri'    => $anak->id_santri,
                'id_wali_asuh' => $anak->wali_asuh_id,
            ]);
        }

        $this->command->info('✅ Seeder CatatanAfektif selesai.');
    }
}
