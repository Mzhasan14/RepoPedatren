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
        $kewaliasuhanList = DB::table('kewaliasuhan')
            ->join('anak_asuh', 'kewaliasuhan.id_anak_asuh', '=', 'anak_asuh.id')
            ->join('santri', 'anak_asuh.id_santri', '=', 'santri.id')
            ->where('santri.status', 'aktif')
            ->select('kewaliasuhan.id_wali_asuh', 'anak_asuh.id_santri')
            ->get();

        if ($kewaliasuhanList->isEmpty()) {
            $this->command->warn('Tidak ada relasi kewaliasuhan. Seeder CatatanKognitif dilewati.');
            return;
        }

        foreach ($kewaliasuhanList->take(25) as $relasi) {
            Catatan_kognitif::factory()->create([
                'id_santri' => $relasi->id_santri,
                'id_wali_asuh' => $relasi->id_wali_asuh,
            ]);
        }
    }
}
