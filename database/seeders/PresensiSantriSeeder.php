<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PresensiSantriSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Data jenis_presensi dengan kode unik (bisa di-generate atau tentukan sendiri)
        $jenisPresensi = [
            [
                'kode' => 'TAHAJJUD',
                'nama' => 'Presensi Tahajjud',
                'deskripsi' => 'Salat Tahajjud berjamaah di masjid',
                'aktif' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'kode' => 'SUBUH',
                'nama' => 'Presensi Subuh',
                'deskripsi' => 'Salat Subuh berjamaah di masjid',
                'aktif' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'kode' => 'MLM_JUMAT',
                'nama' => 'Presensi Malam Jumat',
                'deskripsi' => 'Pembacaan Yasin dan Tahlil setiap malam Jumat',
                'aktif' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'kode' => 'DHUHA',
                'nama' => 'Presensi Dhuha',
                'deskripsi' => 'Salat Dhuha di pagi hari',
                'aktif' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'kode' => 'MAGHRIB',
                'nama' => 'Presensi Maghrib',
                'deskripsi' => 'Salat Maghrib berjamaah di masjid',
                'aktif' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'kode' => 'ISYA',
                'nama' => 'Presensi Isya',
                'deskripsi' => 'Salat Isya berjamaah di masjid',
                'aktif' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'kode' => 'KEG_SORE',
                'nama' => 'Presensi Kegiatan Sore',
                'deskripsi' => 'Kegiatan ekstra sore hari',
                'aktif' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Insert ke jenis_presensi
        DB::table('jenis_presensi')->insert($jenisPresensi);

        // Ambil id dari nama jenis_presensi yang sudah diinsert
        $jenisIds = DB::table('jenis_presensi')->pluck('id', 'nama');

        // Seeder presensi_santri anti duplicate
        foreach ($jenisIds as $nama => $jenisId) {
            $used = [];
            $loop = 0;
            while (count($used) < 30 && $loop < 1000) {
                $santriId = rand(1, 50); // Pastikan santri dengan id 1-50 ada
                $tanggal = Carbon::today()->subDays(rand(0, 60))->toDateString();
                $uniqueKey = "{$santriId}-{$jenisId}-{$tanggal}";

                if (!isset($used[$uniqueKey])) {
                    $used[$uniqueKey] = true;
                    DB::table('presensi_santri')->insert([
                        'santri_id' => $santriId,
                        'jenis_presensi_id' => $jenisId,
                        'tanggal' => $tanggal,
                        'waktu_presensi' => Carbon::now()->subMinutes(rand(1, 240))->format('H:i:s'),
                        'status' => ['hadir', 'izin', 'sakit', 'alfa'][array_rand(['hadir', 'izin', 'sakit', 'alfa'])],
                        'keterangan' => null,
                        'lokasi' => 'Masjid',
                        'metode' => ['qr', 'manual', 'rfid', 'fingerprint'][array_rand(['qr', 'manual', 'rfid', 'fingerprint'])],
                        'biometric_log_id' => null,
                        'device_id' => null,
                        'created_by' => 1, // Atau sesuaikan user admin
                        'updated_by' => 1,
                        'deleted_by' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                        'deleted_at' => null,
                    ]);
                }
                $loop++;
            }
        }
    }
}
