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
        $tanggalMulai  = now()->startOfMonth()->format('Y-m-d');
        $tanggalSampai = now()->endOfMonth()->format('Y-m-d');

        $jadwalData = [
            ['sholat_id' => 1, 'jam_mulai' => '04:30', 'jam_selesai' => '05:00'],
            ['sholat_id' => 2, 'jam_mulai' => '11:45', 'jam_selesai' => '12:30'],
            ['sholat_id' => 3, 'jam_mulai' => '15:15', 'jam_selesai' => '15:45'],
            ['sholat_id' => 4, 'jam_mulai' => '17:35', 'jam_selesai' => '18:05'],
            ['sholat_id' => 5, 'jam_mulai' => '19:00', 'jam_selesai' => '19:30'],
        ];

        foreach ($jadwalData as $item) {
            JadwalSholat::create(array_merge($item, [
                'berlaku_mulai' => $tanggalMulai,
                'berlaku_sampai' => $tanggalSampai,
                'created_by'    => $adminId
            ]));
        }

        /**
         * 3. PILIH 8 SANTRI (4 L, 4 P) DENGAN 1 PASANGAN SAUDARA
         */
        $santriAll = DB::table('santri')
            ->join('biodata', 'santri.biodata_id', '=', 'biodata.id')
            ->leftJoin('keluarga', 'biodata.id', '=', 'keluarga.id_biodata')
            ->where('santri.status', 'aktif')
            ->select(
                'santri.id as santri_id',
                'biodata.nama',
                'biodata.jenis_kelamin',
                'keluarga.no_kk'
            )
            ->get();

        // Cari pasangan saudara (1 L & 1 P)
        $kkGroups = $santriAll->groupBy('no_kk')->filter(function ($group) {
            return $group->count() >= 2 && $group->pluck('jenis_kelamin')->unique()->count() >= 2;
        });

        if ($kkGroups->isEmpty()) {
            throw new \Exception('Tidak ada pasangan saudara laki-laki & perempuan di database!');
        }

        $pair = $kkGroups->first();
        $saudaraL = $pair->firstWhere('jenis_kelamin', 'l');
        $saudaraP = $pair->firstWhere('jenis_kelamin', 'p');

        // Ambil tambahan 3 L dan 3 P lainnya (tidak termasuk pasangan)
        $otherL = $santriAll->where('jenis_kelamin', 'l')->where('santri_id', '!=', $saudaraL->santri_id)->take(3);
        $otherP = $santriAll->where('jenis_kelamin', 'p')->where('santri_id', '!=', $saudaraP->santri_id)->take(3);

        $selectedSantri = collect([$saudaraL, $saudaraP])->merge($otherL)->merge($otherP)->values();

        /**
         * 4. KARTU
         */
        $uidList = [
            '0723409199',
            '0731609999',
            '0724142895',
            '0722142575',
            '0726173807',
            '0733728351',
            '0735066159',
            '0726104367'
        ];

        foreach ($selectedSantri as $index => $santri) {
            Kartu::create([
                'santri_id' => $santri->santri_id,
                'uid_kartu' => $uidList[$index] ?? '0723000000' . $index,
                'pin' => Hash::make('1234'),
                'aktif' => true,
                // 'tanggal_terbit' => now()->subMonths(1)->format('Y-m-d'),
                // 'tanggal_expired' => now()->addYears(2)->format('Y-m-d'),
                'created_by' => $adminId
            ]);
        }

        /**
         * 5. PRESENSI SHOLAT & LOG PRESENSI
         */
        $tanggalPresensi = '2025-08-13';
        $jamPresensi = [
            1 => '04:35:00',
            2 => '12:05:00',
            3 => '15:20:00',
            4 => '18:05:00',
            5 => '19:20:00',
        ];

        foreach ($selectedSantri as $index => $santri) {
            $kartuId = $index + 1; // asumsi kartu ID sesuai urutan

            foreach (range(1, 5) as $sholatId) {
                DB::table('presensi_sholat')->insert([
                    'santri_id' => $santri->santri_id,
                    'sholat_id' => $sholatId,
                    'tanggal' => $tanggalPresensi,
                    'waktu_presensi' => $jamPresensi[$sholatId],
                    'status' => 'Hadir',
                    'metode' => 'Kartu',
                    'created_by' => $adminId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('log_presensi')->insert([
                    'santri_id' => $santri->santri_id,
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

        $this->command->info("Seeder berhasil: 8 santri (4L/4P) termasuk pasangan saudara sudah dibuat kartu & presensi.");
    }
}
