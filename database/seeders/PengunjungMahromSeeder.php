<?php

namespace Database\Seeders;

use App\Models\Santri;
use App\Models\Biodata;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use App\Models\HubunganKeluarga;
use App\Models\PengunjungMahrom;

class PengunjungMahromSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        $santriList = Santri::pluck('id');
        $hubunganList = HubunganKeluarga::pluck('id');

        foreach ($santriList as $santriId) {
            $usedHubungan = $hubunganList->shuffle();

            // Batasi misalnya 1–3 hubungan saja per santri
            $jumlahHubungan = rand(1, min(3, $usedHubungan->count()));

            foreach ($usedHubungan->take($jumlahHubungan) as $hubunganId) {
                // Buat biodata baru via factory
                $biodata = Biodata::factory()->create();

                PengunjungMahrom::create([
                    'biodata_id' => $biodata->id,
                    'santri_id' => $santriId,
                    'hubungan_id' => $hubunganId,
                    'jumlah_rombongan' => $faker->numberBetween(1, 5),
                    'tanggal_kunjungan' => $faker->dateTimeBetween('-1 month', 'now'),
                    'status' => 'selesai',
                    'created_by' => 1,
                    'updated_by' => null,
                    'deleted_by' => null,
                ]);
            }
        }
    }
}
// {
//     /**
//      * Run the database seeds.
//      */
//     public function run(): void
//     {
//         $faker = Faker::create();

//         $santriList = Santri::pluck('id');
//         $hubunganList = HubunganKeluarga::pluck('id');

//         foreach ($santriList as $santriId) {
//             $usedHubungan = $hubunganList->shuffle();

//             // Batasi misalnya 1–3 hubungan saja per santri (opsional)
//             $jumlahHubungan = rand(1, min(3, $usedHubungan->count()));

//             foreach ($usedHubungan->take($jumlahHubungan) as $hubunganId) {
//                 PengunjungMahrom::create([
//                     'santri_id' => $santriId,
//                     'nama_pengunjung' => $faker->name,
//                     'hubungan_id' => $hubunganId,
//                     'jumlah_rombongan' => $faker->numberBetween(1, 5),
//                     'tanggal_kunjungan' => $faker->dateTimeBetween('-1 month', 'now'),
//                     'status'    => 'selesai',
//                     'created_by' => 1,
//                     'updated_by' => null,
//                     'deleted_by' => null,
//                 ]);
//             }
//         }
//     }
//     // public function run(): void
//     // {
//     //     PengunjungMahrom::factory()->count(200)->create();
//     // }
// }
