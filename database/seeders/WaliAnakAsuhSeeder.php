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
        // Ambil semua santri yang sudah ada
        $santriList = DB::table('santri')->pluck('id')->toArray();
        shuffle($santriList);

        // Ambil semua wilayah yang sudah ada
        $wilayahList = DB::table('wilayah')->pluck('id')->toArray();

        // Tentukan santri yang menjadi wali asuh dan anak asuh
        $totalWaliAsuh = max(1, round(count($santriList) * 0.3)); // 30% santri menjadi wali asuh (minimal 1)
        $waliAsuhList = array_slice($santriList, 0, $totalWaliAsuh);
        $anakAsuhList = array_slice($santriList, $totalWaliAsuh);

        // Buat grup wali asuh (1 grup per wali asuh)
        $grupWaliAsuhData = [];
        foreach ($waliAsuhList as $index => $santriId) {
            $grupWaliAsuhData[] = [
                'id_wilayah' => $wilayahList[array_rand($wilayahList)],
                'nama_grup' => 'Grup Wali '.($index + 1),
                'jenis_kelamin' => ['l', 'p'][array_rand(['l', 'p'])],
                'created_by' => 1,
                'status' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
        }
        DB::table('grup_wali_asuh')->insert($grupWaliAsuhData);
        $grupWaliAsuhList = DB::table('grup_wali_asuh')->pluck('id')->toArray();

        //     // Buat data wali asuh (1 wali asuh per grup)
        //     $waliAsuhData = [];
        //     foreach ($waliAsuhList as $index => $santriId) {
        //         $waliAsuhData[] = [
        //             'id_santri' => $santriId,
        //             'id_grup_wali_asuh' => $grupWaliAsuhList[$index], // Setiap wali asuh mendapatkan grup unik
        //             'tanggal_mulai' => Carbon::now()->subYears(rand(1, 5)),
        //             'tanggal_berakhir' => (rand(1, 100) > 80) ? Carbon::now()->subMonths(rand(1, 12)) : null, // 20% sudah selesai
        //             'created_by' => 1,
        //             'status' => true,
        //             'created_at' => Carbon::now(),
        //             'updated_at' => Carbon::now(),
        //         ];
        //     }
        //     DB::table('wali_asuh')->insert($waliAsuhData);
        //     $waliAsuhIdList = DB::table('wali_asuh')->pluck('id', 'id_santri')->toArray();

        //     // Buat data anak asuh
        //     $anakAsuhData = [];
        //     foreach ($anakAsuhList as $santriId) {
        //         $anakAsuhData[] = [
        //             'id_santri' => $santriId,
        //             'created_by' => 1,
        //             'status' => true,
        //             'created_at' => Carbon::now(),
        //             'updated_at' => Carbon::now(),
        //         ];
        //     }
        //     DB::table('anak_asuh')->insert($anakAsuhData);
        //     $anakAsuhIdList = DB::table('anak_asuh')->pluck('id', 'id_santri')->toArray();

        //     // Buat relasi kewaliasuhan
        //     $kewaliasuhanData = [];
        //     foreach ($anakAsuhIdList as $santriId => $anakAsuhId) {
        //         // Pilih wali asuh secara acak
        //         $waliAsuhId = $waliAsuhIdList[array_rand($waliAsuhIdList)];

        //         $kewaliasuhanData[] = [
        //             'id_wali_asuh' => $waliAsuhId,
        //             'id_anak_asuh' => $anakAsuhId,
        //             'tanggal_mulai' => Carbon::now()->subYears(rand(1, 5)),
        //             'tanggal_berakhir' => (rand(1, 100) > 80) ? Carbon::now()->subMonths(rand(1, 12)) : null, // 20% sudah selesai
        //             'created_by' => 1,
        //             'status' => true,
        //             'created_at' => Carbon::now(),
        //             'updated_at' => Carbon::now(),
        //         ];
        //     }
        //     DB::table('kewaliasuhan')->insert($kewaliasuhanData);
        // }

        // Wali Asuh
        $waliAsuhData = [];
        $santriWaliAktif = []; // Santri yang sudah aktif jadi wali asuh

        foreach ($waliAsuhList as $index => $santriId) {
            if (in_array($santriId, $santriWaliAktif)) {
                continue; // Sudah punya relasi aktif
            }

            $tanggalBerakhir = rand(1, 100) > 80 ? now()->subMonths(rand(1, 12)) : null;

            if (! $tanggalBerakhir) {
                $santriWaliAktif[] = $santriId;
            }

            $waliAsuhData[] = [
                'id_santri' => $santriId,
                'id_grup_wali_asuh' => $grupWaliAsuhList[$index],
                'tanggal_mulai' => now()->subYears(rand(1, 5)),
                'tanggal_berakhir' => $tanggalBerakhir,
                'created_by' => 1,
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        DB::table('wali_asuh')->insert($waliAsuhData);
        $waliAsuhIdList = DB::table('wali_asuh')->pluck('id', 'id_santri')->toArray();

        // Anak Asuh
        $anakAsuhData = [];
        $santriAnakAktif = [];

        foreach ($anakAsuhList as $santriId) {
            if (in_array($santriId, $santriAnakAktif)) {
                continue;
            }

            $anakAsuhData[] = [
                'id_santri' => $santriId,
                'created_by' => 1,
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $santriAnakAktif[] = $santriId;
        }

        DB::table('anak_asuh')->insert($anakAsuhData);
        $anakAsuhIdList = DB::table('anak_asuh')->pluck('id', 'id_santri')->toArray();

        // Kewaliasuhan (anak bisa punya banyak wali, asalkan tidak aktif bersamaan)
        $kewaliasuhanData = [];
        $relasiAktif = []; // id_anak_asuh yang aktif

        foreach ($anakAsuhIdList as $santriId => $anakAsuhId) {
            if (in_array($anakAsuhId, $relasiAktif)) {
                continue;
            }

            $waliAsuhId = $waliAsuhIdList[array_rand($waliAsuhIdList)];

            $tanggalBerakhir = rand(1, 100) > 80 ? now()->subMonths(rand(1, 12)) : null;

            if (! $tanggalBerakhir) {
                $relasiAktif[] = $anakAsuhId;
            }

            $kewaliasuhanData[] = [
                'id_wali_asuh' => $waliAsuhId,
                'id_anak_asuh' => $anakAsuhId,
                'tanggal_mulai' => now()->subYears(rand(1, 5)),
                'tanggal_berakhir' => $tanggalBerakhir,
                'created_by' => 1,
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('kewaliasuhan')->insert($kewaliasuhanData);
    }
}
