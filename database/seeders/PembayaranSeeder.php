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
                'bank_id' => 1,
                'va_number' => '9001234567891',
                'status' => true,
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'santri_id' => 2,
                'bank_id' => 2,
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
                'nama_tagihan' => 'SPP Januari 2025',
                'tipe' => 'bulanan',
                'nominal' => 350000,
                'jatuh_tempo' => '2025-01-10',
                'status' => true,
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_tagihan' => 'Daftar Ulang Santri 2025/2026',
                'tipe' => 'tahunan',
                'nominal' => 1500000,
                'jatuh_tempo' => '2025-07-15',
                'status' => true,
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_tagihan' => 'Pembelian Kitab Tahun 2025',
                'tipe' => 'sekali_bayar',
                'nominal' => 500000,
                'jatuh_tempo' => '2025-08-30',
                'status' => true,
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        /**
         * TAGIHAN KHUSUS
         * contoh override untuk santri dengan kondisi khusus
         */
        // DB::table('tagihan_khusus')->insert([
        //     [
        //         'tagihan_id' => 1, // SPP Jan
        //         'angkatan_id' => null,
        //         'lembaga_id' => null,
        //         'jurusan_id' => null,
        //         'jenis_kelamin' => null,
        //         'kategori_santri' => null,
        //         'domisili' => null,
        //         'kondisi_khusus' => 'anak_pegawai',
        //         'nominal' => 250000, // potongan khusus anak pegawai
        //         'created_at' => now(),
        //         'updated_at' => now(),
        //     ],
        //     [
        //         'tagihan_id' => 2, // daftar ulang
        //         'angkatan_id' => null,
        //         'lembaga_id' => null,
        //         'jurusan_id' => null,
        //         'jenis_kelamin' => null,
        //         'kategori_santri' => 'mukim',
        //         'domisili' => 'luar_kota',
        //         'kondisi_khusus' => null,
        //         'nominal' => 1200000, // lebih murah untuk santri mukim luar kota
        //         'created_at' => now(),
        //         'updated_at' => now(),
        //     ],
        //     [
        //         'tagihan_id' => 3, // kitab
        //         'angkatan_id' => null,
        //         'lembaga_id' => null,
        //         'jurusan_id' => null,
        //         'jenis_kelamin' => 'p',
        //         'kategori_santri' => null,
        //         'domisili' => null,
        //         'kondisi_khusus' => 'beasiswa',
        //         'nominal' => 0, // gratis untuk santri beasiswa
        //         'created_at' => now(),
        //         'updated_at' => now(),
        //     ],
        // ]);

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
                'tagihan_santri_id' => 1,
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
                'tagihan_santri_id' => 3,
                'virtual_account_id' => 2,
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
