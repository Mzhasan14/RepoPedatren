<?php

namespace Database\Seeders;

use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

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

// Buat grup wali asuh (10% dari jumlah santri menjadi grup wali asuh)
$grupWaliAsuhData = [];
$totalGrup = max(5, round(count($santriList) * 0.1));
for ($i = 0; $i < $totalGrup; $i++) {
    $grupWaliAsuhData[] = [
        'id_wilayah' => $wilayahList[array_rand($wilayahList)],
        'nama_grup' => 'Grup Wali ' . Str::random(5),
        'jenis_kelamin' => ['l', 'p'][array_rand(['l', 'p'])],
        'created_by' => 1,
        'status' => true,
        'created_at' => Carbon::now(),
        'updated_at' => Carbon::now(),
    ];
}
DB::table('grup_wali_asuh')->insert($grupWaliAsuhData);
$grupWaliAsuhList = DB::table('grup_wali_asuh')->pluck('id')->toArray();

// Tentukan santri yang menjadi wali asuh dan anak asuh
$totalWaliAsuh = round(count($santriList) * 0.3); // 30% santri menjadi wali asuh
$waliAsuhList = array_slice($santriList, 0, $totalWaliAsuh);
$anakAsuhList = array_slice($santriList, $totalWaliAsuh);

// Buat data wali asuh
$waliAsuhData = [];
foreach ($waliAsuhList as $santriId) {
    $waliAsuhData[] = [
        'id_santri' => $santriId,
        'id_grup_wali_asuh' => $grupWaliAsuhList[array_rand($grupWaliAsuhList)],
        'created_by' => 1,
        'status' => true,
        'created_at' => Carbon::now(),
        'updated_at' => Carbon::now(),
    ];
}
DB::table('wali_asuh')->insert($waliAsuhData);
$waliAsuhIdList = DB::table('wali_asuh')->pluck('id', 'id_santri')->toArray();

// Buat data anak asuh
$anakAsuhData = [];
foreach ($anakAsuhList as $santriId) {
    $anakAsuhData[] = [
        'id_santri' => $santriId,
        'created_by' => 1,
        'status' => true,
        'created_at' => Carbon::now(),
        'updated_at' => Carbon::now(),
    ];
}
DB::table('anak_asuh')->insert($anakAsuhData);
$anakAsuhIdList = DB::table('anak_asuh')->pluck('id', 'id_santri')->toArray();

// Buat relasi kewaliasuhan (1 wali asuh punya banyak anak asuh, 1 anak asuh hanya 1 wali asuh)
$kewaliasuhanData = [];
foreach ($anakAsuhIdList as $santriId => $anakAsuhId) {
    $waliAsuhId = array_rand($waliAsuhIdList); // Pilih wali asuh secara acak
    $kewaliasuhanData[] = [
        'id_wali_asuh' => $waliAsuhIdList[$waliAsuhId],
        'id_anak_asuh' => $anakAsuhId,
        'tanggal_mulai' => Carbon::now()->subYears(rand(1, 5)),
        'tanggal_berakhir' => (rand(1, 100) > 80) ? Carbon::now()->subMonths(rand(1, 12)) : null, // 20% sudah selesai
        'created_by' => 1,
        'status' => true,
        'created_at' => Carbon::now(),
        'updated_at' => Carbon::now(),
    ];
}
DB::table('kewaliasuhan')->insert($kewaliasuhanData);

    }
}
