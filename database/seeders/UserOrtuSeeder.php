<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserOrtuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil semua santri yang berdomisili di pesantren
        $santriList = DB::table('santri as s')
            ->join('domisili_santri as ds', function ($join) {
                $join->on('s.id', '=', 'ds.santri_id')
                    ->where('ds.status', 'aktif');
            })
            ->join('biodata as b', 's.biodata_id', '=', 'b.id')
            ->where('s.status', 'aktif')
            ->select('s.id as santri_id', 's.nis', 'b.tanggal_lahir')
            ->get();

        foreach ($santriList as $santri) {
            // Format password: ddmmyyyy
            $passwordPlain = date('dmY', strtotime($santri->tanggal_lahir));

            // Cek kalau user_ortu belum ada
            $exists = DB::table('user_ortu')->where('username', $santri->nis)->exists();
            if (!$exists) {
                DB::table('user_ortu')->insert([
                    'username'   => $santri->nis,
                    'password'   => Hash::make($passwordPlain),
                    'status'     => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
