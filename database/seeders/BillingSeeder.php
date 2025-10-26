<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class BillingSeeder extends Seeder
{
    public function run(): void
    {
        // ==============================
        // TAGIHAN (Master)
        // ==============================
        $tagihanIds = [];
        $tagihanIds[] = DB::table('tagihan')->insertGetId([
            'nama_tagihan' => 'SPP Bulanan',
            'tipe' => 'bulanan',
            'nominal' => 250000,
            'jatuh_tempo' => Carbon::now()->addDays(10),
            'status' => true,
            'created_by' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $tagihanIds[] = DB::table('tagihan')->insertGetId([
            'nama_tagihan' => 'Uang Pangkal',
            'tipe' => 'sekali_bayar',
            'nominal' => 1500000,
            'jatuh_tempo' => Carbon::now()->addMonths(1),
            'status' => true,
            'created_by' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $tagihanIds[] = DB::table('tagihan')->insertGetId([
            'nama_tagihan' => 'Biaya Semester',
            'tipe' => 'semester',
            'nominal' => 1200000,
            'jatuh_tempo' => Carbon::now()->addMonths(6),
            'status' => true,
            'created_by' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // ==============================
        // POTONGAN (Master)
        // ==============================
        $potonganIds = [];
        $potonganIds['anak_pegawai'] = DB::table('potongan')->insertGetId([
            'nama' => 'Potongan Anak Pegawai',
            'kategori' => 'anak_pegawai',
            'jenis' => 'persentase',
            'nilai' => 50, // 50%
            'status' => true,
            'keterangan' => 'Diskon khusus anak pegawai pondok',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $potonganIds['bersaudara'] = DB::table('potongan')->insertGetId([
            'nama' => 'Diskon Bersaudara',
            'kategori' => 'bersaudara',
            'jenis' => 'persentase',
            'nilai' => 25,
            'status' => true,
            'keterangan' => 'Diskon untuk saudara kandung',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $potonganIds['khadam'] = DB::table('potongan')->insertGetId([
            'nama' => 'Diskon Khadam',
            'kategori' => 'khadam',
            'jenis' => 'nominal',
            'nilai' => 100000,
            'status' => true,
            'keterangan' => 'Diskon untuk santri khadam',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $potonganIds['umum'] = DB::table('potongan')->insertGetId([
            'nama' => 'Diskon Umum',
            'kategori' => 'umum',
            'jenis' => 'nominal',
            'nilai' => 50000,
            'status' => true,
            'keterangan' => 'Diskon umum untuk santri tertentu',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // ==============================
        // RELASI POTONGAN ↔ TAGIHAN
        // ==============================
        DB::table('potongan_tagihan')->insert([
            [
                'potongan_id' => $potonganIds['anak_pegawai'],
                'tagihan_id' => $tagihanIds[0], // berlaku untuk SPP Bulanan
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'potongan_id' => $potonganIds['bersaudara'],
                'tagihan_id' => $tagihanIds[0], // berlaku untuk SPP Bulanan
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'potongan_id' => $potonganIds['khadam'],
                'tagihan_id' => $tagihanIds[0], // berlaku untuk SPP Bulanan
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'potongan_id' => $potonganIds['umum'],
                'tagihan_id' => $tagihanIds[2], // Biaya Semester
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // ==============================
        // RELASI SANTRI ↔ POTONGAN
        // ==============================
        // hanya untuk kategori UMUM
        DB::table('santri_potongan')->insert([
            [
                'santri_id' => 3,
                'potongan_id' => $potonganIds['umum'],
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
