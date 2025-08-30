<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class KanzusAngkatanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userId = 1; // id admin
        $tahunMulai = 2017;
        $tahunAkhir = 2025;

        foreach (range($tahunMulai, $tahunAkhir) as $tahun) {
            $tahunAjaran = $tahun . '/' . ($tahun + 1);

            // Default tanggal ajaran
            $tanggalMulai = Carbon::createFromDate($tahun, 7, 1);
            $tanggalSelesai = Carbon::createFromDate($tahun + 1, 6, 30);

            // hanya tahun terakhir yg aktif
            $isActive = ($tahun === $tahunAkhir);

            // Insert / update tahun_ajaran
            $tahunAjaranId = DB::table('tahun_ajaran')->updateOrInsert(
                ['tahun_ajaran' => $tahunAjaran], // unique key
                [
                    'tanggal_mulai'   => $tanggalMulai,
                    'tanggal_selesai' => $tanggalSelesai,
                    'status'          => $isActive,
                    'created_by'      => $userId,
                    'updated_by'      => null,
                    'deleted_by'      => null,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]
            );

            // ambil id tahun_ajaran (karena updateOrInsert tidak kembalikan id)
            $tahunAjaranRow = DB::table('tahun_ajaran')->where('tahun_ajaran', $tahunAjaran)->first();

            // Insert / update angkatan
            DB::table('angkatan')->updateOrInsert(
                [
                    'angkatan'        => $tahun,
                    'kategori'        => 'santri'
                ],
                [
                    'tahun_ajaran_id' => $tahunAjaranRow->id,
                    'status'          => true,
                    'created_by'      => $userId,
                    'updated_by'      => null,
                    'deleted_by'      => null,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]
            );
        }
    }
}
