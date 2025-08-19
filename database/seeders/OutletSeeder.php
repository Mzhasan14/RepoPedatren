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
        $adminId = 1; // Admin pertama
        $faker   = Faker::create('id_ID');

        /**
         * OUTLETS
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
        DB::table('outlets')->insert($outlets);
        $outletMap = DB::table('outlets')->pluck('id', 'nama_outlet')->toArray();

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
            // tambahan untuk saldo
            ['nama_kategori' => 'Top Up Saldo'],
            ['nama_kategori' => 'Tarik Saldo'],
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
            ['outlet_id' => $outletMap['Koperasi Pesantren'], 'kategori_id' => $kategoriMap['Top Up Saldo']],
            ['outlet_id' => $outletMap['Koperasi Pesantren'], 'kategori_id' => $kategoriMap['Tarik Saldo']],
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
         * DETAIL USER OUTLET
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

        // pastikan user id 9 = outlet Koperasi Pesantren
        $detailUserOutlet[] = [
            'user_id' => 10,
            'outlet_id' => $outletMap['Koperasi Pesantren'],
            'status' => true,
            'created_by' => $adminId,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        DB::table('detail_user_outlet')->insert($detailUserOutlet);

        $detailUserOutletMap = DB::table('detail_user_outlet')
            ->pluck('id', 'outlet_id')
            ->toArray();

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
         * TRANSAKSI (dummy)
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
        $transaksiSaldo = [];

        foreach ($outletMap as $outletNama => $outletId) {
            for ($i = 0; $i < 10; $i++) {
                $combined = array_merge($santriPutra, $santriPutri);
                if (empty($combined)) continue;
                $santriId = $faker->randomElement($combined);

                if ($outletNama === 'Kantin Santri Putra') {
                    $kategoriId = $kategoriMap['Makanan & Minuman'];
                    $total = $faker->numberBetween(5000, 20000);
                } elseif ($outletNama === 'Kantin Santri Putri') {
                    $kategoriId = $kategoriMap['Makanan & Minuman'];
                    $total = $faker->numberBetween(5000, 20000);
                } elseif ($outletNama === 'Koperasi Pesantren') {
                    $kategoriId = $faker->randomElement([
                        $kategoriMap['Kitab & Buku'],
                        $kategoriMap['Alat Tulis'],
                        $kategoriMap['Seragam Santri'],
                    ]);
                    $total = $faker->numberBetween(15000, 75000);
                } elseif ($outletNama === 'Toko ATK & Kitab') {
                    $kategoriId = $faker->randomElement([
                        $kategoriMap['Kitab & Buku'],
                        $kategoriMap['Alat Tulis'],
                    ]);
                    $total = $faker->numberBetween(5000, 50000);
                } elseif ($outletNama === 'Laundry & Cuci Pakaian') {
                    $kategoriId = $kategoriMap['Laundry'];
                    $total = $faker->numberBetween(7000, 20000);
                } elseif ($outletNama === 'Apotek Pesantren') {
                    $kategoriId = $kategoriMap['Obat-obatan'];
                    $total = $faker->numberBetween(5000, 30000);
                } else {
                    continue;
                }

                $userOutletId = $detailUserOutletMap[$outletId] ?? null;
                if (!$userOutletId) continue;

                $tanggal = $faker->dateTimeBetween('-1 month', 'now');

                // Transaksi utama
                $transaksi[] = [
                    'santri_id' => $santriId,
                    'outlet_id' => $outletId,
                    'kategori_id' => $kategoriId,
                    'user_outlet_id' => $userOutletId,
                    'total_bayar' => $total,
                    'tanggal' => $tanggal,
                    'created_by' => $adminId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                // Rekap transaksi_saldo (pembayaran = debit)
                $transaksiSaldo[] = [
                    'santri_id' => $santriId,
                    'outlet_id' => $outletId,
                    'kategori_id' => $kategoriId,
                    'user_outlet_id' => $userOutletId,
                    'tipe' => 'debit',
                    'jumlah' => $total,
                    'created_by' => $adminId,
                    'updated_by' => $adminId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        /**
         * Tambahan dummy transaksi saldo khusus Top Up & Tarik (koperasi pesantren)
         */
        if (!empty($allSantri)) {
            foreach (array_slice($allSantri, 0, 5) as $santriId) {
                $userOutletId = $detailUserOutletMap[$outletMap['Koperasi Pesantren']] ?? null;
                if (!$userOutletId) continue;

                // Top up
                $transaksiSaldo[] = [
                    'santri_id' => $santriId,
                    'outlet_id' => $outletMap['Koperasi Pesantren'],
                    'kategori_id' => $kategoriMap['Top Up Saldo'],
                    'user_outlet_id' => $userOutletId,
                    'tipe' => 'topup',
                    'jumlah' => 100000,
                    'created_by' => $adminId,
                    'updated_by' => $adminId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                // Tarik
                $transaksiSaldo[] = [
                    'santri_id' => $santriId,
                    'outlet_id' => $outletMap['Koperasi Pesantren'],
                    'kategori_id' => $kategoriMap['Tarik Saldo'],
                    'user_outlet_id' => $userOutletId,
                    'tipe' => 'debit',
                    'jumlah' => 50000,
                    'created_by' => $adminId,
                    'updated_by' => $adminId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        DB::table('transaksi')->insert($transaksi);
        DB::table('transaksi_saldo')->insert($transaksiSaldo);
    }
}
