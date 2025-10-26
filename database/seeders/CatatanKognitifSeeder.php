<?php

namespace Database\Seeders;

use App\Models\Catatan_kognitif;
use App\Models\Kewaliasuhan\Wali_asuh;
use App\Models\Santri;
use Database\Factories\CatatanKognitifFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CatatanKognitifSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil anak asuh aktif yang grupnya punya wali_asuh
        $anakAsuhList = DB::table('anak_asuh as aa')
            ->join('grup_wali_asuh as g', 'aa.grup_wali_asuh_id', '=', 'g.id')
            ->join('santri as s', 'aa.id_santri', '=', 's.id')
            ->where('aa.status', true)
            ->where('s.status', 'aktif')
            ->whereNotNull('g.wali_asuh_id') // grup harus punya wali
            ->select('aa.id_santri', 'g.wali_asuh_id')
            ->get();

        if ($anakAsuhList->isEmpty()) {
            $this->command->warn('Tidak ada anak asuh dengan grup yang punya wali. Seeder CatatanKognitif dilewati.');
            return;
        }

        // Batasi misal 25 catatan
        foreach ($anakAsuhList->take(25) as $relasi) {
            Catatan_kognitif::factory()->create([
                'id_santri'    => $relasi->id_santri,
                'id_wali_asuh' => $relasi->wali_asuh_id,
            ]);
        }

        $this->command->info('✅ Seeder CatatanKognitif selesai.');
    }
}
