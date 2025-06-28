<?php

namespace Database\Seeders\Pegawai;

use App\Models\Pegawai\JadwalPelajaran;
use App\Models\Pegawai\JamPelajaran;
use App\Models\Pegawai\MataPelajaran;
use App\Models\Pendidikan\Jurusan;
use App\Models\Pendidikan\Kelas;
use App\Models\Pendidikan\Lembaga;
use App\Models\Pendidikan\Rombel;
use App\Models\Semester;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class JadwalPelajaranSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $hariList     = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
        $kelasList    = Kelas::pluck('id')->toArray();
        $rombelList   = Rombel::pluck('id')->toArray();
        $jurusanList  = Jurusan::pluck('id')->toArray();
        $lembagaList  = Lembaga::pluck('id')->toArray();
        $jamList      = JamPelajaran::pluck('id')->toArray();
        $mapelList    = MataPelajaran::pluck('id')->toArray();
        $semesterId   = Semester::value('id') ?? 1;
        $createdBy    = 1;
        $now          = now();

        $counter = 0;
        $max = 5; // Batasi hanya X kombinasi lembaga-jurusan-kelas

        foreach ($lembagaList as $lembagaId) {
            foreach ($jurusanList as $jurusanId) {
                foreach ($kelasList as $kelasId) {

                    if (++$counter > $max) break 3;

                    // ❗ Rombel hanya sekali dipilih untuk kombinasi ini
                    $rombelId = fake()->optional()->randomElement($rombelList);

                    // ❗ Untuk kombinasi ini, isi semua hari dan jam
                    foreach ($hariList as $hari) {
                        foreach ($jamList as $jamId) {
                            JadwalPelajaran::updateOrInsert(
                                [
                                    'hari'             => $hari,
                                    'kelas_id'         => $kelasId,
                                    'jam_pelajaran_id' => $jamId,
                                ],
                                [
                                    'semester_id'        => $semesterId,
                                    'rombel_id'          => $rombelId,
                                    'mata_pelajaran_id'  => fake()->randomElement($mapelList),
                                    'jurusan_id'         => $jurusanId,
                                    'lembaga_id'         => $lembagaId,
                                    'created_by'         => $createdBy,
                                    'created_at'         => $now,
                                    'updated_at'         => $now,
                                ]
                            );
                        }
                    }

                    // ❗ Setelah full 1 minggu, lanjut ke kombinasi berikut
                }
            }
        }
    }
}
