<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PembayaranSeeder extends Seeder
{
    public function run(): void
    {
        /**
         * BANK
         */
        DB::table('banks')->insert([
            [
                'kode_bank'  => 'BJTM',
                'nama_bank'  => 'Bank Jatim',
                'status'     => true,
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'kode_bank'  => 'BNI',
                'nama_bank'  => 'Bank Negara Indonesia',
                'status'     => true,
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        /**
         * VIRTUAL ACCOUNT
         */
        DB::table('virtual_accounts')->insert([
            [
                'santri_id'  => 1,
                'bank_id'    => 1,
                'va_number'  => '9001234567891',
                'status'     => true,
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'santri_id'  => 2,
                'bank_id'    => 2,
                'va_number'  => '9881234567892',
                'status'     => true,
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        /**
         * TAGIHAN
         */
        DB::table('tagihan')->insert([
            [
                'nama_tagihan' => 'SPP Januari 2025',
                'periode'      => '2025-01',
                'tipe'         => 'bulanan',
                'nominal'      => 350000,
                'jatuh_tempo'  => '2025-01-10',
                'status'       => true,
                'created_by'   => 1,
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
            [
                'nama_tagihan' => 'Daftar Ulang Santri 2025/2026',
                'periode'      => '2025-07',
                'tipe'         => 'tahunan',
                'nominal'      => 1500000,
                'jatuh_tempo'  => '2025-07-15',
                'status'       => true,
                'created_by'   => 1,
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
            [
                'nama_tagihan' => 'Pembelian Kitab Tahun 2025',
                'periode'      => '2025-08',
                'tipe'         => 'sekali_bayar',
                'nominal'      => 500000,
                'jatuh_tempo'  => '2025-08-30',
                'status'       => true,
                'created_by'   => 1,
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
        ]);

        /**
         * TAGIHAN SANTRI
         */
        // DB::table('tagihan_santri')->insert([
        //     [
        //         'tagihan_id'          => 1,
        //         'santri_id'           => 1,
        //         'total_potongan'      => 0,
        //         'total_tagihan'       => 350000,
        //         'status'              => 'pending',
        //         'tanggal_jatuh_tempo' => '2025-01-10',
        //         'tanggal_bayar'       => null,
        //         'keterangan'          => null,
        //         'created_by'          => 1,
        //         'updated_by'          => null,
        //         'deleted_by'          => null,
        //         'created_at'          => now(),
        //         'updated_at'          => now(),
        //         'deleted_at'          => null,
        //     ],
        //     [
        //         'tagihan_id'          => 1,
        //         'santri_id'           => 2,
        //         'total_potongan'      => 0,
        //         'total_tagihan'       => 350000,
        //         'status'              => 'lunas',
        //         'tanggal_jatuh_tempo' => '2025-01-10',
        //         'tanggal_bayar'       => now(),
        //         'keterangan'          => 'Pembayaran penuh',
        //         'created_by'          => 1,
        //         'updated_by'          => null,
        //         'deleted_by'          => null,
        //         'created_at'          => now(),
        //         'updated_at'          => now(),
        //         'deleted_at'          => null,
        //     ],
        //     [
        //         'tagihan_id'          => 2,
        //         'santri_id'           => 1,
        //         'total_potongan'      => 0,
        //         'total_tagihan'       => 1500000,
        //         'status'              => 'pending',
        //         'tanggal_jatuh_tempo' => '2025-07-15',
        //         'tanggal_bayar'       => null,
        //         'keterangan'          => null,
        //         'created_by'          => 1,
        //         'updated_by'          => null,
        //         'deleted_by'          => null,
        //         'created_at'          => now(),
        //         'updated_at'          => now(),
        //         'deleted_at'          => null,
        //     ],
        // ]);


        /**
         * PEMBAYARAN
         */
        // DB::table('pembayaran')->insert([
        //     [
        //         'tagihan_santri_id'  => 2, // sesuai dengan data "lunas"
        //         'virtual_account_id' => 1,
        //         'metode'             => 'VA',
        //         'jumlah_bayar'       => 350000,
        //         'tanggal_bayar'      => now(),
        //         'status'             => 'berhasil',
        //         'keterangan'         => 'Pembayaran SPP Januari via Bank Jatim',
        //         'created_by'         => 1,
        //         'created_at'         => now(),
        //         'updated_at'         => now(),
        //     ],
        //     [
        //         'tagihan_santri_id'  => 3, // daftar ulang (masih pending meski ada pembayaran)
        //         'virtual_account_id' => 2,
        //         'metode'             => 'CASH',
        //         'jumlah_bayar'       => 500000,
        //         'tanggal_bayar'      => now(),
        //         'status'             => 'berhasil',
        //         'keterangan'         => 'Pembayaran daftar ulang via kasir (belum lunas)',
        //         'created_by'         => 1,
        //         'created_at'         => now(),
        //         'updated_at'         => now(),
        //     ],
        // ]);
    }
}
