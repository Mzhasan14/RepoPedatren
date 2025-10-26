<?php

namespace Database\Seeders;

use App\Models\Alamat\Kabupaten;
use App\Models\Alamat\Kecamatan;
use App\Models\Alamat\Negara;
use App\Models\Alamat\Provinsi;
use Illuminate\Database\Seeder;

class AlamatSeeder extends Seeder
{
    public function run(): void
    {
        // === Buat Data Negara ===
        $negaraList = [
            'Indonesia',
            'Malaysia',
            'Singapura',
            'Thailand',
            'Filipina',
            'Vietnam',
            'Kamboja',
        ];

        $negaraMap = [];
        foreach ($negaraList as $nama) {
            $negaraMap[$nama] = Negara::create([
                'nama_negara' => $nama,
                'status' => true, // Tambah status = true
            ]);
        }

        // === Indonesia (10 Provinsi) ===
        $indonesia = $negaraMap['Indonesia'];
        $indonesiaData = [
            'Aceh' => [
                'Banda Aceh' => ['Meuraxa', 'Kuta Alam', 'Ulee Kareng'],
                'Lhokseumawe' => ['Muara Dua', 'Muara Satu'],
            ],
            'Sumatera Utara' => [
                'Medan' => ['Medan Petisah', 'Medan Maimun'],
                'Binjai' => ['Binjai Barat', 'Binjai Timur'],
            ],
            'Sumatera Barat' => [
                'Padang' => ['Padang Barat', 'Padang Timur'],
                'Bukittinggi' => ['MKS', 'Guguk Panjang'],
            ],
            'Riau' => [
                'Pekanbaru' => ['Sukajadi', 'Marpoyan Damai'],
                'Dumai' => ['Dumai Kota', 'Bukit Kapur'],
            ],
            'Jawa Barat' => [
                'Bandung' => ['Coblong', 'Sukasari', 'Cibiru'],
                'Bogor' => ['Bogor Utara', 'Bogor Selatan'],
            ],
            'Jawa Tengah' => [
                'Semarang' => ['Banyumanik', 'Tembalang'],
                'Solo' => ['Banjarsari', 'Laweyan'],
            ],
            'Jawa Timur' => [
                'Surabaya' => ['Wonokromo', 'Tegalsari', 'Rungkut'],
                'Malang' => ['Klojen', 'Sukun'],
            ],
            'Bali' => [
                'Denpasar' => ['Denpasar Selatan', 'Denpasar Barat'],
                'Gianyar' => ['Ubud', 'Tegallalang'],
            ],
            'Kalimantan Timur' => [
                'Balikpapan' => ['Balikpapan Selatan', 'Balikpapan Utara'],
                'Samarinda' => ['Samarinda Ilir', 'Samarinda Ulu'],
            ],
            'Sulawesi Selatan' => [
                'Makassar' => ['Panakkukang', 'Rappocini'],
                'Parepare' => ['Soreang', 'Ujung'],
            ],
        ];

        foreach ($indonesiaData as $provinsiName => $kabupatenList) {
            $provinsi = Provinsi::create([
                'nama_provinsi' => $provinsiName,
                'negara_id' => $indonesia->id,
                'status' => true, // Tambah status = true
            ]);

            foreach ($kabupatenList as $kabupatenName => $kecamatanList) {
                $kabupaten = Kabupaten::create([
                    'nama_kabupaten' => $kabupatenName,
                    'provinsi_id' => $provinsi->id,
                    'status' => true, // Tambah status = true
                ]);

                foreach ($kecamatanList as $kecamatanName) {
                    Kecamatan::create([
                        'nama_kecamatan' => $kecamatanName,
                        'kabupaten_id' => $kabupaten->id,
                        'status' => true, // Tambah status = true
                    ]);
                }
            }
        }

        // === Negara ASEAN Lain ===
        $wilayahLain = [
            'Malaysia' => [
                'Selangor' => [
                    'Shah Alam' => ['Seksyen 7', 'Seksyen 13'],
                    'Petaling Jaya' => ['Damansara', 'Kelana Jaya'],
                ],
            ],
            'Singapura' => [
                'Central Region' => [
                    'Singapore' => ['Orchard', 'Marina Bay'],
                ],
            ],
            'Thailand' => [
                'Bangkok' => [
                    'Bangkok' => ['Pathum Wan', 'Chatuchak'],
                ],
            ],
            'Filipina' => [
                'Metro Manila' => [
                    'Quezon City' => ['Diliman', 'Commonwealth'],
                ],
            ],
            'Vietnam' => [
                'Hanoi' => [
                    'Hoan Kiem' => ['Old Quarter', 'French Quarter'],
                ],
            ],
            'Kamboja' => [
                'Phnom Penh' => [
                    'Chamkar Mon' => ['Toul Tom Poung', 'Boeung Keng Kang'],
                ],
            ],
        ];

        foreach ($wilayahLain as $negaraName => $provList) {
            $negara = $negaraMap[$negaraName];
            foreach ($provList as $provName => $kabList) {
                $prov = Provinsi::create([
                    'nama_provinsi' => $provName,
                    'negara_id' => $negara->id,
                    'status' => true, // Tambah status = true
                ]);

                foreach ($kabList as $kabName => $kecList) {
                    $kab = Kabupaten::create([
                        'nama_kabupaten' => $kabName,
                        'provinsi_id' => $prov->id,
                        'status' => true, // Tambah status = true
                    ]);

                    foreach ($kecList as $kecName) {
                        Kecamatan::create([
                            'nama_kecamatan' => $kecName,
                            'kabupaten_id' => $kab->id,
                            'status' => true, // Tambah status = true
                        ]);
                    }
                }
            }
        }
    }
}
