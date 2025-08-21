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
        $userId = 1; // ganti sesuai id user yang buat data (misal admin)
        $tahunMulai = 2017;
        $tahunAkhir = 2025;

        foreach (range($tahunMulai, $tahunAkhir) as $tahun) {
            $tahunAjaran = $tahun . '/' . ($tahun + 1);

            // Buat tanggal mulai & selesai (default: 1 Juli - 30 Juni)
            $tanggalMulai = Carbon::createFromDate($tahun, 7, 1);
            $tanggalSelesai = Carbon::createFromDate($tahun + 1, 6, 30);

            // kalau tahun = tahun terakhir, status = true
            $isActive = ($tahun === $tahunAkhir);

            // Insert tahun ajaran
            $tahunAjaranId = DB::table('tahun_ajaran')->insertGetId([
                'tahun_ajaran'    => $tahunAjaran,
                'tanggal_mulai'   => $tanggalMulai,
                'tanggal_selesai' => $tanggalSelesai,
                'status'          => $isActive, // aktif hanya di tahun terakhir
                'created_by'      => $userId,
                'updated_by'      => null,
                'deleted_by'      => null,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);

            // Insert angkatan (kategori santri)
            DB::table('angkatan')->insert([
                'angkatan'        => $tahun,
                'kategori'        => 'santri',
                'tahun_ajaran_id' => $tahunAjaranId,
                'status'          => true,
                'created_by'      => $userId,
                'updated_by'      => null,
                'deleted_by'      => null,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
        }
    }
}
