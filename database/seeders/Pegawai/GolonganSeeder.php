<?php

namespace Database\Seeders\Pegawai;

use App\Models\Pegawai\Golongan;
use App\Models\Pegawai\KategoriGolongan;
use Database\Factories\Pegawai\GolonganFactory;
use Illuminate\Database\Seeder;

class GolonganSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $golonganList = [
            ['nama' => 'Golongan I', 'kategori' => 'Guru Pertama'],
            ['nama' => 'Golongan II', 'kategori' => 'Guru Muda'],
            ['nama' => 'Golongan III', 'kategori' => 'Guru Madya'],
            ['nama' => 'Golongan IV', 'kategori' => 'Guru Utama'],
        ];

        foreach ($golonganList as $item) {
            $kategori = KategoriGolongan::where('nama_kategori_golongan', $item['kategori'])->first();

            if ($kategori) {
                Golongan::updateOrCreate(
                    ['nama_golongan' => $item['nama']],
                    [
                        'kategori_golongan_id' => $kategori->id,
                        'created_by' => 1,
                        'status' => true,
                    ]
                );
            }
        }
    }
}
