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
         * DETAIL USER OUTLET (khusus user role = petugas)
         * Semua petugas otomatis ditempatkan di Koperasi Pesantren
         */
        $users = DB::table('users')
            ->join('model_has_roles', function ($join) {
                $join->on('users.id', '=', 'model_has_roles.model_id')
                    ->where('model_has_roles.model_type', '=', 'App\\Models\\User');
            })
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('roles.name', 'petugas')
            ->where('users.id', '!=', $adminId)
            ->pluck('users.id')
            ->toArray();

        $detailUserOutlet = [];
        foreach ($users as $userId) {
            $detailUserOutlet[] = [
                'user_id'    => $userId,
                'outlet_id'  => $outletMap['Koperasi Pesantren'],
                'status'     => true,
                'created_by' => $adminId,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('detail_user_outlet')->insert($detailUserOutlet);

        $detailUserOutletMap = DB::table('detail_user_outlet')
            ->pluck('id', 'outlet_id')
            ->toArray();
    }
}
