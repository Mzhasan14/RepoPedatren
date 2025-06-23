<?php

namespace Database\Seeders\Pegawai;

use App\Models\Pegawai\JadwalPelajaran;
use App\Models\Pegawai\JamPelajaran;
use App\Models\Pegawai\MataPelajaran;
use App\Models\Pendidikan\Jurusan;
use App\Models\Pendidikan\Kelas;
use App\Models\Pendidikan\Lembaga;
use App\Models\Pendidikan\Rombel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class JadwalPelajaranSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $kelasList = Kelas::pluck('id')->toArray();
        $jamList = JamPelajaran::pluck('id')->toArray();
        $mapelList = MataPelajaran::pluck('id')->toArray();
        $rombelList = Rombel::pluck('id')->toArray();
        $jurusanList = Jurusan::pluck('id')->toArray();
        $lembagaList = Lembaga::pluck('id')->toArray();

        $semesterId = 1; // bisa diacak atau di-loop juga
        $createdBy = 1;

        foreach ($hariList as $hari) {
            foreach ($kelasList as $kelasId) {
                foreach ($jamList as $jamId) {
                    JadwalPelajaran::create([
                        'hari' => $hari,
                        'semester_id' => $semesterId,
                        'kelas_id' => $kelasId,
                        'rombel_id' => fake()->optional()->randomElement($rombelList),
                        'mata_pelajaran_id' => fake()->randomElement($mapelList),
                        'jam_pelajaran_id' => $jamId,
                        'jurusan_id' => fake()->randomElement($jurusanList), // atau optional() jika nullable
                        'lembaga_id' => fake()->randomElement($lembagaList), // <- tanpa optional()
                        'created_by' => $createdBy,
                    ]);
                }
            }
        }
    }
}
