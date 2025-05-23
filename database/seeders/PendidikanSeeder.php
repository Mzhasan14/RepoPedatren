<?php

namespace Database\Seeders;

use App\Models\Pendidikan\Jurusan;
use App\Models\Pendidikan\Kelas;
use App\Models\Pendidikan\Lembaga;
use App\Models\Pendidikan\Rombel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PendidikanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
                $userId = 1;

            $data = [
                'SMP Negeri 1 Jakarta' => [
                    'jurusan' => [
                        'IPA' => [
                            'kelas' => [
                                '7 IPA 1' => [
                                    'rombel' => [
                                        ['nama' => 'Rombel A', 'gender' => 'putra'],
                                        ['nama' => 'Rombel B', 'gender' => 'putri'],
                                    ],
                                ],
                                '8 IPA 1' => [
                                    'rombel' => [
                                        ['nama' => 'Rombel C', 'gender' => 'putra'],
                                        ['nama' => 'Rombel D', 'gender' => 'putri'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'SMP Negeri 2 Bandung' => [
                    'jurusan' => [
                        'IPS' => [
                            'kelas' => [
                                '8 IPS 1' => [
                                    'rombel' => [
                                        ['nama' => 'Rombel E', 'gender' => 'putra'],
                                        ['nama' => 'Rombel F', 'gender' => 'putri'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'SMA Negeri 3 Yogyakarta' => [
                    'jurusan' => [
                        'IPA' => [
                            'kelas' => [
                                '10 IPA 1' => [
                                    'rombel' => [
                                        ['nama' => 'Rombel G', 'gender' => 'putra'],
                                        ['nama' => 'Rombel H', 'gender' => 'putri'],
                                    ],
                                ],
                                '11 IPA 1' => [
                                    'rombel' => [
                                        ['nama' => 'Rombel I', 'gender' => 'putra'],
                                        ['nama' => 'Rombel J', 'gender' => 'putri'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'SMA Negeri 5 Surabaya' => [
                    'jurusan' => [
                        'Bahasa' => [
                            'kelas' => [
                                '11 Bahasa 1' => [
                                    'rombel' => [
                                        ['nama' => 'Rombel K', 'gender' => 'putra'],
                                        ['nama' => 'Rombel L', 'gender' => 'putri'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'Universitas Indonesia' => [
                    'jurusan' => [
                        'Teknik Informatika' => [
                            'kelas' => [
                                'D3.08' => [
                                    'rombel' => [
                                        ['nama' => 'Kelompok A', 'gender' => 'putra'],
                                        ['nama' => 'Kelompok B', 'gender' => 'putri'],
                                    ],
                                ],
                                'D3.09' => [
                                    'rombel' => [
                                        ['nama' => 'Kelompok C', 'gender' => 'putra'],
                                        ['nama' => 'Kelompok D', 'gender' => 'putri'],
                                    ],
                                ],
                            ],
                        ],
                        'Sistem Informasi' => [
                            'kelas' => [
                                'D3.10' => [
                                    'rombel' => [
                                        ['nama' => 'Kelompok E', 'gender' => 'putra'],
                                        ['nama' => 'Kelompok F', 'gender' => 'putri'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'Universitas Gadjah Mada' => [
                    'jurusan' => [
                        'Manajemen' => [
                            'kelas' => [
                                'D3.11' => [
                                    'rombel' => [
                                        ['nama' => 'Kelompok G', 'gender' => 'putra'],
                                        ['nama' => 'Kelompok H', 'gender' => 'putri'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ];

            foreach ($data as $namaLembaga => $jurusanList) {
                $lembaga = Lembaga::create([
                    'nama_lembaga' => $namaLembaga,
                    'created_by' => $userId,
                    'status' => true,
                ]);

                foreach ($jurusanList['jurusan'] as $namaJurusan => $kelasList) {
                    $jurusan = Jurusan::create([
                        'nama_jurusan' => $namaJurusan,
                        'lembaga_id' => $lembaga->id,
                        'created_by' => $userId,
                        'status' => true,
                    ]);

                    foreach ($kelasList['kelas'] as $namaKelas => $rombelList) {
                        $kelas = Kelas::create([
                            'nama_kelas' => $namaKelas,
                            'jurusan_id' => $jurusan->id,
                            'created_by' => $userId,
                            'status' => true,
                        ]);

                        foreach ($rombelList['rombel'] as $rombelData) {
                            Rombel::create([
                                'nama_rombel' => $rombelData['nama'],
                                'gender_rombel' => $rombelData['gender'],
                                'kelas_id' => $kelas->id,
                                'created_by' => $userId,
                                'status' => true,
                            ]);
                        }
                    }
                }
            }
    }
}
