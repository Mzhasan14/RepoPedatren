<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class OutletSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = 1; // Diasumsikan admin pertama punya ID = 1
        $faker = Faker::create('id_ID');

        /**
         * OUTLET
         */
        $outlets = [
            ['nama_outlet' => 'Kantin Santri Putra'],
            ['nama_outlet' => 'Kantin Santri Putri'],
            ['nama_outlet' => 'Koperasi Pesantren'],
            ['nama_outlet' => 'Toko ATK & Kitab'],
            ['nama_outlet' => 'Laundry & Cuci Pakaian'],
            ['nama_outlet' => 'Apotek Pesantren'],
        ];

        foreach ($outlets as &$outlet) {
            $outlet['status'] = true;
            $outlet['created_by'] = $adminId;
            $outlet['created_at'] = now();
            $outlet['updated_at'] = now();
        }
        DB::table('outlet')->insert($outlets);

        $outletMap = DB::table('outlet')->pluck('id', 'nama_outlet')->toArray();

        /**
         * KATEGORI
         */
        $kategori = [
            ['nama_kategori' => 'Makanan & Minuman'],
            ['nama_kategori' => 'Kitab & Buku'],
            ['nama_kategori' => 'Alat Tulis'],
            ['nama_kategori' => 'Seragam Santri'],
            ['nama_kategori' => 'Laundry'],
            ['nama_kategori' => 'Obat-obatan'],
        ];

        foreach ($kategori as &$kat) {
            $kat['status'] = true;
            $kat['created_by'] = $adminId;
            $kat['created_at'] = now();
            $kat['updated_at'] = now();
        }
        DB::table('kategori')->insert($kategori);

        $kategoriMap = DB::table('kategori')->pluck('id', 'nama_kategori')->toArray();

        /**
         * OUTLET - KATEGORI
         */
        $outletKategori = [
            ['outlet_id' => $outletMap['Kantin Santri Putra'], 'kategori_id' => $kategoriMap['Makanan & Minuman']],
            ['outlet_id' => $outletMap['Kantin Santri Putri'], 'kategori_id' => $kategoriMap['Makanan & Minuman']],
            ['outlet_id' => $outletMap['Koperasi Pesantren'], 'kategori_id' => $kategoriMap['Kitab & Buku']],
            ['outlet_id' => $outletMap['Koperasi Pesantren'], 'kategori_id' => $kategoriMap['Alat Tulis']],
            ['outlet_id' => $outletMap['Koperasi Pesantren'], 'kategori_id' => $kategoriMap['Seragam Santri']],
            ['outlet_id' => $outletMap['Toko ATK & Kitab'], 'kategori_id' => $kategoriMap['Kitab & Buku']],
            ['outlet_id' => $outletMap['Toko ATK & Kitab'], 'kategori_id' => $kategoriMap['Alat Tulis']],
            ['outlet_id' => $outletMap['Laundry & Cuci Pakaian'], 'kategori_id' => $kategoriMap['Laundry']],
            ['outlet_id' => $outletMap['Apotek Pesantren'], 'kategori_id' => $kategoriMap['Obat-obatan']],
        ];

        foreach ($outletKategori as &$ok) {
            $ok['status'] = true;
            $ok['created_at'] = now();
            $ok['updated_at'] = now();
        }
        DB::table('outlet_kategori')->insert($outletKategori);

        /**
         * DETAIL USER OUTLET (exclude admin ID 1)
         */
        $users = DB::table('users')
            ->where('id', '!=', $adminId)
            ->pluck('id')
            ->toArray();
        $outletIds = array_values($outletMap);

        $detailUserOutlet = [];
        foreach ($outletIds as $index => $outletId) {
            if (!isset($users[$index])) continue;
            $userId = $users[$index];
            $detailUserOutlet[] = [
                'user_id' => $userId,
                'outlet_id' => $outletId,
                'status' => true,
                'created_by' => $adminId,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        DB::table('detail_user_outlet')->insert($detailUserOutlet);

        /**
         * SALDO SANTRI
         */
        $allSantri = DB::table('santri')->pluck('id')->toArray();
        $saldoData = [];
        foreach ($allSantri as $santriId) {
            $saldoData[] = [
                'santri_id' => $santriId,
                'saldo' => $faker->randomFloat(2, 50000, 200000),
                'status' => true,
                'created_by' => $adminId,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        DB::table('saldo')->insert($saldoData);

        /**
         * TRANSAKSI (10 per outlet) -> hanya untuk santri yang punya kartu
         */
        $santriPutra = DB::table('santri')
            ->join('biodata', 'santri.biodata_id', '=', 'biodata.id')
            ->join('kartu', 'santri.id', '=', 'kartu.santri_id')
            ->where('biodata.jenis_kelamin', 'l')
            ->pluck('santri.id')
            ->toArray();

        $santriPutri = DB::table('santri')
            ->join('biodata', 'santri.biodata_id', '=', 'biodata.id')
            ->join('kartu', 'santri.id', '=', 'kartu.santri_id')
            ->where('biodata.jenis_kelamin', 'p')
            ->pluck('santri.id')
            ->toArray();

        $transaksi = [];

        foreach ($outletMap as $outletNama => $outletId) {
            for ($i = 0; $i < 10; $i++) {
                if ($outletNama === 'Kantin Santri Putra') {
                    if (empty($santriPutra)) continue;
                    $santriId = $faker->randomElement($santriPutra);
                    $kategoriId = $kategoriMap['Makanan & Minuman'];
                    $total = $faker->numberBetween(5000, 20000);
                } elseif ($outletNama === 'Kantin Santri Putri') {
                    if (empty($santriPutri)) continue;
                    $santriId = $faker->randomElement($santriPutri);
                    $kategoriId = $kategoriMap['Makanan & Minuman'];
                    $total = $faker->numberBetween(5000, 20000);
                } else {
                    $combined = array_merge($santriPutra, $santriPutri);
                    if (empty($combined)) continue;
                    $santriId = $faker->randomElement($combined);

                    switch ($outletNama) {
                        case 'Koperasi Pesantren':
                            $kategoriId = $faker->randomElement([
                                $kategoriMap['Kitab & Buku'],
                                $kategoriMap['Alat Tulis'],
                                $kategoriMap['Seragam Santri'],
                            ]);
                            $total = $faker->numberBetween(15000, 75000);
                            break;
                        case 'Toko ATK & Kitab':
                            $kategoriId = $faker->randomElement([
                                $kategoriMap['Kitab & Buku'],
                                $kategoriMap['Alat Tulis'],
                            ]);
                            $total = $faker->numberBetween(5000, 50000);
                            break;
                        case 'Laundry & Cuci Pakaian':
                            $kategoriId = $kategoriMap['Laundry'];
                            $total = $faker->numberBetween(7000, 20000);
                            break;
                        case 'Apotek Pesantren':
                            $kategoriId = $kategoriMap['Obat-obatan'];
                            $total = $faker->numberBetween(5000, 30000);
                            break;
                    }
                }

                $transaksi[] = [
                    'santri_id' => $santriId,
                    'outlet_id' => $outletId,
                    'kategori_id' => $kategoriId,
                    'total_bayar' => $total,
                    'tanggal' => $faker->dateTimeBetween('-1 month', 'now'),
                    'created_by' => $adminId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        DB::table('transaksi')->insert($transaksi);
    }
}
