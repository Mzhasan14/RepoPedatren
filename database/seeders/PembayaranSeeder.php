<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PembayaranSeeder extends Seeder
{
    public function run(): void
    {
        /**
         * BANK
         */
        DB::table('banks')->insert([
            [
                'kode_bank' => 'BJTM',
                'nama_bank' => 'Bank Jatim',
                'status' => true,
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'kode_bank' => 'BNI',
                'nama_bank' => 'Bank Negara Indonesia',
                'status' => true,
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        /**
         * VIRTUAL ACCOUNT
         * contoh untuk santri id 1 & 2
         */
        DB::table('virtual_accounts')->insert([
            [
                'santri_id' => 1,
                'bank_code' => 'BJTM',
                'va_number' => '9001234567891',
                'status' => true,
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'santri_id' => 2,
                'bank_code' => 'BNI',
                'va_number' => '9881234567892',
                'status' => true,
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
                'kode_tagihan' => 'SPP2025JAN',
                'nama_tagihan' => 'SPP Januari 2025',
                'nominal' => 350000,
                'jatuh_tempo' => '2025-01-10',
                'status' => true,
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'kode_tagihan' => 'DAFTARULANG2025',
                'nama_tagihan' => 'Daftar Ulang Santri 2025/2026',
                'nominal' => 1500000,
                'jatuh_tempo' => '2025-07-15',
                'status' => true,
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'kode_tagihan' => 'KITAB2025',
                'nama_tagihan' => 'Pembelian Kitab Tahun 2025',
                'nominal' => 500000,
                'jatuh_tempo' => '2025-08-30',
                'status' => true,
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        /**
         * TAGIHAN SANTRI
         */
        DB::table('tagihan_santri')->insert([
            [
                'tagihan_id' => 1, // SPP Jan
                'santri_id' => 1,
                'nominal' => 350000,
                'status' => 'pending',
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tagihan_id' => 1,
                'santri_id' => 2,
                'nominal' => 350000,
                'status' => 'lunas',
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tagihan_id' => 2, // daftar ulang
                'santri_id' => 1,
                'nominal' => 1500000,
                'status' => 'sebagian',
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        /**
         * PEMBAYARAN
         */
        DB::table('pembayaran')->insert([
            [
                'tagihan_id' => 1,
                'virtual_account_id' => 1,
                'metode' => 'VA',
                'jumlah_bayar' => 350000,
                'tanggal_bayar' => now(),
                'status' => 'berhasil',
                'keterangan' => 'Pembayaran SPP Januari via Bank Jatim',
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tagihan_id' => 2,
                'virtual_account_id' => null,
                'metode' => 'CASH',
                'jumlah_bayar' => 500000,
                'tanggal_bayar' => now(),
                'status' => 'berhasil',
                'keterangan' => 'Cicilan daftar ulang via kasir',
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
