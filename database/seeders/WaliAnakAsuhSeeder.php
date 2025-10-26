<?php

namespace Database\Seeders;

use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WaliAnakAsuhSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();
        $userId = 1; // user admin

        $santri = DB::table('santri')
            ->join('biodata', 'santri.biodata_id', '=', 'biodata.id')
            ->join('domisili_santri', function ($join) {
                $join->on('santri.id', '=', 'domisili_santri.santri_id')
                    ->where('domisili_santri.status', 'aktif');
            })
            ->where('santri.status', true)
            ->select(
                'santri.id as santri_id',
                'biodata.jenis_kelamin',
                'domisili_santri.wilayah_id'
            )
            ->inRandomOrder()
            ->get();

        if ($santri->count() < 10) {
            $this->command->warn("⚠️ Seeder butuh minimal 10 santri aktif untuk banyak data.");
            return;
        }

        $usedSantriIds = [];
        $usedWaliIds = [];

        $wilayahIds = DB::table('wilayah')->pluck('id')->toArray();

        $groupCount = 10; // jumlah grup
        for ($i = 1; $i <= $groupCount; $i++) {
            $grupNama = "Grup Seeder $i";
            $idWilayah = $wilayahIds[array_rand($wilayahIds)];

            $jenisKelamin = null;
            $waliAsuhId = null;

            // Tentukan apakah grup punya wali (50% chance)
            $hasWali = rand(0, 1) === 1;

            if ($hasWali) {
                // Cari wali di wilayah grup + belum dipakai
                $wali = $santri
                    ->where('wilayah_id', $idWilayah)
                    ->whereNotIn('santri_id', $usedSantriIds)
                    ->random();

                if (!$wali) {
                    $this->command->warn("⚠️ Grup $i dilewati (tidak ada wali di wilayah $idWilayah).");
                    continue;
                }

                $jenisKelamin = $wali->jenis_kelamin;

                $waliAsuhId = DB::table('wali_asuh')->insertGetId([
                    'id_santri' => $wali->santri_id,
                    'tanggal_mulai' => $now->toDateString(),
                    'tanggal_berakhir' => null,
                    'created_by' => $userId,
                    'status' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                $usedSantriIds[] = $wali->santri_id;
                $usedWaliIds[] = $wali->santri_id;
            } else {
                // Kalau tanpa wali → tentukan jenis kelamin dari anak pertama di wilayah
                $anakPertama = $santri
                    ->where('wilayah_id', $idWilayah)
                    ->whereNotIn('santri_id', $usedSantriIds)
                    ->whereNotIn('santri_id', $usedWaliIds)
                    ->first();

                if (!$anakPertama) {
                    $this->command->warn("⚠️ Grup $i dilewati (tidak ada anak di wilayah $idWilayah).");
                    continue;
                }

                $jenisKelamin = $anakPertama->jenis_kelamin;
            }

            // Cari anak-anak sesuai jenis kelamin + wilayah grup
            $anakCandidates = $santri
                ->where('wilayah_id', $idWilayah)
                ->where('jenis_kelamin', $jenisKelamin)
                ->whereNotIn('santri_id', $usedSantriIds)
                ->whereNotIn('santri_id', $usedWaliIds)
                ->take(rand(1, 5));

            if ($anakCandidates->isEmpty()) {
                $this->command->warn("⚠️ Grup $i dilewati (tidak ada anak cocok di wilayah $idWilayah).");
                continue;
            }

            // Buat grup
            $grupId = DB::table('grup_wali_asuh')->insertGetId([
                'id_wilayah' => $idWilayah,
                'wali_asuh_id' => $waliAsuhId,
                'nama_grup' => $grupNama,
                'jenis_kelamin' => $jenisKelamin,
                'created_by' => $userId,
                'status' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // Tambahkan anak-anak
            foreach ($anakCandidates as $anak) {
                $usedSantriIds[] = $anak->santri_id;
                DB::table('anak_asuh')->insert([
                    'id_santri' => $anak->santri_id,
                    'grup_wali_asuh_id' => $grupId,
                    'created_by' => $userId,
                    'status' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        // Tambahkan wali tanpa grup (sisa santri yang belum dipakai)
        $sisaWali = $santri->whereNotIn('santri_id', $usedSantriIds)->take(5);
        foreach ($sisaWali as $wali) {
            DB::table('wali_asuh')->insert([
                'id_santri' => $wali->santri_id,
                'tanggal_mulai' => $now->toDateString(),
                'tanggal_berakhir' => null,
                'created_by' => $userId,
                'status' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $this->command->info("✅ Seeder grup & anak asuh selesai. Semua grup sesuai jenis kelamin & wilayah.");
    }
}
