<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class OutletSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminId = 1; // diasumsikan admin pertama punya ID = 1

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
        $outletMap   = DB::table('outlet')->pluck('id', 'nama_outlet')->toArray();

        /**
         * OUTLET - KATEGORI
         */
        $outletKategori = [
            // Kantin Putra & Putri → makanan
            ['outlet_id' => $outletMap['Kantin Santri Putra'], 'kategori_id' => $kategoriMap['Makanan & Minuman']],
            ['outlet_id' => $outletMap['Kantin Santri Putri'], 'kategori_id' => $kategoriMap['Makanan & Minuman']],

            // Koperasi → kitab, alat tulis, seragam
            ['outlet_id' => $outletMap['Koperasi Pesantren'], 'kategori_id' => $kategoriMap['Kitab & Buku']],
            ['outlet_id' => $outletMap['Koperasi Pesantren'], 'kategori_id' => $kategoriMap['Alat Tulis']],
            ['outlet_id' => $outletMap['Koperasi Pesantren'], 'kategori_id' => $kategoriMap['Seragam Santri']],

            // ATK & Kitab → kitab & alat tulis
            ['outlet_id' => $outletMap['Toko ATK & Kitab'], 'kategori_id' => $kategoriMap['Kitab & Buku']],
            ['outlet_id' => $outletMap['Toko ATK & Kitab'], 'kategori_id' => $kategoriMap['Alat Tulis']],

            // Laundry
            ['outlet_id' => $outletMap['Laundry & Cuci Pakaian'], 'kategori_id' => $kategoriMap['Laundry']],

            // Apotek
            ['outlet_id' => $outletMap['Apotek Pesantren'], 'kategori_id' => $kategoriMap['Obat-obatan']],
        ];

        foreach ($outletKategori as &$ok) {
            $ok['status'] = true;
            $ok['created_at'] = now();
            $ok['updated_at'] = now();
        }
        DB::table('outlet_kategori')->insert($outletKategori);

        /**
         * TRANSAKSI (10 transaksi per outlet)
         */
        $santriPutra = DB::table('santri')
            ->join('biodata', 'santri.biodata_id', '=', 'biodata.id')
            ->where('biodata.jenis_kelamin', 'l')
            ->pluck('santri.id')
            ->toArray();

        $santriPutri = DB::table('santri')
            ->join('biodata', 'santri.biodata_id', '=', 'biodata.id')
            ->where('biodata.jenis_kelamin', 'p')
            ->pluck('santri.id')
            ->toArray();

        $faker = \Faker\Factory::create('id_ID');
        $transaksi = [];

        foreach ($outletMap as $outletNama => $outletId) {
            for ($i = 0; $i < 10; $i++) {
                if ($outletNama === 'Kantin Santri Putra') {
                    $santriId = $faker->randomElement($santriPutra);
                    $kategoriId = $kategoriMap['Makanan & Minuman'];
                    $total = $faker->numberBetween(5000, 20000);
                } elseif ($outletNama === 'Kantin Santri Putri') {
                    $santriId = $faker->randomElement($santriPutri);
                    $kategoriId = $kategoriMap['Makanan & Minuman'];
                    $total = $faker->numberBetween(5000, 20000);
                } elseif ($outletNama === 'Koperasi Pesantren') {
                    $santriId = $faker->randomElement(array_merge($santriPutra, $santriPutri));
                    $kategoriId = $faker->randomElement([
                        $kategoriMap['Kitab & Buku'],
                        $kategoriMap['Alat Tulis'],
                        $kategoriMap['Seragam Santri'],
                    ]);
                    $total = $faker->numberBetween(15000, 75000);
                } elseif ($outletNama === 'Toko ATK & Kitab') {
                    $santriId = $faker->randomElement(array_merge($santriPutra, $santriPutri));
                    $kategoriId = $faker->randomElement([
                        $kategoriMap['Kitab & Buku'],
                        $kategoriMap['Alat Tulis'],
                    ]);
                    $total = $faker->numberBetween(5000, 50000);
                } elseif ($outletNama === 'Laundry & Cuci Pakaian') {
                    $santriId = $faker->randomElement(array_merge($santriPutra, $santriPutri));
                    $kategoriId = $kategoriMap['Laundry'];
                    $total = $faker->numberBetween(7000, 20000);
                } else { // Apotek
                    $santriId = $faker->randomElement(array_merge($santriPutra, $santriPutri));
                    $kategoriId = $kategoriMap['Obat-obatan'];
                    $total = $faker->numberBetween(5000, 30000);
                }

                $transaksi[] = [
                    'santri_id' => $santriId,
                    'outlet_id' => $outletId,
                    'kategori_id' => $kategoriId,
                    'total_bayar' => $total,
                    'tanggal' => $faker->dateTimeBetween('-1 month', 'now'),
                    'status' => true,
                    'created_by' => $adminId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        DB::table('transaksi')->insert($transaksi);
    }
}
