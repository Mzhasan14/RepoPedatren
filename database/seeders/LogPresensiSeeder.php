<?php

namespace Database\Seeders;

use Carbon\Carbon;
use App\Models\Kartu;
use App\Models\Sholat;
use App\Models\LogPresensi;
use App\Models\JadwalSholat;
use App\Models\PresensiSholat;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class LogPresensiSeeder extends Seeder
{
    // public function run(): void
    // {
    //     $now = Carbon::now();

    //     // 1. SHOLAT
    //     DB::table('sholat')->insert([
    //         [
    //             'nama_sholat' => 'Shubuh',
    //             'urutan' => 1,
    //             'aktif' => true,
    //             'created_by' => 1,
    //             'created_at' => $now,
    //             'updated_at' => $now,
    //         ],
    //         [
    //             'nama_sholat' => 'Dzuhur',
    //             'urutan' => 2,
    //             'aktif' => true,
    //             'created_by' => 1,
    //             'created_at' => $now,
    //             'updated_at' => $now,
    //         ],
    //         [
    //             'nama_sholat' => 'Ashar',
    //             'urutan' => 3,
    //             'aktif' => true,
    //             'created_by' => 1,
    //             'created_at' => $now,
    //             'updated_at' => $now,
    //         ],
    //         [
    //             'nama_sholat' => 'Maghrib',
    //             'urutan' => 4,
    //             'aktif' => true,
    //             'created_by' => 1,
    //             'created_at' => $now,
    //             'updated_at' => $now,
    //         ],
    //         [
    //             'nama_sholat' => 'Isya',
    //             'urutan' => 5,
    //             'aktif' => true,
    //             'created_by' => 1,
    //             'created_at' => $now,
    //             'updated_at' => $now,
    //         ],
    //     ]);

    //     // 2. JADWAL SHOLAT (contoh berlaku 1 minggu)
    //     DB::table('jadwal_sholat')->insert([
    //         [
    //             'sholat_id' => 1,
    //             'jam_mulai' => '04:30:00',
    //             'jam_selesai' => '05:15:00',
    //             'berlaku_mulai' => '2025-08-01',
    //             'berlaku_sampai' => '2025-08-31',
    //             'created_by' => 1,
    //             'created_at' => $now,
    //             'updated_at' => $now,
    //         ],
    //         [
    //             'sholat_id' => 2,
    //             'jam_mulai' => '12:00:00',
    //             'jam_selesai' => '12:45:00',
    //             'berlaku_mulai' => '2025-08-01',
    //             'berlaku_sampai' => '2025-08-31',
    //             'created_by' => 1,
    //             'created_at' => $now,
    //             'updated_at' => $now,
    //         ],
    //         [
    //             'sholat_id' => 3,
    //             'jam_mulai' => '15:15:00',
    //             'jam_selesai' => '16:00:00',
    //             'berlaku_mulai' => '2025-08-01',
    //             'berlaku_sampai' => '2025-08-31',
    //             'created_by' => 1,
    //             'created_at' => $now,
    //             'updated_at' => $now,
    //         ],
    //         [
    //             'sholat_id' => 4,
    //             'jam_mulai' => '17:45:00',
    //             'jam_selesai' => '18:30:00',
    //             'berlaku_mulai' => '2025-08-01',
    //             'berlaku_sampai' => '2025-08-31',
    //             'created_by' => 1,
    //             'created_at' => $now,
    //             'updated_at' => $now,
    //         ],
    //         [
    //             'sholat_id' => 5,
    //             'jam_mulai' => '19:00:00',
    //             'jam_selesai' => '19:45:00',
    //             'berlaku_mulai' => '2025-08-01',
    //             'berlaku_sampai' => '2025-08-31',
    //             'created_by' => 1,
    //             'created_at' => $now,
    //             'updated_at' => $now,
    //         ],
    //     ]);

    //     // 3. KARTU (pastikan santri_id sesuai yang sudah ada)
    //     DB::table('kartu')->insert([
    //         [
    //             'santri_id' => 1,
    //             'uid_kartu' => 'UID1234567890',
    //             'aktif' => true,
    //             'tanggal_terbit' => '2025-08-01',
    //             'tanggal_expired' => '2026-08-01',
    //             'created_by' => 1,
    //             'created_at' => $now,
    //             'updated_at' => $now,
    //         ],
    //         [
    //             'santri_id' => 2,
    //             'uid_kartu' => 'UID0987654321',
    //             'aktif' => true,
    //             'tanggal_terbit' => '2025-08-01',
    //             'tanggal_expired' => '2026-08-01',
    //             'created_by' => 1,
    //             'created_at' => $now,
    //             'updated_at' => $now,
    //         ],
    //         [
    //             'santri_id' => 3,
    //             'uid_kartu' => 'UID5555555555',
    //             'aktif' => true,
    //             'tanggal_terbit' => '2025-08-01',
    //             'tanggal_expired' => '2026-08-01',
    //             'created_by' => 1,
    //             'created_at' => $now,
    //             'updated_at' => $now,
    //         ],
    //     ]);

    //     // 4. LOG PRESENSI (contoh dummy untuk test scan Postman)
    //     DB::table('log_presensi')->insert([
    //         [
    //             'santri_id' => 1,
    //             'kartu_id' => 1,
    //             'sholat_id' => 1,
    //             'waktu_scan' => Carbon::now(),
    //             'hasil' => 'Sukses',
    //             'pesan' => 'Presensi berhasil',
    //             'metode' => 'Kartu',
    //             'user_id' => 1,
    //             'created_by' => 1,
    //             'created_at' => $now,
    //             'updated_at' => $now,
    //         ],
    //         [
    //             'santri_id' => 2,
    //             'kartu_id' => 2,
    //             'sholat_id' => 2,
    //             'waktu_scan' => Carbon::now(),
    //             'hasil' => 'Gagal',
    //             'pesan' => 'Kartu tidak terdaftar',
    //             'metode' => 'Kartu',
    //             'user_id' => 1,
    //             'created_by' => 1,
    //             'created_at' => $now,
    //             'updated_at' => $now,
    //         ],
    //     ]);
    // }

    
    public function run(): void
    {
        $adminId = 1; // ID user admin pertama

        /**
         * 1. SHOLAT
         */
        $sholatData = [
            ['nama_sholat' => 'Subuh',   'urutan' => 1],
            ['nama_sholat' => 'Dzuhur',  'urutan' => 2],
            ['nama_sholat' => 'Ashar',   'urutan' => 3],
            ['nama_sholat' => 'Maghrib', 'urutan' => 4],
            ['nama_sholat' => 'Isya',    'urutan' => 5],
        ];

        foreach ($sholatData as $item) {
            Sholat::create(array_merge($item, [
                'aktif' => true,
                'created_by' => $adminId
            ]));
        }

        /**
         * 2. JADWAL SHOLAT
         */
        $tanggalMulai = now()->startOfMonth()->format('Y-m-d');
        $tanggalSampai = now()->endOfMonth()->format('Y-m-d');

        $jadwalData = [
            ['sholat_id' => 1, 'jam_mulai' => '04:30', 'jam_selesai' => '05:30'],
            ['sholat_id' => 2, 'jam_mulai' => '12:00', 'jam_selesai' => '13:00'],
            ['sholat_id' => 3, 'jam_mulai' => '15:15', 'jam_selesai' => '16:15'],
            ['sholat_id' => 4, 'jam_mulai' => '18:00', 'jam_selesai' => '18:45'],
            ['sholat_id' => 5, 'jam_mulai' => '19:15', 'jam_selesai' => '20:00'],
        ];

        foreach ($jadwalData as $item) {
            JadwalSholat::create(array_merge($item, [
                'berlaku_mulai' => $tanggalMulai,
                'berlaku_sampai' => $tanggalSampai,
                'created_by' => $adminId
            ]));
        }

        /**
         * 3. KARTU
         * (Asumsikan sudah ada 5 santri di tabel santri)
         */
        foreach (range(1, 5) as $santriId) {
            Kartu::create([
                'santri_id' => $santriId,
                'uid_kartu' => 'UID' . str_pad($santriId, 6, '0', STR_PAD_LEFT),
                'pin' => Hash::make('1234'),
                'aktif' => true,
                'tanggal_terbit' => now()->subMonths(1)->format('Y-m-d'),
                'tanggal_expired' => now()->addYears(2)->format('Y-m-d'),
                'created_by' => $adminId
            ]);
        }

        /**
         * 4. PRESENSI SHOLAT
         */
        $tanggalPresensi = now()->format('Y-m-d');
        foreach (range(1, 5) as $santriId) {
            foreach (range(1, 5) as $sholatId) {
                PresensiSholat::create([
                    'santri_id' => $santriId,
                    'sholat_id' => $sholatId,
                    'tanggal' => $tanggalPresensi,
                    'waktu_presensi' => now()->format('H:i:s'),
                    'status' => 'Hadir',
                    'metode' => 'Kartu',
                    'created_by' => $adminId
                ]);
            }
        }

        /**
         * 5. LOG PRESENSI
         */
        foreach (range(1, 10) as $i) {
            LogPresensi::create([
                'santri_id' => rand(1, 5),
                'kartu_id' => rand(1, 5),
                'sholat_id' => rand(1, 5),
                'waktu_scan' => now()->subMinutes(rand(1, 300)),
                'hasil' => 'Sukses',
                'pesan' => 'Presensi berhasil',
                'metode' => 'Kartu',
                'user_id' => $adminId,
                'created_by' => $adminId
            ]);
        }
    }
}
