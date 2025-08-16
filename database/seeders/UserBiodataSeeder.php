<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserBiodataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. User biasa (bukan wali asuh) → ambil biodata yang BUKAN santri aktif
        $usersNonWaliAsuh = User::whereDoesntHave('roles', function ($query) {
            $query->where('name', 'waliasuh');
        })->pluck('id');

        $biodataNonSantriAktif = DB::table('biodata')
            ->whereNotIn('id', function ($query) {
                $query->select('biodata_id')
                    ->from('santri')
                    ->where('status', 'aktif');
            })
            ->pluck('id')
            ->toArray();

        foreach ($usersNonWaliAsuh as $userId) {
            $biodataId = collect($biodataNonSantriAktif)->random();
            DB::table('user_biodata')->updateOrInsert(
                ['user_id' => $userId],
                ['biodata_id' => $biodataId, 'created_at' => now(), 'updated_at' => now()]
            );
        }

        // 2. User dengan role Wali Asuh → ambil dari biodata -> santri -> wali_asuh
        $waliAsuhUsers = DB::table('users')
            ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->where('roles.name', 'waliasuh')
            ->pluck('users.id');

        $waliAsuhList = DB::table('wali_asuh')
            ->join('santri', 'wali_asuh.id_santri', '=', 'santri.id')
            ->join('biodata', 'santri.biodata_id', '=', 'biodata.id')
            ->select('wali_asuh.id as wali_asuh_id', 'biodata.id as biodata_id')
            ->get();

        foreach ($waliAsuhUsers as $index => $userId) {
            if (!isset($waliAsuhList[$index])) continue;
            $biodataId = $waliAsuhList[$index]->biodata_id;

            DB::table('user_biodata')->updateOrInsert(
                ['user_id' => $userId],
                ['biodata_id' => $biodataId, 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }
}
