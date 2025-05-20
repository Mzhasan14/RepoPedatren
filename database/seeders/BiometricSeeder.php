<?php

namespace Database\Seeders;

use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class BiometricSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
   public function run()
    {
        // 1. Tambah biometric_devices
        $devices = [
            [
                'id' => Str::uuid(),
                'device_name' => 'Fingerprint Reader A',
                'location' => 'Gerbang Utama',
                'ip_address' => '192.168.1.10',
                'type' => 'fingerprint',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'device_name' => 'Card Reader B',
                'location' => 'Pintu Belakang',
                'ip_address' => '192.168.1.11',
                'type' => 'card',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('biometric_devices')->insert($devices);

        // 2. Ambil beberapa santri
        $santriList = DB::table('santri')->limit(5)->get(); // pastikan ada data di tabel santri

        foreach ($santriList as $santri) {
            $profileId = Str::uuid();

            // 3. Tambah biometric_profiles
            DB::table('biometric_profiles')->insert([
                'id' => $profileId,
                'santri_id' => $santri->id,
                'card_uid' => Str::upper(Str::random(10)),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 4. Tambah finger positions
            $positions = ['right_thumb', 'left_index'];
            foreach ($positions as $pos) {
                $fingerPositionId = Str::uuid();

                DB::table('biometric_finger_positions')->insert([
                    'id' => $fingerPositionId,
                    'biometric_profile_id' => $profileId,
                    'finger_position' => $pos,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // 5. Tambah fingerprint templates (multiple scans per finger)
                for ($i = 1; $i <= 2; $i++) {
                    DB::table('biometric_fingerprint_templates')->insert([
                        'id' => Str::uuid(),
                        'finger_position_id' => $fingerPositionId,
                        'template' => base64_encode(Str::random(100)), // simulasi template fingerprint
                        'scan_order' => $i,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            // 6. Tambah biometric_logs
            foreach (range(1, 3) as $i) {
                DB::table('biometric_logs')->insert([
                    'id' => Str::uuid(),
                    'biometric_profile_id' => $profileId,
                    'device_id' => $devices[array_rand($devices)]['id'],
                    'method' => ['fingerprint', 'card'][rand(0,1)],
                    'scanned_at' => Carbon::now()->subMinutes(rand(0, 500)),
                    'success' => rand(0,1),
                    'message' => rand(0,1) ? null : 'Gagal otentikasi',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
