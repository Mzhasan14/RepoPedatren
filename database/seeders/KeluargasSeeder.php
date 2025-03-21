<?php

namespace Database\Seeders;

use App\Models\Santri;
use App\Models\Biodata;
use App\Models\Pelajar;
use App\Models\Keluarga;
use App\Models\OrangTua;
use Faker\Factory as Faker;
use App\Models\Peserta_didik;
use App\Models\Status_keluarga;
use Illuminate\Database\Seeder;
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
            $faker = Faker::create('id_ID');
            $createdBy = 1; // ID user pembuat

            // Ambil status keluarga
            $statusAyah = Status_keluarga::firstOrCreate(['nama_status' => 'ayah', 'created_by' => $createdBy]);
            $statusIbu = Status_keluarga::firstOrCreate(['nama_status' => 'ibu', 'created_by' => $createdBy]);
            $statusAnak = Status_keluarga::firstOrCreate(['nama_status' => 'anak', 'created_by' => $createdBy]);
            $statusWali = Status_keluarga::firstOrCreate(['nama_status' => 'wali', 'created_by' => $createdBy]);

            // Ambil referensi data wajib untuk santri dan pelajar
            $wilayah = Wilayah::pluck('id')->toArray();
            $blok = Blok::pluck('id')->toArray();
            $kamar = Kamar::pluck('id')->toArray();
            $domisili = Domisili::pluck('id')->toArray();
            $lembaga = Lembaga::pluck('id')->toArray();
            $jurusan = Jurusan::pluck('id')->toArray();
            $kelas = Kelas::pluck('id')->toArray();
            $rombel = Rombel::pluck('id')->toArray();

            for ($i = 0; $i < 50; $i++) {
                // Buat No KK
                $no_kk = $faker->numerify('###############');

                // Buat Ayah dan Ibu
                $ayah = Biodata::factory()->create();
                $ibu = Biodata::factory()->create();

                // Tentukan apakah ayah atau ibu sudah wafat
                $ayahWafat = $faker->boolean(20); // 20% kemungkinan wafat
                $ibuWafat = $faker->boolean(15);  // 15% kemungkinan wafat

                $status = true;

                // Tambahkan data orang tua
                OrangTua::create([
                    'id_biodata' => $ayah->id,
                    'pekerjaan' => $faker->jobTitle,
                    'penghasilan' => $faker->numberBetween(1000000, 10000000),
                    'wafat' => $ayahWafat,
                    'created_by' => $createdBy,
                    'status' => $status
                ]);

                OrangTua::create([
                    'id_biodata' => $ibu->id,
                    'pekerjaan' => $faker->jobTitle,
                    'penghasilan' => $faker->numberBetween(1000000, 10000000),
                    'wafat' => $ibuWafat,
                    'created_by' => $createdBy,
                    'status' => $status
                ]);

                // Tentukan wali
                $wali_id = null;
                if (!$ayahWafat) {
                    $wali_id = $ayah->id; // Jika ayah hidup, wali adalah ayah
                } elseif (!$ibuWafat) {
                    $wali_id = $ibu->id; // Jika ayah wafat dan ibu hidup, wali adalah ibu
                }

                // Masukkan ayah dan ibu ke dalam tabel keluarga
                Keluarga::create([
                    'no_kk' => $no_kk,
                    'id_biodata' => $ayah->id,
                    'id_status_keluarga' => $statusAyah->id,
                    'wali' => ($wali_id === $ayah->id),
                    'created_by' => $createdBy
                ]);

                Keluarga::create([
                    'no_kk' => $no_kk,
                    'id_biodata' => $ibu->id,
                    'id_status_keluarga' => $statusIbu->id,
                    'wali' => ($wali_id === $ibu->id),
                    'created_by' => $createdBy
                ]);

                // Buat anak (1-3 anak per keluarga)
                $jumlahAnak = $faker->numberBetween(1, 3);
                for ($j = 0; $j < $jumlahAnak; $j++) {
                    $anak = Biodata::factory()->create();

                    // Masukkan anak ke dalam keluarga
                    Keluarga::create([
                        'no_kk' => $no_kk,
                        'id_biodata' => $anak->id,
                        'id_status_keluarga' => $statusAnak->id,
                        'wali' => false,
                        'created_by' => $createdBy
                    ]);

                    // Masukkan anak sebagai peserta didik
                    $pesertaDidik = Peserta_didik::create([
                        'id_biodata' => $anak->id,
                        'status' => true,
                        'created_by' => $createdBy
                    ]);

                    // Tentukan apakah anak ini menjadi santri, pelajar, atau keduanya
                    $isSantri = $faker->boolean(60); // 60% kemungkinan menjadi santri
                    $isPelajar = $faker->boolean(70); // 70% kemungkinan menjadi pelajar

                    if ($isSantri && !empty($wilayah) && !empty($blok) && !empty($kamar) && !empty($domisili)) {
                        Santri::create([
                            'id_peserta_didik' => $pesertaDidik->id,
                            'id_wilayah' => $faker->randomElement($wilayah),
                            'id_blok' => $faker->randomElement($blok),
                            'id_kamar' => $faker->randomElement($kamar),
                            'id_domisili' => $faker->randomElement($domisili),
                            'nis' => $faker->unique()->numerify('###########'),
                            'angkatan' => fake()->year(),
                            'tanggal_masuk' => fake()->date(),
                            'tanggal_keluar' => null,
                            'created_by' => 1,
                            'updated_by' => null,
                            'status' => fake()->randomElement([
                                'aktif'
                            ]),
                            'created_by' => $createdBy
                        ]);
                    }

                    if ($isPelajar && !empty($lembaga) && !empty($jurusan) && !empty($kelas) && !empty($rombel)) {
                        Pelajar::create([
                            'id_peserta_didik' => $pesertaDidik->id,
                            'id_lembaga' => $faker->randomElement($lembaga),
                            'id_jurusan' => $faker->randomElement($jurusan),
                            'id_kelas' => $faker->randomElement($kelas),
                            'id_rombel' => $faker->randomElement($rombel),
                            'no_induk' => $faker->unique()->numerify('###########'),
                            'angkatan' => fake()->year(),
                            'tanggal_masuk' => fake()->date(),
                            'tanggal_keluar' => null,
                            'created_by' => 1,
                            'updated_by' => null,
                            'status' => fake()->randomElement([
                                'aktif'
                            ]),
                            'created_by' => $createdBy
                        ]);
                    }
                }

                // Jika ayah & ibu wafat, maka buat wali dari luar keluarga
                if ($ayahWafat && $ibuWafat) {
                    $wali = Biodata::factory()->create();

                    Keluarga::create([
                        'no_kk' => $no_kk,
                        'id_biodata' => $wali->id,
                        'id_status_keluarga' => $statusWali->id,
                        'wali' => true,
                        'created_by' => $createdBy
                    ]);
                }
            }
        });
    }
}
