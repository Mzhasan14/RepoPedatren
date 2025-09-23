<?php

namespace Database\Seeders\Pegawai;

use App\Models\Pegawai\Pegawai;
use App\Models\Pegawai\WaliKelas;
use App\Models\Pendidikan;
use Illuminate\Database\Seeder;

class WaliKelasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pegawaiList = Pegawai::whereDoesntHave('wali_kelas')
            ->inRandomOrder()
            ->limit(50)
            ->get();

        foreach ($pegawaiList as $pegawai) {
            // ambil salah satu pendidikan full row
            $pendidikan = Pendidikan::inRandomOrder()->first();

            if ($pendidikan) {
                WaliKelas::factory()->create([
                    'pegawai_id'   => $pegawai->id,
                    'status_aktif' => 'aktif',
                    'lembaga_id'   => $pendidikan->lembaga_id,
                    'jurusan_id'   => $pendidikan->jurusan_id,
                    'kelas_id'     => $pendidikan->kelas_id,
                    'rombel_id'    => $pendidikan->rombel_id,
                ]);
            }
        }
    }
}
