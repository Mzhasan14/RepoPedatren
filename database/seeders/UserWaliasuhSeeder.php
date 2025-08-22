<?php

namespace Database\Seeders;

use App\Models\Biodata;
use App\Models\Kewaliasuhan\Wali_asuh;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserWaliasuhSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Ambil User Wali Asuh
        $waliAsuhUser = User::where('email', 'waliasuh@example.com')->first();

        if (!$waliAsuhUser) {
            $this->command->warn('User Wali Asuh belum ada.');
            return;
        }

        // 2. Ambil biodata dari relasi Wali Asuh → Santri
        $waliAsuh = Wali_asuh::first();

        if (!$waliAsuh || !$waliAsuh->santri) {
            $this->command->warn('Belum ada relasi Wali Asuh ke Santri.');
            return;
        }

        $biodataId = $waliAsuh->santri->biodata_id;

        if (!$biodataId) {
            $this->command->warn('Santri terkait Wali Asuh belum punya biodata.');
            return;
        }

        // 3. Update User Wali Asuh dengan biodata yang benar
        $waliAsuhUser->update([
            'biodata_id' => $biodataId,
        ]);

        $this->command->info("User Wali Asuh berhasil diupdate dengan biodata_id: $biodataId");

        // 4. Hapus biodata system lama
        $systemBiodata = Biodata::where('email', 'system@example.com')->first();

        if ($systemBiodata) {
            $systemBiodata->delete();
            $this->command->info("Biodata system (system@example.com) berhasil dihapus.");
        } else {
            $this->command->warn("Biodata system tidak ditemukan, tidak ada yang dihapus.");
        }
    }
}
