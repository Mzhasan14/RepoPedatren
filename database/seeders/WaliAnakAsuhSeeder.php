<?php

namespace Database\Seeders;

use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WaliAnakAsuhSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('kewaliasuhan')->delete();
        DB::table('anak_asuh')->delete();
        DB::table('wali_asuh')->delete();
        DB::table('grup_wali_asuh')->delete();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $santriList = DB::table('santri')
            ->where('status', 'aktif')
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('domisili_santri')
                    ->whereRaw('domisili_santri.santri_id = santri.id');
            })
            ->pluck('id')
            ->toArray();

        shuffle($santriList);


        $wilayahList = DB::table('wilayah')->pluck('id')->toArray();
        $totalSantri = count($santriList);
        $jumlahGrup = max(20, min(25, intdiv($totalSantri, 13)));

        $waliAsuhList = array_slice($santriList, 0, $jumlahGrup);
        $anakAsuhList = array_slice($santriList, $jumlahGrup);

        // Ambil data biodata jenis kelamin
        $biodataMap = DB::table('biodata')
            ->join('santri', 'biodata.id', '=', 'santri.biodata_id')
            ->pluck('biodata.jenis_kelamin', 'santri.id');

        // Buat grup wali asuh
        $grupWaliAsuhData = [];
        $waliAsuhData = [];
        foreach ($waliAsuhList as $index => $santriId) {
            $jkSantri = $biodataMap[$santriId] ?? 'l';

            // Pilih wilayah acak
            $wilayah = DB::table('wilayah')->inRandomOrder()->first();

            // Tentukan jenis kelamin grup berdasar kategori wilayah
            $jenisKelaminGrup = Str::contains(Str::lower($wilayah->kategori), 'putri') ? 'p' : 'l';

            // Jika jenis kelamin santri tidak sesuai grup, skip
            if ($jkSantri !== $jenisKelaminGrup) {
                continue;
            }

            $grupId = DB::table('grup_wali_asuh')->insertGetId([
                'id_wilayah' => $wilayah->id,
                'nama_grup' => 'Grup Wali ' . ($index + 1),
                'jenis_kelamin' => $jenisKelaminGrup,
                'created_by' => 1,
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $waliAsuhId = DB::table('wali_asuh')->insertGetId([
                'id_santri' => $santriId,
                'id_grup_wali_asuh' => $grupId,
                'tanggal_mulai' => now()->subYears(rand(1, 5)),
                'tanggal_berakhir' => null,
                'created_by' => 1,
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $waliPool[] = [
                'id' => $waliAsuhId,
                'grup_id' => $grupId,
                'jenis_kelamin' => $jenisKelaminGrup,
            ];
        }

        // Anak Asuh
        foreach ($anakAsuhList as $santriId) {
            $anakAsuhId = DB::table('anak_asuh')->insertGetId([
                'id_santri' => $santriId,
                'created_by' => 1,
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $jkAnak = $biodataMap[$santriId] ?? 'l';
            $waliCocok = collect($waliPool)->where('jenis_kelamin', $jkAnak)->shuffle()->first();

            if (! $waliCocok) continue;

            DB::table('kewaliasuhan')->insert([
                'id_wali_asuh' => $waliCocok['id'],
                'id_anak_asuh' => $anakAsuhId,
                'tanggal_mulai' => now()->subYears(rand(1, 5)),
                'tanggal_berakhir' => null,
                'created_by' => 1,
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}