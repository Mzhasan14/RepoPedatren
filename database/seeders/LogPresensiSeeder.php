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
            ['sholat_id' => 1, 'jam_mulai' => '04:30', 'jam_selesai' => '11:59'],
            ['sholat_id' => 2, 'jam_mulai' => '12:00', 'jam_selesai' => '13:00'],
            ['sholat_id' => 3, 'jam_mulai' => '15:15', 'jam_selesai' => '16:15'],
            ['sholat_id' => 4, 'jam_mulai' => '18:00', 'jam_selesai' => '18:45'],
            ['sholat_id' => 5, 'jam_mulai' => '19:15', 'jam_selesai' => '23:59'],
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
         * UID decimal manual (asumsi 5 santri)
         */
        $uidList = [
            '0723409199',
            '0731609999',
            '0724142895',
            '0722142575',
            '0733728351',
        ];

        foreach ($uidList as $index => $uid) {
            Kartu::create([
                'santri_id' => $index + 1,
                'uid_kartu' => $uid,
                'pin' => Hash::make('1234'),
                'aktif' => true,
                'tanggal_terbit' => now()->subMonths(1)->format('Y-m-d'),
                'tanggal_expired' => now()->addYears(2)->format('Y-m-d'),
                'created_by' => $adminId
            ]);
        }

        /**
         * 4. PRESENSI SHOLAT & LOG PRESENSI
         * Asumsi semua hadir di tanggal 2025-08-13
         */
        $tanggalPresensi = '2025-08-13';
        $jamPresensi = [
            1 => '04:35:00', // Subuh
            2 => '12:05:00', // Dzuhur
            3 => '15:20:00', // Ashar
            4 => '18:05:00', // Maghrib
            5 => '19:20:00', // Isya
        ];

        foreach (range(1, 5) as $santriId) {
            $kartuId = $santriId; // karena urutan kartu sesuai ID santri
            foreach (range(1, 5) as $sholatId) {
                // Insert ke presensi_sholat
                DB::table('presensi_sholat')->insert([
                    'santri_id' => $santriId,
                    'sholat_id' => $sholatId,
                    'tanggal' => $tanggalPresensi,
                    'waktu_presensi' => $jamPresensi[$sholatId],
                    'status' => 'Hadir',
                    'metode' => 'Kartu',
                    'created_by' => $adminId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Insert ke log_presensi
                DB::table('log_presensi')->insert([
                    'santri_id' => $santriId,
                    'kartu_id' => $kartuId,
                    'sholat_id' => $sholatId,
                    'waktu_scan' => $tanggalPresensi . ' ' . $jamPresensi[$sholatId],
                    'hasil' => 'Sukses',
                    'pesan' => null,
                    'metode' => 'Kartu',
                    'user_id' => null,
                    'created_by' => $adminId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
