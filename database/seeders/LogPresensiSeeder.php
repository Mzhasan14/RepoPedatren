<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LogPresensiSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // 1. SHOLAT
        DB::table('sholat')->insert([
            [
                'nama_sholat' => 'Shubuh',
                'urutan' => 1,
                'aktif' => true,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'nama_sholat' => 'Dzuhur',
                'urutan' => 2,
                'aktif' => true,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'nama_sholat' => 'Ashar',
                'urutan' => 3,
                'aktif' => true,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'nama_sholat' => 'Maghrib',
                'urutan' => 4,
                'aktif' => true,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'nama_sholat' => 'Isya',
                'urutan' => 5,
                'aktif' => true,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        // 2. JADWAL SHOLAT (contoh berlaku 1 minggu)
        DB::table('jadwal_sholat')->insert([
            [
                'sholat_id' => 1,
                'jam_mulai' => '04:30:00',
                'jam_selesai' => '05:15:00',
                'berlaku_mulai' => '2025-08-01',
                'berlaku_sampai' => '2025-08-31',
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'sholat_id' => 2,
                'jam_mulai' => '12:00:00',
                'jam_selesai' => '12:45:00',
                'berlaku_mulai' => '2025-08-01',
                'berlaku_sampai' => '2025-08-31',
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'sholat_id' => 3,
                'jam_mulai' => '15:15:00',
                'jam_selesai' => '16:00:00',
                'berlaku_mulai' => '2025-08-01',
                'berlaku_sampai' => '2025-08-31',
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'sholat_id' => 4,
                'jam_mulai' => '17:45:00',
                'jam_selesai' => '18:30:00',
                'berlaku_mulai' => '2025-08-01',
                'berlaku_sampai' => '2025-08-31',
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'sholat_id' => 5,
                'jam_mulai' => '19:00:00',
                'jam_selesai' => '19:45:00',
                'berlaku_mulai' => '2025-08-01',
                'berlaku_sampai' => '2025-08-31',
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        // 3. KARTU (pastikan santri_id sesuai yang sudah ada)
        DB::table('kartu')->insert([
            [
                'santri_id' => 1,
                'uid_kartu' => 'UID1234567890',
                'aktif' => true,
                'tanggal_terbit' => '2025-08-01',
                'tanggal_expired' => '2026-08-01',
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'santri_id' => 2,
                'uid_kartu' => 'UID0987654321',
                'aktif' => true,
                'tanggal_terbit' => '2025-08-01',
                'tanggal_expired' => '2026-08-01',
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'santri_id' => 3,
                'uid_kartu' => 'UID5555555555',
                'aktif' => true,
                'tanggal_terbit' => '2025-08-01',
                'tanggal_expired' => '2026-08-01',
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        // 4. LOG PRESENSI (contoh dummy untuk test scan Postman)
        DB::table('log_presensi')->insert([
            [
                'santri_id' => 1,
                'kartu_id' => 1,
                'sholat_id' => 1,
                'waktu_scan' => Carbon::now(),
                'hasil' => 'Sukses',
                'pesan' => 'Presensi berhasil',
                'metode' => 'Kartu',
                'user_id' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'santri_id' => 2,
                'kartu_id' => 2,
                'sholat_id' => 2,
                'waktu_scan' => Carbon::now(),
                'hasil' => 'Gagal',
                'pesan' => 'Kartu tidak terdaftar',
                'metode' => 'Kartu',
                'user_id' => 1,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
