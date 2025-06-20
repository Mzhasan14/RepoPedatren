<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class WaliAnakAsuhSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Disable FK checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        DB::table('kewaliasuhan')->delete();
        DB::table('anak_asuh')->delete();
        DB::table('wali_asuh')->delete();
        DB::table('grup_wali_asuh')->delete();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $santriList = DB::table('santri')->pluck('id')->toArray();
        shuffle($santriList);

        $wilayahList = DB::table('wilayah')->pluck('id')->toArray();

        $totalSantri = count($santriList);

        // Tentukan jumlah grup wali asuh (antara 20 - 25 tergantung banyaknya santri)
        $jumlahGrup = max(20, min(25, intdiv($totalSantri, 13))); // rata-rata 13 anak asuh per grup

        $waliAsuhList = array_slice($santriList, 0, $jumlahGrup);
        $anakAsuhList = array_slice($santriList, $jumlahGrup);

        // Buat grup wali asuh
        $grupWaliAsuhData = [];
        $grupWaliAsuhJenisKelamin = [];
        foreach ($waliAsuhList as $index => $santriId) {
            $jenisKelamin = ['l', 'p'][array_rand(['l', 'p'])];
            $grupWaliAsuhData[] = [
                'id_wilayah' => $wilayahList[array_rand($wilayahList)],
                'nama_grup' => 'Grup Wali ' . ($index + 1),
                'jenis_kelamin' => $jenisKelamin,
                'created_by' => 1,
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            $grupWaliAsuhJenisKelamin[$index] = $jenisKelamin;
        }
        DB::table('grup_wali_asuh')->insert($grupWaliAsuhData);
        $grupWaliAsuhList = DB::table('grup_wali_asuh')->get();

        // Wali Asuh
        $waliAsuhData = [];
        foreach ($waliAsuhList as $index => $santriId) {
            $waliAsuhData[] = [
                'id_santri' => $santriId,
                'id_grup_wali_asuh' => $grupWaliAsuhList[$index]->id,
                'tanggal_mulai' => now()->subYears(rand(1, 5)),
                'tanggal_berakhir' => null,
                'created_by' => 1,
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        DB::table('wali_asuh')->insert($waliAsuhData);
        $waliAsuhRecords = DB::table('wali_asuh')->get();
        $waliAsuhIdList = $waliAsuhRecords->pluck('id', 'id_santri')->toArray();
        $grupByWaliId = $waliAsuhRecords->pluck('id_grup_wali_asuh', 'id')->toArray();

        // Anak Asuh
        $anakAsuhData = [];
        foreach ($anakAsuhList as $santriId) {
            $anakAsuhData[] = [
                'id_santri' => $santriId,
                'created_by' => 1,
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        DB::table('anak_asuh')->insert($anakAsuhData);
        $anakAsuhIdList = DB::table('anak_asuh')->pluck('id', 'id_santri')->toArray();

        // Kewaliasuhan - berdasarkan kecocokan jenis kelamin antara anak asuh dan wali asuh
        $kewaliasuhanData = [];
        $anakAsuhAssigned = [];

        foreach ($anakAsuhIdList as $santriId => $anakAsuhId) {
            $biodata = DB::table('biodata')
                ->join('santri', 'biodata.id', '=', 'santri.biodata_id')
                ->where('santri.id', $santriId)
                ->select('biodata.jenis_kelamin')
                ->first();

            $waliCocok = $waliAsuhRecords->filter(function ($wali) use ($grupWaliAsuhList, $grupByWaliId, $biodata) {
                $grup = $grupWaliAsuhList->firstWhere('id', $wali->id_grup_wali_asuh);
                return $grup && $grup->jenis_kelamin === $biodata->jenis_kelamin;
            })->shuffle()->first();

            if (! $waliCocok) continue;

            $kewaliasuhanData[] = [
                'id_wali_asuh' => $waliCocok->id,
                'id_anak_asuh' => $anakAsuhId,
                'tanggal_mulai' => now()->subYears(rand(1, 5)),
                'tanggal_berakhir' => null,
                'created_by' => 1,
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('kewaliasuhan')->insert($kewaliasuhanData);
    }
}