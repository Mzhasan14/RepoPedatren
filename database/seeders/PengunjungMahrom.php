<?php

namespace Database\Seeders;

use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PengunjungMahrom extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pengunjungData = [];

        // Ambil semua santri
        $santriRows = DB::table('santri')->select('id', 'id_peserta_didik')->get();

        foreach ($santriRows as $santri) {
            // Ambil peserta didik terkait
            $peserta = DB::table('peserta_didik')
                ->where('id', $santri->id_peserta_didik)
                ->first();
            if (!$peserta) {
                continue;
            }

            // Ambil biodata santri dari peserta didik
            $biodataSantri = DB::table('biodata')
                ->where('id', $peserta->id_biodata)
                ->first();
            if (!$biodataSantri) {
                continue;
            }

            // Ambil data keluarga untuk santri (berdasarkan biodata)
            $keluargaSantri = DB::table('keluarga')
                ->where('id_biodata', $biodataSantri->id)
                ->first();
            if (!$keluargaSantri || !$keluargaSantri->no_kk) {
                continue;
            }

            // Cari data keluarga lain dengan no_kk yang sama (mewakili orang tua)
            $parentKeluarga = DB::table('keluarga')
                ->where('no_kk', $keluargaSantri->no_kk)
                ->where('id_biodata', '!=', $biodataSantri->id)
                ->get();

            if ($parentKeluarga->isEmpty()) {
                continue;
            }

            // Untuk tiap data keluarga orang tua, cari data di orang_tua_wali dengan hubungan 'ayah' atau 'ibu'
            foreach ($parentKeluarga as $parentKel) {
                $parentRecord = DB::table('orang_tua_wali')
                    ->join('hubungan_keluarga', 'orang_tua_wali.id_hubungan_keluarga', '=', 'hubungan_keluarga.id')
                    ->join('biodata', 'orang_tua_wali.id_biodata', '=', 'biodata.id')
                    ->where('orang_tua_wali.id_biodata', $parentKel->id_biodata)
                    ->whereIn('hubungan_keluarga.nama_status', ['ayah', 'ibu'])
                    ->select('biodata.nama')
                    ->first();

                if ($parentRecord) {
                    $pengunjungData[] = [
                        'id_santri'         => $santri->id,
                        'nama_pengunjung'   => $parentRecord->nama,
                        'jumlah_rombongan'  => rand(1, 5),
                        'tanggal'           => Carbon::now()->subDays(rand(1, 365)),
                        'created_at'        => Carbon::now(),
                        'updated_at'        => Carbon::now(),
                    ];
                }
            }
        }

        // Insert data ke tabel pengunjung_mahrom jika ada data
        if (!empty($pengunjungData)) {
            DB::table('pengunjung_mahrom')->insert($pengunjungData);
        }
    }
}
