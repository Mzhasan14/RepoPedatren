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
        $now = Carbon::now();

        // Ambil semua santri (dengan biodata_id langsung di tabel santri)
        $santriRows = DB::table('santri')
            ->select('id as santri_id', 'biodata_id')
            ->get();

        foreach ($santriRows as $santri) {
            // Ambil biodata santri
            $biodata = DB::table('biodata')
                ->where('id', $santri->biodata_id)
                ->first();

            if (!$biodata) {
                continue;
            }

            // Ambil catatan keluarga santri (harus punya no_kk)
            $keluarga = DB::table('keluarga')
                ->where('id_biodata', $biodata->id)
                ->whereNotNull('no_kk')
                ->first();

            if (!$keluarga) {
                continue;
            }

            // Cari anggota keluarga lain (ortu) dengan no_kk yang sama
            $parentKeluarga = DB::table('keluarga')
                ->where('no_kk', $keluarga->no_kk)
                ->where('id_biodata', '!=', $biodata->id)
                ->get();

            if ($parentKeluarga->isEmpty()) {
                continue;
            }

            // Untuk tiap anggota keluarga tersebut, cek di orang_tua_wali
            foreach ($parentKeluarga as $parent) {
                $parentRecord = DB::table('orang_tua_wali as otw')
                    ->join('hubungan_keluarga as hk', 'otw.id_hubungan_keluarga', '=', 'hk.id')
                    ->join('biodata as b2', 'otw.id_biodata', '=', 'b2.id')
                    ->where('otw.id_biodata', $parent->id_biodata)
                    ->whereIn('hk.nama_status', ['ayah', 'ibu'])
                    ->select('b2.nama')
                    ->first();

                if ($parentRecord) {
                    $pengunjungData[] = [
                        'santri_id'        => $santri->santri_id,
                        'nama_pengunjung'  => $parentRecord->nama,
                        'jumlah_rombongan' => rand(1, 5),
                        'tanggal'          => Carbon::now()->subDays(rand(1, 365)),
                        'created_at'       => $now,
                        'updated_at'       => $now,
                    ];
                }
            }
        }

        // Bulk insert jika ada data
        if (!empty($pengunjungData)) {
            DB::table('pengunjung_mahrom')->insert($pengunjungData);
        }
    }
}
