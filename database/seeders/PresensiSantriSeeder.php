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
        // Seeder jenis_presensi
        $jenisPresensi = [
            ['nama' => 'Presensi Tahajjud', 'deskripsi' => 'Salat Tahajjud berjamaah di masjid', 'aktif' => true],
            ['nama' => 'Presensi Subuh', 'deskripsi' => 'Salat Subuh berjamaah di masjid', 'aktif' => true],
            ['nama' => 'Presensi Malam Jumat', 'deskripsi' => 'Pembacaan Yasin dan Tahlil setiap malam Jumat', 'aktif' => true],
            ['nama' => 'Presensi Dhuha', 'deskripsi' => 'Salat Dhuha di pagi hari', 'aktif' => true],
            ['nama' => 'Presensi Maghrib', 'deskripsi' => 'Salat Maghrib berjamaah di masjid', 'aktif' => true],
            ['nama' => 'Presensi Isya', 'deskripsi' => 'Salat Isya berjamaah di masjid', 'aktif' => true],
            ['nama' => 'Presensi Kegiatan Sore', 'deskripsi' => 'Kegiatan ekstra sore hari', 'aktif' => true],
        ];

        DB::table('jenis_presensi')->insert($jenisPresensi);

        $jenisIds = DB::table('jenis_presensi')->pluck('id', 'nama');

        // --- Seeder presensi_santri anti duplicate ---
        foreach ($jenisIds as $nama => $jenisId) {
            $used = [];
            $loop = 0;
            while (count($used) < 30 && $loop < 1000) { // 30 unik per jenis, max 1000 percobaan biar tidak infinite loop
                $santriId = rand(1, 50);
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
                        'created_by' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
                $loop++;
            }
        }
    }
}
