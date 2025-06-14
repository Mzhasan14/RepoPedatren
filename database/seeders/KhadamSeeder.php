<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class KhadamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userId = 1; // ID user pembuat data

        // Ambil biodata_id yang berelasi ke santri dan riwayat_domisili
        $biodataIds = DB::table('biodata')
            ->join('santri', 'biodata.id', '=', 'santri.biodata_id')
            ->join('riwayat_domisili', 'santri.id', '=', 'riwayat_domisili.santri_id')
            ->select('biodata.id')
            ->distinct()
            ->pluck('id')
            ->toArray();

        $jumlahKhadam = (int) floor(count($biodataIds) * 0.5);
        $selectedBiodataIds = collect($biodataIds)->random($jumlahKhadam);

        foreach ($selectedBiodataIds as $biodataId) {
            $jumlahRiwayat = rand(1, 3);
            $tanggalMulai = Carbon::now()->subYears(rand(1, 5))->subMonths(rand(0, 11));

            for ($i = 1; $i <= $jumlahRiwayat; $i++) {
                $tanggalAkhir = null;
                if ($i < $jumlahRiwayat) {
                    // tanggal_akhir wajib diisi untuk riwayat sebelumnya
                    $tanggalAkhir = (clone $tanggalMulai)->addMonths(rand(3, 12));
                }

                DB::table('khadam')->insert([
                    'biodata_id' => $biodataId,
                    'keterangan' => "Menjabat sebagai khadam periode ke-$i",
                    'tanggal_mulai' => $tanggalMulai->toDateString(),
                    'tanggal_akhir' => $tanggalAkhir ? $tanggalAkhir->toDateString() : null,
                    'created_by' => $userId,
                    'updated_by' => null,
                    'deleted_by' => null,
                    'status' => $tanggalAkhir ? false : true, // UBAH DI SINI
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                if ($tanggalAkhir) {
                    $tanggalMulai = (clone $tanggalAkhir)->addDays(rand(1, 30));
                }
            }
        }
    }
}
