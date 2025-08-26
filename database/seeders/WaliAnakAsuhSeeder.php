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

        // 1️⃣ Ambil semua santri aktif dengan biodata (biar bisa filter gender)
        $santri = DB::table('santri')
            ->join('biodata', 'santri.biodata_id', '=', 'biodata.id')
            ->where('santri.status', true)
            ->select('santri.id as santri_id', 'biodata.jenis_kelamin')
            ->inRandomOrder()
            ->get();

        if ($santri->count() < 5) {
            $this->command->warn("⚠️ Seeder butuh minimal 5 santri aktif agar variasi cukup.");
            return;
        }

        // Simpan ID yang sudah dipakai agar tidak duplikat
        $usedSantriIds = [];

        // Tentukan berapa wali asuh mau dibuat
        $jumlahWali = min(5, floor($santri->count() / 2)); // max 5 wali

        for ($i = 1; $i <= $jumlahWali; $i++) {
            // 2️⃣ Pilih 1 santri jadi wali asuh (yang belum dipakai)
            $wali = $santri->firstWhere(fn($s) => !in_array($s->santri_id, $usedSantriIds));
            if (! $wali) break; // habis stok

            $waliSantriId = $wali->santri_id;
            $jenisKelamin = $wali->jenis_kelamin;
            $usedSantriIds[] = $waliSantriId;

            // 3️⃣ Insert wali asuh
            $waliAsuhId = DB::table('wali_asuh')->insertGetId([
                'id_santri'        => $waliSantriId,
                'tanggal_mulai'    => $now->toDateString(),
                'tanggal_berakhir' => null,
                'created_by'       => $userId,
                'status'           => true,
                'created_at'       => $now,
                'updated_at'       => $now,
            ]);

            // 4️⃣ Insert grup wali asuh
            DB::table('grup_wali_asuh')->insert([
                'id_wilayah'   => DB::table('wilayah')->inRandomOrder()->value('id'),
                'wali_asuh_id' => $waliAsuhId,
                'nama_grup'    => 'Grup Wali ' . $i,
                'jenis_kelamin' => $jenisKelamin,
                'created_by'   => $userId,
                'status'       => true,
                'created_at'   => $now,
                'updated_at'   => $now,
            ]);

            // 5️⃣ Pilih 1–3 anak asuh dengan jenis kelamin sama dan belum dipakai
            $jumlahAnak = rand(1, 3);
            $anakCandidates = $santri
                ->where('jenis_kelamin', $jenisKelamin)
                ->whereNotIn('santri_id', $usedSantriIds)
                ->take($jumlahAnak);

            foreach ($anakCandidates as $anak) {
                $usedSantriIds[] = $anak->santri_id;

                DB::table('anak_asuh')->insert([
                    'id_santri'    => $anak->santri_id,
                    'wali_asuh_id' => $waliAsuhId,
                    'created_by'   => $userId,
                    'status'       => true,
                    'created_at'   => $now,
                    'updated_at'   => $now,
                ]);
            }

            $this->command->info("✅ Wali #$i (santri #$waliSantriId, $jenisKelamin) punya " . $anakCandidates->count() . " anak asuh.");
        }
    }
}
