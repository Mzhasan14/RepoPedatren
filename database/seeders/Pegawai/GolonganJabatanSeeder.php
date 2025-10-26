<?php

namespace Database\Seeders\Pegawai;

use App\Models\Pegawai\GolonganJabatan;
use Database\Factories\Pegawai\GolonganJabatanFactory;
use Illuminate\Database\Seeder;

class GolonganJabatanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $grades = [
            'Grade A1', 'Grade A2', 'Grade A3', 'Grade A4',
            'Grade B1', 'Grade B2', 'Grade B3', 'Grade B4',
            'Grade C1', 'Grade C2', 'Grade C3',
        ];

        foreach ($grades as $nama) {
            GolonganJabatan::updateOrCreate(
                ['nama_golongan_jabatan' => $nama],
                [
                    'created_by' => 1,
                    'updated_by' => null,
                    'status' => true,
                ]
            );
        }
    }
}
