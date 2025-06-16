<?php

namespace Database\Seeders\Pegawai;

use App\Models\Pegawai\KategoriGolongan;
use Database\Factories\Pegawai\KategoriGolonganFactory;
use Illuminate\Database\Seeder;

class KategoriGolonganSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
                $kategoriList = ['Guru Pertama', 'Guru Muda', 'Guru Madya', 'Guru Utama'];

        foreach ($kategoriList as $nama) {
            KategoriGolongan::updateOrCreate(
                ['nama_kategori_golongan' => $nama],
                [
                    'created_by' => 1,
                    'status' => true,
                ]
            );
        }
    }
}
