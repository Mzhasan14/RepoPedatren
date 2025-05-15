<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Santri;
use App\Models\Pegawai\Pegawai;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AnakPegawaiSeeder extends Seeder
{
    public function run()
    {
        // Pastikan ambil kolom 'id', bukan 'pegawai_id' (sesuaikan kalau memang berbeda)
        $pegawaiIds = Pegawai::pluck('id')->toArray();
        $santriList = Santri::all();

        foreach ($santriList as $santri) {
            $hasAyah = rand(0, 1) === 1;
            $hasIbu = rand(0, 1) === 1;
            $assignedPegawai = [];

            if ($hasAyah && count($pegawaiIds) > 0) {
                $pegawaiAyah = $this->getUniquePegawai($pegawaiIds, $assignedPegawai);
                if ($pegawaiAyah) {
                    $assignedPegawai[] = $pegawaiAyah;
                    DB::table('anak_pegawai')->insert([
                        // gunakan $santri->id jika primary keynya id
                        'santri_id' => $santri->id,
                        'pegawai_id' => $pegawaiAyah,
                        'status_hubungan' => 'ayah',
                        'status' => true,
                        'created_by' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            if ($hasIbu && count($pegawaiIds) > 0) {
                $pegawaiIbu = $this->getUniquePegawai($pegawaiIds, $assignedPegawai);
                if ($pegawaiIbu) {
                    $assignedPegawai[] = $pegawaiIbu;
                    DB::table('anak_pegawai')->insert([
                        'santri_id' => $santri->id,
                        'pegawai_id' => $pegawaiIbu,
                        'status_hubungan' => 'ibu',
                        'status' => true,
                        'created_by' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    protected function getUniquePegawai(array $pegawaiIds, array $assignedPegawai)
    {
        $available = array_diff($pegawaiIds, $assignedPegawai);
        if (empty($available)) {
            return null;
        }
        return $available[array_rand($available)];
    }
}

