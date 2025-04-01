<?php

namespace Database\Seeders;

use App\Models\Santri;
use App\Models\Biodata;
use App\Models\Pelajar;
use App\Models\Keluarga;
use App\Models\OrangTua;
use Faker\Factory as Faker;
use Illuminate\Support\Str;
use App\Models\OrangTuaWali;
use App\Models\PesertaDidik;
use App\Models\Peserta_didik;
use App\Models\Status_keluarga;
use Illuminate\Database\Seeder;
use App\Models\HubunganKeluarga;
use App\Models\Kewilayahan\Blok;
use App\Models\Pendidikan\Kelas;
use App\Models\Kewilayahan\Kamar;
use App\Models\Pendidikan\Rombel;
use App\Models\Pendidikan\Jurusan;
use App\Models\Pendidikan\Lembaga;
use Illuminate\Support\Facades\DB;
use App\Models\Kewilayahan\Wilayah;
use App\Models\Kewilayahan\Domisili;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class KeluargasSeeder extends Seeder
{
    public function run()
    {
        DB::transaction(function () {
            // Insert default family relationships
            $ayahId = HubunganKeluarga::firstOrCreate(['nama_status' => 'ayah', 'create_by' => 1])->id;
            $ibuId = HubunganKeluarga::firstOrCreate(['nama_status' => 'ibu', 'create_by' => 1])->id;
            $waliId = HubunganKeluarga::firstOrCreate(['nama_status' => 'wali', 'create_by' => 1])->id;

            // Generate biodata with related entities
            Biodata::factory(500)->create()->each(function ($biodata) use ($ayahId, $ibuId, $waliId) {
                $noKk = Str::random(16);

                // Create parents
                $ayah = OrangTuaWali::create([
                    'id_biodata' => Biodata::factory()->create()->id,
                    'id_hubungan_keluarga' => $ayahId,
                    'wafat' => rand(0, 1),
                    'wali' => false,
                    'status' => true,
                    'created_by' => 1
                ]);
                
                $ibu = OrangTuaWali::create([
                    'id_biodata' => Biodata::factory()->create()->id,
                    'id_hubungan_keluarga' => $ibuId,
                    'wafat' => rand(0, 1),
                    'wali' => !$ayah->wafat,
                    'status' => true,
                    'created_by' => 1
                ]);

                if ($ayah->wafat && $ibu->wafat) {
                    OrangTuaWali::create([
                        'id_biodata' => Biodata::factory()->create()->id,
                        'id_hubungan_keluarga' => $waliId,
                        'wafat' => false,
                        'wali' => true,
                        'status' => true,
                        'created_by' => 1
                    ]);
                }

                // Assign family
                Keluarga::create([
                    'no_kk' => $noKk,
                    'id_biodata' => $biodata->id,
                    'status' => true,
                    'created_by' => 1
                ]);
                
                Keluarga::create([
                    'no_kk' => $noKk,
                    'id_biodata' => $ayah->id_biodata,
                    'status' => true,
                    'created_by' => 1
                ]);
                
                Keluarga::create([
                    'no_kk' => $noKk,
                    'id_biodata' => $ibu->id_biodata,
                    'status' => true,
                    'created_by' => 1
                ]);

                // Assign Peserta Didik
                $pesertaDidik = PesertaDidik::create([
                    'id_biodata' => $biodata->id,
                    'status' => true,
                    'created_by' => 1
                ]);

                // Assign Pelajar or Santri
                if (rand(0, 1)) {
                    Pelajar::create([
                        'id_peserta_didik' => $pesertaDidik->id,
                        'no_induk' => Str::random(10),
                        'angkatan_pelajar' => now()->year,
                        'tanggal_masuk_pelajar' => now(),
                        'status_pelajar' => 'aktif',
                        'created_by' => 1
                    ]);
                }
                
                if (rand(0, 1)) {
                    Santri::create([
                        'id_peserta_didik' => $pesertaDidik->id,
                        'nis' => Str::random(11),
                        'angkatan_santri' => now()->year,
                        'tanggal_masuk_santri' => now(),
                        'status_santri' => 'aktif',
                        'created_by' => 1
                    ]);
                }
            });
        });
    }
}
