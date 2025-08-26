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
        $anakAsuhList = DB::table('anak_asuh')
            ->join('santri', 'anak_asuh.id_santri', '=', 'santri.id')
            ->where('santri.status', 'aktif')
            ->where('anak_asuh.status', true)
            ->select('anak_asuh.wali_asuh_id', 'anak_asuh.id_santri')
            ->get();

        if ($anakAsuhList->isEmpty()) {
            $this->command->warn('Tidak ada relasi anak asuh. Seeder CatatanKognitif dilewati.');
            return;
        }

        foreach ($anakAsuhList->take(25) as $relasi) {
            Catatan_kognitif::factory()->create([
                'id_santri'   => $relasi->id_santri,
                'id_wali_asuh' => $relasi->wali_asuh_id,
            ]);
        }
    }
}
