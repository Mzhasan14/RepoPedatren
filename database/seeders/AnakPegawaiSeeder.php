<?php

namespace Database\Seeders;

use App\Models\Biodata;
use App\Models\Pegawai\Pegawai;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AnakPegawaiSeeder extends Seeder
{
    public function run()
    {
        // Pastikan ambil kolom 'id', bukan 'pegawai_id' (sesuaikan kalau memang berbeda)
        $pegawaiIds = Pegawai::pluck('id')->toArray();
        $bioList = Biodata::all();

        foreach ($bioList as $bio) {
            $hasAyah = rand(0, 1) === 1;
            $hasIbu = rand(0, 1) === 1;
            $assignedPegawai = [];

            if ($hasAyah && count($pegawaiIds) > 0) {
                $pegawaiAyah = $this->getUniquePegawai($pegawaiIds, $assignedPegawai);
                if ($pegawaiAyah) {
                    $assignedPegawai[] = $pegawaiAyah;
                    DB::table('anak_pegawai')->insert([
                        // gunakan $bio->id jika primary keynya id
                        'biodata_id' => $bio->id,
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
                        'biodata_id' => $bio->id,
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
