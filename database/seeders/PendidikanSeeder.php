<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use App\Models\Pendidikan\Kelas;
use App\Models\Pendidikan\Rombel;
use App\Models\Pendidikan\Jurusan;
use App\Models\Pendidikan\Lembaga;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PendidikanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();
        $userId = 1; // ID admin default

        $data = [
            'PAUD/TK/TPA' => [
                'jurusan' => ['PAUD', 'TK', 'TPA'],
            ],
            'MI Nurul Mun\'im' => [
                'jurusan' => ['MI'],
            ],
            'MTs Nurul Jadid' => [
                'jurusan' => ['MTs'],
            ],
            'SMP Nurul Jadid' => [
                'jurusan' => ['Umum'],
            ],
            'SMA Nurul Jadid' => [
                'jurusan' => ['IPA', 'IPS', 'Bahasa'],
            ],
            'MA Nurul Jadid' => [
                'jurusan' => ['IPA Reguler', 'IPA Unggulan', 'Tahfidz', 'IPS', 'Bahasa'],
            ],
            'SMK Nurul Jadid' => [
                'jurusan' => ['TKJ', 'RPL'],
            ],
            'Universitas Nurul Jadid (UNUJA)' => [
                'jurusan' => ['Tarbiyah', 'Syariah', 'Da\'wah'],
            ],
            'Ma\'had Aly Nurul Jadid' => [
                'jurusan' => ['Fiqh', 'Ushul Fiqh'],
            ],
        ];

        foreach ($data as $lembagaName => $content) {
            $lembagaId = DB::table('lembaga')->insertGetId([
                'nama_lembaga' => $lembagaName,
                'created_by' => $userId,
                'status' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            foreach ($content['jurusan'] as $jurusanName) {
                $jurusanId = DB::table('jurusan')->insertGetId([
                    'nama_jurusan' => $jurusanName,
                    'lembaga_id' => $lembagaId,
                    'created_by' => $userId,
                    'status' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                // Tentukan kelas berdasarkan jenjang
                $kelasList = [];
                if (str_contains($lembagaName, 'SMP')) {
                    $kelasList = ['7', '8', '9'];
                } elseif (str_contains($lembagaName, 'SMA') || str_contains($lembagaName, 'MA') || str_contains($lembagaName, 'SMK')) {
                    $kelasList = ['10', '11', '12'];
                } elseif (str_contains($lembagaName, 'MI') || str_contains($lembagaName, 'MTs')) {
                    $kelasList = ['1', '2', '3', '4', '5', '6'];
                } elseif (str_contains($lembagaName, 'PAUD') || str_contains($lembagaName, 'TK') || str_contains($lembagaName, 'TPA')) {
                    $kelasList = ['A', 'B', 'C'];
                } else {
                    $kelasList = ['Angkatan I', 'Angkatan II'];
                }

                foreach ($kelasList as $kelasName) {
                    $kelasId = DB::table('kelas')->insertGetId([
                        'nama_kelas' => $kelasName,
                        'jurusan_id' => $jurusanId,
                        'created_by' => $userId,
                        'status' => true,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);

                    foreach (['putra', 'putri'] as $gender) {
                        DB::table('rombel')->insert([
                            'nama_rombel' => $kelasName . ' ' . ucfirst($gender),
                            'gender_rombel' => $gender,
                            'kelas_id' => $kelasId,
                            'created_by' => $userId,
                            'status' => true,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);
                    }
                }
            }
        }
    }
}
