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
        $now = Carbon::now();

        /**
         * OUTLET
         */
        $outlets = [
            ['id' => 1, 'nama_outlet' => 'Kantin Santri', 'jenis_outlet' => 'kantin', 'status' => true],
            ['id' => 2, 'nama_outlet' => 'Koperasi Pesantren', 'jenis_outlet' => 'koperasi', 'status' => true],
            ['id' => 3, 'nama_outlet' => 'Laundry Pesantren', 'jenis_outlet' => 'laundry', 'status' => true],
        ];

        foreach ($outlets as $outlet) {
            DB::table('outlet')->insert(array_merge($outlet, [
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }

        /**
         * DETAIL USER OUTLET
         * user_id disesuaikan dengan user yang sudah ada di tabel users
         */
        $detailUserOutlets = [
            ['user_id' => 2, 'outlet_id' => 1, 'status' => true],
            ['user_id' => 3, 'outlet_id' => 2, 'status' => true],
            ['user_id' => 4, 'outlet_id' => 3, 'status' => true],
        ];

        foreach ($detailUserOutlets as $detail) {
            DB::table('detail_user_outlet')->insert(array_merge($detail, [
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }

        /**
         * KATEGORI TRANSAKSI
         */
        $kategori = [
            ['id' => 1, 'nama_kategori' => 'Makanan & Minuman', 'status' => true],
            ['id' => 2, 'nama_kategori' => 'Alat Tulis & Kitab', 'status' => true],
            ['id' => 3, 'nama_kategori' => 'Laundry', 'status' => true],
        ];

        foreach ($kategori as $kat) {
            DB::table('kategori')->insert(array_merge($kat, [
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }

        /**
         * TRANSAKSI (Contoh Data Awal)
         * santri_id harus sesuai dengan data santri yang ada
         */
        $transaksi = [
            [
                'santri_id'   => 1,
                'outlet_id'   => 1,
                'kategori_id' => 1,
                'total_bayar' => 15000.00,
                'tanggal'     => $now,
                'status'      => true,
            ],
            [
                'santri_id'   => 2,
                'outlet_id'   => 2,
                'kategori_id' => 2,
                'total_bayar' => 30000.00,
                'tanggal'     => $now,
                'status'      => true,
            ],
            [
                'santri_id'   => 3,
                'outlet_id'   => 3,
                'kategori_id' => 3,
                'total_bayar' => 10000.00,
                'tanggal'     => $now,
                'status'      => true,
            ],
        ];

        foreach ($transaksi as $trx) {
            DB::table('transaksi')->insert(array_merge($trx, [
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }
    }
}
